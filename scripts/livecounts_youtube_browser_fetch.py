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


def normalize_stats(payload):
    if not isinstance(payload, dict):
        return None

    for container in ("data", "stats", "channel", "counts"):
        nested = payload.get(container)
        if isinstance(nested, dict):
            if (
                "followerCount" in nested
                or "subscriberCount" in nested
                or "bottomOdos" in nested
                or "viewCount" in nested
                or "videoCount" in nested
            ):
                payload = nested
                break

    follower = as_int(
        payload.get("followerCount")
        if "followerCount" in payload
        else payload.get("subscriberCount")
    )

    bottom = payload.get("bottomOdos")
    if not isinstance(bottom, list):
        bottom = payload.get("stats")

    if isinstance(bottom, list):
        views = as_int(bottom[0]) if len(bottom) > 0 else None
        videos = as_int(bottom[1]) if len(bottom) > 1 else None
        goal = as_int(bottom[2]) if len(bottom) > 2 else None
    else:
        views = as_int(
            payload.get("viewCount")
            if "viewCount" in payload
            else payload.get("views")
        )
        videos = as_int(
            payload.get("videoCount")
            if "videoCount" in payload
            else payload.get("videos")
        )
        goal = as_int(
            payload.get("goalCount")
            if "goalCount" in payload
            else payload.get("goal")
        )

    if follower is None and views is None and videos is None and goal is None:
        return None

    return {
        "subscribers": follower,
        "views": views,
        "videos": videos,
        "goal": goal,
    }


def parse_visible_stats(page):
    script = r"""
    () => {
      const clean = (s) => String(s || '')
        .replace(/ /g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

      const rawBody = String(
        document.body ? document.body.innerText : ''
      ).replace(/ /g, ' ');
      const lines = rawBody.split(/\n+/).map(clean).filter(Boolean);

      const numberNear = (label) => {
        const target = label.toLowerCase();

        for (let i = 0; i < lines.length; i++) {
          const line = lines[i].toLowerCase();

          if (!(line === target || line.startsWith(target + ' '))) continue;

          for (let d = 1; d <= 7; d++) {
            for (const idx of [i - d, i + d]) {
              if (idx < 0 || idx >= lines.length) continue;
              const candidate = lines[idx];
              if (/^[0-9][0-9,.\s]*$/.test(candidate)) {
                const digits = candidate.replace(/\D/g, '');
                if (digits) return Number(digits);
              }
            }
          }
        }

        return null;
      };

      let title = null;
      let avatar = null;
      let banner = null;
      let description = null;

      const avatarImage = Array.from(document.images || []).find(img => {
        const alt = clean(img.getAttribute('alt'));
        return /avatar$/i.test(alt) || /'s avatar$/i.test(alt);
      });

      if (avatarImage) {
        avatar = avatarImage.currentSrc
          || avatarImage.getAttribute('src')
          || avatarImage.src
          || null;

        const alt = clean(avatarImage.getAttribute('alt'));
        const match = alt.match(/^(.*?)'?s Avatar$/i);
        if (match && match[1]) title = match[1];
      }

      if (!title) {
        const heading = Array.from(document.querySelectorAll('h1,h2,h3,strong'))
          .map(el => clean(el.textContent))
          .find(text =>
            text
            && !/youtube live subscriber counter/i.test(text)
            && !/subscribers?/i.test(text)
            && text.length <= 100
          );

        if (heading) title = heading;
      }

      const bannerImage = Array.from(document.images || []).find(img => {
        const alt = clean(img.getAttribute('alt'));
        return /banner$/i.test(alt) || /'s banner$/i.test(alt);
      });

      if (bannerImage) {
        banner = bannerImage.currentSrc
          || bannerImage.getAttribute('src')
          || bannerImage.src
          || null;
      }

      if (title) {
        const aboutHeading = Array.from(
          document.querySelectorAll('h1,h2,h3,h4,strong')
        ).find(el => {
          const text = clean(el.textContent);
          return text.toLowerCase() === ('about ' + title).toLowerCase();
        });

        if (aboutHeading) {
          let container = aboutHeading.parentElement;
          for (let depth = 0; depth < 4 && container; depth++) {
            const text = clean(container.innerText);
            const prefix = clean(aboutHeading.textContent);

            if (
              text
              && text.length > prefix.length + 10
              && text.length < 5000
            ) {
              description = clean(
                text.slice(text.indexOf(prefix) + prefix.length)
              );
              break;
            }

            container = container.parentElement;
          }
        }
      }

      return {
        stats: {
          subscribers: numberNear('Subscribers'),
          views: numberNear('Channel Views'),
          videos: numberNear('Videos'),
          goal: numberNear('Goal')
        },
        title,
        avatar,
        banner,
        description
      };
    }
    """

    try:
        value = page.evaluate(script)
        return value if isinstance(value, dict) else {}
    except Exception:
        return {}


def main():
    if len(sys.argv) < 2:
        emit({"success": False, "message": "channel id ontbreekt"}, 2)

    channel_id = sys.argv[1].strip()
    if not re.fullmatch(r"UC[A-Za-z0-9_-]{22}", channel_id):
        emit({"success": False, "message": "ongeldig channel id"}, 2)

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
        "channel_id": channel_id,
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
                        "/youtube-live-subscriber-counter/stats/" in lower
                    )

                    if is_stats_url:
                        captured["url"] = url
                        captured["status"] = resp.status

                    # Ignore 429/error bodies but keep the listener alive.
                    # The public Livecounts page may retry and later return 2xx.
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

                    if (
                        stats
                        and stats.get("subscribers") is not None
                        and stats.get("views") is not None
                        and stats.get("videos") is not None
                        and stats.get("goal") is not None
                    ):
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
                    "https://livecounts.io/youtube-live-subscriber-counter/"
                    + channel_id,
                    wait_until="commit",
                    timeout=8000,
                )
            except Exception as exc:
                debug["navigation_warning"] = str(exc)

            debug["page_http_status"] = response.status if response else None
            debug["final_url"] = page.url
            debug["livecounts_mode"] = "browser-network-capture"
            debug["stage"] = "wait_for_livecounts_youtube_stats"

            stats = None
            source = None
            deadline = time.time() + 14

            while time.time() < deadline:
                stats = normalize_stats(captured.get("payload"))

                if (
                    stats
                    and stats.get("subscribers") is not None
                    and stats.get("views") is not None
                    and stats.get("videos") is not None
                    and stats.get("goal") is not None
                ):
                    source = "livecounts-youtube-browser-network"
                    break

                page.wait_for_timeout(100)

            visible = parse_visible_stats(page)

            if not stats:
                visible_stats = visible.get("stats")
                if isinstance(visible_stats, dict):
                    if all(
                        visible_stats.get(key) is not None
                        for key in ("subscribers", "views", "videos", "goal")
                    ):
                        stats = visible_stats
                        source = "livecounts-youtube-rendered-page"

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
                    "message": "Livecounts YouTube gaf nog geen volledige stats terug.",
                    "debug": debug,
                }, 10)

            emit({
                "success": True,
                "stats": stats,
                "title": visible.get("title"),
                "avatar": visible.get("avatar"),
                "banner": visible.get("banner"),
                "description": visible.get("description"),
                "source": source or "livecounts-youtube-browser-network",
                "debug": debug,
            })

    except Exception as exc:
        debug["stage"] = "browser_exception"
        debug["message"] = str(exc)
        emit({
            "success": False,
            "message": "YouTube live stats ophalen mislukt.",
            "debug": debug,
        }, 11)


if __name__ == "__main__":
    main()
