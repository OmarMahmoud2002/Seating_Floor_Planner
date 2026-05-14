# Review Checklist

Use this checklist before marking a phase as complete.

## General

- [ ] UI is Arabic.
- [ ] Layout direction is RTL.
- [ ] Colors follow the brand palette.
- [ ] Main actions are visually clear.
- [ ] Empty states are written in Arabic.
- [ ] Error messages are understandable.
- [ ] No unnecessary SaaS complexity was added.
- [ ] No package requires PHP higher than 8.2.

## Laravel

- [ ] Routes are protected with auth middleware.
- [ ] User cannot access another user's event/floorplan.
- [ ] Controllers are not overloaded.
- [ ] Validation is handled with Form Requests where practical.
- [ ] Migrations include indexes and foreign keys.
- [ ] Models define relationships and casts.
- [ ] Database writes that affect multiple records use transactions.
- [ ] Uploads are validated by type and size.

## Frontend

- [ ] `npm run build` passes.
- [ ] Dashboard pages look good in Arabic.
- [ ] Buttons, inputs, cards, and badges are consistent.
- [ ] Form focus states are visible.
- [ ] Toasts/messages are Arabic.
- [ ] No broken LTR alignment in RTL screens.

## Editor

- [ ] Canvas loads correctly.
- [ ] Grid is visible and not distracting.
- [ ] Background image can be displayed if uploaded.
- [ ] Elements can be added.
- [ ] Elements can be moved.
- [ ] Table labels are readable.
- [ ] Seats are generated correctly for selected table shape/count.
- [ ] Seat labels/codes are consistent.
- [ ] Assigned guest name appears clearly.
- [ ] Seated count updates correctly.
- [ ] Save/load preserves layout.
- [ ] Zoom controls work.
- [ ] No guest can be assigned to two seats at the same time.
- [ ] No two guests can occupy the same seat.

## Guests

- [ ] Guest CRUD works.
- [ ] Guest types work.
- [ ] Guest type colors/icons appear consistently.
- [ ] Search/filter works if implemented.
- [ ] Assigned table and seat appear in the guest list.
- [ ] Unassigning a guest updates the canvas and list.

## Excel import

- [ ] Invalid files are rejected.
- [ ] Missing names are handled.
- [ ] Duplicate guests are skipped or reported.
- [ ] Unknown guest types are mapped or created according to the chosen rule.
- [ ] Import summary is shown in Arabic.

## Exports

- [ ] Floor plan PDF includes event name.
- [ ] Floor plan PDF includes floor plan name.
- [ ] Floor plan PDF includes canvas image.
- [ ] PDF layout is printable.
- [ ] Guest list export includes guest name, type, table, and seat.
- [ ] Export does not require Puppeteer, Chrome, or npm on the server.

## Security

- [ ] `.env` is not committed.
- [ ] Uploaded files cannot execute code.
- [ ] API/editor endpoints validate ownership.
- [ ] CSRF is handled.
- [ ] Deletions confirm user intent.
- [ ] Sensitive guest data is not logged unnecessarily.

## Deployment

- [ ] `composer install --no-dev --optimize-autoloader` works locally.
- [ ] `npm run build` creates `public/build`.
- [ ] `php artisan config:cache` works.
- [ ] `php artisan route:cache` works, if route closures are not blocking it.
- [ ] `php artisan view:cache` works.
- [ ] `php artisan storage:link` documented.
- [ ] Shared hosting deployment notes are updated.
