# Laravel Authentication & Account Management API

A secure REST API built with Laravel for user authentication and profile management, fully documented with Swagger/OpenAPI.

### Features

- **User Authentication**: Registration, login, and logout with token-based authentication
- **Profile Management**: Retrieve, update, and delete user profiles
- **Password Management**: Secure password change with validation
- **Protected Routes**: All profile endpoints require valid authentication token
- **API Documentation**: Interactive Swagger/OpenAPI documentation
- **Security**: Input validation, password hashing, and token-based authorization

---

## Prerequisites

- PHP 8.1 or higher
- Composer
- Laravel 10.x
- MySQL or SQLite
- Postman (for API testing)

---

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd <project-directory>

2. **Install dependencies**

```bash
composer install
Create environment file
```

```bash
cp .env.example .env
Generate application key
```

```bash
php artisan key:generate
Configure database (in .env)
```

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_auth_api
DB_USERNAME=root
DB_PASSWORD=
Run migrations

```bash
php artisan migrate
Install Swagger documentation
```

```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
Start the development server
```

```bash
php artisan serve
```


```markdown
# API Documentation

## Overview

This document provides comprehensive documentation for the Laravel Authentication & Account Management API. All endpoints are fully testable via Postman or the interactive Swagger UI.

---

## Base URL

```
http://localhost:8000/api
```

---

## Authentication

The API uses **token-based authentication** (Laravel Sanctum). Protected routes require an `Authorization` header with a valid token:

```
Authorization: Bearer {token}
```

---

## Response Format

All responses follow a consistent JSON format:

```json
{
  "message": "Operation description",
  "data": {},
  "token": "optional_token_for_auth_routes"
}
```

---

## Endpoints

### 1. Authentication Endpoints

#### 1.1 Register User

**Endpoint**: `POST /register`

**Description**: Create a new user account

**Request Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "string (required)",
  "email": "string (required, valid email format)",
  "password": "string (required, min 8 characters)",
  "password_confirmation": "string (required, must match password)"
}
```

**Success Response (201)**:
```json
{
  "message": "Account created successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-03-11T10:30:00Z"
  }
}
```

**Error Responses**:

| Scenario | Status | Response |
|---|---|---|
| Email already exists | 422 | `{"message": "The email has already been taken"}` |
| Validation error | 422 | `{"message": "Validation failed", "errors": {...}}` |
| Password too short | 422 | `{"message": "The password must be at least 8 characters"}` |

**Example cURL**:
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123"
  }'
```

---

#### 1.2 Login User

**Endpoint**: `POST /login`

**Description**: Authenticate user and receive authentication token

**Request Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "email": "string (required, valid email)",
  "password": "string (required)"
}
```

**Success Response (200)**:
```json
{
  "message": "Login successful",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Error Responses**:

| Scenario | Status | Response |
|---|---|---|
| Invalid credentials | 401 | `{"message": "Invalid credentials"}` |
| Email not found | 401 | `{"message": "Invalid credentials"}` |

**Example cURL**:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123"
  }'
```

---

#### 1.3 Logout User

**Endpoint**: `POST /logout`

**Description**: Invalidate the current authentication token

**Request Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Success Response (200)**:
```json
{
  "message": "Logout successful"
}
```

**Error Responses**:

| Scenario | Status | Response |
|---|---|---|
| Missing token | 401 | `{"message": "Unauthorized"}` |
| Invalid token | 401 | `{"message": "Unauthorized"}` |

**Example cURL**:
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### 2. Profile Management Endpoints (Protected)

> ️ **All endpoints in this section require valid authentication token**

#### 2.1 Get User Profile

**Endpoint**: `GET /me`

**Description**: Retrieve the authenticated user's profile information

**Request Headers**:
```
Authorization: Bearer {token}
```

**Success Response (200)**:
```json
{
  "message": "Profile fetched successfully",
  "data": {}
}