#!/usr/bin/env python3
import json
import os
import re
import shutil
import sys
import time
from http.server import BaseHTTPRequestHandler, HTTPServer
from urllib.parse import parse_qs, unquote, urlparse

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from livecounts_follower_search import normalize_results, parse_visible_results


HOST = "127.0.0.1"
PORT = int(os.getenv("LIVECOUNTS_SEARCH_PORT", "8765"))
PAGE_URL = "https://livecounts.io/tiktok-live-follower-counter"


class SearchBrowser:
    def __init__(self):
        from playwright.sync_api import sync_playwright

        self.sync_playwright = sync_playwright
        self.pw = None
        self.browser = None
        self.context = None
        self.page = None
        self.captured = {
            "query": None,
            "payload": None,
            "url": None,
            "status": None,
        }
        self._start()

    def _start(self):
        chromium = (
            os.getenv("CHROMIUM_PATH")
            or shutil.which("chromium")
            or shutil.which("chromium-browser")
            or shutil.which("google-chrome")
        )

        self.pw = self.sync_playwright().start()

        launch_args = {
            "headless": True,
            "args": [
                "--no-sandbox",
                "--disable-dev-shm-usage",
                "--disable-gpu",
                "--disable-background-networking",
                "--disable-default-apps",
                "--disable-extensions",
                "--mute-audio",
            ],
        }

        if chromium:
            launch_args["executable_path"] = chromium

        self.browser = self.pw.chromium.launch(**launch_args)
        self.context = self.browser.new_context(
            viewport={"width": 1100, "height": 900},
            locale="en-US",
            user_agent=(
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                "AppleWebKit/537.36 (KHTML, like Gecko) "
                "Chrome/141.0.0.0 Safari/537.36"
            ),
        )

        self.page = self.context.new_page()

        def block_heavy_assets(route):
            try:
                if route.request.resource_type in ("image", "media", "font"):
                    route.abort()
                else:
                    route.continue_()
            except Exception:
                try:
                    route.continue_()
                except Exception:
                    pass

        self.page.route("**/*", block_heavy_assets)
        self.page.on("response", self._on_response)
        self._load_page()

    def _load_page(self):
        try:
            self.page.goto(
                PAGE_URL,
                wait_until="domcontentloaded",
                timeout=12000,
            )
        except Exception:
            pass

        self._find_input(timeout_seconds=10)

    def _find_input(self, timeout_seconds=4):
        selectors = [
            'input[placeholder="Search Accounts"]',
            'input[placeholder*="Search Accounts"]',
            'input[placeholder*="Search accounts"]',
            'input[type="search"]',
            'input[type="text"]',
        ]

        deadline = time.time() + timeout_seconds

        while time.time() < deadline:
            for selector in selectors:
                try:
                    locator = self.page.locator(selector).first
                    if locator.count() and locator.is_visible(timeout=250):
                        return locator
                except Exception:
                    pass

            self.page.wait_for_timeout(80)

        return None

    def _on_response(self, resp):
        try:
            url = resp.url
            parsed = urlparse(url)

            if "/user/search/" not in parsed.path.lower():
                return

            requested = unquote(parsed.path.rsplit("/", 1)[-1]).strip().lower()
            active = str(self.captured.get("query") or "").strip().lower()

            if active and requested != active:
                return

            self.captured["url"] = url
            self.captured["status"] = resp.status

            if not (200 <= resp.status < 300):
                return

            payload = resp.json()

            if normalize_results(payload):
                self.captured["payload"] = payload
        except Exception:
            pass

    def _reset_capture(self, query):
        self.captured = {
            "query": query,
            "payload": None,
            "url": None,
            "status": None,
        }

    def _matches_query(self, item, query):
        q = query.lower()
        username = str(item.get("username") or "").lower()
        display_name = str(item.get("display_name") or "").lower()
        return q in username or q in display_name

    def search(self, query):
        query = query.strip().lstrip("@")

        if len(query) < 2 or len(query) > 40:
            return {
                "success": True,
                "query": query,
                "results": [],
                "source": "livecounts-warm-browser",
            }

        if re.search(r"[\r\n\x00]", query):
            return {
                "success": False,
                "query": query,
                "results": [],
                "message": "ongeldige zoekterm",
            }

        if self.page is None or self.page.is_closed():
            self.restart()

        search_input = self._find_input(timeout_seconds=1.5)

        if search_input is None:
            self._load_page()
            search_input = self._find_input(timeout_seconds=2)

        if search_input is None:
            raise RuntimeError("Livecounts zoekveld kon niet worden gevonden.")

        self._reset_capture(query)

        try:
            search_input.click(timeout=700)
        except Exception:
            pass

        try:
            search_input.press("Control+A")
            search_input.press("Backspace")
        except Exception:
            search_input.fill("")

        # Warm browser means we can type quickly; no Chromium startup/page load
        # is needed for each autocomplete request.
        try:
            search_input.press_sequentially(query, delay=22)
        except Exception:
            search_input.type(query, delay=22)

        deadline = time.time() + 2.8
        results = []
        source = "livecounts-warm-browser-network"

        while time.time() < deadline:
            payload = self.captured.get("payload")
            results = normalize_results(payload)

            if results:
                break

            visible = parse_visible_results(self.page, query)
            if visible:
                results = visible
                source = "livecounts-warm-browser-rendered"
                break

            self.page.wait_for_timeout(60)

        if not results:
            try:
                search_input.press("Enter")
            except Exception:
                pass

            deadline = time.time() + 1.5
            while time.time() < deadline:
                payload = self.captured.get("payload")
                results = normalize_results(payload)

                if results:
                    break

                visible = parse_visible_results(self.page, query)
                if visible:
                    results = visible
                    source = "livecounts-warm-browser-rendered"
                    break

                self.page.wait_for_timeout(60)

        filtered = [
            item for item in results
            if isinstance(item, dict) and self._matches_query(item, query)
        ]

        if filtered:
            results = filtered

        return {
            "success": True,
            "query": query,
            "results": results[:8],
            "source": source,
            "debug": {
                "search_url": self.captured.get("url"),
                "search_status": self.captured.get("status"),
                "warm_browser": True,
            },
        }

    def restart(self):
        try:
            if self.context:
                self.context.close()
        except Exception:
            pass

        try:
            if self.browser:
                self.browser.close()
        except Exception:
            pass

        try:
            if self.pw:
                self.pw.stop()
        except Exception:
            pass

        self.pw = None
        self.browser = None
        self.context = None
        self.page = None
        self._start()


BROWSER = None


def browser():
    global BROWSER

    if BROWSER is None:
        BROWSER = SearchBrowser()

    return BROWSER


class Handler(BaseHTTPRequestHandler):
    def send_json(self, payload, status=200):
        raw = json.dumps(payload, ensure_ascii=False).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Cache-Control", "no-store")
        self.send_header("Content-Length", str(len(raw)))
        self.end_headers()
        self.wfile.write(raw)

    def do_GET(self):
        parsed = urlparse(self.path)

        if parsed.path == "/health":
            try:
                browser()
                self.send_json({"success": True, "ready": True})
            except Exception as exc:
                self.send_json(
                    {"success": False, "ready": False, "message": str(exc)},
                    503,
                )
            return

        if parsed.path != "/search":
            self.send_json({"success": False, "message": "not found"}, 404)
            return

        query = parse_qs(parsed.query).get("q", [""])[0]

        try:
            payload = browser().search(query)
            self.send_json(payload)
        except Exception as exc:
            # Recreate the warm browser once after a renderer/browser failure.
            try:
                browser().restart()
                payload = browser().search(query)
                self.send_json(payload)
            except Exception as retry_exc:
                self.send_json({
                    "success": False,
                    "query": query,
                    "results": [],
                    "message": str(retry_exc or exc),
                }, 500)

    def log_message(self, fmt, *args):
        return


def main():
    # Binding to loopback keeps this helper private to the Laravel container.
    server = HTTPServer((HOST, PORT), Handler)

    # Warm Chromium + the public Livecounts page before serving autocomplete.
    browser()

    server.serve_forever()


if __name__ == "__main__":
    main()
