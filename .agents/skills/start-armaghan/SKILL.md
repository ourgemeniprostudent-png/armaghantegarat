---
name: start-armaghan
description: Start and check the Armaghan WordPress development site in a prepared Codex Cloud workspace.
---

# Start the project

1. Work from the repository root. This is a PHP/WordPress project, not a Node app.
2. Run `bash scripts/setup.sh` to restore source links and initialize only a fresh runtime. Never delete an existing `.runtime/` to reset content.
3. Run `bash scripts/start.sh`. In an execution tool that terminates background child processes when a command returns, use `bash scripts/start.sh --foreground` in a retained long-running command session; check the site from another command. If its saved PID is stale, inspect it before stopping anything; use `bash scripts/stop.sh` only for this project's server.
4. Run `python3 scripts/smoke.py` and fix actual errors.
5. Read `docs/HANDOFF-fa.md` before editing. Keep the original WordPress/multisite architecture and current approved design.
6. Development credentials are generated in `.runtime/access.txt`. Never commit or print its password in a public report.
7. Cloud UI/browser access and URL forwarding depend on the environment. Do not claim the old Mac's localhost URL or a permanent public preview works in the cloud.
