# Laravel 10 + PHP 8.2 Engineering Rules

Use these rules for all backend and full-stack implementation in this repository.

## Runtime constraint

PHP 8.2 is the maximum allowed runtime.

Do not:

- Use PHP 8.3 or PHP 8.4 syntax/features.
- Install packages that require PHP 8.3+.
- Upgrade Laravel above version 10.
- Assume the production server has Node.js, npm, Chrome, Puppeteer, or long-running workers.

Do:

- Keep `composer.json` compatible with PHP 8.2.
- Use Composer platform config when needed:

```json
"config": {
  "platform": {
    "php": "8.2.0"
  }
}
```

- Run `composer validate` after editing Composer files.
- Run `composer check-platform-reqs` before deployment when possible.

## Framework

Use:

- Laravel 10
- MySQL
- Blade
- Vue 3 only for editor page
- Konva.js
- Laravel Excel
- DomPDF

Avoid:

- Laravel 11/12
- Inertia unless already intentionally chosen
- Livewire for the canvas editor
- Browsershot / Puppeteer
- WebSockets
- Tenancy packages
- Billing/subscription packages
- Unnecessary admin panel generators unless approved

## Code style

- Follow Laravel conventions.
- Use PSR-12 formatting.
- Use descriptive English class and method names.
- Keep Arabic text in lang files, Blade views, or UI constants, not in random backend logic.
- Keep controllers thin.
- Prefer Form Request classes for validation.
- Use policies for access control when needed.
- Use services for complex logic.

## Suggested service boundaries

Use services only when they reduce complexity:

- `app/Services/FloorPlanner/FloorplanLayoutService.php`
- `app/Services/FloorPlanner/SeatGenerationService.php`
- `app/Services/Guests/GuestImportService.php`
- `app/Services/Exports/FloorplanPdfExportService.php`
- `app/Services/Exports/GuestListExportService.php`

Avoid creating interfaces/repositories unless there is a real need.

## Database rules

- Use clear migration names.
- Add foreign keys and indexes.
- Use nullable foreign keys only when the relation is truly optional.
- Use soft deletes only where business recovery is needed.
- Use JSON columns for flexible editor layout data.
- Store important reporting data such as seat assignments in relational tables.

Recommended simple approach:

- `floorplans.design_json` stores the visual layout.
- `seats` stores generated seat records and optional `guest_id`.
- `guests` stores guest data.
- `guest_types` stores categories/colors/icons.

This keeps the canvas flexible and reports reliable.

## Validation

Validate:

- Event name, date, and location.
- Floor plan dimensions and unit.
- Uploaded background image type and size.
- Excel file type and size.
- Guest name.
- Guest type.
- Seat assignment rules.

Seat assignment must prevent:

- Assigning two guests to the same seat.
- Assigning the same guest to multiple seats in the same floor plan unless the old assignment is cleared.
- Assigning a guest from another event.

## Authorization

At minimum:

- All application routes must require authentication.
- Users can only access their own events/floorplans unless an admin role is explicitly added.
- Use policies or scoped queries to prevent data leaks.

## File uploads

- Store images in `storage/app/public`.
- Use `php artisan storage:link`.
- Validate extensions and MIME types.
- Limit image sizes.
- Consider resizing large background images.
- Never trust original file names.

## PDF export

Use DomPDF.

Because DomPDF does not render a live canvas directly:

1. Export the Konva canvas from Vue as an image data URL.
2. Send the image to Laravel.
3. Generate a PDF containing event info, floor plan info, seating count, and the image.

Do not require server-side Chrome.

## Excel import

Use Laravel Excel.

Import flow:

1. Upload file.
2. Validate file.
3. Parse rows.
4. Preview rows if practical.
5. Map or create guest types.
6. Skip duplicates.
7. Return Arabic success/error summary.

## Testing

Add practical tests for:

- Event CRUD authorization.
- Floorplan save/load.
- Guest creation.
- Guest import validation.
- Seat assignment rules.
- Export endpoint basic response.

Do not block progress by writing excessive tests for canvas internals that are better tested manually.

## Deployment to shared hosting

Production may not have SSH/npm.

Prepare locally:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Upload:

- Laravel files
- `vendor/` if Composer is unavailable on hosting
- `public/build`
- `.env` configured for production
- `storage` folders as needed

Do not deploy `node_modules`.
