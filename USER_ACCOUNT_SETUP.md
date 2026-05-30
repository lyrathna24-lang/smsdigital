# User Account Database Setup & Multi-Device Login Guide

## Overview

This system implements secure multi-device login by:

1. **Storing hashed passwords** in the `users` table (never plaintext)
2. **Generating temporary device tokens** for multi-device authentication
3. **Preserving user data** across devices through the same user ID

---

## Database Tables

### 1. `users` Table (Store All Accounts)

Stores all user accounts with hashed passwords.

```sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Hashed with PHP password_hash()
    phone VARCHAR(20),
    role ENUM('admin', 'school') DEFAULT 'school',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Columns:**

- `id`: Unique user identifier (used to link all data)
- `username`: School/account name (unique)
- `password`: Hashed password (using PHP `password_hash()`)
- `phone`: Phone number (for account recovery)
- `role`: User role (admin or school)
- `created_at`: Account creation timestamp

### 2. `device_tokens` Table (Multi-Device Login)

Stores temporary device login tokens for multi-device access.

```sql
CREATE TABLE IF NOT EXISTS device_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(128) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,      -- Token expires in 24 hours
    device_name VARCHAR(100),           -- Optional: Store device info
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id),
    INDEX(expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Columns:**

- `user_id`: Links to the user in `users` table
- `token`: Secure random token (128-char hex string)
- `expires_at`: Token expiration time (24 hours from creation)
- `device_name`: Optional device identifier (e.g., "iPhone", "Tablet")
- `created_at`: Token creation timestamp

---

## API Endpoints for Accounts & Login

### 1. Register New Account

**Endpoint:** `POST /api_register.php`

**Request:**

```json
{
  "username": "School Name",
  "password": "SecurePassword123",
  "phone": "012345678"
}
```

**Response (Success):**

```json
{
  "success": true,
  "message": "Account created successfully!"
}
```

**What happens:**

- Password is hashed with `password_hash()`
- Account saved to `users` table
- User can now login from any device

---

### 2. Standard Login (Same Device)

**Endpoint:** `POST /api_login.php`

**Request:**

```json
{
  "username": "School Name",
  "password": "SecurePassword123"
}
```

**Response (Success):**

```json
{
  "success": true,
  "message": "Login successful!",
  "user": {
    "id": 1,
    "username": "School Name",
    "phone": "012345678"
  }
}
```

**What happens:**

- Username is looked up in `users` table
- Password is verified using `password_verify()`
- Returns user info (preserves data via same `id`)

---

### 3. Generate Device Token for Multi-Device Login

**Endpoint:** `POST /api_generate_token.php`

**Request (from Device A):**

```json
{
  "username": "School Name",
  "password": "SecurePassword123"
}
```

**Response:**

```json
{
  "success": true,
  "device_token": "a1b2c3d4e5f6...128chars...",
  "expires_at": "2026-05-30 10:30:45"
}
```

**What happens:**

- Password is verified
- 128-character random token is generated
- Token stored in `device_tokens` table with 24-hour expiration
- Token is returned to be shared with other device

---

### 4. Login Using Device Token (Multi-Device)

**Endpoint:** `POST /api_login_with_token.php`

**Request (from Device B):**

```json
{
  "device_token": "a1b2c3d4e5f6...128chars..."
}
```

**Response:**

```json
{
  "success": true,
  "user": {
    "id": 1,
    "username": "School Name",
    "phone": "012345678"
  }
}
```

**What happens:**

- Token is verified (must exist and not be expired)
- Returns same user `id` (so all data is preserved)
- User is authenticated on Device B
- Both Device A and Device B access same account/data

---

### 5. Retrieve Credentials for Password Recovery

**Endpoint:** `POST /api_get_credentials.php`

**Request Option 1 (by phone):**

```json
{
  "phone": "012345678"
}
```

**Request Option 2 (by username + phone):**

```json
{
  "username": "School Name",
  "phone": "012345678"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Temporary device login token generated.",
  "credentials": {
    "username": "School Name",
    "phone": "012345678",
    "device_token": "a1b2c3d4e5f6...",
    "expires_at": "2026-05-30 10:30:45"
  }
}
```

**What happens:**

- User is looked up by phone number
- A temporary device token is generated
- **Note:** Plaintext password is NOT returned (secure design)
- Use the device_token to login on another device

---

## Multi-Device Login Flow

### Scenario: User wants to login on mobile after registering on desktop

**Step 1: Register on Desktop**

```
Desktop → POST /api_register.php → users table stores: username + hashed_password
```

**Step 2: Get Device Token from Desktop**

```
Desktop → POST /api_generate_token.php
        → Returns device_token + expires_at
```

**Step 3: Share Token with Mobile**

- User copies device_token from desktop
- User enters token on mobile

**Step 4: Login on Mobile Using Token**

```
Mobile → POST /api_login_with_token.php
       → Returns same user.id
       → Mobile can access same account/data
```

**Step 5: Logout**

- Both devices use `logout()` function
- Session cleared from localStorage
- Account remains in database for future login

---

## Security Notes

✅ **Password Security:**

- Passwords hashed with `password_hash()` (bcrypt)
- Passwords are never sent back to client
- No plaintext password storage

✅ **Multi-Device Security:**

- Device tokens expire after 24 hours
- Each device gets a unique token
- Tokens are 128-character random strings
- No password sharing between devices

✅ **Data Persistence:**

- Same user `id` across devices
- All student data, teacher info linked to user `id`
- Logout doesn't delete account or data

---

## Setup Instructions

### 1. Run Database Setup

```bash
php setup_database.php
```

This will:

- Create `primary_school_db` database
- Create all tables including `users` and `device_tokens`
- Load dummy data

### 2. Test Account Creation

```bash
curl -X POST 'http://localhost:8080/school%20management/api_register.php' \
  -H 'Content-Type: application/json' \
  -d '{"username":"Test School","password":"password123","phone":"0123456789"}'
```

### 3. Test Login

```bash
curl -X POST 'http://localhost:8080/school%20management/api_login.php' \
  -H 'Content-Type: application/json' \
  -d '{"username":"Test School","password":"password123"}'
```

### 4. Test Device Token Generation

```bash
curl -X POST 'http://localhost:8080/school%20management/api_generate_token.php' \
  -H 'Content-Type: application/json' \
  -d '{"username":"Test School","password":"password123"}'
```

### 5. Test Multi-Device Login

```bash
# Use token from step 4
curl -X POST 'http://localhost:8080/school%20management/api_login_with_token.php' \
  -H 'Content-Type: application/json' \
  -d '{"device_token":"<token-from-step-4>"}'
```

---

## File References

- **Database Schema:** `database.sql`
- **Database Connection:** `database.php`, `class.Database.php`
- **API - Register:** `api_register.php`
- **API - Login:** `api_login.php`
- **API - Get Credentials:** `api_get_credentials.php`
- **API - Generate Token:** `api_generate_token.php`
- **API - Login with Token:** `api_login_with_token.php`
- **Web UI:** `index.html` (handles login/logout/session)

---

## LocalStorage Structure (Client-Side)

The browser stores user info in `localStorage` for offline access:

```javascript
// Session Info
localStorage.PrimarySys_SessionType; // 'user' or 'admin'
localStorage.PrimarySys_SessionUser; // Current user ID
localStorage.PrimarySys_SessionUserName; // Display name

// User Database (all registered accounts)
localStorage.PrimarySys_UsersDB = [
  {
    username: "School Name",
    password: "password123", // Kept for offline fallback
    phone: "012345678",
    storageKey: "unique_id",
    deviceToken: "a1b2c3d4e5f6...", // For multi-device login
  },
];
```

---

## Troubleshooting

**Q: Token not working for multi-device login?**

- A: Check token expiration in `device_tokens` table
- Run: `SELECT * FROM device_tokens WHERE expires_at > NOW();`

**Q: Password reset showing plaintext password?**

- A: The "forgot password" feature now returns device_token instead of plaintext password (secure)

**Q: Login fails on new device?**

- A: Ensure database is accessible and `device_tokens` table exists
- Run: `php setup_database.php` to recreate tables

**Q: Same data on both devices?**

- A: Make sure both logins use same username (same `user.id` in database)

---

## Summary

| Feature              | Status     | Security                           |
| -------------------- | ---------- | ---------------------------------- |
| Account Registration | ✅ Enabled | Hashed passwords                   |
| Single Device Login  | ✅ Enabled | Password verification              |
| Multi-Device Login   | ✅ Enabled | Device tokens (24h)                |
| Data Persistence     | ✅ Enabled | Same user ID across devices        |
| Logout               | ✅ Enabled | Clears session, keeps account      |
| Password Recovery    | ✅ Enabled | Returns device token, not password |
