# AMS Dashboard

## Overview
PHP-based skeleton dashboards for the Academic Management System (AMS). These are minimal implementations focused on authentication and API integration.

## Directory Structure

```
dashboard/
├── admin_dashboard/
│   └── index.php          # Admin dashboard
└── teacher_dashboard/
    └── index.php          # Teacher dashboard
```

## Admin Dashboard

### Location
```
http://localhost/WEBSYST1_FINAL/ams/dashboard/admin_dashboard/
```

### Files
- `index.php` - Dashboard template with authentication and API testing

### Features
- Displays current user information
- Shows logged-in username in header
- Test button to fetch users from API (`GET /api/users`)
- Logout link
- Auto-redirects to login if not authenticated
- No styling (skeleton only)

### Usage
1. Login at `/ams/login/`
2. Redirected to admin dashboard
3. Click "Fetch Users" to test API connection
4. Click "Logout" to end session

## Teacher Dashboard

### Location
```
http://localhost/WEBSYST1_FINAL/ams/dashboard/teacher_dashboard/
```

### Files
- `index.php` - Dashboard template with authentication and API testing

### Features
- Displays current user information
- Shows logged-in username in header
- Test button to fetch enrollments from API (`GET /api/enrollments`)
- Logout link
- Auto-redirects to login if not authenticated
- No styling (skeleton only)

### Usage
1. Login at `/ams/login/`
2. Redirected to admin dashboard (by default)
3. Navigate to `/ams/dashboard/teacher_dashboard/`
4. Click "Fetch Enrollments" to test API connection
5. Click "Logout" to end session

## Implementation Details

### Authentication
```php
// At top of each dashboard file
require_once __DIR__ . '/../../login/SessionManager.php';

// Require authentication (redirects if not logged in)
SessionManager::requireAuth();

// Get current user
$user = SessionManager::getUser();
```

### API Testing
Each dashboard has a form that tests API connectivity:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_api'])) {
    $response = SessionManager::apiRequest($apiUrl, 'GET');
    
    if ($response['success']) {
        $testResult = json_encode($response['data'], JSON_PRETTY_PRINT);
    } else {
        $testError = $response['data']['message'] ?? 'API request failed';
    }
}
```

### User Information Display
```php
<li><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></li>
<li><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></li>
<li><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
```

## Session Management

All dashboards use the same SessionManager for authentication:

```php
// Check if authenticated
if (!SessionManager::isAuthenticated()) {
    header('Location: /ams/login/');
    exit;
}

// Get user info
$user = SessionManager::getUser();

// Make API request with token
$response = SessionManager::apiRequest($url, 'GET');
```

## API Endpoints Used

### Admin Dashboard
- `GET /api/users` - Lists all users with pagination

**Response:**
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [ /* array of users */ ],
  "pagination": {
    "total": 100,
    "page": 1,
    "limit": 10,
    "pages": 10
  }
}
```

### Teacher Dashboard
- `GET /api/enrollments` - Lists all enrollments with pagination and filters

**Response:**
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [ /* array of enrollments */ ],
  "pagination": {
    "total": 50,
    "page": 1,
    "limit": 10,
    "pages": 5
  }
}
```

## Testing the Dashboards

### Test Admin Dashboard
1. Navigate to `http://localhost/WEBSYST1_FINAL/ams/login/`
2. Enter admin credentials
3. Should redirect to `/dashboard/admin_dashboard/`
4. User info should display
5. Click "Fetch Users" button
6. API response should display as JSON

### Test Teacher Dashboard
1. Login (redirects to admin dashboard by default)
2. Manually navigate to `/ams/dashboard/teacher_dashboard/`
3. User info should display
4. Click "Fetch Enrollments" button
5. API response should display as JSON

### Test Logout
1. From any dashboard, click "Logout" link
2. Should clear session
3. Should redirect to login page
4. Browser back button should not work (session cleared)

## Creating Additional Dashboards

To create a new dashboard for a different role:

```php
<?php
// new_dashboard/index.php

require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/../../config/config.php';

// Require authentication
SessionManager::requireAuth();

$user = SessionManager::getUser();

// Optional: Restrict to specific role
if (!SessionManager::hasRole('NEW_ROLE')) {
    header('Location: ../../login/');
    exit;
}

// Get API data
$apiResponse = SessionManager::apiRequest('http://localhost/WEBSYST1_FINAL/ams/api/endpoint', 'GET');
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Dashboard</title>
</head>
<body>
    <h1>New Dashboard</h1>
    <p>Logged in as: <?php echo htmlspecialchars($user['username']); ?></p>
    <!-- Content here -->
</body>
</html>
```

## Security Considerations

1. **Session Security:**
   - Tokens stored in server-side $_SESSION
   - Session ID in httpOnly cookie (default PHP behavior)
   - Cannot be accessed by JavaScript

2. **Token Expiry:**
   - Token expiry checked on each page load
   - Expired tokens trigger automatic logout and redirect

3. **API Calls:**
   - Made server-to-server (PHP to PHP)
   - Token included in Authorization header
   - 401 responses trigger logout

4. **Output Escaping:**
   - User data echoed with `htmlspecialchars()`
   - Prevents XSS attacks

5. **Session Fixation:**
   - Session ID changed on each login (PHP default)
   - Old session destroyed on logout

## Troubleshooting

### Redirects to login immediately
- Check SessionManager::isAuthenticated() logic
- Verify session is being set in process.php
- Check cookie settings in php.ini
- Clear browser cookies and try again

### User info shows as blank
- Check SessionManager::getUser() is retrieving data
- Verify session contains auth_user key
- Check htmlspecialchars() is not over-escaping

### API test returns error
- Check API endpoint is correct
- Verify token is valid (not expired)
- Check API response format
- Review API_DOCUMENTATION.md for endpoint details

### Cannot access teacher dashboard from link
- Navigate directly to URL (may need to check role restrictions)
- By default, all authenticated users can access any dashboard
- Add role checks if needed: `SessionManager::hasRole('TEACHER')`

## Future Enhancements

### Admin Dashboard
- [ ] User management interface
- [ ] Enrollment list with filters
- [ ] Statistics/dashboard metrics
- [ ] System settings panel
- [ ] Logs viewer
- [ ] Data export functionality

### Teacher Dashboard
- [ ] Class list
- [ ] Student list
- [ ] Attendance tracking
- [ ] Grades management
- [ ] Progress reports
- [ ] Communication center

### All Dashboards
- [ ] CSS styling
- [ ] Sidebar navigation
- [ ] Search functionality
- [ ] Data tables with sorting
- [ ] Forms for data entry
- [ ] Real-time updates
- [ ] Print functionality
- [ ] Mobile responsiveness
- [ ] Dark mode
- [ ] Customizable widgets

## Development Tips

### Include SessionManager in Template
```php
<?php
require_once __DIR__ . '/../../login/SessionManager.php';
SessionManager::requireAuth();
?>
```

### Access User Data
```php
$user = SessionManager::getUser();
echo $user['id'];
echo $user['username'];
echo $user['email'];
echo $user['role'];
echo $user['is_active'];
```

### Make API Request
```php
$response = SessionManager::apiRequest(
    'http://localhost/WEBSYST1_FINAL/ams/api/users',
    'GET'
);

if ($response['success']) {
    $data = $response['data'];
    $total = $data['pagination']['total'];
    $items = $data['data'];
}
```

### Check User Role
```php
if (SessionManager::hasRole('ADMIN')) {
    // Show admin features
} else if (SessionManager::hasRole('TEACHER')) {
    // Show teacher features
}
```

## Support

For issues:
1. Check error logs in `/xampp/apache/logs/`
2. Check PHP error log
3. Review SessionManager.php for session state
4. Verify API is responding at `/api/` endpoints
5. Check database connection in config.php
