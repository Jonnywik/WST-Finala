# AdopIT! — Pet Adoption & Vet Care Registry

A small shelter system built with **PHP, HTML and vanilla JS** (Bootstrap 5 bundled locally), running on **XAMPP / localhost** with MySQL.

The final design is **Night Shift**, the version selected from <http://localhost:8003/>. It is the active application at the repository root on `master`. Previous designs are stored as source snapshots in [`archive/`](archive/README.md).

## Run it

1. Copy this folder to `C:\xampp\htdocs\adopit`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open <http://localhost/adopit/setup.php> and click **Install database**
   (or import `sql/database.sql` in phpMyAdmin).
4. Open <http://localhost/adopit/>.

Database settings (host, user, password) live in `config/db.php`. The defaults match a stock XAMPP install (`root`, no password).

## How it meets the project requirements

| Requirement | Where |
|---|---|
| Own small system | Pet adoption and vet care registry for an animal shelter |
| Create, Read, Update, Delete | Pets (`create.php`, `view.php`, `edit.php`, `delete.php`), vet visits (`vet_care.php`), adoption inquiries (`adoptions.php`) |
| Welcome page with records from the database | `index.php`: greeting, Pet of the Day, live counts and the full pets table |
| Localhost | XAMPP Apache + MySQL (`adopit_db`) |
| PHP, HTML and JS only | No frameworks; PDO for the database, `assets/app.js` for search, filters and validation |
| Optional Bootstrap | Bootstrap 5.3 in `assets/vendor/bootstrap/` (works offline) |

## Demo walkthrough

1. **Welcome page** — bento hero with the MySQL status pill, Pet of the Day (rotates daily through Available pets) and four live stat tiles.
2. **Search and filter** — type in the search box or pick a species/status; the table filters instantly.
3. **Create** — *Register Pet*; the next `PET-XXX` code is suggested. Try submitting it empty to show validation.
4. **Read** — click a pet name for its profile, vet timeline and inquiries.
5. **Update** — *Edit* on a profile or the pencil icon in any table.
6. **Delete** — the bin icon opens a confirmation dialog. Deleting a pet also removes its vet logs and inquiries.
7. **Vet Care Logs** — log a Vaccination with the box ticked and the pet becomes *Up to Date*.
8. **Adoption Inquiries** — set an inquiry to *Meet & Greet* or *Approved* and the pet becomes *Pending Adoption* or *Adopted*.

## Files

```
config/db.php            PDO connection and timezone
includes/functions.php   option lists, validation, CSRF, flash messages
includes/svg_helpers.php inline SVG icons, species avatars, status pills
includes/header.php      pill navigation     includes/footer.php  delete dialog + scripts
includes/pet_form.php    form shared by create.php and edit.php
index.php  create.php  view.php  edit.php  delete.php  vet_care.php  adoptions.php
setup.php                one-click installer / reset
sql/database.sql         schema + sample data
assets/app.css  assets/app.js  assets/vendor/bootstrap/
design-system/adopit/    the design rules the styles follow
```

Every query uses prepared statements, all output is HTML-escaped, and every form that changes data carries a CSRF token.

## Motion ("Night Shift" redesign, branch `redesign-motion`)

All plain CSS and vanilla JS in `assets/app.css` and `assets/app.js` (Part 2), no animation library. With "reduce motion" switched on in the OS, none of it runs and every section shows in its final state.

**Scroll**

| Effect | How it works |
|---|---|
| Hero pins, shrinks and rounds off; title lifts; avatars drift upward | The hero sits in a tall `.stage` with a sticky inner box. One `requestAnimationFrame` loop turns how far you are through the stage into `scale`, `border-radius` and `translate`. |
| Page colour changes per section (ink, plum, sun yellow) | An `IntersectionObserver` watching the middle line of the screen swaps `--bg`/`--fg` on `<html>`; `body` transitions its colours. |
| Statement words light up in reading order | Each word is a `<span>`; the loop lights the first *n* words, where *n* follows the paragraph's position on screen. |
| "Meet the crew" cards slide sideways while you scroll down | The section is made as tall as the card row is wide; the sticky row is translated left by the scroll progress. Counter and bar follow it. On phones it is a normal swipe strip. |
| Headings rise letter by letter, rows and panels fade up, numbers count up, bars fill | `IntersectionObserver` adds `.is-in` once; CSS transitions do the rest, staggered with `--i`. |
| Marquee tapes speed up and reverse with scroll direction; tapes and card row lean with speed | The loop measures scroll velocity and adds it to marquee speed and a `skewX`/`skewY`. |
| Spinning "Adopt · Don't shop" badge speeds up with scroll | Same velocity value drives its rotation. |
| Nav hides when scrolling down, returns when scrolling up | The loop compares this frame's scroll position with the last one. |
| Footer "AdopIT!" letters rise one after another | The loop maps footer progress to a per-letter `translateY`. |
| Progress line and back-to-top ring | Scroll position written to `--prog`; the ring is an SVG `stroke-dashoffset`. |

**Hover**: rolling nav letters, button fill growing from the point the pointer entered, magnetic buttons, 3D tilt and a spotlight glow on cards, avatar bounces, a floating preview card that trails the pointer over table rows, codes and numbers that scramble, icon-specific gestures (eye blinks, pencil scribbles, bin shakes), a cursor ring that grows and shows labels ("View", "Add", "Scroll").

**Click**: paw-print burst on the main buttons, ripples on the others, whole table rows open the pet, the delete dialog springs in and its icon shakes, success toasts slide in and dismiss themselves (hover pauses the timer).

**Page changes** (Chrome and Edge): the next page opens as a circle growing from where you clicked, the nav dot slides to the new tab, and a pet's avatar morphs into the big avatar on its profile. Filtering the tables makes the remaining rows glide into place. Other browsers just navigate normally.

**First visit**: a yellow loading screen counts to 100% and wipes away (once per browser tab).
