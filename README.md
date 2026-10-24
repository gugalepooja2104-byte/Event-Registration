# College Event Registration System

A small PHP and MySQL application for student event sign-ups and admin event management. The interface uses HTML, CSS, and vanilla JavaScript; the server requires PHP with `mysqli` and a MySQL/MariaDB database.

## XAMPP Setup

1. Install and open XAMPP. Start **Apache** and **MySQL** from the Control Panel.
2. Copy this project folder into XAMPP's `htdocs` directory. For example:
   `C:\xampp\htdocs\event-registration\`
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Select **Import**, choose this project's `database.sql`, and run the import. The script creates `college_event_db`, its three tables, and four sample events.
5. If your MySQL credentials differ from XAMPP's defaults, update `$dbHost`, `$dbUser`, `$dbPassword`, and `$dbName` in `config.php`. The default XAMPP setup uses `root` with an empty password.
6. Visit `http://localhost/event-registration/` in your browser and create a student account.
7. For the safest admin setup, create an account through the normal student registration form and promote it in phpMyAdmin, substituting the account's email:

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'admin@college.edu';
   ```

8. Sign out, then sign in with that account and select **Admin** to open the admin dashboard. The selected login role must match the account's stored role.
9. Ensure `uploads/events/` exists and is writable by Apache. The included `.htaccess` disables directory listings and denies requests for PHP-like scripts.
10. Confirm PHP's `upload_max_filesize` is at least `5M` and `post_max_size` is greater than `5M` in the active `php.ini`; restart Apache after changing it.

### Optional Admin Invitation Registration

Public admin sign-up is disabled unless an invitation code is configured. To enable it, set `ADMIN_REGISTRATION_CODE` in the Apache environment (for example, in the active Apache VirtualHost configuration using `SetEnv ADMIN_REGISTRATION_CODE "replace-with-a-long-random-secret"`), restart Apache, and share that secret only with trusted administrators. Admin registration requires an exact match. Do not put a real invitation secret in source control or in a publicly accessible file. Leaving the variable unset disables admin registration; manual promotion through phpMyAdmin remains available.

### Existing Database Upgrade

The `CREATE TABLE IF NOT EXISTS` statement only applies the new event columns to fresh installations. For an existing database, open phpMyAdmin, select the `college_event_db` database, choose **Import**, and run `migrations/20261024_add_event_details.sql` once. Alternatively, run its `ALTER TABLE` statement in the SQL tab. This adds the `image_path`, `rules_regulation`, and `registration_fee` columns required by the updated dashboards and event form. Do not run it more than once; MySQL will report a duplicate-column error if the columns already exist.

Verify the migration by running `SHOW COLUMNS FROM events;` and checking that all three columns appear, then refresh the student dashboard.

The four seed records in `database.sql` use illustrative paths under `uploads/events/`; upload matching images through the event form or replace those paths with existing images. Missing sample image files fall back to a branded placeholder in the UI.

Registration fees are shown in the event listings; this application does not collect or process payments.

## Project Structure

```text
event-registration/
|-- admin/
|   |-- attendees.php
|   |-- create_event.php
|   `-- dashboard.php
|-- assets/
|   |-- css/style.css
|   `-- js/validation.js
|-- includes/
|   |-- bootstrap.php
|   |-- footer.php
|   `-- header.php
|-- migrations/
|   `-- 20261024_add_event_details.sql
|-- student/
|   |-- dashboard.php
|   `-- register_event.php
|-- uploads/
|   `-- events/.htaccess
|-- config.php
|-- database.sql
|-- index.php
|-- login.php
|-- logout.php
|-- README.md
`-- register.php
```

## Database

`database.sql` creates `users`, `events`, and `registrations`. It includes unique email and roll-number constraints, a unique student/event registration constraint, foreign keys, and an index for event dates. Event dates and times are stored in the requested `date` and `time` columns. `seats_available` is the remaining capacity and starts at the event's total seat count. Events also store an optional image path and rules, plus a decimal registration fee.

## Included Safeguards

- Passwords are stored with `password_hash()` and checked with `password_verify()`.
- Dashboard access is restricted by session role; admin registration requires a configured invitation code, and the login role must match the stored account role.
- State-changing forms use a session CSRF token.
- Student event registration decrements seats and inserts the registration within one transaction. The unique registration constraint prevents duplicate sign-ups and rollback restores capacity if the insert fails.
- SQL is parameterized, and rendered user content is HTML-escaped.
- Client-side form checks improve usability; PHP repeats the important validation on the server.
- Event images are limited to JPG/JPEG/PNG, checked by extension and detected image MIME type, capped at 5 MB, and stored under randomized names in a directory that blocks PHP execution.

The Google Fonts import is optional; system fallbacks keep the application usable if the external font service is unavailable.
