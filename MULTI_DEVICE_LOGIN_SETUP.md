# School Management System - Multi-Device Login Setup

## Changes Made

### 1. Created `api_login.php`

- **Purpose**: Allows users to login from any device using database credentials
- **How it works**:
  - Accepts POST requests with username and password
  - Checks credentials against the `users` table in the database
  - Returns user info if credentials are correct
  - Enables login from multiple devices without local storage dependency

### 2. Updated Login JavaScript in `index.html`

- **New behavior**:
  - First attempts to login via `api_login.php` (database check)
  - Falls back to localStorage if database is unavailable (offline mode)
  - On successful database login, saves user to localStorage for offline use
  - Maintains admin login functionality

## How Users Can Now Use the System

### Creating an Account

1. Click **"បង្កើតគណនី"** (Create Account) on the landing page
2. Enter:
   - **ឈ្មោះសាលា** (School Name/Username)
   - **លេខទូរស័ព្ទ** (Phone Number - for password recovery)
   - **លេខសម្ងាត់** (Password)
3. Confirm password
4. Click **"រក្សាទុកគណនី"** (Save Account)
5. Account is saved to both database and localStorage

### Logging In from Any Device

1. Click **"ចូលប្រើប្រាស់"** (Login) on the landing page
2. Enter:
   - **ឈ្មោះសាលា** (Username)
   - **លេខសម្ងាត់** (Password)
3. Click **"ចូលប្រព័ន្ធ"** (Enter System)
4. System will:
   - Check database first (database-based login)
   - Allow login from any device with internet connection
   - Work offline using localStorage if database is unavailable

## Features Now Enabled

✅ **All users can create accounts** - No restrictions on registration
✅ **Multi-device login** - Login from any device with username/password
✅ **Offline fallback** - Works without database connection using localStorage
✅ **Account persistence** - Accounts saved in database persist across sessions
✅ **Admin login** - Admin account still works with hardcoded credentials

## System Architecture

```
User Registration Flow:
┌─────────────────┐
│  User Registers │
└────────┬────────┘
         │
         ├──→ api_register.php → Database (users table)
         │
         └──→ localStorage (PrimarySys_UsersDB)

User Login Flow (NEW):
┌─────────────────┐
│  User Logins    │
└────────┬────────┘
         │
         ├──→ api_login.php → Database check ✓ (Multi-device)
         │
         ├──→ localStorage fallback ✓ (Offline mode)
         │
         └──→ Session saved + Dashboard loaded
```

## Database Requirements

The `users` table should have:

- `id` (INT PRIMARY KEY AUTO_INCREMENT)
- `username` (VARCHAR UNIQUE NOT NULL)
- `password` (VARCHAR NOT NULL)
- `phone` (VARCHAR)
- `created_at` (TIMESTAMP)

This is already configured in `database.sql`.

## Notes

- Passwords are currently stored in plain text in the database
- For production, consider hashing passwords using `password_hash()` and `password_verify()`
- Phone number can be used for password recovery feature
- Original code structure is preserved; only additions made for login API
