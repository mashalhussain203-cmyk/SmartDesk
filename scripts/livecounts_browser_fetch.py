#!/usr/bin/env python3
import json
import os
import re
import shutil
import sys
import time


def emit(payload, code=0):
    print(json.dumps(payload, ensure_ascii=False))
    raise SystemExit(code)


def clean_number(value):
    if value is None:
        return None
    digits = re.sub(r"[^0-9]", "", str(value))
    if not digits:
        return None
    try:
        return int(digits)
    except Exception:
        return None


def find_near_label(lines, label):
    target = label.lower()
    for idx, line in enumerate(lines):
        if line.strip().lower() != target:
            continue

        candidates = []
        for distance in (1, 2, 3):
            if idx - distance >= 0:
                candidates.append(lines[idx - distance])
            if idx + distance < len(lines):
                candidates.append(lines[idx + distance])

        for candidate in candidates:
            if re.fullmatch(r"[0-9][0-9\s.,]*", candidate.strip()):
                number = clean_number(candidate)
                if number is not None:
                    return number
    return None


def parse_body_text(text):
    lines = [line.strip() for line in text.splitlines() if line.strip()]
    return {
        "views": find_near_label(lines, "Views"),
        "likes": find_near_label(lines, "Likes"),
        "comments": find_near_label(lines, "Comments"),
        "shares": find_near_label(lines, "Shares"),
        "favorites": (
            find_near_label(lines, "Favorites")
            or find_near_label(lines, "Favourites")
        ),
    }


def parse_dom(page):
    script = """
    () => {
      const labels = ['Views', 'Likes', 'Comments', 'Shares', 'Favorites'];
      const out = {};
      const all = Array.from(document.querySelectorAll('body *'));

      function parseNumber(s) {
        const t = String(s || '').trim();
        if (!/^[0-9][0-9\\s.,]*$/.test(t)) return null;
        const digits = t.replace(/[^0-9]/g, '');
        if (!digits) return null;
        const n = Number(digits);
        return Number.isFinite(n) ? n : null;
      }

      for (const label of labels) {
        const key = label.toLowerCase();
        const exact = all.filter(el =>
          el.children.length === 0 &&
          String(el.textContent || '').trim().toLowerCase() === key
        );

        let value = null;
        for (const el of exact) {
          const probes = [
            el.previousElementSibling,
            el.nextElementSibling,
            el.parentElement && el.parentElement.previousElementSibling,
            el.parentElement && el.parentElement.nextElementSibling,
          ].filter(Boolean);

          for (const probe of probes) {
            value = parseNumber(probe.textContent);
            if (value !== null) break;
          }

          if (value === null) {
            let node = el.parentElement;
            for (let depth = 0; node && depth < 4 && value === null; depth += 1) {
              const lines = String(node.innerText || '')
                .split(/\\n+/)
                .map(x => x.trim())
                .filter(Boolean);
              const pos = lines.findIndex(x => x.toLowerCase() === key);
              if (pos >= 0) {
                for (const offset of [-1, 1, -2, 2, -3, 3]) {
                  const i = pos + offset;
                  if (i >= 0 && i < lines.length) {
                    value = parseNumber(lines[i]);
                    if (value !== null) break;
                  }
                }
              }
              node = node.parentElement;
            }
          }

          if (value !== null) break;
        }

        out[key] = value;
      }

      return out;
    }
    """
    try:
        return page.evaluate(script)
    except Exception:
        return {}


def main():
    if len(sys.argv) < 2:
        emit({"success": False, "message": "video id ontbreekt", "stage": "args"}, 2)

    video_id = re.sub(r"[^0-9]", "", sys.argv[1])
    if not video_id:
        emit({"success": False, "message": "ongeldig video id", "stage": "args"}, 2)

    try:
        from playwright.sync_api import sync_playwright
    except Exception as exc:
        emit({
            "success": False,
            "message": f"Playwright import mislukt: {exc}",
            "stage": "import_playwright",
        }, 3)

    chromium = (
        os.getenv("CHROMIUM_PATH")
        or shutil.which("chromium")
        or shutil.which("chromium-browser")
        or shutil.which("google-chrome")
    )

    debug = {
        "stage": "browser_start",
        "video_id": video_id,
        "chromium": chromium,
    }

    url = f"https://livecounts.io/tiktok-live-view-counter/{video_id}"

    try:
        with sync_playwright() as p:
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

            browser = p.chromium.launch(**launch_args)
            context = browser.new_context(
                viewport={"width": 1440, "height": 1200},
                locale="en-US",
                user_agent=(
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                    "AppleWebKit/537.36 (KHTML, like Gecko) "
                    "Chrome/141.0.0.0 Safari/537.36"
                ),
            )
            page = context.new_page()

            captured = {
                "stats": None,
                "meta": None,
                "stats_url": None,
                "meta_url": None,
            }

            def on_response(resp):
                try:
                    response_url = resp.url
                    if f"/video/stats/{video_id}" in response_url:
                        data = resp.json()
                        if isinstance(data, dict):
                            captured["stats"] = data
                            captured["stats_url"] = response_url
                    elif f"/video/data/{video_id}" in response_url:
                        data = resp.json()
                        if isinstance(data, dict):
                            captured["meta"] = data
                            captured["meta_url"] = response_url
                except Exception:
                    pass

            page.on("response", on_response)
            response = page.goto(url, wait_until="domcontentloaded", timeout=18000)

            debug["http_status"] = response.status if response else None
            debug["final_url"] = page.url
            debug["stage"] = "wait_for_render"

            deadline = time.time() + 12
            best = {}
            body_text = ""

            while time.time() < deadline:
                try:
                    body_text = page.locator("body").inner_text(timeout=2000)
                except Exception:
                    body_text = ""

                body_stats = parse_body_text(body_text) if body_text else {}
                dom_stats = parse_dom(page)
                network_stats = captured.get("stats") or {}

                def pick(*values):
                    for value in values:
                        number = clean_number(value)
                        if number is not None:
                            return number
                    return None

                merged = {
                    "views": pick(
                        network_stats.get("views"),
                        network_stats.get("viewCount"),
                        network_stats.get("view_count"),
                        dom_stats.get("views"),
                        body_stats.get("views"),
                    ),
                    "likes": pick(
                        network_stats.get("likes"),
                        network_stats.get("likeCount"),
                        network_stats.get("like_count"),
                        dom_stats.get("likes"),
                        body_stats.get("likes"),
                    ),
                    "comments": pick(
                        network_stats.get("comments"),
                        network_stats.get("commentCount"),
                        network_stats.get("comment_count"),
                        dom_stats.get("comments"),
                        body_stats.get("comments"),
                    ),
                    "shares": pick(
                        network_stats.get("shares"),
                        network_stats.get("shareCount"),
                        network_stats.get("share_count"),
                        dom_stats.get("shares"),
                        body_stats.get("shares"),
                    ),
                    "favorites": pick(
                        network_stats.get("favorites"),
                        network_stats.get("favoriteCount"),
                        network_stats.get("favouriteCount"),
                        network_stats.get("collectCount"),
                        network_stats.get("collect_count"),
                        dom_stats.get("favorites"),
                        body_stats.get("favorites"),
                    ),
                }

                best = merged

                available = sum(v is not None for v in merged.values())
                nonzero = sum((v or 0) > 0 for v in merged.values())

                # Prefer the full five-field network response. If favorites are
                # not exposed, accept the four visible counters after waiting.
                if available >= 5 and nonzero >= 1:
                    break
                if available >= 4 and nonzero >= 1 and time.time() > deadline - 3:
                    break

                page.wait_for_timeout(500)

            title = page.title()
            browser.close()

            debug["stage"] = "parsed"
            debug["body_prefix"] = body_text[:1800]
            debug["available"] = sum(v is not None for v in best.values())

            if (
                not best
                or all(best.get(k) is None for k in ("views", "likes", "comments", "shares", "favorites"))
                or all((best.get(k) or 0) == 0 for k in ("views", "likes", "comments", "shares", "favorites"))
            ):
                emit({
                    "success": False,
                    "message": "Livecounts bleef op placeholderwaarden staan; geen echte gerenderde counters gevonden.",
                    "stage": "parse_rendered_page",
                    "debug": debug,
                }, 10)

            meta = captured.get("meta") or {}
            author = meta.get("author")
            if isinstance(author, dict):
                author_name = (
                    author.get("uniqueId")
                    or author.get("username")
                    or author.get("id")
                    or author.get("nickname")
                )
            elif isinstance(author, str):
                author_name = author
            else:
                author_name = None

            meta_title = (
                meta.get("title")
                or meta.get("desc")
                or meta.get("description")
                or title
                or None
            )
            thumbnail_url = (
                meta.get("cover")
                or meta.get("thumbnail")
                or meta.get("avatar")
                or meta.get("banner")
            )

            debug["network_stats_captured"] = isinstance(captured.get("stats"), dict)
            debug["network_meta_captured"] = isinstance(captured.get("meta"), dict)
            debug["network_stats_keys"] = list((captured.get("stats") or {}).keys())[:30]
            debug["network_meta_keys"] = list((captured.get("meta") or {}).keys())[:30]

            emit({
                "success": True,
                "source": "livecounts-rendered-page",
                "precision": "raw_integer",
                "stats": best,
                "title": meta_title,
                "author_name": author_name,
                "thumbnail_url": thumbnail_url,
                "debug": debug,
            })

    except Exception as exc:
        debug["stage"] = "browser_exception"
        debug["message"] = str(exc)
        emit({
            "success": False,
            "message": "Livecounts browser-rendering mislukt.",
            "stage": "browser_exception",
            "debug": debug,
        }, 11)


if __name__ == "__main__":
    main()
