# AMS REST API - Implementation Guide

## Overview
This is a complete REST API built with PHP and PDO for the Academic Management System (AMS) using the `gem_db` database.

## Project Structure
```
api/
├── .htaccess                          # URL rewriting rules
├── index.php                          # Main API entry point with routing
├── API_DOCUMENTATION.md               # Complete API documentation
├── README.md                          # This file
├── classes/
│   ├── Database.php                   # PDO database wrapper
│   ├── ApiResponse.php                # Standardized response handler
│   └── BaseController.php             # Base controller for all endpoints
├── endpoints/
│   ├── Users.php                      # User management (CRUD)
│   ├── Auth.php                       # Authentication/Login
│   ├── Enrollments.php                # Student enrollments (CRUD)
│   ├── EnrollmentAddresses.php        # Enrollment addresses
│   ├── EnrollmentMedical.php          # Medical information
│   ├── EnrollmentParents.php          # Parent/Guardian information
│   ├── EnrollmentSpecialNeeds.php     # Special needs information
│   └── Lookup.php                     # Reference data (3 classes)
│       ├── MotherTongue
│       ├── Religion
│       └── IndigenousGroup
└── config/
    └── config.php                     # Database configuration (in parent folder)
```

## Key Features

✅ **RESTful API Design** - Following REST principles for clean, predictable endpoints
✅ **PDO-Based Database Access** - Secure prepared statements, no SQL injection
✅ **CRUD Operations** - Complete Create, Read, Update, Delete functionality
✅ **Dynamic Routing** - Automatic routing based on URL patterns
✅ **Error Handling** - Consistent error responses with appropriate HTTP status codes
✅ **Pagination Support** - Built-in pagination for list endpoints
✅ **Data Validation** - Server-side validation for data integrity
✅ **CORS Support** - Cross-origin requests enabled for frontend integration
✅ **Standardized Responses** - All responses follow consistent format

## Database Architecture

The API is designed around the `gem_db` database with the following main tables:

### Core Tables
- **enrollment2** - Main enrollment records
- **enrollment_address2** - Address information
- **enrollment_medical2** - Medical history and allergies
- **enrollment_parent2** - Parent/Guardian information
- **enrollment_special_needs2** - Special needs documentation
- **user_account** - User accounts and authentication

### Reference Tables
- **mother_tongue** - Language preferences
- **religion** - Religious affiliations
- **indigenous_group** - Indigenous group classifications

## API Architecture

### Request Flow
```
HTTP Request
    ↓
index.php (routing & initialization)
    ↓
Route matching with dynamic file loading
    ↓
Controller class instantiation
    ↓
Database operations via Database class
    ↓
PDO prepared statements (secure)
    ↓
ApiResponse standardized output
    ↓
JSON response to client
```

### Component Responsibilities

**index.php**
- Parses incoming requests
- Routes requests to appropriate controllers
- Initializes database connection
- Handles CORS headers

**Database.php**
- Wrapper around PDO
- Provides helper methods: query(), fetch(), insert(), update(), delete()
- Handles parameter binding for security
- Provides transaction support

**ApiResponse.php**
- Standardizes all API responses
- Manages HTTP status codes
- Handles pagination responses
- Provides CORS headers

**BaseController.php**
- Abstract base class for all controllers
- Implements common CRUD methods
- Handles request data parsing
- Provides validation framework

**Endpoint Controllers**
- Inherit from BaseController
- Implement CRUD operations
- Business logic for specific resources
- Table-specific validation and error handling

## Getting Started

### Installation
1. Ensure PHP 7.4+ is installed
2. Database configuration is in `/config/config.php`
3. No additional dependencies required (uses built-in PDO)

### Configuration
Edit `/config/config.php` to set your database credentials:
```php
$host = "localhost";
$db   = "gem_db";
$user = "root";
$pass = "";
```

### Enable .htaccess (Optional)
For clean URLs without `index.php`, ensure `.htaccess` is enabled:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9/_-]*)$ index.php?request=$1 [QSA,L]
```

## API Usage Examples

### 1. User Authentication
```bash
# Login
POST /api/auth
Content-Type: application/json

{
  "username": "john_doe",
  "password": "password123"
}
```

### 2. Get Enrollments
```bash
# List all enrollments (paginated)
GET /api/enrollments?page=1&limit=10

# Filter by grade level
GET /api/enrollments?grade_level=6&school_year=2025-2026

# Get specific enrollment with related data
GET /api/enrollments/John%20Doe
```

### 3. Create New Enrollment
```bash
POST /api/enrollments
Content-Type: application/json

{
  "fk_full_name_bd": "Jane Smith",
  "ed_grade_level": "7",
  "ed_lrn": 123456789012,
  "ed_school_year": "2025-2026",
  "pi_first_name": "Jane",
  "pi_last_name": "Smith",
  "pi_birth_date": "2015-03-20",
  "pi_sex": "FEMALE",
  "pi_mother_tongue_id": 2,
  "pi_religion_id": 1,
  "user_account_id": 1
}
```

### 4. Update Enrollment
```bash
PUT /api/enrollments/Jane%20Smith
Content-Type: application/json

{
  "ed_grade_level": "8",
  "ed_school_year": "2026-2027"
}
```

### 5. Get Reference Data
```bash
# Get all mother tongue languages
GET /api/mother-tongue

# Get all religions
GET /api/religions

# Get all indigenous groups
GET /api/indigenous-groups
```

## Response Examples

### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "role": "PARENT",
    "created_at": "2026-05-29 08:00:00",
    "is_active": 1
  },
  "timestamp": "2026-05-29 08:32:00"
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [...],
  "pagination": {
    "total": 150,
    "page": 1,
    "limit": 10,
    "pages": 15
  },
  "timestamp": "2026-05-29 08:32:00"
}
```

### Error Response (400/404/500)
```json
{
  "success": false,
  "message": "User not found",
  "errors": {},
  "timestamp": "2026-05-29 08:32:00"
}
```

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Successful GET, PUT, DELETE |
| 201 | Created - Successful POST |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Authentication failed |
| 404 | Not Found - Resource doesn't exist |
| 409 | Conflict - Resource already exists |
| 422 | Unprocessable Entity - Validation error |
| 500 | Internal Server Error - Server error |

## Security Considerations

### ✅ Implemented
- **Prepared Statements** - All queries use parameterized queries
- **Password Hashing** - Bcrypt hashing for passwords
- **Input Validation** - Server-side validation on all inputs
- **CORS Headers** - Allow cross-origin requests

### ⚠️ To Implement Before Production
- [ ] JWT token authentication
- [ ] Role-based access control (RBAC)
- [ ] HTTPS/SSL encryption
- [ ] Rate limiting
- [ ] Request logging & audit trails
- [ ] Input sanitization
- [ ] API key management
- [ ] SQL injection prevention (already done via PDO)

## Adding New Endpoints

### 1. Create a new controller in `/endpoints/`

```php
<?php
namespace API;

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ApiResponse.php';

class NewResource extends BaseController
{
    protected $table = 'table_name';

    public function index()
    {
        // List all
    }

    public function show($id)
    {
        // Get one
    }

    public function store()
    {
        // Create
    }

    public function update($id)
    {
        // Update
    }

    public function destroy($id)
    {
        // Delete
    }
}
?>
```

### 2. Add route in `index.php`

```php
$routes = [
    'new-resource' => ['controller' => 'NewResource', 'file' => 'NewResource.php'],
    // ... other routes
];
```

### 3. Access the endpoint

```
GET /api/new-resource
GET /api/new-resource/{id}
POST /api/new-resource
PUT /api/new-resource/{id}
DELETE /api/new-resource/{id}
```

## Testing the API

### Using cURL
```bash
curl -X GET http://localhost/WEBSYST1_FINAL/ams/api/enrollments
curl -X POST http://localhost/WEBSYST1_FINAL/ams/api/auth \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test123"}'
```

### Using Postman
1. Import API endpoints from API_DOCUMENTATION.md
2. Set base URL: `http://localhost/WEBSYST1_FINAL/ams/api/`
3. Test endpoints with sample data

### Using JavaScript/Fetch
```javascript
fetch('http://localhost/WEBSYST1_FINAL/ams/api/enrollments?page=1&limit=10')
  .then(response => response.json())
  .then(data => console.log(data));
```

## Troubleshooting

### 404 Not Found
- Check URL spelling and case sensitivity
- Ensure controller file exists
- Verify route mapping in index.php

### 400 Bad Request
- Verify request method (GET, POST, PUT, DELETE)
- Check Content-Type header is `application/json`
- Validate request body JSON syntax

### 422 Unprocessable Entity
- Required fields are missing
- Field validation failed
- Check error messages in response

### 500 Internal Server Error
- Check PHP error logs
- Verify database connection
- Check file permissions

## Performance Optimization

### Implemented
- Pagination for large result sets
- Query optimization with indexes
- Prepared statements for caching

### Future Improvements
- [ ] Query result caching
- [ ] Database connection pooling
- [ ] API response compression
- [ ] Query optimization
- [ ] Database indexing review

## Documentation

- **API_DOCUMENTATION.md** - Complete endpoint reference
- **This README** - Setup and architecture guide
- **Code comments** - Inline documentation in source files

## Version History

- **v1.0** - Initial REST API implementation
  - User management (CRUD)
  - Enrollment management (CRUD)
  - Related data endpoints (addresses, medical, parents, special needs)
  - Reference data endpoints (lookup tables)
  - Basic authentication

## Support

For issues or questions:
1. Check API_DOCUMENTATION.md
2. Review error messages in responses
3. Check server logs for detailed errors
4. Verify database is running and accessible
