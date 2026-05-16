# EduSchedule v3.5.0

A modern booking platform for educators, coaches, and consultants. Frontend register/login/dashboard with a pink theme, dark indigo admin, slot-based bookings (1:1 / Group / Open / Personal), Zoom auto-create, and country-aware timezones.

> Designed to run **alongside** the original Course Booking Calendar (v2.x) — separate plugin slug, separate database tables, no conflicts.

## What's New in v3.5.0

- **Calendar → Add Slot** can now create a *direct meeting*: when slot type is **1:1** pick one user, when **Group** pick multiple users. The slot is created, a Zoom meeting is auto-generated, and a confirmation email goes out — all from a single dialog. (Plain availability slots still work for **Open Slot** and **Personal** types, and the **My Slots** page is unchanged.)
- **All Bookings → Join Zoom** column now renders a clear button. If the booking has a host `start_url`, the button reads *Join as Host* and opens admin straight into the meeting as host. The Zoom meeting ID is shown beneath the button.
- **New Students tab** at `admin.php?page=eduschedule-students`. Lists every registered user with bookings count, has an **Add Student** modal (creates a real WP user with phone / parent / reference / comment meta and optionally sends a "set your password" email), and a **Details** modal showing the user's full booking history with status pills and Join Zoom links.

## What's New in v3.0

- **Frontend pages** — pink-themed register, login, and user dashboard via shortcodes
- **No weekly schedule** — slots are added one-by-one with date + start time + duration + notes
- **4 slot types** — 1:1 Call, Group Call, Open Slot, Personal (admin-only blocker)
- **Country-aware timezones** — admin sets a "Work Country" → users see times converted to their own country's timezone
- **Dark indigo admin** matching the EduSchedule reference UI
- **Stripped down** — only Calendar, My Slots, All Bookings, Zoom Integration, Settings (no holidays, templates, etc.)

## Installation

1. Upload `eduschedule.zip` via **Plugins → Add New → Upload Plugin**
2. Activate **EduSchedule**
3. Go to **EduSchedule → Settings**
   - Set your **Work Country** (this defines your timezone)
   - Click **Auto-Create Frontend Pages** to create Login / Register / Dashboard pages
   - Set the dropdown values for those page IDs
4. (Optional) Go to **EduSchedule → Zoom Integration** and add S2S OAuth credentials
5. Go to **EduSchedule → My Slots** and add some availability

## Shortcodes

| Shortcode | Purpose |
|-----------|---------|
| `[eduschedule_login]` | Pink-themed login form |
| `[eduschedule_register]` | Pink-themed registration with country selection |
| `[eduschedule_dashboard]` | User's bookings + slot booking calendar |
| `[course_booking_calendar]` | **Public** booking calendar — anyone can browse, must log in to book |
| `[eduschedule_calendar]` | Alias for `[course_booking_calendar]` |

### `[course_booking_calendar]` attributes

```
[course_booking_calendar
  title="Book a Session"
  subtitle="Pick a date that works for you."
  types="1to1,group,open"
  months_ahead="3"
  show_legend="yes"]
```

| Attribute | Default | Notes |
|-----------|---------|-------|
| `title` | "Book a Session" | Heading text |
| `subtitle` | "Pick a date, choose a time…" | Set to `""` to hide |
| `types` | `1to1,group,open` | Comma-separated. `personal` is always blocked |
| `months_ahead` | `12` | How far ahead users can browse |
| `show_legend` | `yes` | Show the colored type legend (`yes`/`no`) |

Guests can browse the calendar freely, but clicking a slot opens a login prompt that bounces them to your login page (with `redirect_to` so they come back here after login).

## Slot Types

- **1:1 Call** (blue) — Capacity 1, one user only
- **Group Call** (green) — Admin sets capacity (e.g. 5), multiple users can join
- **Open Slot** (purple) — Drop-in style, configurable capacity
- **Personal** (pink) — Admin's own time, hidden from users (blocks other slot creation)

## Timezone Handling

Slot times are stored in the **Work Country** timezone (set in Settings). When a user views slots, they're automatically converted to the user's timezone (set during registration based on their country selection).

Example: Admin in India (Asia/Kolkata) creates a 9:00 AM slot. A user in the US (America/New_York) sees it as 11:30 PM the previous day during winter.

## Zoom Setup

1. Go to https://marketplace.zoom.us/develop/create → **Server-to-Server OAuth**
2. Required scopes:
   - `meeting:write:meeting:admin`
   - `meeting:write:meeting`
   - `meeting:delete:meeting:admin`
   - `user:read:user:admin`
3. Activate the app
4. In WP: **EduSchedule → Zoom Integration** → paste Account ID, Client ID, Client Secret
5. Click **Test Connection**

When users book a slot whose platform contains "Zoom", a unique Zoom meeting is auto-created and the join URL is included in the confirmation email.

## Database Tables

- `wp_es_slots` — Availability slots
- `wp_es_bookings` — User bookings (with Zoom meeting refs)

These are completely separate from any v2.x tables (`wp_cbc_*`), so the two plugins coexist safely.

## File Structure

```
eduschedule/
├── eduschedule.php           Plugin bootstrap
├── includes/
│   ├── class-es-activator.php   DB schema
│   ├── class-es-helpers.php     Settings, timezones, slot types
│   ├── class-es-db.php          DB queries
│   ├── class-es-zoom.php        Zoom S2S OAuth integration
│   ├── class-es-mailer.php      Booking emails
│   ├── class-es-shortcodes.php  Frontend shortcodes
│   ├── class-es-ajax.php        Frontend AJAX handlers
│   └── class-es-auth.php        Login redirects
├── admin/
│   ├── class-es-admin.php       Admin menu, page handlers
│   └── class-es-admin-ajax.php  Admin AJAX (slot CRUD)
├── public/
│   ├── css/
│   │   ├── admin.css            Dark indigo admin theme
│   │   └── frontend.css         Pink/dark frontend theme
│   └── js/
│       ├── admin.js
│       └── frontend.js
└── templates/
    ├── admin-calendar.php       Month view (image 3)
    ├── admin-slots.php          Mini-cal + slot list (image 2)
    ├── admin-bookings.php       All bookings table
    ├── admin-zoom.php           Zoom integration
    ├── admin-settings.php       General settings
    ├── frontend-login.php       Pink login (image 5)
    ├── frontend-register.php    Pink register (image 4)
    └── frontend-dashboard.php   User dashboard
```

## Coexistence with Course Booking Calendar v2.x

EduSchedule v3.0 uses a different plugin slug, namespace prefix (`ES_`), table prefix (`wp_es_`), option names (`es_settings`, `es_zoom_settings`), and admin menu slug (`eduschedule`). Both plugins can be active simultaneously without conflicts.

Pick whichever fits the use case — or run both for different audiences.

## Version

3.0.0 — Initial release
