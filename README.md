# User Auth Microservice

A PHP microservice for user registration, login, and profile retrieval, using JWT bearer tokens for authentication and MySQL for storage.

## Features

- User registration with username/password validation
- Login with bcrypt password verification and JWT issuance
- Authenticated `me` endpoint to fetch the current user's profile
- JWT-based authentication (HS256) via `firebase/php-jwt`
- Configurable CORS handling with an origin allow-list
- Centralized JSON error handling mapped from typed exceptions
- Environment-based configuration via `vlucas/phpdotenv`

## Requirements

- PHP 8.1+ (uses `declare(strict_types=1)` and typed properties)
- MySQL (accessed via PDO)
- Composer, with the following packages:
  - `firebase/php-jwt`
  - `vlucas/phpdotenv`

## Installation

1. Install dependencies:
   ```bash
   composer require firebase/php-jwt vlucas/phpdotenv
   ```
2. Create a `.env` file in the project root (see [Configuration](#configuration)).
3. Create the `users` table in your MySQL database:
   ```sql
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(32) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL,
       isadmin TINYINT(1) NOT NULL DEFAULT 0,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```
4. Point your web server's document root at the directory containing `index.php`, ensuring all requests are routed through it (e.g. via an Apache `.htaccess` rewrite or Nginx `try_files`).

## Configuration

Configuration is loaded from environment variables (or a `.env` file) by `App\Utilities\Config`.

| Variable | Required | Default | Description |
|---|---|---|---|
| `DB_HOST` | Yes | — | MySQL host |
| `DB_NAME` | Yes | — | MySQL database name |
| `DB_USER` | Yes | — | MySQL username |
| `DB_PASSWORD` | Yes | — | MySQL password |
| `JWT_SECRET` | Yes | — | Secret key used to sign/verify JWTs |
| `JWT_EXPIRY` | No | `3600` | Token lifetime in seconds |
| `ALLOWED_ORIGINS` | No | `http://localhost` | Comma-separated list of allowed CORS origins |

If any required variable is missing, the application fails fast with a `RuntimeException` on startup.

## API Endpoints

All request/response bodies are JSON. Requests with a body must set `Content-Type: application/json`.

### `POST /user/register`

Registers a new user.

**Request body**
```json
{ "username": "johndoe", "password": "secret123" }
```

**Validation rules**
- Username: required, 6–32 characters, letters/numbers/underscores only
- Password: required, 6–72 characters

**Responses**
- `201 Created` — `{ "message": "Registration successful." }`
- `422 Unprocessable Entity` — validation failure
- `409 Conflict` — username already taken

### `POST /user/login`

Authenticates a user and issues a JWT.

**Request body**
```json
{ "username": "johndoe", "password": "secret123" }
```

**Response — `200 OK`**
```json
{
  "message": "Login successful.",
  "token": "<jwt>",
  "expires_in": 3600,
  "user": { "id": 1, "username": "johndoe" }
}
```

**Errors**
- `422 Unprocessable Entity` — validation failure
- `401 Unauthorized` — invalid username or password

### `GET /user/me`

Returns the authenticated user's profile.

**Headers**
```
Authorization: Bearer <jwt>
```

**Response — `200 OK`**
```json
{ "id": 1, "username": "johndoe", "created_at": "2026-01-01 12:00:00" }
```

**Errors**
- `401 Unauthorized` — missing, invalid, or expired token
- `404 Not Found` — user no longer exists

## Error Handling

Exceptions thrown from the service layer are mapped centrally by the router to HTTP status codes:

| Exception | Status |
|---|---|
| `ValidationException` | 422 |
| `DuplicateUserException` | 409 |
| `AuthenticationException` | 401 |
| `UnauthorizedException` | 401 |
| Anything else | 500 |

Unhandled errors are logged server-side via `error_log` and never expose internal details to the client.

## Project Structure

```
├── index.php                  # Entry point: loads config, runs CORS + router
├── router.php                 # Route dispatch and exception-to-status mapping
├── Request.php / Response.php # HTTP abstraction (App\Http)
├── UserController.php         # Handles register/login/me requests
├── UserService.php            # Business logic, validation, JWT issuance
├── UserRepository.php         # Data access (PDO/MySQL)
├── UserValidator.php          # Username/password validation rules
├── JwtHelper.php               # JWT encode/decode
├── AuthHelper.php / AuthMiddleware.php  # Bearer token authentication
├── CorsMiddleware.php         # CORS origin allow-list handling
├── Config.php                 # Environment variable loading/access
├── Database.php               # PDO connection singleton
└── *Exception.php             # Typed exceptions (App\Exceptions)
```

## Security Notes

- Passwords are hashed with bcrypt (`password_hash` / `PASSWORD_BCRYPT`).
- Login uses a dummy hash comparison for unknown usernames to mitigate username enumeration via timing.
- JWTs are signed with HS256; keep `JWT_SECRET` long, random, and out of version control.
- CORS origins are explicitly allow-listed via `ALLOWED_ORIGINS`.
