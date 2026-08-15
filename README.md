User Authentication Microservice
PHP microservice for user registration, login, and profile retrieval using JWT authentication. Built with a simple MVC-like structure, it uses MySQL for persistence and follows RESTful principles.

Features
User Registration – Create a new account with username/password.

User Login – Authenticate and receive a JWT token.

Profile Retrieval – Get the authenticated user's profile (protected endpoint).

JWT-based Authentication – Tokens are signed with HS256 and include expiration.

CORS Support – Configurable allowed origins, handles preflight requests.

Input Validation – Username and password rules enforced.

Error Handling – Consistent JSON error responses with appropriate HTTP status codes.

Secure Password Storage – Passwords hashed using password_hash() with BCRYPT.

