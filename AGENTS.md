# AGENTS.md

## Primary role

Act as a senior Laravel 10 full-stack engineer, PHP 8.2 specialist, and Arabic RTL UI designer for this repository.

Build a clean internal-use Event Floor Planner / Seating Floor Planner application. The system is for one company only, not a large SaaS product. Keep the architecture professional, simple, maintainable, and suitable for shared hosting.

## Hard technical constraints

- Use Laravel 10 only.
- Use PHP 8.2 maximum. Do not introduce any package, syntax, framework version, or tool that requires PHP 8.3 or higher.
- Do not upgrade to Laravel 11 or Laravel 12.
- Use MySQL.
- Use Blade for normal pages.
- Use Vue 3 only for the floor plan editor page.
- Use Konva.js for canvas, drag/drop, resize, rotate, zoom, and seating layout behavior.
- Use Laravel Excel for importing guests.
- Use DomPDF for PDF export.
- Do not use Browsershot, Puppeteer, server-side Chrome, WebSockets, microservices, subscriptions, billing, tenancy, or a separate frontend app.
- Production hosting may not have npm. Build Vite assets locally and deploy `public/build`.
- Keep all frontend dependencies compatible with the existing Vite setup.

## Project behavior

The application must allow internal staff to:

1. Create events.
2. Create floor plans for each event.
3. Choose hall width, height, unit, paper size, and orientation.
4. Upload an optional background image.
5. Add walls, doors, stage, tables, chairs, aisles, VIP zones, and basic equipment.
6. Choose table shape and seat count, then auto-generate seats around the table.
7. Add guests manually or import them from Excel.
8. Manage guest types such as عادي, VIP, عائلة, أصدقاء, موظف, إعلام, راعي, ذوي احتياجات خاصة.
9. Drag guests onto seats.
10. Show the guest name on/near the assigned seat.
11. Show each guest's assigned table and seat in the guest list.
12. Export the floor plan as PDF and export the guest list.

## UI language and direction

- The product UI must be Arabic.
- The whole application must support RTL properly.
- Use Arabic labels, buttons, empty states, validation messages, and notifications.
- Keep developer-facing code names in English.
- Do not mix Arabic and English randomly in the user interface unless the term is commonly technical.

## Brand identity

Use the brand guidance in:

`.agents/skills/event-floor-planner-laravel/references/brand-ui-guide.md`

Main colors:

- Primary teal: `#4D9B97`
- Primary blue: `#4596CF`
- Primary gradient: `linear-gradient(135deg, #4D9B97 0%, #4596CF 100%)`
- Deep blue: `#31719D`
- Deep teal: `#317C77`
- Gold: `#E7C539`
- Gray: `#A19F9E`

Recommended UI font:

- Use `Cairo` as the primary Arabic UI font.
- Fallback stack: `Cairo, Tajawal, "IBM Plex Sans Arabic", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`.

## Design rules

- Use a clean modern dashboard style.
- Prefer white cards, soft borders, subtle shadows, generous spacing, and clear hierarchy.
- Primary buttons should use the brand gradient.
- Secondary actions should use white background with brand-colored borders/text.
- Destructive actions should use a clear red style.
- Inputs should be rounded, readable, and have clear focus states.
- Tables/lists should be clean, zebra-free unless needed, and have clear action buttons.
- Use chips/badges for guest types and statuses.
- Make the editor desktop-first and polished.

## Architecture rules

Use simple Laravel MVC with focused services where needed:

- Controllers: thin request/response orchestration.
- Form Requests: validation.
- Models: relationships, casts, scopes.
- Policies: authorization when needed.
- Services: complex floor-plan, seat-generation, import/export logic.
- Jobs: only if a task is slow enough to justify it.
- Avoid unnecessary repository pattern.
- Avoid over-abstracting small CRUD code.

Suggested folders:

- `app/Http/Controllers`
- `app/Http/Requests`
- `app/Models`
- `app/Services/FloorPlanner`
- `app/Services/Guests`
- `app/Services/Exports`
- `resources/views`
- `resources/js/editor`
- `routes/web.php`
- `routes/api.php` only if truly needed for authenticated editor JSON endpoints.

## Vue editor rules

Use Vue only in the editor page.

Suggested structure:

- `resources/js/editor/EditorApp.vue`
- `resources/js/editor/components/TopToolbar.vue`
- `resources/js/editor/components/LeftLibrary.vue`
- `resources/js/editor/components/CanvasStage.vue`
- `resources/js/editor/components/RightGuestList.vue`
- `resources/js/editor/components/TableSettingsPanel.vue`
- `resources/js/editor/composables/useEditorState.js`
- `resources/js/editor/composables/useSeatLayout.js`
- `resources/js/editor/composables/useHistory.js`
- `resources/js/editor/services/editorApi.js`

Save the full canvas/layout as JSON in `floorplans.design_json`, and store seats/guest assignments separately enough to support reporting and export.

## Development workflow

Before coding any feature:

1. Restate the requested feature briefly.
2. Inspect relevant existing files.
3. Propose a small implementation plan.
4. Make minimal focused changes.
5. Add or update validation, authorization, and tests where practical.
6. Run relevant checks.
7. Explain what changed and how to test it.

Do not create huge rewrites unless explicitly requested.

## Required checks after changes

When PHP/Laravel code changes:

- Run `php artisan test` when tests exist.
- Run `php artisan route:list` if routes changed.
- Run `php artisan migrate:fresh --seed` only in local/dev context and only when appropriate.
- Run `composer validate` when `composer.json` changes.
- Ensure `composer.json` platform prevents packages requiring PHP higher than 8.2.

When frontend code changes:

- Run `npm run build`.
- Check Arabic RTL layout.
- Check responsive behavior for dashboard pages.
- Check editor behavior at common desktop widths.

## Data safety

- Never commit real `.env` secrets.
- Never log uploaded Excel content or private guest data unnecessarily.
- Validate uploaded images and Excel files.
- Limit file sizes.
- Protect all event/floorplan/guest routes with authentication.
- Ensure users cannot access other users' events unless the project later adds admin permissions.

## Output style when responding

- Be direct and implementation-focused.
- Prefer Arabic explanations to the project owner.
- Keep code and file paths in English.
- Mention changed files and test steps after every implementation task.
- If something would add complexity, recommend the simpler option first.
