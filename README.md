# Leave Management System

A production-ready Leave Management System built with Laravel 13, MySQL, Blade, and Laravel Reverb (WebSockets). Includes a full REST API with Sanctum token authentication for React or any frontend client.

---

## Tech Stack

- Laravel 13
- MySQL
- Blade + Tailwind CSS
- Laravel Breeze (authentication)
- Laravel Sanctum (API token auth)
- Laravel Reverb (WebSockets / real-time notifications)

---

## Features

- Register / Login / Logout
- Role-based access control (admin / employee)
- Employees can apply for leave, view history, cancel pending requests
- Admins can approve or reject leave requests with optional notes
- Real-time WebSocket notifications (admin notified on new request, employee notified on review)
- REST API with consistent JSON responses and proper HTTP status codes
- Role-based dashboards with live stats

---

## Requirements

- PHP 8.3+
- Composer
- Node.js + npm
- MySQL
- php8.3-xml and php8.3-mysql extensions

Install missing PHP extensions:
```bash
sudo apt install php8.3-xml php8.3-mysql -y
```

---

## Quick Setup (Automated)

```bash
git clone https://github.com/keyadaniel56/leave-management-system.git
cd leave-management-system
```

Edit `.env.example` with your database credentials before running setup:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leave_management
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

Then run:

```bash
chmod +x setup.sh
./setup.sh
```

This will:
1. Install PHP and Node dependencies
2. Generate app key
3. Run all migrations
4. Seed admin user and leave types
5. Build frontend assets
6. Start the Laravel development server

---

## Manual Setup

### 1. Clone and install dependencies

```bash
git clone https://github.com/keyadaniel56/leave-management-system.git
cd leave-management-system
composer install
npm install
```

### 2. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials.

### 3. Create the MySQL database

```sql
CREATE DATABASE leave_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

### 5. Build frontend assets

```bash
npm run build
```

### 6. Start the servers

Terminal 1 — Laravel:
```bash
php artisan serve
```

Terminal 2 — WebSocket server:
```bash
php artisan reverb:start
```

Visit `http://127.0.0.1:8000`

---

## Default Admin Account

| Field    | Value           |
|----------|-----------------|
| Email    | admin@leave.com |
| Password | password        |

---

## Default Leave Types

| Type             | Max Days |
|------------------|----------|
| Annual Leave     | 21       |
| Sick Leave       | 14       |
| Maternity Leave  | 90       |
| Paternity Leave  | 14       |
| Unpaid Leave     | 30       |

---

## Database Schema

### users
| Column    | Type                      |
|-----------|---------------------------|
| id        | bigint (PK)               |
| name      | string                    |
| email     | string (unique)           |
| password  | string (hashed)           |
| role      | enum: admin, employee     |

### leave_types
| Column    | Type        |
|-----------|-------------|
| id        | bigint (PK) |
| name      | string      |
| max_days  | integer     |

### leave_requests
| Column        | Type                              |
|---------------|-----------------------------------|
| id            | bigint (PK)                       |
| user_id       | FK → users                        |
| leave_type_id | FK → leave_types                  |
| start_date    | date                              |
| end_date      | date                              |
| total_days    | integer                           |
| reason        | text                              |
| status        | enum: pending, approved, rejected |
| admin_note    | text (nullable)                   |
| reviewed_by   | FK → users (nullable)             |
| reviewed_at   | timestamp (nullable)              |

---

## Blade UI Endpoints

| Method | URL                           | Description                  | Role     |
|--------|-------------------------------|------------------------------|----------|
| GET    | /dashboard                    | Role-based dashboard         | Both     |
| GET    | /leave                        | Employee leave history       | Employee |
| GET    | /leave/create                 | Leave application form       | Employee |
| POST   | /leave                        | Submit leave request         | Employee |
| DELETE | /leave/{id}                   | Cancel pending request       | Employee |
| GET    | /admin/leaves                 | All leave requests           | Admin    |
| GET    | /admin/leaves/{id}            | Leave request detail         | Admin    |
| POST   | /admin/leaves/{id}/approve    | Approve leave request        | Admin    |
| POST   | /admin/leaves/{id}/reject     | Reject leave request         | Admin    |
| GET    | /admin/users                  | List all employees           | Admin    |
| GET    | /admin/users/{id}             | Employee leave history       | Admin    |
| GET    | /admin/leave-types            | Manage leave types           | Admin    |
| POST   | /admin/leave-types            | Add a new leave type         | Admin    |
| PUT    | /admin/leave-types/{id}       | Update a leave type          | Admin    |
| DELETE | /admin/leave-types/{id}       | Delete a leave type          | Admin    |

---

## REST API

All API endpoints are prefixed with `/api`.

Protected routes require the header:
```
Authorization: Bearer {token}
```

All responses follow this structure:
```json
{
  "status": "success",
  "message": "Human readable message",
  "data": {}
}
```

### Auth Endpoints

| Method | Endpoint              | Description                        | Auth |
|--------|-----------------------|------------------------------------|------|
| POST   | /api/register         | Register as employee               | No   |
| POST   | /api/register/admin   | Register as admin (secret required)| No   |
| POST   | /api/login            | Login, returns token               | No   |
| POST   | /api/logout           | Revoke current token               | Yes  |
| GET    | /api/me               | Get authenticated user             | Yes  |

### Employee Endpoints

| Method | Endpoint             | Description               |
|--------|----------------------|---------------------------|
| GET    | /api/leave-types     | List all leave types      |
| GET    | /api/leaves          | My leave requests         |
| POST   | /api/leaves          | Submit a leave request    |
| GET    | /api/leaves/{id}     | View a single request     |
| DELETE | /api/leaves/{id}     | Cancel a pending request  |

### Admin Endpoints

| Method | Endpoint                        | Description           |
|--------|---------------------------------|-----------------------|
| GET    | /api/admin/leaves               | All leave requests    |
| GET    | /api/admin/leaves/{id}          | Single leave request  |
| POST   | /api/admin/leaves/{id}/approve  | Approve a request     |
| POST   | /api/admin/leaves/{id}/reject   | Reject a request      |
| GET    | /api/admin/leave-types          | List all leave types  |
| POST   | /api/admin/leave-types          | Create a leave type   |
| PUT    | /api/admin/leave-types/{id}     | Update a leave type   |
| DELETE | /api/admin/leave-types/{id}     | Delete a leave type   |

---

## API Usage Examples

### Register as Admin
```bash
curl -X POST http://127.0.0.1:8000/api/register/admin \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin2@example.com",
    "password": "password",
    "password_confirmation": "password",
    "admin_secret": "your_admin_secret_key_here"
  }'
```

### Register
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password","password_confirmation":"password"}'
```

### Login
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@leave.com","password":"password"}'
```

### Submit Leave Request
```bash
curl -X POST http://127.0.0.1:8000/api/leaves \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"leave_type_id":1,"start_date":"2026-05-01","end_date":"2026-05-05","reason":"Family vacation"}'
```

### Approve Leave (Admin)
```bash
curl -X POST http://127.0.0.1:8000/api/admin/leaves/1/approve \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"admin_note":"Approved. Enjoy your leave."}'
```

---

## Real-time WebSocket Notifications

Powered by Laravel Reverb. Notifications are pushed instantly via WebSockets:

- Admin receives a notification when an employee submits a leave request
- Employee receives a notification when their request is approved or rejected

WebSocket server runs on `ws://127.0.0.1:8080` by default.

Start it with:
```bash
php artisan reverb:start
```

---

## Project Structure

```
app/
├── Events/
│   ├── LeaveRequestSubmitted.php   # Fired when employee submits leave
│   └── LeaveRequestReviewed.php    # Fired when admin approves/rejects
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── LeaveController.php
│   │   │   └── Admin/LeaveController.php
│   │   ├── Admin/
│   │   │   ├── LeaveController.php
│   │   │   └── UserController.php
│   │   ├── DashboardController.php
│   │   └── LeaveController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   └── Traits/
│       └── ApiResponse.php
├── Models/
│   ├── User.php
│   ├── LeaveType.php
│   └── LeaveRequest.php
database/
├── migrations/
└── seeders/
routes/
├── api.php
└── web.php
```
