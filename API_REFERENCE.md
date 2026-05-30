# API Reference - Multi-Device Login

## Overview

This document provides API reference and examples for the school management system's authentication endpoints.

---

## Endpoint 1: User Registration

### URL

```
POST /api_register.php
```

### Description

Creates a new user account and saves it to the database and localStorage.

### Request Headers

```
Content-Type: application/json
```

### Request Body

```json
{
  "username": "សាលាបឋមសិក្សា",
  "password": "my_password_123",
  "phone": "012345678"
}
```

### Parameters

| Parameter | Type   | Required | Description          |
| --------- | ------ | -------- | -------------------- |
| username  | string | Yes      | School name/Username |
| password  | string | Yes      | User password        |
| phone     | string | No       | Phone number         |

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Account created successfully!"
}
```

### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "ឈ្មោះអ្នកប្រើនេះមានរួចហើយ!"
}
```

### Error Messages

| Message                        | Meaning                 |
| ------------------------------ | ----------------------- |
| "បញ្ចូលទិន្នន័យមិនគ្រប់គ្រាន់" | Missing required fields |
| "ឈ្មោះអ្នកប្រើនេះមានរួចហើយ!"   | Username already exists |
| "Error: ..."                   | Database error          |

### Example: JavaScript

```javascript
async function registerUser() {
  const response = await fetch("api_register.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      username: "សាលាបឋមសិក្សា",
      password: "password123",
      phone: "012345678",
    }),
  });

  const result = await response.json();
  if (result.success) {
    console.log("Registration successful!");
  } else {
    console.error("Registration failed:", result.message);
  }
}
```

### Example: cURL

```bash
curl -X POST http://localhost:8080/school%20management/api_register.php \
  -H "Content-Type: application/json" \
  -d '{"username":"សាលាបឋមសិក្សា","password":"password123","phone":"012345678"}'
```

### Example: Python

```python
import requests
import json

url = "http://localhost:8080/school%20management/api_register.php"
data = {
    "username": "សាលាបឋមសិក្សា",
    "password": "password123",
    "phone": "012345678"
}

response = requests.post(url, json=data)
result = response.json()
print(result)
```

---

## Endpoint 2: User Login (NEW)

### URL

```
POST /api_login.php
```

### Description

Authenticates a user by checking credentials against the database. This enables login from any device using the same credentials.

### Request Headers

```
Content-Type: application/json
```

### Request Body

```json
{
  "username": "សាលាបឋមសិក្សា",
  "password": "my_password_123"
}
```

### Parameters

| Parameter | Type   | Required | Description |
| --------- | ------ | -------- | ----------- |
| username  | string | Yes      | Username    |
| password  | string | Yes      | Password    |

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Login successful!",
  "user": {
    "id": 1,
    "username": "សាលាបឋមសិក្សា",
    "phone": "012345678"
  }
}
```

### Error Response (200 OK) - Credentials Invalid

```json
{
  "success": false,
  "message": "ឈ្មោះ ឬលេខសម្ងាត់ខុស!"
}
```

### Example: JavaScript (Basic)

```javascript
async function loginUser() {
  const response = await fetch("api_login.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      username: "សាលាបឋមសិក្សា",
      password: "password123",
    }),
  });

  const result = await response.json();
  if (result.success) {
    console.log("Login successful!");
    console.log("User ID:", result.user.id);
    console.log("Username:", result.user.username);
  } else {
    console.error("Login failed:", result.message);
  }
}
```

### Example: JavaScript (With Error Handling)

```javascript
async function loginWithFallback() {
  try {
    const response = await fetch("api_login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        username: "សាលាបឋមសិក្សា",
        password: "password123",
      }),
      timeout: 5000, // 5 second timeout
    });

    const result = await response.json();
    if (result.success) {
      return { success: true, user: result.user };
    } else {
      // Try localStorage fallback
      return fallbackToLocalStorage();
    }
  } catch (error) {
    console.log("Database unavailable, using localStorage");
    return fallbackToLocalStorage();
  }
}

function fallbackToLocalStorage() {
  const usersDB = JSON.parse(localStorage.getItem("PrimarySys_UsersDB")) || [];
  const user = usersDB.find(
    (u) => u.username === "សាលាបឋមសិក្សា" && u.password === "password123",
  );
  return user ? { success: true, user } : { success: false };
}
```

### Example: cURL

```bash
curl -X POST http://localhost:8080/school%20management/api_login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"សាលាបឋមសិក្សា","password":"password123"}'
```

### Example: Python

```python
import requests

url = "http://localhost:8080/school%20management/api_login.php"
data = {
    "username": "សាលាបឋមសិក្សា",
    "password": "password123"
}

response = requests.post(url, json=data)
result = response.json()

if result['success']:
    print("Login successful!")
    print("User:", result['user'])
else:
    print("Login failed:", result['message'])
```

### Example: React.js

```javascript
import { useState } from "react";

function LoginForm() {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");

  const handleLogin = async (e) => {
    e.preventDefault();

    try {
      const response = await fetch("api_login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password }),
      });

      const result = await response.json();

      if (result.success) {
        // Store user info and redirect
        localStorage.setItem("currentUser", JSON.stringify(result.user));
        window.location.href = "/dashboard";
      } else {
        setError(result.message);
      }
    } catch (err) {
      setError("Connection error. Please try again.");
    }
  };

  return (
    <form onSubmit={handleLogin}>
      <input
        type="text"
        placeholder="Username"
        value={username}
        onChange={(e) => setUsername(e.target.value)}
      />
      <input
        type="password"
        placeholder="Password"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
      />
      {error && <p style={{ color: "red" }}>{error}</p>}
      <button type="submit">Login</button>
    </form>
  );
}

export default LoginForm;
```

---

## Multi-Device Login Workflow

### Scenario: User logs in from Device A, then Device B

**Device A - First Login:**

```
1. User enters username: "សាលាបឋមសិក្សា" and password: "password123"
2. POST to api_login.php
3. Database checks and confirms credentials
4. Returns: { success: true, user: {...} }
5. Device A saves to localStorage
6. Dashboard loads
```

**Device B - Login with Same Credentials:**

```
1. User opens browser on different computer
2. Enters same username: "សាលាបឋមសិក្សា" and password: "password123"
3. POST to api_login.php (from Device B)
4. Database checks and confirms credentials ← Works because data is in DB!
5. Returns: { success: true, user: {...} }
6. Device B saves to localStorage
7. Dashboard loads
```

**Key Difference**: Database persists credentials across all devices!

---

## Response Status Codes

| Code | Meaning            | Description                                       |
| ---- | ------------------ | ------------------------------------------------- |
| 200  | OK                 | Request processed (check JSON for success status) |
| 405  | Method Not Allowed | Request wasn't POST                               |
| 500  | Server Error       | Database connection failed                        |

---

## Database Queries Behind the APIs

### Registration Query

```sql
INSERT INTO users (username, password, phone) VALUES (?, ?, ?)
```

### Login Query

```sql
SELECT id, username, password, phone FROM users WHERE username = ?
-- Then compare password in PHP
```

---

## Security Best Practices

### For Your Application

```javascript
// ✅ GOOD: Send requests over HTTPS (in production)
// ❌ BAD: Send plain text passwords

// ✅ GOOD: Hash passwords
// ❌ BAD: Store plain text passwords (current system)

// ✅ GOOD: Use tokens/sessions
// ❌ BAD: Send credentials on every request (current system)

// ✅ GOOD: Set HTTP-only cookies for tokens
// ❌ BAD: Store tokens in localStorage (vulnerable to XSS)
```

### Future Enhancement: Password Hashing

```php
// In api_register.php:
$hashed = password_hash($data['password'], PASSWORD_BCRYPT);
$stmt->execute([$data['username'], $hashed, $data['phone']]);

// In api_login.php:
if ($user && password_verify($data['password'], $user['password'])) {
    // Password is correct
}
```

---

## Limitations

Current implementation:

- ❌ Passwords stored as plain text
- ❌ No session tokens
- ❌ No password hashing
- ❌ No rate limiting
- ❌ No HTTPS requirement

Capabilities:

- ✅ Multi-device login
- ✅ Database persistence
- ✅ localStorage fallback
- ✅ Error messages in Khmer

---

## Support

For issues or questions:

1. Check `MULTI_DEVICE_LOGIN_GUIDE.md` for full documentation
2. Verify database is running: `setup_database.php`
3. Check browser console for error messages
4. Ensure both API files exist: `api_register.php` and `api_login.php`
