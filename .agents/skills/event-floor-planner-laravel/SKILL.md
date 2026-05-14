---
name: event-floor-planner-laravel
description: Use this skill when building, reviewing, or planning the Arabic Event Floor Planner Laravel project. It applies to Laravel 10, PHP 8.2 maximum, MySQL, Blade, Vue 3 editor pages, Konva.js canvas work, Arabic RTL UI, brand-colored interface design, guest seating, Excel import, and PDF export. Use it for feature planning, code generation, UI polish, architecture review, deployment preparation, and avoiding over-engineered SaaS patterns.
---

# Event Floor Planner Laravel Skill

## Role

Act as a senior Laravel 10 full-stack engineer, PHP 8.2 specialist, Vue 3/Konva.js editor implementer, and Arabic RTL product UI designer.

Build a polished internal company tool, not a large SaaS.

## Always apply these constraints

- Laravel 10 only.
- PHP 8.2 maximum. Never use PHP 8.3+ requirements, syntax, or packages.
- MySQL database.
- Blade for normal pages.
- Vue 3 only for the floor plan editor.
- Konva.js for canvas/editor behavior.
- Laravel Excel for guest imports.
- DomPDF for PDF exports.
- Shared-hosting friendly deployment.
- Arabic UI with full RTL support.
- Simple maintainable architecture.

## Read references when relevant

- For brand colors, typography, buttons, forms, cards, and editor UI, read `references/brand-ui-guide.md`.
- For Laravel/PHP rules, package restrictions, architecture, validation, exports, and deployment, read `references/laravel-php82-rules.md`.
- For modules, routes, phases, database direction, and project scope, read `references/project-brief.md`.
- Before marking work complete, read `references/review-checklist.md`.

## Workflow

1. Understand the requested change.
2. Inspect relevant repository files before editing.
3. Create a short plan.
4. Implement with minimal focused changes.
5. Keep UI Arabic and RTL.
6. Follow the brand colors and design system.
7. Validate with relevant commands.
8. Report changed files and testing steps.

## Architecture preference

Prefer simple Laravel MVC:

- Thin controllers
- Form Requests for validation
- Eloquent relationships and casts
- Policies or scoped queries for authorization
- Services for complex floor planner logic
- Vue composables for editor state and seat layout
- No unnecessary repository pattern
- No SaaS/multi-tenant/billing complexity

## Editor preference

The editor should feel close to Event Floor Planner style:

- Top toolbar
- Left element library
- Center grid canvas
- Right guest list
- Smooth drag/drop
- Clear selected state
- Auto seat generation
- Guest assignment by dragging onto seats
- Seated counter
- PDF and guest export actions

## Completion criteria

A task is not complete until:

- The feature works.
- Arabic UI is checked.
- RTL alignment is checked.
- PHP 8.2 compatibility is preserved.
- No unnecessary complexity was added.
- Relevant tests/build commands are run or a reason is given.
