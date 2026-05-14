# Codex Workflow for This Project

Use this workflow whenever Codex implements a task.

## Step 1: Understand the task

Restate:

- What feature is being requested
- Which module it touches
- Whether it is UI, backend, editor, import/export, or deployment
- Any risks or unknowns

Ask a question only if the task is blocked by missing information.

## Step 2: Inspect first

Before editing, inspect relevant files:

- Routes
- Controllers
- Models
- Migrations
- Views
- Vue editor files
- CSS/design tokens
- Existing tests

Do not assume the structure.

## Step 3: Make a small plan

Plan with 3 to 7 steps.

Keep the plan practical and focused. Avoid proposing a large rewrite.

## Step 4: Implement

Follow these priorities:

1. Correctness
2. Simple architecture
3. Arabic RTL polish
4. Maintainability
5. Performance

Use the brand guide for UI work.

## Step 5: Validate

Run checks relevant to the changed area.

Backend:

```bash
php artisan test
php artisan route:list
composer validate
```

Frontend:

```bash
npm run build
```

Only run commands that make sense for the change.

## Step 6: Report

After each task, report:

- Files changed
- What was implemented
- How to test manually
- Commands run
- Any remaining risks

## Task prompt template

Use this template when asking Codex to do work:

```text
Use the repository AGENTS.md and the event-floor-planner-laravel skill.

Task:
[describe the feature]

Requirements:
- Keep Laravel 10.
- Keep PHP max 8.2.
- Arabic RTL UI.
- Follow the brand colors and UI guide.
- Keep the solution simple, internal-use only.
- Do not add SaaS/multi-tenant/billing complexity.

Before coding:
- Inspect relevant files.
- Give a short plan.
- Then implement phase by phase.

After coding:
- List changed files.
- Explain how to test.
- Run relevant checks.
```
