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
        normalized = line.strip().lower()
        if not (normalized == target or normalized.startswith(target + " ")):
            continue

        candidates = []
        for distance in range(1, 9):
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
      const wanted = ['views', 'likes', 'comments', 'shares'];
      const all = Array.from(document.querySelectorAll('body *'));

      const clean = (s) => String(s || '')
        .replace(/\u00a0/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

      const parseNumber = (s) => {
        const t = clean(s);
        if (!/^[0-9][0-9\s.,]*$/.test(t)) return null;
        const digits = t.replace(/[^0-9]/g, '');
        if (!digits) return null;
        const n = Number(digits);
        return Number.isFinite(n) ? n : null;
      };

      const visible = (el) => {
        if (!el) return false;
        const r = el.getBoundingClientRect();
        const st = getComputedStyle(el);
        return r.width > 0 && r.height > 0
          && st.display !== 'none'
          && st.visibility !== 'hidden'
          && Number(st.opacity || 1) > 0;
      };

      const labelMatches = (text, key) => {
        const t = clean(text).toLowerCase();
        return t === key
          || t.startsWith(key + ' ')
          || t.startsWith(key + '\u00a0');
      };

      const numericLeaves = (root) => Array.from(root.querySelectorAll('*'))
        .filter(el => visible(el) && el.children.length === 0)
        .map(el => {
          const value = parseNumber(el.textContent);
          if (value === null) return null;
          const r = el.getBoundingClientRect();
          return {
            value,
            x: r.left + r.width / 2,
            y: r.top + r.height / 2,
            font: parseFloat(getComputedStyle(el).fontSize || '0')
          };
        })
        .filter(Boolean);

      const out = {
        views: null,
        likes: null,
        comments: null,
        shares: null
      };

      for (const key of wanted) {
        const labels = all.filter(el =>
          visible(el)
          && el.children.length <= 3
          && labelMatches(el.textContent, key)
        );

        let winner = null;

        for (const label of labels) {
          const lr = label.getBoundingClientRect();
          const lx = lr.left + lr.width / 2;
          const ly = lr.top + lr.height / 2;

          // Walk up until we hit the visual stat card containing this label.
          let node = label;
          for (let depth = 0; node && depth < 7; depth += 1, node = node.parentElement) {
            const r = node.getBoundingClientRect();
            if (
              r.width < 80 || r.height < 45
              || r.width > Math.min(window.innerWidth * 0.98, 1300)
              || r.height > 420
            ) {
              continue;
            }

            const nums = numericLeaves(node);
            for (const num of nums) {
              const dx = num.x - lx;
              const dy = num.y - ly;
              const distance = Math.sqrt(dx * dx + dy * dy);

              // The screenshot layout puts the number directly above the label.
              // Prefer larger text inside the same card and close to the label.
              const score = distance - (num.font * 10);
              if (!winner || score < winner.score) {
                winner = {
                  score,
                  value: num.value,
                  font: num.font,
                  distance
                };
              }
            }

            // Once a plausible card has a large numeric value, don't climb into
            // a much larger container where unrelated numbers/ads can interfere.
            if (winner && winner.font >= 24 && winner.distance < 260) {
              break;
            }
          }
        }

        if (winner) {
          out[key] = winner.value;
        }
      }

      // Main Views counter can have a different layout. If its label was not
      // found, use the largest visible numeric text in the upper counter area.
      if (out.views === null) {
        const candidates = all
          .filter(el => visible(el) && el.children.length === 0)
          .map(el => {
            const value = parseNumber(el.textContent);
            if (value === null) return null;
            const r = el.getBoundingClientRect();
            return {
              value,
              y: r.top + r.height / 2,
              font: parseFloat(getComputedStyle(el).fontSize || '0')
            };
          })
          .filter(Boolean)
          .filter(x => x.value >= 1000 && x.font >= 26)
          .sort((a, b) => {
            if (b.font !== a.font) return b.font - a.font;
            return a.y - b.y;
          });

        if (candidates.length) {
          out.views = candidates[0].value;
        }
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

    page_url = f"https://livecounts.io/tiktok-live-view-counter/{video_id}"

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
                viewport={"width": 1280, "height": 1600},
                locale="en-US",
                user_agent=(
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                    "AppleWebKit/537.36 (KHTML, like Gecko) "
                    "Chrome/141.0.0.0 Safari/537.36"
                ),
            )

            page = context.new_page()

            # The provider's stats request only needs its JavaScript.
            # Skip heavy visual assets so the counter API response can arrive sooner.
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

            page.route("**/*", block_heavy_assets)

            captured = {
                "stats": None,
                "stats_url": None,
                "stats_status": None,
                "data": None,
                "data_url": None,
                "data_status": None,
            }

            def on_response(resp):
                try:
                    u = resp.url
                    if f"/video/stats/{video_id}" in u:
                        captured["stats_url"] = u
                        captured["stats_status"] = resp.status

                        # A provider rate-limit response is not counter data.
                        # Keep listening because the page may retry on its own.
                        if 200 <= resp.status < 300:
                            payload = resp.json()
                            if isinstance(payload, dict):
                                captured["stats"] = payload
                    elif f"/video/data/{video_id}" in u:
                        payload = resp.json()
                        if isinstance(payload, dict):
                            captured["data"] = payload
                            captured["data_url"] = u
                            captured["data_status"] = resp.status
                except Exception:
                    pass

            page.on("response", on_response)

            response = None
            try:
                response = page.goto(
                    page_url,
                    wait_until="commit",
                    timeout=8000,
                )
            except Exception as nav_exc:
                debug["navigation_warning"] = str(nav_exc)

            debug["page_http_status"] = response.status if response else None
            debug["final_url"] = page.url
            debug["livecounts_mode"] = "browser-network-capture"
            debug["stage"] = "wait_for_livecounts_network"

            deadline = time.time() + 10
            body_text = ""
            fallback_stats = {}

            # Fast path: do not parse the DOM while the provider's own JSON
            # response is still in flight. This lets us return almost
            # immediately after /video/stats/{id} arrives.
            while time.time() < deadline:
                if isinstance(captured.get("stats"), dict):
                    break
                page.wait_for_timeout(100)

            stats_payload = captured.get("stats")
            data_payload = captured.get("data")

            # Only touch the rendered DOM if the normal network response was
            # not captured. This is a fallback, not part of the hot path.
            if not isinstance(stats_payload, dict):
                try:
                    body_text = page.locator("body").inner_text(timeout=1000)
                    fallback_stats = parse_body_text(body_text)
                    dom_stats = parse_dom(page)
                    for key in ("views", "likes", "comments", "shares"):
                        if dom_stats.get(key) is not None:
                            fallback_stats[key] = dom_stats.get(key)
                except Exception:
                    pass

            def pick(d, *keys):
                if not isinstance(d, dict):
                    return None
                for key in keys:
                    if key in d:
                        val = clean_number(d.get(key))
                        if val is not None:
                            return val
                return None

            if isinstance(stats_payload, dict):
                stats = {
                    "views": pick(stats_payload, "viewCount", "views", "view_count"),
                    "likes": pick(stats_payload, "likeCount", "likes", "like_count"),
                    "comments": pick(stats_payload, "commentCount", "comments", "comment_count"),
                    "shares": pick(stats_payload, "shareCount", "shares", "share_count"),
                }
                source = "livecounts-browser-network"
            else:
                stats = {
                    key: clean_number(fallback_stats.get(key))
                    for key in ("views", "likes", "comments", "shares")
                }
                source = "livecounts-public-page-rendered"

            debug["network_stats_captured"] = isinstance(stats_payload, dict)
            debug["network_stats_url"] = captured.get("stats_url")
            debug["network_stats_status"] = captured.get("stats_status")
            debug["network_data_captured"] = isinstance(data_payload, dict)
            debug["network_data_url"] = captured.get("data_url")
            debug["network_data_status"] = captured.get("data_status")
            debug["stats_keys"] = list(stats_payload.keys())[:30] if isinstance(stats_payload, dict) else []
            debug["data_keys"] = list(data_payload.keys())[:30] if isinstance(data_payload, dict) else []
            debug["body_prefix"] = body_text[:1400]
            debug["stage"] = "parsed"

            browser.close()

            if all(stats.get(k) is None for k in ("views", "likes", "comments", "shares")):
                emit({
                    "success": False,
                    "message": "Livecounts-pagina laadde, maar de eigen stats-response werd niet ontvangen.",
                    "stage": "livecounts_network_missing",
                    "debug": debug,
                }, 10)

            if any(stats.get(k) is None for k in ("views", "likes", "comments", "shares")):
                emit({
                    "success": False,
                    "message": "Livecounts-response was onvolledig; niet alle vier tellers zijn beschikbaar.",
                    "stage": "livecounts_network_incomplete",
                    "stats": stats,
                    "debug": debug,
                }, 10)

            title = None
            thumbnail_url = None
            author_name = None

            if isinstance(data_payload, dict):
                title = (
                    data_payload.get("title")
                    or data_payload.get("description")
                    or data_payload.get("desc")
                )
                thumbnail_url = (
                    data_payload.get("cover")
                    or data_payload.get("thumbnail")
                )
                author = data_payload.get("author")
                if isinstance(author, dict):
                    author_name = (
                        author.get("uniqueId")
                        or author.get("username")
                        or author.get("nickname")
                    )
                elif isinstance(author, str):
                    author_name = author

            emit({
                "success": True,
                "source": source,
                "precision": "raw_integer",
                "stats": stats,
                "title": title,
                "thumbnail_url": thumbnail_url,
                "author_name": author_name,
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
