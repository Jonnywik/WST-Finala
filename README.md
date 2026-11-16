# AdopIT! — Pet Adoption & Vet Care Registry

A small shelter system built with **PHP, HTML and vanilla JS** (Bootstrap 5 bundled locally), running on **XAMPP / localhost** with MySQL.

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
