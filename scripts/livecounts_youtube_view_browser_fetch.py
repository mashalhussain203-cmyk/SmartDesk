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


def as_int(value):
    if value is None:
        return None

    if isinstance(value, bool):
        return int(value)

    if isinstance(value, (int, float)):
        return int(value)

    digits = re.sub(r"[^0-9]", "", str(value))
    return int(digits) if digits else None


def first_int(*values):
    for value in values:
        number = as_int(value)
        if number is not None:
            return number
    return None


def normalize_stats(payload):
    if not isinstance(payload, dict):
        return None

    candidates = [payload]

    for key in ("data", "stats", "video", "counts", "statistics"):
        nested = payload.get(key)
        if isinstance(nested, dict):
            candidates.append(nested)

    for current in candidates:
        views = first_int(
            current.get("viewCount"),
            current.get("views"),
            current.get("view_count"),
            current.get("followerCount"),
        )
        likes = first_int(
            current.get("likeCount"),
            current.get("likes"),
            current.get("like_count"),
        )
        dislikes = first_int(
            current.get("dislikeCount"),
            current.get("dislikes"),
            current.get("dislike_count"),
        )
        comments = first_int(
            current.get("commentCount"),
            current.get("comments"),
            current.get("comment_count"),
        )

        bottom = current.get("bottomOdos")
        if not isinstance(bottom, list):
            bottom = current.get("bottom")
        if not isinstance(bottom, list):
            bottom = current.get("secondary")

        if isinstance(bottom, list):
            if likes is None and len(bottom) > 0:
                likes = as_int(bottom[0])
            if dislikes is None and len(bottom) > 1:
                dislikes = as_int(bottom[1])
            if comments is None and len(bottom) > 2:
                comments = as_int(bottom[2])

        if all(
            value is not None
            for value in (views, likes, dislikes, comments)
        ):
            return {
                "views": views,
                "likes": likes,
                "dislikes": dislikes,
                "comments": comments,
            }

    return None


def parse_visible(page):
    script = r"""
    () => {
      const clean = (s) => String(s || '')
        .replace(/ /g, ' ')
        .replace(/s+/g, ' ')
        .trim();

      const lines = String(document.body ? document.body.innerText : '')
        .replace(/ /g, ' ')
        .split(/
+/)
        .map(clean)
        .filter(Boolean);

      const numberNear = (label) => {
        const target = label.toLowerCase();

        for (let i = 0; i < lines.length; i++) {
          const lower = lines[i].toLowerCase();

          if (!(lower === target || lower.startsWith(target + ' '))) {
            continue;
          }

          for (let d = 1; d <= 6; d++) {
            for (const idx of [i - d, i + d]) {
              if (idx < 0 || idx >= lines.length) continue;

              const candidate = lines[idx];
              if (/^[0-9][0-9,.s]*$/.test(candidate)) {
                const digits = candidate.replace(/D/g, '');
                if (digits) return Number(digits);
              }
            }
          }
        }

        return null;
      };

      let title = null;
      let channel = null;
      let thumbnail = null;
      let description = null;

      const h1 = document.querySelector('h1');
      if (h1) {
        const text = clean(h1.textContent);
        if (text && !/youtube live view counter/i.test(text)) {
          title = text;
        }
      }

      if (!title) {
        const headings = Array.from(document.querySelectorAll('h1,h2,h3,strong'))
          .map(el => clean(el.textContent))
          .filter(Boolean);

        title = headings.find(text =>
          !/youtube live view counter/i.test(text)
          && !/views?|likes?|dislikes?|comments?/i.test(text)
          && text.length <= 180
        ) || null;
      }

      const ogImage = document.querySelector('meta[property="og:image"]');
      if (ogImage) {
        thumbnail = ogImage.getAttribute('content') || null;
      }

      if (!thumbnail) {
        const img = Array.from(document.images || []).find(el => {
          const src = el.currentSrc || el.getAttribute('src') || '';
          return /ytimg|youtube|thumbnail/i.test(src);
        });
        if (img) {
          thumbnail = img.currentSrc || img.getAttribute('src') || img.src || null;
        }
      }

      const channelCandidate = Array.from(
        document.querySelectorAll('a[href*="/channel/"], a[href*="/@"]')
      ).find(el => clean(el.textContent));

      if (channelCandidate) {
        channel = clean(channelCandidate.textContent);
      }

      if (title) {
        const aboutHeading = Array.from(
          document.querySelectorAll('h1,h2,h3,h4,strong')
        ).find(el => clean(el.textContent).toLowerCase() ===
          ('about ' + title).toLowerCase());

        if (aboutHeading && aboutHeading.parentElement) {
          const text = clean(aboutHeading.parentElement.innerText);
          const prefix = clean(aboutHeading.textContent);

          if (text.length > prefix.length) {
            description = clean(text.slice(prefix.length));
          }
        }
      }

      return {
        stats: {
          views: numberNear('Views'),
          likes: numberNear('Likes'),
          dislikes: numberNear('Dislikes'),
          comments: numberNear('Comments')
        },
        title,
        channel,
        thumbnail,
        description
      };
    }
    """

    try:
        result = page.evaluate(script)
        return result if isinstance(result, dict) else {}
    except Exception:
        return {}


def main():
    if len(sys.argv) < 2:
        emit({"success": False, "message": "video id ontbreekt"}, 2)

    video_id = sys.argv[1].strip()

    if not re.fullmatch(r"[A-Za-z0-9_-]{11}", video_id):
        emit({"success": False, "message": "ongeldig video id"}, 2)

    try:
        from playwright.sync_api import sync_playwright
    except Exception as exc:
        emit({
            "success": False,
            "message": f"Playwright import mislukt: {exc}",
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
                viewport={"width": 1100, "height": 900},
                locale="en-US",
                user_agent=(
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                    "AppleWebKit/537.36 (KHTML, like Gecko) "
                    "Chrome/141.0.0.0 Safari/537.36"
                ),
            )
            page = context.new_page()

            def block_heavy(route):
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

            page.route("**/*", block_heavy)

            captured = {
                "payload": None,
                "url": None,
                "status": None,
                "json_candidates": [],
            }

            def on_response(resp):
                try:
                    url = resp.url
                    lower = url.lower()

                    is_stats_url = (
                        "/youtube-live-view-counter/stats/" in lower
                    )

                    if is_stats_url:
                        captured["url"] = url
                        captured["status"] = resp.status

                    if not (200 <= resp.status < 300):
                        return

                    content_type = (
                        resp.headers.get("content-type") or ""
                    ).lower()

                    if (
                        not is_stats_url
                        and "json" not in content_type
                        and "api.livecounts.io" not in lower
                    ):
                        return

                    payload = resp.json()
                    stats = normalize_stats(payload)

                    if stats:
                        captured["payload"] = payload
                        captured["url"] = url
                        captured["status"] = resp.status
                        captured["json_candidates"].append(url)
                except Exception:
                    pass

            page.on("response", on_response)

            response = None

            try:
                response = page.goto(
                    "https://livecounts.io/youtube-live-view-counter/"
                    + video_id,
                    wait_until="commit",
                    timeout=8000,
                )
            except Exception as exc:
                debug["navigation_warning"] = str(exc)

            debug["page_http_status"] = response.status if response else None
            debug["final_url"] = page.url
            debug["livecounts_mode"] = "browser-network-capture"
            debug["stage"] = "wait_for_livecounts_youtube_view_stats"

            stats = None
            source = None
            deadline = time.time() + 14

            while time.time() < deadline:
                stats = normalize_stats(captured.get("payload"))

                if stats:
                    source = "livecounts-youtube-view-browser-network"
                    break

                page.wait_for_timeout(100)

            visible = parse_visible(page)

            if not stats:
                visible_stats = visible.get("stats")
                if isinstance(visible_stats, dict):
                    if all(
                        visible_stats.get(key) is not None
                        for key in ("views", "likes", "dislikes", "comments")
                    ):
                        stats = visible_stats
                        source = "livecounts-youtube-view-rendered-page"

            debug["network_stats_captured"] = isinstance(
                captured.get("payload"), dict
            )
            debug["stats_url"] = captured.get("url")
            debug["stats_status"] = captured.get("status")
            debug["json_candidates"] = captured.get("json_candidates")
            debug["stats_keys"] = (
                list(captured.get("payload").keys())[:30]
                if isinstance(captured.get("payload"), dict)
                else []
            )
            debug["stage"] = "parsed"

            browser.close()

            if not stats:
                emit({
                    "success": False,
                    "message": (
                        "Livecounts YouTube View gaf nog geen volledige stats terug."
                    ),
                    "debug": debug,
                }, 10)

            emit({
                "success": True,
                "stats": stats,
                "title": visible.get("title"),
                "channel": visible.get("channel"),
                "thumbnail": visible.get("thumbnail"),
                "description": visible.get("description"),
                "source": source or "livecounts-youtube-view-browser-network",
                "debug": debug,
            })

    except Exception as exc:
        debug["stage"] = "browser_exception"
        debug["message"] = str(exc)
        emit({
            "success": False,
            "message": "YouTube live view stats ophalen mislukt.",
            "debug": debug,
        }, 11)


if __name__ == "__main__":
    main()
