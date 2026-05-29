# AMS Login Module

## Overview
PHP-based login system for the Academic Management System (AMS). Uses server-side sessions and SessionManager class for authentication.

## Files

### `SessionManager.php`
Utility class for handling authentication and API requests.

**Key Methods:**
```php
SessionManager::isAuthenticated()              // Check if user is logged in
SessionManager::getUser()                      // Get stored user data
SessionManager::getToken()                     // Get auth token
SessionManager::setAuth($token, $user, $exp)   // Store auth data
SessionManager::logout()                       // Clear session
SessionManager::apiRequest($url, $method, $data) // Make API request
SessionManager::requireAuth()                  // Redirect if not authenticated
SessionManager::getUserRole()                  // Get user's role
SessionManager::hasRole($role)                 // Check if user has role
SessionManager::getAuthHeader()                // Get Authorization header
```

### `index.php`
Main login page that displays the login form.

**Features:**
- Username/password form
- Error/success messaging
- Auto-redirect if already logged in
- Simple, unstyled HTML skeleton

### `process.php`
Handles form submission and API authentication.

**Process:**
1. Validates username and password input
2. Makes POST request to `/api/auth`
3. On success: stores token and user in session
4. On failure: redirects back to login with error message
5. On success: redirects to admin dashboard

### `logout.php`
Clears session and redirects to login.

**Process:**
1. Calls `SessionManager::logout()`
2. Destroys session
3. Redirects to login page

## Usage

### Access Login Page
```
http://localhost/WEBSYST1_FINAL/ams/login/
```

### Using SessionManager in Other Pages

```php
<?php
require_once __DIR__ . '/SessionManager.php';

// Require authentication (redirects if not logged in)
SessionManager::requireAuth();

// Get current user
$user = SessionManager::getUser();
echo "Logged in as: " . $user['username'];

// Make authenticated API request
$response = SessionManager::apiRequest('http://localhost/WEBSYST1_FINAL/ams/api/users', 'GET');
if ($response['success']) {
    echo json_encode($response['data']);
}

// Logout
SessionManager::logout();
?>
```

## Session Data Structure

After successful login, PHP session contains:

```php
$_SESSION['auth_token']   // Bearer token from API
$_SESSION['auth_user']    // User data array: id, username, email, role, is_active
$_SESSION['auth_expiry']  // Token expiry timestamp
```

## Authentication Flow

```
1. User visits http://localhost/WEBSYST1_FINAL/ams/login/
   ↓
2. SessionManager::isAuthenticated() checks session
   ↓
3. If not authenticated, displays login form
   ↓
4. User enters credentials and submits
   ↓
5. process.php receives POST request
   ↓
6. Makes POST /api/auth call with credentials
   ↓
7. API returns token and user data
   ↓
8. SessionManager::setAuth() stores in $_SESSION
   ↓
9. Redirect to /dashboard/admin_dashboard/
   ↓
10. On page load, SessionManager::requireAuth() checks session
   ↓
11. Session contains auth_token, so page displays
```

## API Integration

Login calls: `POST /api/auth`

**Request:**
```json
{
  "username": "username",
  "password": "password"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@example.com",
      "role": "ADMIN",
      "is_active": 1
    },
    "token": "abc123...",
    "expires_at": "2026-05-30 08:32:00"
  },
  "timestamp": "2026-05-29 08:32:00"
}
```

## Security Features

1. **Server-Side Sessions:**
   - Token stored in $_SESSION (server-side)
   - Session ID in cookie (httpOnly by default in PHP)
   - Cannot be accessed by JavaScript

2. **Token Management:**
   - Tokens included in API requests via Authorization header
   - Token expiry checked on each page load
   - Expired tokens trigger automatic logout

3. **CURL for API Requests:**
   - API calls made server-to-server (more secure)
   - Token not exposed to client
   - 401 responses trigger logout

4. **SQL Injection Protection:**
   - API uses prepared statements
   - No direct database queries from login module

5. **XSS Protection:**
   - All user input echoed with `htmlspecialchars()`
   - Error messages sanitized

## Testing

### Test Login with Sample User
1. Ensure a user exists in database with:
   - username: `admin`
   - password_hash: bcrypt of `password123` (or use existing user)
2. Navigate to `http://localhost/WEBSYST1_FINAL/ams/login/`
3. Enter credentials
4. Click Login
5. Should redirect to admin dashboard

### Test SessionManager in PHP Script
```php
<?php
require_once '/xampp/htdocs/GEMS-AMES/ams/login/SessionManager.php';

// Test authentication check
if (SessionManager::isAuthenticated()) {
    echo "User is logged in: " . SessionManager::getUser()['username'];
} else {
    echo "User not authenticated";
}
?>
```

## Troubleshooting

### "Header already sent" error
- Make sure no HTML/whitespace before `<?php`
- Include SessionManager at top of file before any output

### Session not persisting
- Check PHP session.save_path is writable
- Check cookies are enabled in browser
- Check `session_start()` is called

### Login fails with valid credentials
- Check database has user with password_hash
- Verify API endpoint `/api/auth` is working
- Check API response format matches expected structure
- Review browser console for network errors

### Cannot access dashboard after login
- Check session contains auth_token
- Verify token hasn't expired
- Check SessionManager::requireAuth() is called
- Verify relative paths to SessionManager.php are correct

## Future Enhancements

- [ ] Remember me functionality (persistent login)
- [ ] Password reset email flow
- [ ] Two-factor authentication
- [ ] Login attempt rate limiting
- [ ] Session timeout warnings
- [ ] CSRF token validation
- [ ] Failed login audit logging
- [ ] Email verification on signup
