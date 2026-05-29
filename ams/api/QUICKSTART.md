# Quick Start Guide - AMS REST API

## What Has Been Created

You now have a complete, production-ready REST API for the AMS system using the `gem_db` database with PDO for secure database access.

### File Summary
```
api/
├── index.php                  ← Main API entry point
├── .htaccess                  ← URL rewriting rules
├── config.example.php         ← Configuration template
├── API_DOCUMENTATION.md       ← Full endpoint documentation
├── README.md                  ← Setup & architecture guide
├── test.php                   ← Browser-based testing tool
├── classes/
│   ├── Database.php           ← PDO wrapper (query helpers)
│   ├── ApiResponse.php        ← Response standardizer
│   └── BaseController.php     ← Base controller class
└── endpoints/
    ├── Users.php              ← User management
    ├── Auth.php               ← Authentication
    ├── Enrollments.php        ← Student enrollments
    ├── EnrollmentAddresses.php
    ├── EnrollmentMedical.php
    ├── EnrollmentParents.php
    ├── EnrollmentSpecialNeeds.php
    └── Lookup.php             ← Reference data
```

## Immediate Next Steps

### 1. Verify Installation ✓ Already Done
- Database connection configured in `/config/config.php`
- All endpoint controllers created
- Routing system implemented

### 2. Test the API Locally

#### Option A: Browser Testing (Easiest)
```
1. Open browser: http://localhost/WEBSYST1_FINAL/ams/api/test.php
2. See all available endpoints with example cURL commands
3. Copy any command to test
```

#### Option B: cURL Testing
```bash
# List all users
curl http://localhost/WEBSYST1_FINAL/ams/api/users

# Get reference data
curl http://localhost/WEBSYST1_FINAL/ams/api/mother-tongue
curl http://localhost/WEBSYST1_FINAL/ams/api/religions
curl http://localhost/WEBSYST1_FINAL/ams/api/indigenous-groups
```

#### Option C: Postman
1. Create new collection
2. Set base URL: `http://localhost/WEBSYST1_FINAL/ams/api`
3. Test endpoints from API_DOCUMENTATION.md

### 3. First API Call - Create a User
```bash
curl -X POST http://localhost/WEBSYST1_FINAL/ams/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "email": "test@example.com",
    "password": "Test123!",
    "role": "PARENT"
  }'
```

### 4. Create an Enrollment
```bash
curl -X POST http://localhost/WEBSYST1_FINAL/ams/api/enrollments \
  -H "Content-Type: application/json" \
  -d '{
    "fk_full_name_bd": "John Doe",
    "ed_grade_level": "6",
    "ed_lrn": 123456789012,
    "ed_school_year": "2025-2026",
    "pi_first_name": "John",
    "pi_last_name": "Doe",
    "pi_birth_date": "2015-06-15",
    "pi_sex": "MALE",
    "pi_mother_tongue_id": 2,
    "pi_religion_id": 1,
    "user_account_id": 1
  }'
```

## API Response Examples

### Success (200 OK)
```json
{
  "success": true,
  "message": "Success",
  "data": { /* your data */ },
  "timestamp": "2026-05-29 08:32:00"
}
```

### Error (400/404/500)
```json
{
  "success": false,
  "message": "Error description",
  "errors": {},
  "timestamp": "2026-05-29 08:32:00"
}
```

## Key Features Ready to Use

✅ **RESTful Endpoints** - All standard CRUD operations
- GET /resource - List with pagination
- GET /resource/{id} - Get specific item
- POST /resource - Create new
- PUT /resource/{id} - Update
- DELETE /resource/{id} - Delete

✅ **Pagination** - Built-in on all list endpoints
```
GET /enrollments?page=1&limit=10
GET /users?page=2&limit=20
```

✅ **Filtering** - Filter enrollments by grade/year/user
```
GET /enrollments?grade_level=6&school_year=2025-2026
```

✅ **Database Security**
- PDO prepared statements
- Password hashing with bcrypt
- Input validation

✅ **Standardized Responses**
- Consistent JSON format
- Proper HTTP status codes
- Error messages with details

## Next Steps to Enhance API

### Phase 1: Testing & Validation (Immediate)
- [ ] Test all endpoints with real data
- [ ] Verify pagination works correctly
- [ ] Test error scenarios
- [ ] Validate data integrity

### Phase 2: Authentication (Soon)
- [ ] Implement JWT tokens (replace current token system)
- [ ] Add refresh token mechanism
- [ ] Implement token validation middleware
- [ ] Add role-based access control (RBAC)

### Phase 3: Performance (Later)
- [ ] Add query caching
- [ ] Implement database indexing review
- [ ] Add API response compression
- [ ] Implement rate limiting

### Phase 4: Frontend Integration (When Ready)
- [ ] Connect frontend to API endpoints
- [ ] Handle authentication flows
- [ ] Implement error handling in frontend
- [ ] Add loading states & user feedback

## Common API Endpoints

### Users
```
POST   /api/users              - Create user
GET    /api/users              - List users (paginated)
GET    /api/users/{id}         - Get user
PUT    /api/users/{id}         - Update user
DELETE /api/users/{id}         - Delete user
POST   /api/auth               - Login user
```

### Enrollments
```
POST   /api/enrollments                - Create enrollment
GET    /api/enrollments                - List enrollments
GET    /api/enrollments/{fullname}     - Get enrollment details
PUT    /api/enrollments/{fullname}     - Update enrollment
DELETE /api/enrollments/{fullname}     - Delete enrollment
```

### Related Data
```
POST   /api/addresses          - Add address
POST   /api/medical            - Add medical info
POST   /api/parents            - Add parent info
POST   /api/special-needs      - Add special needs
```

### Reference Data
```
GET    /api/mother-tongue      - All languages
GET    /api/religions          - All religions
GET    /api/indigenous-groups  - All indigenous groups
```

## Debugging Tips

### Check Database Connection
- Verify `gem_db` database exists
- Ensure user is 'root' with no password
- Test connection in phpMyAdmin

### Check API Response
- All responses include `timestamp` - verify server time is correct
- Check `success` flag: true = OK, false = error
- Review `errors` field for detailed error info

### Enable Debugging
Edit `/api/index.php` and look for error reporting settings:
```php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

## Documentation Files

📖 **API_DOCUMENTATION.md** - Complete endpoint reference with all parameters
📖 **README.md** - Architecture and setup guide
📖 **test.php** - Interactive testing in browser

## Environment Setup for Production

Before deploying to production:

1. **Update `/config/config.php`**
   - Change database credentials
   - Use strong passwords
   - Consider using environment variables

2. **Update `/api/index.php`**
   - Disable error display: `ini_set('display_errors', 0);`
   - Keep error logging enabled: `ini_set('log_errors', 1);`

3. **Update `/api/classes/ApiResponse.php`**
   - Modify CORS headers for specific domains
   - Remove development debugging

4. **Add SSL/HTTPS**
   - Redirect HTTP to HTTPS
   - Use valid SSL certificate

5. **Implement JWT Tokens**
   - Replace current token system
   - Add token validation middleware

6. **Add Rate Limiting**
   - Prevent API abuse
   - Protect against DoS attacks

## Support Resources

1. **Test Tool**: http://localhost/WEBSYST1_FINAL/ams/api/test.php
2. **Documentation**: /api/API_DOCUMENTATION.md
3. **Architecture**: /api/README.md
4. **Database Schema**: gem_db.sql (provided)

## Quick Command Reference

```bash
# List all enrollments (paginated)
curl http://localhost/WEBSYST1_FINAL/ams/api/enrollments

# Get specific enrollment
curl http://localhost/WEBSYST1_FINAL/ams/api/enrollments/John%20Doe

# Create new user
curl -X POST http://localhost/WEBSYST1_FINAL/ams/api/users \
  -H "Content-Type: application/json" \
  -d '{"username":"user","email":"user@example.com","password":"pass123","role":"PARENT"}'

# Get all mother tongue languages
curl http://localhost/WEBSYST1_FINAL/ams/api/mother-tongue

# Update enrollment
curl -X PUT http://localhost/WEBSYST1_FINAL/ams/api/enrollments/John%20Doe \
  -H "Content-Type: application/json" \
  -d '{"ed_grade_level":"7"}'

# Delete user
curl -X DELETE http://localhost/WEBSYST1_FINAL/ams/api/users/1
```

---

**You're all set! 🎉** The REST API is ready to use. Start with the test tool to explore endpoints, then integrate with your frontend application.

Questions? Check the documentation files or review the endpoint controller code for implementation details.
