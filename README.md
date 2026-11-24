# Django Girls Sierra Leone ? Community Platform

A PHP community and organisation-management platform for the DCOMP207 final project. Members can create accounts, manage profiles, browse learning resources and events, register for workshops, start forum topics, and request mentorship. Administrators can manage roles, events, resources, and mentorship request statuses.

## Technology

- PHP with PDO
- MySQL/MariaDB (SQLite fallback for local development)
- HTML and CSS
- PHP sessions

## Local setup (XAMPP)

1. Place the project folder under XAMPP `htdocs` and start Apache and MySQL.
2. Import `database.sql` using phpMyAdmin. The PHP application can also create tables on startup.
3. Configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` in the PHP process environment for your local database.
4. Open `http://localhost/<project-folder>/index.php` and register a member account.
5. To grant administrator access in a local development database, promote your chosen account explicitly with a local SQL command, for example: `UPDATE users SET role = 'admin' WHERE username = '<your-username>';`. Do not use shared or default credentials.

The repository intentionally contains no session files, local SQLite database, uploaded user data, or default administrator credentials. Sample events and learning resources are inserted by the application when their tables are empty.

## Project files

- PHP pages at the repository root implement authentication, profiles, events, resources, forum, mentorship, and administration.
- `database.sql` defines the MySQL schema.
- `images/` and `screenshots/` contain the logo and project evidence.
- `portfolio/` contains the report and A3 presentation exports.

The current implementation records event registrations but does not track attendance or measured learning progress; administrator summaries are totals rather than generated engagement reports.
