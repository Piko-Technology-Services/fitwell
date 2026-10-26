# FITWELL STUDIO LIMITED — website

*Move Together. Live Better.*

This is a PHP and MySQL site that runs directly from XAMPP. It needs no Composer, no build step and no framework.

## First-time setup
1. Open the XAMPP control panel and start **Apache** and **MySQL**.
2. Go to `http://localhost/fitwell%20studio/install.php`.
3. Create your administrator account. Leave "sample content" ticked if you want five sample classes and three weeks of sample sessions.
4. You'll then be taken to the Studio admin at `admin/`.

The database settings live in `app/config.php`. The XAMPP defaults are user `root` with an empty password and the database `fitwell_studio`.

## What to edit
| What | Where |
|---|---|
| Contact details, social links, opening hours | `app/config.php` → `company`, `social` |
| Membership names, benefits and **prices** | `app/config.php` → `memberships` |
| Cancellation cutoff, timezone, booking window | `app/config.php` |
| Instructor profiles (About page) | `app/config.php` → `instructors` |
| Photography | `app/images.php`: replace each `unsplash:` source with your own file, e.g. `assets/img/studio/hero.jpg` |
| Classes and sessions | Studio admin (`/admin`) |

Any company detail left as `null` shows on the site as a dashed, italic placeholder ("· to be confirmed"). That way nothing invented looks like real company information. Once everything real is filled in, set `show_placeholder_markers` to `false`.

## Placeholder content to replace before launch
- **Photography.** The images are free Unsplash stock photos (credits in `app/images.php`). Swap them for real Fitwell studio photos.
- **Sample classes and sessions.** These are created by the installer and can be edited or hidden in the admin.
- **Prices, contact details, socials and instructors.** These are not supplied yet and are marked as placeholders.
- **Privacy Policy and Terms.** These are drafts and need a legal review.

## Features (initial release)
- Member registration, login and logout, plus a profile page with upcoming and past bookings.
- Class browsing, a timetable with day and week navigation, booking and cancellation.
- Server-side rules: no duplicate bookings, no overbooking (row lock in a transaction), no booking of past or cancelled sessions, and cancellation allowed only up to the cutoff.
- Security: passwords stored with `password_hash`, CSRF tokens on every form, prepared statements, session ID regenerated on login, and throttling after repeated failed logins.
- Minimal admin: create and edit classes, publish and hide them, schedule sessions (with weekly repeat), set capacity, view attendees, and cancel sessions or bookings.

Not included yet (by design): payments, QR attendance, corporate portals and loyalty features.

## File map
```
index.php, classes.php, class.php, timetable.php, membership.php,
corporate.php, about.php, register.php, login.php, account.php,
book.php, cancel.php, logout.php, privacy.php, terms.php, install.php
admin/            minimal studio admin
app/              config, bootstrap, booking logic, schema, partials (blocked from the web)
assets/css/fitwell.css   design system
assets/js/fitwell.js     interactions (no dependencies)
assets/img/       logo variants and favicon
```

The original Skilline template files (`css/skilline.css`, `img/*`) are no longer used and can be deleted. The same goes for `_photos.html`.
