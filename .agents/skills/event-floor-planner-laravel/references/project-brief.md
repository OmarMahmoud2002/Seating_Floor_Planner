# Project Brief: Arabic Event Floor Planner

## Goal

Build an internal Arabic RTL web application for one company to create event floor plans, arrange tables/seats, manage guests, assign guests to seats, and export printable PDFs and guest lists.

This is not a large SaaS application. Keep the implementation clean, polished, and maintainable without unnecessary enterprise complexity.

## Core users

- Admin
- Internal event coordinator / staff

No public booking flow is required unless requested later.

## Main modules

### 1. Authentication

Use Laravel authentication.

Required:

- Login
- Logout
- Optional register depending on project decision
- Password reset if easy and already available

### 2. Dashboard

Show:

- Total events
- Upcoming events
- Total guests
- Recent floor plans
- Quick action: إنشاء حدث جديد

### 3. Events

Fields:

- name
- type
- event_date
- location
- description
- user_id

Features:

- List
- Search
- Create
- Edit
- Delete
- View details

### 4. Floor plans

Fields:

- event_id
- name
- width
- height
- unit: meter or foot
- paper_size: A4 or Letter
- orientation: portrait or landscape
- background_image
- design_json

Features:

- Create floor plan
- Upload optional background image
- Open editor
- Save layout
- Export PDF

### 5. Floor planner editor

Use Blade page that mounts Vue 3.

Layout:

- Top toolbar
- Left element library
- Center Konva canvas with grid
- Right guest list

Editor features:

- Add tables
- Add stage
- Add walls
- Add doors
- Add aisles
- Add VIP zones
- Drag elements
- Resize elements
- Rotate elements
- Duplicate/delete elements
- Zoom in/out
- Optional snap to grid
- Save/load JSON

### 6. Tables and seats

When adding a table, allow choosing:

- table name/number
- shape: round, rectangle, square, long banquet, theater rows
- number of seats
- color/category
- seat numbering

The system should auto-place seats:

- Round table: circular distribution
- Rectangle table: distribution along sides
- Theater rows: straight rows
- Square table: even distribution around edges

Each seat should have:

- seat_key or code
- table_key/name
- x/y
- rotation
- status
- nullable guest_id

### 7. Guests

Fields:

- event_id
- guest_type_id
- name
- phone
- email
- notes

Guest list should show:

- name
- type
- assigned table
- assigned seat
- action to unassign/delete

### 8. Guest types

Default types:

- عادي
- VIP
- عائلة
- أصدقاء
- موظف
- إعلام
- راعي
- ذوي احتياجات خاصة

Fields:

- event_id nullable or global default
- name_ar
- color
- icon
- sort_order

Allow admin to create/edit/delete types.

### 9. Seating assignment

User flow:

1. Guest appears in the right guest list.
2. User drags guest onto an empty seat.
3. Seat becomes assigned.
4. Guest name appears on or near the seat.
5. Guest card shows table and seat.
6. Seated counter updates.
7. User can unassign guest.

Validation rules:

- Seat must belong to the same floor plan.
- Guest must belong to the same event.
- Seat cannot already have another guest.
- Guest cannot already be seated in the same floor plan unless reassigned.

### 10. Excel import

Columns:

- name
- phone
- email
- type
- notes

Behavior:

- Validate file type and size.
- Validate name.
- Map existing guest types.
- Create new guest type only if allowed.
- Skip duplicates.
- Return Arabic summary.

### 11. Exports

Floor plan PDF:

- Event name
- Floor plan name
- Event date
- Hall dimensions
- Seated count
- Canvas image
- Optional summary

Guest list export:

- Guest name
- Type
- Table
- Seat
- Notes

## Recommended database tables

- users
- events
- floorplans
- guest_types
- guests
- seats

Optional later:

- floorplan_versions
- floorplan_exports
- activity_logs

Avoid adding these until needed.

## Suggested routes

Use authenticated routes.

Web pages:

- `GET /dashboard`
- `GET /events`
- `GET /events/create`
- `POST /events`
- `GET /events/{event}`
- `GET /events/{event}/edit`
- `PUT /events/{event}`
- `DELETE /events/{event}`

Floor plans:

- `GET /events/{event}/floorplans/create`
- `POST /events/{event}/floorplans`
- `GET /floorplans/{floorplan}/editor`

Editor API:

- `GET /floorplans/{floorplan}/data`
- `POST /floorplans/{floorplan}/save`
- `POST /floorplans/{floorplan}/seats/assign`
- `POST /floorplans/{floorplan}/seats/unassign`
- `POST /floorplans/{floorplan}/export-pdf`

Guests:

- `GET /events/{event}/guests`
- `POST /events/{event}/guests`
- `PUT /guests/{guest}`
- `DELETE /guests/{guest}`
- `POST /events/{event}/guests/import`
- `GET /events/{event}/guests/export`

Guest types:

- `GET /events/{event}/guest-types`
- `POST /events/{event}/guest-types`
- `PUT /guest-types/{guestType}`
- `DELETE /guest-types/{guestType}`

## Implementation phases

### Phase 1

Laravel setup, auth, dashboard, Arabic RTL layout, brand CSS tokens.

### Phase 2

Events CRUD and event details.

### Phase 3

Floorplan CRUD with dimensions and background image upload.

### Phase 4

Basic Vue + Konva editor: grid, add/move simple table, save/load JSON.

### Phase 5

Table builder and automatic seat generation.

### Phase 6

Guests and guest types CRUD.

### Phase 7

Drag guest to seat, assignment validation, seated counter.

### Phase 8

Excel import.

### Phase 9

PDF floorplan export and guest list export.

### Phase 10

UI polish, validation, tests, deployment checklist.

## Non-goals

Do not build unless explicitly requested:

- Multi-company tenancy
- Billing/subscriptions
- Public ticket booking
- Real-time collaboration
- WebSockets
- Advanced analytics
- Mobile app
- Large permission matrix
