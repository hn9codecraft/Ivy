# EduSchedule v3.9.2

A modern booking platform for educators, coaches, and consultants. Frontend register/login/dashboard with a pink theme, dark indigo admin, slot-based bookings (1:1 / Group / Open / Personal), Zoom auto-create, country-aware timezones, and **logged-in pricing page with inline Stripe Elements and multi-currency Monthly / Yearly billing**.

> Designed to run **alongside** the original Course Booking Calendar (v2.x) — separate plugin slug, separate database tables, no conflicts.

## What's New in v3.9.2

- **Inline payment is back — no redirect.** *Select This Plan* now slides the secure payment panel into the same page (Stripe Elements card field, name, email, order summary). Card details enter Stripe-hosted Elements (PCI-compliant — your server never sees the card). After paying, the form is swapped for an inline thank-you state showing plan, amount paid, and active-until date, without leaving the page.
- **Login required to view and buy packages.** The `[eduschedule_packages]` shortcode now requires the visitor to be logged in. Anonymous visitors see a clean "Login Required" card with a *Log in to continue* button that returns them to the pricing page after authenticating (and a *Sign up* link if registration is open).
- **Personalised links are bound to the recipient.** A `?user_id=X&token=Y` link only works for the user it was issued to. If a different account is logged in, they see a clear message: "This personalised link belongs to a different account." Expired tokens get their own message: "This link has expired or is invalid."
- **Logged-in self-serve.** Any logged-in user can buy any active package without a personalised link — the page mints a short-lived token on the fly for the inline payment flow.
- **"Save X%" badge on every yearly price.** When the global Yearly Discount is set, each card shows a green Save X% badge next to the yearly amount so the discount is visible at a glance, not just hidden in the toggle label.
- **Defence-in-depth on payment AJAX.** `stripe_create_intent` and `stripe_finalize_intent` now require a logged-in session AND verify that the form's `user_id` matches the current user before charging. Prevents a logged-in user from purchasing on behalf of someone else by tampering with hidden fields.
- **Email on purchase** (already in 3.8 — unchanged): student gets a styled HTML receipt; admin gets a plain-text notification with all order details.
- **Email on After Call submit** (already in 3.7 — unchanged): student gets the personalised package selection link plus any *Additional Comments* the admin typed; admin gets a summary email.

## What's New in v3.9.0

- **Public pricing page with self-serve Stripe Checkout** *(superseded in 3.9.2 — login is now required)*.
- **Hosted Stripe Checkout default** *(superseded in 3.9.2 — inline Elements is the default again)*.
- **Monthly / Yearly toggle on public pricing pages.** *(retained — now login-gated)*.
- **Admin enters only the monthly price.** Frontend toggle automatically computes yearly = `monthly × 12 − global discount %`.

## What's New in v3.8.0

- **Inline on-page Stripe payment form** matching the personalised "Decision Hub" design. *(Kept available in 3.9 as a legacy fallback; hosted Checkout is now preferred — see above.)*
- **Pay Monthly / Pay per Semester toggle** with a sliding pill switch directly under the package grid. Toggling updates every card's displayed price AND the amount charged in Stripe, so what the student sees on the card is exactly what gets charged. The discount % shown in the toggle label comes from *Settings → Currency & Billing → Yearly Discount*.
- **Simplified package creation form.** The admin package modal no longer asks for *Default Billing Cycle*, *Yearly Price (optional)*, or the *Active (visible to students)* checkbox — new packages are active by default, and yearly pricing is derived globally from `monthly × 12 − yearly_discount %`.
- **After Call form is now always visible** on the student detail page. The toggle button is gone — open a student, scroll past the share-link card, and the After Call section is already there waiting. Email triggers fire to both student and admin the moment you click *Submit & Convert*.
- **Shortcode attributes for the new layout** — `[eduschedule_packages monthly_label="Pay Monthly" semester_label="Pay per Semester" period_unit="month" period_unit_yearly="semester" brand_name="Ivy Quest Academy" brand_logo="https://.../logo.png" recommended="2" recommendation_text="Based on our consultation, I recommend the Subject Mastery Protocol."]`.

## What's New in v3.7.0

- **After Call is now an inline form** on the student detail page — no more popup.
- **Stripe Checkout integration** (hosted page flow — kept as fallback in v3.8.0).
- **Multi-currency on packages** — INR, USD, EUR, GBP, AUD, CAD, AED, SGD, JPY, NZD.
- **12-month / Yearly billing with one-time payment** — `valid_until = paid_at + 12 months`.
- **Email triggers on every form submit** — After Call, payment success, non-Stripe selection.

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
