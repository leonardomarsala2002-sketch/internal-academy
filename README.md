# Internal Academy

Internal Academy is a Laravel 13 + Vue 3 + Inertia.js web app for managing internal company workshops.

The platform supports two roles:
- `admin`: manages workshops (create, update, delete)
- `employee`: views upcoming workshops, registers, and cancels registrations

Registration data is managed through a single source of truth: the `registrations` table.

## Tech Stack

- Laravel 13
- Vue 3
- Inertia.js
- Tailwind CSS
- SQLite/MySQL/PostgreSQL supported through Laravel database config

## Setup

1. Install PHP dependencies:

```bash
composer install
```

2. Install Node dependencies:

```bash
npm install
```

3. Create environment file and app key:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in `.env`.

5. Run migrations and seed demo data:

```bash
php artisan migrate --seed
```

6. Build frontend assets (or run dev server):

```bash
npm run dev
```

7. Start Laravel server:

```bash
php artisan serve
```

## Demo Credentials

After running `php artisan migrate --seed`:

- Admin
  - Email: `admin@example.com`
  - Password: `password`
- Employee 1
  - Email: `employee1@example.com`
  - Password: `password`
- Employee 2
  - Email: `employee2@example.com`
  - Password: `password`

## Core Flows

- Admin workshop CRUD available under `/admin/workshops`
- Admin dashboard includes live workshop statistics and registrations-per-workshop (polling refresh every 10 seconds while page is open)
- Employee dashboard available under `/dashboard`
- Employee workshop actions:
  - register: `POST /workshops/{workshop}/register`
  - cancel: `DELETE /workshops/{workshop}/register`

## Reminder Command

Send reminders to confirmed participants of workshops scheduled for the next day:

```bash
php artisan academy:remind
```

## Run Tests

```bash
php artisan test
```

## Notes

- Workshop capacity is enforced at registration time.
- Duplicate registrations are blocked.
- Full workshops add new users to a waiting list.
- When a confirmed registration is cancelled, the first waitlisted user is promoted automatically (FIFO).
- Users cannot register (or join a waiting list) for workshops that overlap with another workshop where they are already confirmed.
