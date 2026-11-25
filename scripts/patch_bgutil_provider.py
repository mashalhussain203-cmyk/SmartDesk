from pathlib import Path

path = Path("/app/bgutil-ytdlp-pot-provider/server/src/session_manager.ts")
source = path.read_text(encoding="utf-8")
old = '''            if (!Reflect.has(globalThis, "navigator")) {
                Object.defineProperty(globalThis, "navigator", {
                    value: dom.window.navigator,
                });
            }'''
new = '''            Object.defineProperty(globalThis, "navigator", {
                value: dom.window.navigator,
            });'''
if old not in source:
    raise SystemExit("Expected bgutil navigator block was not found; refusing to build an unpatched provider.")
path.write_text(source.replace(old, new, 1), encoding="utf-8")
print("Applied bgutil navigator refresh patch.")