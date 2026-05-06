# StageConnect

StageConnect is a native **PHP + MySQL** web platform for internship management. It helps students discover internship offers, create a public portfolio, upload a PDF CV, apply for offers, and track application status. It also provides an administrator dashboard for managing offers and reviewing applications.

The project was designed as a full-stack academic web application using HTML, CSS, JavaScript, PHP, and MySQL.

## Table of Contents

- [Overview](#overview)
- [Main Features](#main-features)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Application Architecture](#application-architecture)
- [Database Structure](#database-structure)
- [Installation with XAMPP](#installation-with-xampp)
- [User Flows](#user-flows)
- [Admin Access](#admin-access)
- [Optional Groq Chatbot](#optional-groq-chatbot)
- [Security Measures](#security-measures)
- [Future Improvements](#future-improvements)

## Overview

Internship searching and application tracking can become difficult when offers, CVs, portfolios, and responses are managed separately. StageConnect centralizes this workflow in one platform.

Students can:

- Browse internship offers.
- Register and log in.
- Create a public portfolio.
- Upload a PDF CV.
- Apply to internship offers.
- Track the status of their applications.

Administrators can:

- Add new internship offers.
- Edit existing offers.
- Delete offers.
- View submitted applications.
- Access uploaded CV files.
- Update application status.

## Main Features

- Dynamic internship offer listing from MySQL.
- Student registration and login.
- Password hashing with `password_hash()` and verification with `password_verify()`.
- PHP session management with `student` and `admin` roles.
- Student portfolio with public profile URL.
- PDF CV upload and validation.
- Internship application system.
- Duplicate application prevention.
- Student-side application tracking.
- Admin dashboard with statistics, offers, applications, and CV access.
- Optional AI chatbot integration using the Groq API.

## Technology Stack

| Layer | Technologies |
| --- | --- |
| Frontend | HTML, CSS, JavaScript |
| Backend | Native PHP |
| Database | MySQL |
| Database Access | PDO |
| Local Server | XAMPP / Apache |
| Optional AI | Groq API |

These technologies were chosen because they are simple to deploy, suitable for academic projects, and demonstrate the complete lifecycle of a full-stack web application.

## Project Structure

```text
StageConnect/
|-- css/
|   |-- accueil.css
|   |-- auth.css
|   |-- chatbot.css
|   |-- dashboard.css
|   |-- offres.css
|   |-- portfolio.css
|   `-- shared.css
|-- html/
|   |-- accueil.html
|   |-- dashboard.html
|   |-- login.html
|   |-- offres.html
|   |-- portfolio.html
|   `-- register.html
|-- js/
|   |-- chatbot.js
|   `-- main.js
|-- php/
|   |-- add_offer.php
|   |-- apply_offer.php
|   |-- chatbot.php
|   |-- config.php
|   |-- dashboard_data.php
|   |-- db.php
|   |-- delete_offer.php
|   |-- fetch_offers.php
|   |-- get_portfolio.php
|   |-- helpers.php
|   |-- login.php
|   |-- logout.php
|   |-- register.php
|   |-- save_portfolio.php
|   |-- session.php
|   |-- update_application_status.php
|   `-- update_offer.php
|-- sql/
|   `-- database.sql
|-- tmp/
|   `-- sessions/
|-- uploads/
|   `-- cv/
|-- .env
|-- index.php
`-- README.md
```

## Application Architecture

StageConnect follows a simple three-layer architecture.

### Frontend

The frontend is built with HTML, CSS, and JavaScript.

- HTML pages define the visible structure of the application.
- `shared.css` provides the common design system: layout, buttons, forms, cards, tables, badges, and responsive rules.
- Page-specific CSS files customize each page.
- `main.js` loads session data, offers, portfolio data, and dashboard data dynamically.
- `chatbot.js` manages the optional chatbot interface.

### Backend

The backend is built with native PHP.

- PHP files process forms and API-like requests.
- PDO is used for secure database communication.
- Sessions store authenticated user data and roles.
- Helper functions centralize redirects, JSON responses, authentication checks, and CV upload validation.

### Database

MySQL stores users, offers, applications, and portfolios. Relationships are defined with foreign keys to keep the data consistent.

## Database Structure

The database is defined in `sql/database.sql`.

### `users`

Stores registered users.

Main fields:

- `id`
- `full_name`
- `email`
- `password`
- `role`
- `created_at`

Roles:

- `student`
- `admin`

### `offers`

Stores internship offers.

Main fields:

- `id`
- `title`
- `company`
- `location`
- `duration`
- `description`
- `created_at`

### `applications`

Stores student applications to internship offers.

Main fields:

- `id`
- `user_id`
- `offer_id`
- `status`
- `created_at`

Statuses:

- `pending`
- `accepted`
- `rejected`

The table includes a unique constraint on `user_id` and `offer_id` to prevent duplicate applications.

### `portfolios`

Stores student portfolio information.

Main fields:

- `id`
- `user_id`
- `phone`
- `bio`
- `skills`
- `education`
- `experience`
- `languages`
- `cv_path`
- `updated_at`

Each student can have one portfolio.

## Installation with XAMPP

1. Place the project folder in:

```text
C:\xampp\htdocs\StageConnect
```

2. Start **Apache** and **MySQL** from the XAMPP control panel.

3. Open **phpMyAdmin**.

4. Import the database file:

```text
sql/database.sql
```

5. Check database configuration in:

```text
php/config.php
```

Default configuration:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'stageconnect');
define('DB_USER', 'root');
define('DB_PASS', '');
```

6. Open the project in the browser:

```text
http://localhost/StageConnect/
```

The root `index.php` redirects automatically to:

```text
html/accueil.html
```

## User Flows

### Student Flow

1. Register from `html/register.html`.
2. Log in from `html/login.html`.
3. Complete the portfolio from `html/portfolio.html`.
4. Upload a PDF CV.
5. Browse offers from `html/offres.html`.
6. Apply to an offer.
7. Track application status from the portfolio page.

### Admin Flow

1. Log in with an account that has the `admin` role.
2. Open `html/dashboard.html`.
3. Add internship offers.
4. Edit or delete published offers.
5. View submitted applications.
6. Open student CV files.
7. Update application status to `pending`, `accepted`, or `rejected`.

## Admin Access

New accounts are created as students by default. To promote a user to administrator, run this SQL query in phpMyAdmin:

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'your-email@example.com';
```

After promotion, log out and log in again so the PHP session receives the updated role.

## Optional Groq Chatbot

StageConnect includes an optional chatbot integration using the Groq API.

Related files:

- `php/chatbot.php`
- `js/chatbot.js`
- `css/chatbot.css`

The chatbot can help students understand how to use the platform, prepare their portfolio, upload a CV, and apply for offers.

To enable it, add a Groq API key in the `.env` file:

```env
GROQ_API_KEY=your_groq_api_key_here
```

If no API key is configured, the chatbot endpoint returns an error message and the rest of the platform continues to work normally.

## Security Measures

The project includes several security practices:

- PDO prepared statements for database queries.
- Password hashing with `password_hash()`.
- Password verification with `password_verify()`.
- PHP session regeneration after login.
- Role-based access control for admin actions.
- Server-side validation for required form fields.
- Email validation with `filter_var()`.
- PDF extension validation for CV uploads.
- MIME type validation for uploaded CV files.
- Maximum CV size of 2 MB.
- Unique generated file names for uploaded CV files.
- Protected upload folder using `.htaccess`.
- JSON responses with appropriate HTTP status codes for dynamic endpoints.

## Important PHP Files

| File | Role |
| --- | --- |
| `config.php` | Loads environment values, starts sessions, defines constants. |
| `db.php` | Creates the PDO connection and checks database schema. |
| `helpers.php` | Shared helpers for redirects, JSON responses, authentication, and CV upload. |
| `register.php` | Handles student account creation. |
| `login.php` | Handles authentication. |
| `logout.php` | Ends the current session. |
| `session.php` | Returns current session data as JSON. |
| `fetch_offers.php` | Returns internship offers as JSON. |
| `save_portfolio.php` | Saves portfolio information and uploaded CV. |
| `get_portfolio.php` | Returns current or public portfolio data. |
| `apply_offer.php` | Creates a student application for an offer. |
| `dashboard_data.php` | Returns admin statistics, offers, and applications. |
| `add_offer.php` | Adds an internship offer. |
| `update_offer.php` | Updates an internship offer. |
| `delete_offer.php` | Deletes an internship offer. |
| `update_application_status.php` | Updates application status. |
| `chatbot.php` | Sends chatbot messages to the Groq API. |

## Future Improvements

Possible improvements include:

- Company accounts for recruiters.
- Email notifications after application status changes.
- Advanced search and filtering.
- Admin analytics dashboard.
- CV preview directly inside the dashboard.
- Student-to-offer matching recommendations.
- Password reset flow.
- More detailed application history.

## Authors

StageConnect was created as a university web development project by a 2-person team.
