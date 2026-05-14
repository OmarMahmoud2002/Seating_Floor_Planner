# Codex Setup Guide

This pack gives Codex two things:

1. `AGENTS.md` as the project role and always-on instructions.
2. `.agents/skills/event-floor-planner-laravel/` as a reusable Codex skill for this specific Laravel floor planner project.

## Where to put the files

Copy the contents of this pack into the root of your Laravel project.

Expected structure:

```text
your-laravel-project/
├── AGENTS.md
├── .agents/
│   └── skills/
│       └── event-floor-planner-laravel/
│           ├── SKILL.md
│           ├── agents/
│           │   └── openai.yaml
│           └── references/
│               ├── brand-ui-guide.md
│               ├── laravel-php82-rules.md
│               ├── project-brief.md
│               └── review-checklist.md
```

## How to use with Codex

From the Laravel project root, run Codex normally.

To make sure Codex loaded the project instructions, ask:

```bash
codex --ask-for-approval never "Summarize the current instructions for this repository."
```

To explicitly use the project skill in a task, mention:

```text
Use $event-floor-planner-laravel.
```

Example prompt:

```text
Use $event-floor-planner-laravel.

Build the Events CRUD screens in Arabic RTL.
Keep Laravel 10 and PHP max 8.2.
Follow the brand colors and design guide.
Before coding, inspect the existing files and give me a short plan.
After coding, list changed files and testing steps.
```

## Recommended first Codex prompt

```text
Use $event-floor-planner-laravel.

I want to start Phase 1 of the Arabic Event Floor Planner project:
- Laravel 10
- PHP max 8.2
- Arabic RTL layout
- Brand colors from the guide
- Simple internal dashboard
- No SaaS complexity

First inspect the repository, then propose a small plan.
Do not code until you explain the plan.
```

## Notes

- Keep `AGENTS.md` at the Laravel project root.
- Keep the skill folder under `.agents/skills`.
- If Codex does not see updated instructions, restart the Codex session.
- Do not deploy `.env` secrets.
- Do not deploy `node_modules`.
- Build frontend assets locally with `npm run build`.
