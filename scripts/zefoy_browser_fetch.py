#!/usr/bin/env python3
import json
import os
import re
import shutil
import sys
import time


ZEOFY_URL = "https://zefoy.com"
SERVICE_LABELS = {
    "hearts": ["Hearts"],
    "comments": ["Comments Hearts", "Comment Hearts"],
    "favorites": ["Favorites"],
}


def emit(payload, code=0):
    print(json.dumps(payload, ensure_ascii=False))
    raise SystemExit(code)


def clean_text(value):
    return re.sub(r"\s+", " ", str(value or "")).strip()


def visible_text(page):
    try:
        return clean_text(page.locator("body").inner_text(timeout=1200))
    except Exception:
        return ""


def looks_blocked(text):
    lower = text.lower()
    markers = [
        "captcha",
        "verify you are human",
        "verification",
        "cloudflare",
        "access denied",
        "forbidden",
        "checking your browser",
    ]
    return any(marker in lower for marker in markers)


def find_service(page, labels):
    for label in labels:
        try:
            node = page.get_by_text(label, exact=True).first
            if node.count() and node.is_visible(timeout=500):
                return node, label
        except Exception:
            pass
    return None, None


def nearest_clickable(node):
    for selector in ("a", "button", "[role=button]"):
        try:
            parent = node.locator(f"xpath=ancestor-or-self::{selector}[1]").first
            if parent.count():
                return parent
        except Exception:
            pass

    try:
        parent = node.locator("xpath=..").first
        if parent.count():
            return parent
    except Exception:
        pass

    return node


def parse_home_services(page):
    text = visible_text(page)
    services = []

    candidates = [
        ("followers", "Followers"),
        ("hearts", "Hearts"),
        ("comments", "Comments Hearts"),
        ("views", "Views"),
        ("shares", "Shares"),
        ("favorites", "Favorites"),
    ]

    for key, label in candidates:
        node, matched = find_service(page, [label, label.replace("Comments", "Comment")])
        if node is None:
            continue

        state = "unknown"
        status_text = None

        try:
            container = node.locator(
                "xpath=ancestor::*[self::div or self::section or self::article][1]"
            ).first
            container_text = clean_text(container.inner_text(timeout=700))
        except Exception:
            container_text = ""

        combined = container_text or text

        if re.search(r"soon\s+will\s+be\s+update", combined, re.I):
            state = "unavailable"
            status_text = "soon will be update"
        else:
            updated = re.search(
                r"(\d+\s+(?:minute|minutes|hour|hours|day|days|month|months)\s+ago\s+updated)",
                combined,
                re.I,
            )
            if updated:
                state = "available"
                status_text = updated.group(1)
            elif key in ("hearts", "comments", "favorites"):
                state = "available"

        services.append({
            "key": key,
            "label": matched or label,
            "state": state,
            "status": status_text,
        })

    return services


def parse_cooldown(text):
    patterns = [
        r"Please\s+wait\s+(\d+)\s+minute\(s\)\s+(\d+)\s+second\(s\)\s+before\s+trying\s+again",
        r"Please\s+wait\s+(\d+)\s+minute(?:s)?\s+(\d+)\s+second(?:s)?\s+before\s+trying\s+again",
    ]
    for pattern in patterns:
        match = re.search(pattern, text, re.I)
        if match:
            minutes = int(match.group(1))
            seconds = int(match.group(2))
            return {
                "minutes": minutes,
                "seconds": seconds,
                "total_seconds": minutes * 60 + seconds,
                "message": clean_text(match.group(0)),
            }
    return None


def parse_limits(page):
    values = []
    try:
        selects = page.locator("select")
        for i in range(selects.count()):
            select = selects.nth(i)
            for option in select.locator("option").all():
                try:
                    text = clean_text(option.inner_text())
                except Exception:
                    continue
                match = re.fullmatch(r"\d+", text)
                if match:
                    number = int(text)
                    if number not in values:
                        values.append(number)
    except Exception:
        pass

    return values[:12]


def parse_video_card(page, text):
    username = None
    caption = None
    age = None
    hearts = None

    match = re.search(r"@([A-Za-z0-9._]{1,24})", text)
    if match:
        username = match.group(1)

    age_match = re.search(
        r"(\d+\s+(?:minute|minutes|hour|hours|day|days|week|weeks)\s+ago)",
        text,
        re.I,
    )
    if age_match:
        age = clean_text(age_match.group(1))

    heart_match = re.search(r"(\d[\d,.]*)\s*[❤♥]", text)
    if heart_match:
        hearts = heart_match.group(1)

    if username:
        try:
            user_node = page.get_by_text("@" + username, exact=True).first
            card = user_node.locator(
                "xpath=ancestor::*[self::div or self::section or self::article][1]"
            ).first
            card_text = clean_text(card.inner_text(timeout=700))
            parts = [p.strip() for p in card_text.split(" ") if p.strip()]
            # Keep only a small readable preview; Zefoy may include controls in the same card.
            caption = " ".join(parts[:18]) if parts else None
        except Exception:
            pass

    return {
        "username": username,
        "caption": caption,
        "age": age,
        "hearts": hearts,
    }


def click_service(page, labels):
    node, matched = find_service(page, labels)
    if node is None:
        return False, None

    try:
        container = node.locator(
            "xpath=ancestor::*[self::div or self::section or self::article][1]"
        ).first
        container_text = clean_text(container.inner_text(timeout=700))
        if re.search(r"soon\s+will\s+be\s+update", container_text, re.I):
            return False, "unavailable"
    except Exception:
        pass

    clickable = nearest_clickable(node)

    try:
        clickable.click(timeout=1600)
        return True, matched
    except Exception:
        try:
            node.click(timeout=1600)
            return True, matched
        except Exception:
            return False, None


def wait_for_search_input(page):
    selectors = [
        'input[placeholder*="Enter Video URL" i]',
        'input[type="url"]',
        'input[type="text"]',
    ]

    deadline = time.time() + 5.0
    while time.time() < deadline:
        for selector in selectors:
            try:
                locator = page.locator(selector).first
                if locator.count() and locator.is_visible(timeout=350):
                    return locator
            except Exception:
                pass
        page.wait_for_timeout(80)

    return None


def run(mode, service=None, video_url=None):
    try:
        from playwright.sync_api import sync_playwright
    except Exception as exc:
        emit({
            "success": False,
            "state": "runtime_error",
            "message": f"Playwright import failed: {exc}",
        }, 3)

    chromium = (
        os.getenv("CHROMIUM_PATH")
        or shutil.which("chromium")
        or shutil.which("chromium-browser")
        or shutil.which("google-chrome")
    )

    started = time.time()

    with sync_playwright() as p:
        launch_args = {
            "headless": True,
            "args": [
                "--no-sandbox",
                "--disable-dev-shm-usage",
                "--disable-gpu",
                "--disable-extensions",
                "--mute-audio",
            ],
        }

        if chromium:
            launch_args["executable_path"] = chromium

        browser = p.chromium.launch(**launch_args)
        context = browser.new_context(
            viewport={"width": 980, "height": 1100},
            locale="en-US",
            user_agent=(
                "Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) "
                "AppleWebKit/605.1.15 (KHTML, like Gecko) "
                "Version/18.6 Mobile/15E148 Safari/604.1"
            ),
        )
        page = context.new_page()

        # Keep JavaScript/XHR/forms. Only skip heavy binary resources.
        def route_handler(route):
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

        page.route("**/*", route_handler)

        response_status = None

        def on_response(resp):
            nonlocal response_status
            try:
                if resp.url.rstrip("/") == ZEOFY_URL:
                    response_status = resp.status
            except Exception:
                pass

        page.on("response", on_response)

        try:
            page.goto(ZEOFY_URL, wait_until="domcontentloaded", timeout=9000)
        except Exception:
            pass

        page.wait_for_timeout(700)
        home_text = visible_text(page)

        if response_status == 403 or looks_blocked(home_text):
            browser.close()
            return {
                "success": False,
                "state": "blocked",
                "message": "Zefoy requires browser verification or blocked this server session.",
                "http_status": response_status,
                "elapsed_ms": int((time.time() - started) * 1000),
            }

        if mode == "services":
            services = parse_home_services(page)
            browser.close()
            return {
                "success": True,
                "state": "services",
                "services": services,
                "elapsed_ms": int((time.time() - started) * 1000),
            }

        labels = SERVICE_LABELS.get(service or "")
        if not labels:
            browser.close()
            return {
                "success": False,
                "state": "invalid_service",
                "message": "Unsupported Zefoy service.",
            }

        clicked, click_state = click_service(page, labels)

        if not clicked:
            browser.close()
            return {
                "success": False,
                "state": click_state or "service_missing",
                "message": (
                    "This Zefoy service is currently unavailable."
                    if click_state == "unavailable"
                    else "Zefoy service could not be opened."
                ),
                "elapsed_ms": int((time.time() - started) * 1000),
            }

        search_input = wait_for_search_input(page)

        if search_input is None:
            text = visible_text(page)
            browser.close()
            return {
                "success": False,
                "state": "search_form_missing",
                "message": "Zefoy search form was not available.",
                "page_text": text[:600],
                "elapsed_ms": int((time.time() - started) * 1000),
            }

        try:
            search_input.fill(video_url, timeout=1000)
        except Exception:
            search_input.click()
            search_input.type(video_url, delay=20)

        # Click the visible Search button. This only performs Zefoy's lookup;
        # it does not press any later send/boost action.
        search_button = None
        for candidate in [
            page.get_by_role("button", name=re.compile("Search", re.I)).first,
            page.get_by_text("Search", exact=True).first,
        ]:
            try:
                if candidate.count() and candidate.is_visible(timeout=500):
                    search_button = candidate
                    break
            except Exception:
                pass

        if search_button is None:
            browser.close()
            return {
                "success": False,
                "state": "search_button_missing",
                "message": "Zefoy Search button was not found.",
                "elapsed_ms": int((time.time() - started) * 1000),
            }

        search_button.click(timeout=1500)

        deadline = time.time() + 7.0
        last_text = ""
        cooldown = None
        limits = []
        video = None

        while time.time() < deadline:
            page.wait_for_timeout(160)
            last_text = visible_text(page)

            if looks_blocked(last_text):
                browser.close()
                return {
                    "success": False,
                    "state": "blocked",
                    "message": "Zefoy requested browser verification during search.",
                    "elapsed_ms": int((time.time() - started) * 1000),
                }

            cooldown = parse_cooldown(last_text)
            if cooldown:
                break

            limits = parse_limits(page)
            video = parse_video_card(page, last_text)

            if limits or video.get("username"):
                break

            if re.search(r"successfully\s+sent", last_text, re.I):
                # We never submit a send action; this is only defensive parsing.
                break

        if cooldown:
            browser.close()
            return {
                "success": True,
                "state": "cooldown",
                "service": service,
                "cooldown": cooldown,
                "elapsed_ms": int((time.time() - started) * 1000),
            }

        if video is None:
            video = parse_video_card(page, last_text)

        if not limits:
            limits = parse_limits(page)

        browser.close()

        return {
            "success": True,
            "state": "ready" if (limits or video.get("username")) else "searched",
            "service": service,
            "video": video,
            "limits": limits,
            "message": None if (limits or video.get("username")) else last_text[:500],
            "elapsed_ms": int((time.time() - started) * 1000),
        }


def main():
    if len(sys.argv) < 2:
        emit({"success": False, "state": "args", "message": "mode missing"}, 2)

    mode = sys.argv[1].strip().lower()

    if mode == "services":
        emit(run("services"))

    if mode != "search" or len(sys.argv) < 4:
        emit({"success": False, "state": "args", "message": "search args missing"}, 2)

    service = sys.argv[2].strip().lower()
    video_url = sys.argv[3].strip()

    if not re.match(r"^https?://", video_url, re.I):
        emit({"success": False, "state": "args", "message": "invalid video url"}, 2)

    emit(run("search", service=service, video_url=video_url))


if __name__ == "__main__":
    main()
