# Leave Management System

A production-ready Leave Management System built with Laravel 13, MySQL, and Blade templates.

## Features

- Authentication (register, login, logout) via Laravel Breeze
- Role-based access control (admin / employee)
- Employees can apply for leave, view history, and cancel pending requests
- Admins can approve or reject leave requests with optional notes
- Admin dashboard with live stats
- Employee dashboard with recent leave activity
- Leave types with configurable max days

## Tech Stack

- Laravel 13
- MySQL
- Blade + Tailwind CSS
- Laravel Breeze (authentication)

## Setup Instructions

### 1. Clone the repository

```bash
git clone https://github.com/keyadaniel56/leave-management-system.git
cd leave-management-system
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leave_management
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### 4. Create the database

```sql
CREATE DATABASE leave_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

### 6. Build frontend assets

```bash
npm run build
```

### 7. Start the server

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000`

## Default Admin Account

| Field    | Value             |
|----------|-------------------|
| Email    | admin@leave.com   |
| Password | password          |

## Key Endpoints

| Method | URL                              | Description                  | Role     |
|--------|----------------------------------|------------------------------|----------|
| GET    | /dashboard                       | Role-based dashboard         | Both     |
| GET    | /leave                           | Employee leave history        | Employee |
| GET    | /leave/create                    | Leave application form        | Employee |
| POST   | /leave                           | Submit leave request          | Employee |
| DELETE | /leave/{id}                      | Cancel pending request        | Employee |
| GET    | /admin/leaves                    | All leave requests            | Admin    |
| GET    | /admin/leaves/{id}               | Leave request detail          | Admin    |
| POST   | /admin/leaves/{id}/approve       | Approve leave request         | Admin    |
| POST   | /admin/leaves/{id}/reject        | Reject leave request          | Admin    |
| GET    | /admin/users                     | List all employees            | Admin    |
| GET    | /admin/users/{id}                | Employee leave history        | Admin    |

## Database Schema

- `users` — id, name, email, password, role (admin/employee)
- `leave_types` — id, name, max_days
- `leave_requests` — id, user_id, leave_type_id, start_date, end_date, total_days, reason, status, admin_note, reviewed_by, reviewed_at
