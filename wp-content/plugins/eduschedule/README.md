## Version 4.5.8

- Hard overrides reset-password shortcodes using `pre_do_shortcode_tag`, so conflicting theme/form plugin callbacks cannot show the wrong Forgot Password form when the URL contains `?es_action=rp&key=...&login=...`.
- Adds unique fallback shortcode `[ivy_reset_password]` for reset password pages.

EduSchedule 4.5.6
=================

- Fixes reset-password links opening the wrong Forgot Password form on pages using [es_reset_password_form].
- Re-registers EduSchedule shortcodes late to avoid shortcode conflicts.

# EduSchedule v4.5.2

A modern booking platform for educators, coaches, and consultants. Frontend register/login/dashboard with a pink theme, dark indigo admin, slot-based bookings (1:1 / Group / Open / Personal), Zoom auto-create, country-aware timezones, and **public pricing page with inline Stripe Elements and Monthly / Yearly billing**.

> Designed to run **alongside** the original Course Booking Calendar (v2.x) — separate plugin slug, separate database tables, no conflicts.


## What's New in v4.5.2

- **Single Course selector** in After Call and Renew. The selected course is saved on the student and used in mail subject/body.
- **Renew tab package filtering.** Active, non-expired packages with sessions left are hidden and blocked server-side until expiry/completion.
- **Package-wise attendance filter** on 1:1 student attendance so each session clearly shows its linked package.
- **Group Course dropdown** added to group create/edit and group schedule flow.
- **Schedule, files and videos remain merged** in the Schedule tab with common upload and per-session upload support.
- **Frontend Files & Videos library** now shows package-level/common uploads first, followed by individual and group session material.
- **Renewal mail fixed.** Manual renewals now create a valid paid payment row, store package/course snapshots and fire student/admin emails correctly.

## What's New in v4.3.3

- **Yearly toggle now bills for the discount months.** On the public pricing page, switching to the discounted ("yearly") tab now charges — and grants access/sessions for — the package's configured **discount months**, not the package's full default duration. So a package with "5 discount months" is billed and activated for 5 months. The package card, the live order summary, the amount actually charged in Stripe, and the access window (`valid_until`) all agree, driven by a single `effective_term_months()` helper so they can never drift apart.
- **"+ Schedule" button on Group pages.** Groups can now create a session directly from the group detail page (header **+ Schedule** button and one in the Schedule tab), opening the same scheduling popup the 1:1 page uses — it books every group member and auto-creates the Zoom meeting. The 1:1 flow is unchanged.
- **Advanced group attendance: bulk actions.** Each session in the group **Attendance** tab now has a *Mark all* toolbar — **Present**, **Absent (no permission)**, **Absent (with permission)**, and **Clear all** — that marks every member for that session in one request, then live-updates the per-row buttons, the "N/M marked" counter, and the group's Total/Used/Left session panel without a reload. The existing per-member marking still works exactly as before.
- **Group package details match the 1:1 page.** The group **Package** tab now shows the same fields as the 1:1 student page (Package, Sub Heading, Duration, Monthly/Total/Used/Remaining Sessions, Package Discount, description).
- **Package + Course shown first on the Package tab.** Both the 1:1 and group **Package** tabs now lead with a header showing the **Package name** and the **linked Course name(s)** together.
- **Course name in After-Call emails.** The student's (or group's) linked course name is now included in both After-Call emails — in the **subject** and the **body**.
- **Standalone reset-password page.** A new `[eduschedule_reset]` shortcode hosts the entire self-service reset flow (request link → branded email → set new password) on its own page, so the custom reset functionality is fully self-contained in the plugin and no longer depends on the login page being configured. *Auto-Create Frontend Pages* now also creates a **Reset Password** page.

## What's New in v4.1.8

- **Fixed attendance double-counting (the "3 sessions but 15 used" bug).** Re-marking a student's status for the same session now correctly **updates** the one existing record instead of inserting a new row every time. The lookup is NULL-safe, so sessions with no slot/group no longer slip past the match and duplicate. Used-sessions is now **recomputed directly from attendance** on every save (one *Present* or *Absent · without permission* = one used session; *Absent · with permission* and unmarked never count), so the counter can never drift or exceed the sessions actually purchased.
- **Automatic repair on upgrade.** A one-time migration collapses any duplicate attendance rows left by the old behaviour (keeping the earliest) and recomputes every paid plan's used-sessions, fixing inflated counts without re-marking anything. (DB version bumped to 1.6.0 so it runs automatically.)

## What's New in v4.1.7

- **1:1 & Group lists redesigned as full-width tables** matching the Students page. Each row shows avatar, email/members, package, sessions left and a **Details** button that opens the student/group full-screen. The narrow sidebar list is kept only inside the detail view.
- **New "Schedule" tab on 1:1 & Group detail pages.** A read-only list of **all** scheduled meetings with full details (date, time, duration, title, type, platform, status, and a Join link / meeting ID). Creating sessions still happens via the existing **+ Schedule** button.
- **Group attendance now tracks per session (bug fix).** Group attendance previously wasn't tied to a real session (`slot_id` was always 0), so it couldn't correctly consume sessions. The Attendance tab now has a **Session selector**; each member's Present / Absent-without-permission / Absent-with-permission is saved against the chosen session and pre-fills its saved state. Present and Absent-without-permission use one session; Absent-with-permission does not.
- **Custom forgot-password flow (no wp-login.php).** "Forgot password?" now opens a branded on-site **reset** page: request a link by email → receive a branded reset email (sent through your SMTP settings) → set a new password on-site. Uses WordPress's own secure, expiring reset keys.
- **Package details unified** across the 1:1, Group and Student-detail pages (Package, Sub Heading, Duration, Monthly/Total/Used/Remaining Sessions, Valid Until, Package Discount, description).
- **Schedule popup defaults to 60 minutes** (still editable).

## What's New in v4.1.6

- **SMTP email delivery (fixes "emails not arriving").** New **Settings → SMTP** panel lets you send all plugin mail (After-Call, booking confirmations, payment receipts, admin copies) through an authenticated SMTP server. This is the reliable fix for hosts where PHP's default `mail()` is silently dropped by Gmail and other providers. For Gmail, use `smtp.gmail.com`, port `587`, TLS, your Gmail address, and a Google **App Password**. A **Send Test Email** button confirms delivery. When SMTP is left off, mail behaves exactly as before.
- **Additional comment shown on each purchase.** On the student detail page (`admin.php?page=eduschedule-students&view=detail&user_id=X`), the **Payments** tab now shows the admin's After-Call *Additional Comment* alongside each purchase — matched to the package that was purchased, with the most recent comment as a fallback. Applies to both 1:1 and Group students (they share this detail page).

## What's New in v4.1.4

- **Fixed: sessions not granted after purchase.** A paid student could show "0 sessions left" because the payment record stored 0 total sessions (it happened when the package's monthly session limit was 0, or for older purchases). Three-layer fix: (1) the session snapshot stored at purchase is now computed correctly, including proportional scaling for a yearly (12-month) purchase; (2) a safety net re-derives sessions from the package at the moment payment is confirmed (inline Elements, hosted Checkout, and webhook paths); (3) a one-time migration backfills sessions on existing paid purchases that were stuck at 0. (DB version bumped to 1.5.0 so this runs automatically on upgrade.)
- **Attendance now supports "with / without permission."** Each session can be marked **Present**, **Absent · no permission**, or **Absent · with permission**. Present and Absent-without-permission count as a *used* session; Absent-with-permission does **not**. Attendance is now the single source of truth for session usage (scheduling/booking no longer pre-consumes a session, preventing double-counting), and the "sessions left" figure updates live as you mark attendance.
- **Videos are uploaded, not linked.** The Videos tab on both 1:1 and Group pages now opens the WordPress media library so you can upload a video file (or pick an existing one) instead of pasting a URL. The chosen attachment's URL, title, and duration are captured automatically.
- **Login / package-access screen polished.** Logged-out visitors are now asked to log in *before* seeing any packages (no package list for guests). A logged-in user who opens a personalised link meant for a different account now sees a professional "this package isn't available on your account" message with a link to their dashboard, instead of a confusing "log in again" prompt.

## What's New in v4.1.3

- **Fixed front-side slot booking (the main bug).** The day-view slot query used an `INNER JOIN` against confirmed bookings, which silently dropped every slot that had **zero** bookings — i.e. brand-new, fully-available slots never appeared when a visitor drilled into a date, so they could never be booked. The day view now uses a `LEFT JOIN` (matching the month view), so available slots show up and are bookable. This affected both the public calendar and the admin day view.
- **Confirm Password on registration.** Both the standalone Register page and the combined Login/Register page now have a "Confirm Password" field with its own show/hide eye toggle. The match is validated in the browser (instant feedback) and again on the server before the account is created.
- **Email sending fixed + made configurable.** Booking and notification emails were sent through bare `wp_mail()` with no `From:` header and `@`-suppressed errors, which most hosts silently drop. All plugin email now goes through a single `ES_Mailer::send()` helper that adds a proper From / Reply-To header (defaulting to `no-reply@yourdomain`), and **logs any delivery failure** to the PHP error log instead of hiding it. New **Settings → Email Notifications** panel: From Name, From Email, Reply-To, an admin-copy toggle, and a custom admin-notification address.
- **Additional comment in confirmation emails.** The note a user types when booking (and the note an admin adds when scheduling) is now included as an "Additional notes" block in both the student and admin emails.
- **Session limit enforced on self-booking.** If a student holds a session-limited package and has used all their sessions, the front-end booking is blocked with a clear message.
- **Clearer booking errors.** A stale security token (common on cached pages) now returns a friendly "Your session expired — please refresh" message on both booking surfaces, instead of a confusing silent failure.

## What's New in v4.1.1

- **Tabbed 1:1 & Group UI restyled to match the WordPress dashboard.** The new pages previously carried dark mockup styling (light text on the light admin background, hard to read). All of it now uses the plugin's own light admin design tokens — white cards, proper text colours, indigo accents, hover states, and a polished usage progress bar — so the pages feel native to wp-admin. The styling lives in `admin.css` as dedicated classes rather than inline styles.
- **Login / Register button fix.** The login form (and the combined auth template) referenced button classes (`btn-primary`, `btn-full`, `btn-link`) that don't exist in the stylesheet, so the "Log in" button and the password-visibility toggle rendered unstyled. All auth forms now use the correct `es-fe-btn es-fe-btn-primary` / `es-fe-btn-link` classes, matching the Register page.

## What's New in v4.1.0

- **Tabbed 1:1 Student & Group pages.** Both admin pages were rebuilt into a list + tabbed-detail layout matching the design guide: **Attendance · Package · Files · Videos · Schedule** for 1:1 students, plus a **Members** tab for groups. The student list shows live "N sessions left," and a "Join Meet" button links to the next upcoming meeting URL.
- **Attendance tracking (new).** Mark each session Present/Absent with a per-session comment; saved instantly. Groups get a per-member roster view.
- **Videos (new).** Attach lesson recordings or video links (title + URL + duration) to a 1:1 student or a group, with quick add/delete.
- **Editable student profile.** Parent, Phone, Source, Goal, Level/Band and Notes are editable inline on the Package tab and persist to user meta.
- **Package usage panel.** Total / Used / Left session counts with a progress bar and "% used · N-month program," on both 1:1 and Group detail.
- Existing group management (create/edit/delete, member add/remove via the group modal), file uploads, and session scheduling are preserved and wired into the new tabs.

## What's New in v4.0.1

- **Yearly toggle now bills for a full 12-month year.** On the public packages page, switching the toggle to *Yearly* multiplies the per-month price by **12** (with the global discount applied per month) and shows the duration as 12 months — both in each card's breakdown and in the order summary. The amount actually charged and the granted access window match. *Monthly* mode still uses the package's own duration.
- **Package details panel on 1:1 & Group pages.** The admin student-detail page and the group-detail page now show a Package panel with **Total / Used / Left** session counts and a usage progress bar (e.g. "58% used · 3 months program"), mirroring the design guide.
- **Mini-calendar "FULL" indicator (Availability Slots page).** The slots-page mini calendar previously labelled every day with slots as "X open" even when they were fully booked. It now counts only slots that still have room; a day whose slots are all full shows **FULL** instead.

## What's New in v4.0.0

- **Monthly-based package pricing.** Package creation now takes a **Monthly Price**, a **Duration (Months)**, and a **Monthly Session Limit**. The total package price is auto-calculated as `Monthly Price × Months` (e.g. ₹5,000 × 3 = ₹15,000), and total sessions as `Monthly Session Limit × Months`. The admin form shows a live-updating total + session count as you type.
- **Sessions tracking for 1:1 and Group.** Each purchase snapshots its session terms (months, monthly limit, total sessions) onto the payment record, so later edits to a package don't change what an existing student already bought. A `used_sessions` counter decrements as sessions are scheduled, and remaining sessions are shown everywhere.
- **Full purchase details after checkout.** The Stripe thank-you screen and the student dashboard's *Your Plan* card now show Package Name, Package Duration, Total Sessions, Monthly Sessions, and Remaining Sessions.
- **Front-end pricing box breakdown.** Each package card shows Duration, Sessions, and **Total payable**. The right-side order summary lists **Monthly Price · Duration · Sessions · Total Payable**, and the full total (`monthly × months`) is what gets charged. Access is granted for the full purchased duration.
- **File uploads on sessions (1:1 + Group).** Upload **PDF, DOC/DOCX, PPT/PPTX, and video** files and attach them to a student (1:1) or a whole group. Files are listed with type, size, and a delete option.
- **Schedule a Session (meeting) from the detail page.** A *Schedule a Session* card on both the student and group pages creates a slot, auto-creates a Zoom meeting (when configured), books the student(s), optionally emails a confirmation, and consumes one session from the student's plan — all in one click.

## What's New in v3.9.7

- **In-page login modal.** Clicking *Select This Plan* while logged out now opens a clean, branded login popup on the same page (no separate `wp-login.php` browser window). On success the page reloads in its logged-in state so the payment form is ready. A *Sign up* link and *Forgot password?* link are included.
- **Payment form is now an always-visible side column.** The payment panel no longer slides in — it sits beside the packages in a 2-column layout (and stacks below them on mobile/tablet). The first/featured package is pre-selected, and choosing a different plan highlights its card and updates the form instantly.
- **Removed the "Payment Method" dropdown** from the payment form — it was redundant since card is the only method.
- **"Yearly (X% off)" labelling.** The toggle and order summary now read "Pay Yearly (10% off)" / "Yearly (10% off)" (the percentage reflects your configured discount). The price shown is still per month, just discounted.
- **Nicely-styled payment errors.** Stripe errors (including the India-exports notice for accounts that can't accept international cards) now render in a clean bordered box with an icon, and any URL in the message is auto-linked.
- **Richer after-call emails.** The student email is now a branded HTML message with the staged packages, a *Select Your Package* button, and the admin's additional comments. The admin copy is also HTML and includes the additional comments plus a *View Student Profile* button linking straight to the student detail page.

> **Note on India payments:** the "only registered Indian businesses can accept international payments" message comes from Stripe at the account level (per RBI rules), not from this plugin. It can't be removed in code — it depends on the Stripe account type and the buyer's card origin. The plugin now just presents that message cleanly.

## What's New in v3.9.6

- **Packages are public — login only at checkout.** The `[eduschedule_packages]` shortcode now shows all 3 packages to everyone, including logged-out visitors. The old "Login Required" wall is gone. When a logged-out visitor clicks **Select This Plan**, a login popup opens; after they sign in, the page reloads in its logged-in state and the secure payment panel becomes available.
- **Student dashboard now shows the active plan.** A new **Your Plan** section on the front-end dashboard lists every paid package with type (1:1 / Group), amount paid, plan length, and valid-until date.
- **New Admin → Payments screen.** Lists every purchase with the buyer's name, email, course/package, 1:1-or-Group type, amount, plan length, status, and date — plus a "Collected" total, status filter, and search.
- **Students locked out of /wp-admin.** Subscriber-level users are redirected to the front-end dashboard if they try to reach the WordPress admin, and the admin toolbar is hidden for them. Admins (and any role granted the plugin capability) are unaffected.
- **Split Stripe card fields.** The payment form now uses Stripe's individual Elements: **Card Number on its own row**, then **Expiry + CVC side-by-side** on the second row.
- **Billing address captured for India (RBI) compliance.** The payment form collects address, city, state, postal code, and country (pre-filled from the logged-in user's profile), passes them to Stripe's `billing_details`, saves them to the user profile, and attaches them to the PaymentIntent — required for INR/India card payments.
- **Yearly toggle = discounted monthly price.** The toggle no longer multiplies by 12 or shows a separate yearly figure. Instead it applies the global discount % to the same monthly price (e.g. ₹1,000/mo → ₹900/mo with a 10% discount), shown with the original price struck through and a *Save X%* badge. Access still grants one month.

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
