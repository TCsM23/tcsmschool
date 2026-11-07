# Online Enrollment System

PHP 8 + MySQL/MariaDB enrollment app for **[SCHOOL NAME]**. No framework, no CSS/JS libraries.

## Setup (XAMPP)

1. Copy this folder to `C:\xampp\htdocs\tcsmschool`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open phpMyAdmin → **Import** → choose `database.sql` → Go.
   This creates the `enrollment_db` database with sample data.
4. If your folder name or MySQL password differs, edit `config/config.php`
   (`BASE_URL`, `DB_USER`, `DB_PASS`).
5. Visit http://localhost/tcsmschool/

## Seeded logins

| Role    | Username    | Password     |
|---------|-------------|--------------|
| Admin   | `admin`     | `admin123`   |
| Student | `jdelacruz` | `student123` |
| Student | `mreyes`    | `student123` |
| Student | `asantos`   | `student123` |

Change the admin password after the first login (Edit admin account).

## Where to change things

| What                                         | File                     |
|----------------------------------------------|--------------------------|
| School name, address, school year, DB login  | `config/config.php`      |
| Tuition per unit, lab/tutorial and fixed fees | `config/fees.php`        |
| Courses, programs, mission, vision, events   | `config/content.php`     |
| Logo / campus photo                          | `assets/img/logo.png`, `assets/img/campus.jpg` (keep the file names) |

## Fee rules

- Tuition = total units × ₱296
- +₱1,500 per subject with a lab, +₱1,500 per subject with a tutorial
- Fixed: entrance ₱2,050, internet ₱150, miscellaneous ₱50

## Structure

```
config/     settings, fees, page content
includes/   db connection, auth/session + CSRF, helpers, icons, header/nav/footer
admin/      admin pages
student/    student pages
assets/     css (base.css + one file per page), js, fonts, img
```

CSS classes follow `(page)-(what it is)-(its role)`, e.g. `enroll-subjects-grid`.
Shared components use `global-`, e.g. `global-button-primary`.

Font: Plus Jakarta Sans, SIL Open Font License (`assets/fonts/OFL.txt`).
