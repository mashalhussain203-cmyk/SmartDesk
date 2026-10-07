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
    }


def parse_dom(page):
    script = """
    () => {
      const labels = ['Views', 'Likes', 'Comments', 'Shares'];
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

                merged = {}
                for key in ("views", "likes", "comments", "shares"):
                    merged[key] = dom_stats.get(key)
                    if merged[key] is None:
                        merged[key] = body_stats.get(key)

                best = merged

                available = sum(v is not None for v in merged.values())
                nonzero = sum((v or 0) > 0 for v in merged.values())
                if available >= 4 and nonzero >= 1:
                    break

                page.wait_for_timeout(500)

            title = page.title()
            browser.close()

            debug["stage"] = "parsed"
            debug["body_prefix"] = body_text[:1800]
            debug["available"] = sum(v is not None for v in best.values())

            if (
                not best
                or all(best.get(k) is None for k in ("views", "likes", "comments", "shares"))
                or all((best.get(k) or 0) == 0 for k in ("views", "likes", "comments", "shares"))
            ):
                emit({
                    "success": False,
                    "message": "Livecounts bleef op placeholderwaarden staan; geen echte gerenderde counters gevonden.",
                    "stage": "parse_rendered_page",
                    "debug": debug,
                }, 10)

            emit({
                "success": True,
                "source": "livecounts-rendered-page",
                "precision": "raw_integer",
                "stats": best,
                "title": title or None,
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
