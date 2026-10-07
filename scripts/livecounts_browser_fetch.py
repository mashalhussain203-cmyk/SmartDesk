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

      const numericEls = all
        .filter(el => visible(el) && el.children.length === 0)
        .map(el => {
          const value = parseNumber(el.textContent);
          if (value === null) return null;
          const r = el.getBoundingClientRect();
          return {
            el,
            value,
            x: r.left + r.width / 2,
            y: r.top + r.height / 2,
            font: parseFloat(getComputedStyle(el).fontSize || '0')
          };
        })
        .filter(Boolean);

      const out = {};

      for (const key of wanted) {
        const labelCandidates = all.filter(el => {
          if (!visible(el)) return false;
          const txt = clean(el.textContent).toLowerCase();
          if (!txt) return false;
          if (txt === key) return true;
          if (el.children.length <= 2 && txt === key) return true;
          return false;
        });

        let best = null;

        for (const labelEl of labelCandidates) {
          const lr = labelEl.getBoundingClientRect();
          const lx = lr.left + lr.width / 2;
          const ly = lr.top + lr.height / 2;

          for (const num of numericEls) {
            const dx = num.x - lx;
            const dy = num.y - ly;
            const distance = Math.sqrt(dx * dx + dy * dy);

            // Counters should be reasonably close to their label.
            if (distance > 520) continue;

            // Prefer bigger text and nearby numbers.
            const score = distance - (num.font * 7);

            if (!best || score < best.score) {
              best = { score, value: num.value };
            }
          }

          // Also inspect compact ancestor text; odometer digits can be nested.
          let node = labelEl.parentElement;
          for (let depth = 0; node && depth < 5; depth += 1) {
            const lines = String(node.innerText || '')
              .split(/\n+/)
              .map(clean)
              .filter(Boolean);
            const idx = lines.findIndex(x => x.toLowerCase() === key);

            if (idx >= 0) {
              for (let radius = 1; radius <= 8; radius += 1) {
                for (const pos of [idx - radius, idx + radius]) {
                  if (pos >= 0 && pos < lines.length) {
                    const value = parseNumber(lines[pos]);
                    if (value !== null) {
                      const score = radius * 40;
                      if (!best || score < best.score) {
                        best = { score, value };
                      }
                    }
                  }
                }
              }
            }

            node = node.parentElement;
          }
        }

        out[key] = best ? best.value : null;
      }

      // Official embed fallback. The TikTok embed is arranged as one
      // large Views odometer plus three lower odometers:
      // Likes, Comments, Shares. Use geometry only when text labels failed.
      if (numericEls.length) {
        const candidates = numericEls
          .filter(x => x.font >= 14)
          .sort((a, b) => {
            if (a.y !== b.y) return a.y - b.y;
            return a.x - b.x;
          });

        if (out.views === null) {
          const primary = candidates
            .slice()
            .sort((a, b) => {
              if (b.font !== a.font) return b.font - a.font;
              return a.y - b.y;
            })[0];

          if (primary) out.views = primary.value;
        }

        const primaryY = candidates.length
          ? candidates.slice().sort((a, b) => b.font - a.font)[0].y
          : 0;

        const lower = candidates
          .filter(x => x.y > primaryY + 20)
          .sort((a, b) => a.x - b.x);

        if (lower.length >= 3) {
          if (out.likes === null) out.likes = lower[0].value;
          if (out.comments === null) out.comments = lower[1].value;
          if (out.shares === null) out.shares = lower[2].value;
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

    url = f"https://livecounts.io/embed/tiktok-live-view-counter/{video_id}"

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
                viewport={"width": 1440, "height": 1800},
                locale="en-US",
                user_agent=(
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                    "AppleWebKit/537.36 (KHTML, like Gecko) "
                    "Chrome/141.0.0.0 Safari/537.36"
                ),
            )
            page = context.new_page()

            def route_handler(route):
                try:
                    resource_type = route.request.resource_type
                    if resource_type in ("media", "font"):
                        route.abort()
                    else:
                        route.continue_()
                except Exception:
                    try:
                        route.continue_()
                    except Exception:
                        pass

            page.route("**/*", route_handler)
            response = page.goto(url, wait_until="domcontentloaded", timeout=10000)

            debug["http_status"] = response.status if response else None
            debug["final_url"] = page.url
            debug["livecounts_mode"] = "official-embed"
            debug["stage"] = "wait_for_render"

            deadline = time.time() + 8
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

            try:
                meta = page.evaluate("""
                () => {
                  const images = Array.from(document.querySelectorAll('img'));
                  const avatar = images.find(img =>
                    /avatar/i.test(img.getAttribute('alt') || '')
                  );
                  const banner = images.find(img =>
                    /banner/i.test(img.getAttribute('alt') || '')
                  );
                  const external = Array.from(document.querySelectorAll('a[href]'))
                    .find(a => /tiktok\.com\//i.test(a.href || ''));

                  return {
                    avatar: avatar ? avatar.src : null,
                    banner: banner ? banner.src : null,
                    external_url: external ? external.href : null,
                    page_title: document.title || null
                  };
                }
                """)
            except Exception:
                meta = {}

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
                "source": "livecounts-official-embed-rendered",
                "precision": "raw_integer",
                "stats": best,
                "title": (meta.get("page_title") if isinstance(meta, dict) else None) or title or None,
                "thumbnail_url": (
                    (meta.get("avatar") if isinstance(meta, dict) else None)
                    or (meta.get("banner") if isinstance(meta, dict) else None)
                ),
                "external_url": meta.get("external_url") if isinstance(meta, dict) else None,
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
