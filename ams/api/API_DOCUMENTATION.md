# AMS REST API Documentation

## Base URL
```
http://localhost/WEBSYST1_FINAL/ams/api/
```

## Authentication
Currently uses simple token-based authentication. Include the token in the request header:
```
Authorization: Bearer {token}
```

---

## API Endpoints

### Authentication

#### Login
- **POST** `/auth`
- **Description**: User login endpoint
- **Request Body**:
  ```json
  {
    "username": "user@example.com",
    "password": "password123"
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Login successful",
    "data": {
      "user": {
        "id": 1,
        "username": "user@example.com",
        "email": "user@example.com",
        "role": "PARENT",
        "is_active": 1
      },
      "token": "abc123def456...",
      "expires_at": "2026-05-30 08:32:00"
    },
    "timestamp": "2026-05-29 08:32:00"
  }
  ```

---

### Users Management

#### List All Users
- **GET** `/users?page=1&limit=10`
- **Query Parameters**:
  - `page`: Page number (default: 1)
  - `limit`: Items per page (default: 10, max: 100)
- **Response**: Paginated user list

#### Get User by ID
- **GET** `/users/{id}`
- **Response**: Single user object

#### Create User
- **POST** `/users`
- **Request Body**:
  ```json
  {
    "username": "john_doe",
    "email": "john@example.com",
    "password": "securepass123",
    "role": "PARENT"
  }
  ```
- **Response**: Created user object with `201 Created` status

#### Update User
- **PUT** `/users/{id}`
- **Request Body** (all fields optional):
  ```json
  {
    "username": "updated_username",
    "email": "newemail@example.com",
    "password": "newpass123",
    "role": "TEACHER",
    "is_active": 1
  }
  ```
- **Response**: Updated user object

#### Delete User
- **DELETE** `/users/{id}`
- **Response**: Success message with `200 OK` status

---

### Enrollments

#### List All Enrollments
- **GET** `/enrollments?page=1&limit=10&grade_level=6&school_year=2025-2026&user_id=1`
- **Query Parameters**:
  - `page`: Page number (default: 1)
  - `limit`: Items per page (default: 10, max: 100)
  - `grade_level`: Filter by grade level (optional)
  - `school_year`: Filter by school year (optional)
  - `user_id`: Filter by user/parent (optional)
- **Response**: Paginated enrollment list

#### Get Enrollment Details
- **GET** `/enrollments/{full_name}`
- **Response**: 
  ```json
  {
    "success": true,
    "message": "Success",
    "data": {
      "fk_full_name_bd": "John Doe",
      "ed_grade_level": "6",
      "ed_lrn": 123456789012,
      "ed_school_year": "2025-2026",
      "pi_first_name": "John",
      "pi_last_name": "Doe",
      "pi_birth_date": "2015-06-15",
      "pi_sex": "MALE",
      "pi_mother_tongue_id": 2,
      "mother_tongue_name": "Tagalog",
      "pi_religion_id": 1,
      "religion_name": "Roman Catholic",
      "address": { ... },
      "medical": { ... },
      "parents": [ ... ],
      "special_needs": { ... }
    },
    "timestamp": "2026-05-29 08:32:00"
  }
  ```

#### Create Enrollment
- **POST** `/enrollments`
- **Request Body**:
  ```json
  {
    "fk_full_name_bd": "John Doe",
    "ed_grade_level": "6",
    "ed_lrn": 123456789012,
    "ed_school_year": "2025-2026",
    "pi_first_name": "John",
    "pi_last_name": "Doe",
    "pi_middle_name": "M",
    "pi_birth_date": "2015-06-15",
    "pi_sex": "MALE",
    "pi_mother_tongue_id": 2,
    "pi_religion_id": 1,
    "ac_indigenous_group_id": 1,
    "user_account_id": 1,
    "li_learning_modality": "BLENDED (COMBINATION)"
  }
  ```
- **Response**: Created enrollment object with `201 Created` status

#### Update Enrollment
- **PUT** `/enrollments/{full_name}`
- **Request Body** (all fields optional):
  ```json
  {
    "ed_grade_level": "7",
    "ed_school_year": "2026-2027",
    "pi_mother_tongue_id": 3,
    "pi_religion_id": 2
  }
  ```
- **Response**: Updated enrollment object

#### Delete Enrollment
- **DELETE** `/enrollments/{full_name}`
- **Response**: Success message

---

### Enrollment Addresses

#### Get Address
- **GET** `/addresses/{full_name}`
- **Response**: Address details

#### Create Address
- **POST** `/addresses`
- **Request Body**:
  ```json
  {
    "fk_full_name_bd": "John Doe",
    "ca_house_number": "123",
    "ca_street_name": "Main Street",
    "ca_barangay": "San Antonio",
    "ca_municipality": "Quezon City",
    "ca_provice": "Metro Manila",
    "ca_country": "Philippines",
    "ca_zipcode": 1109,
    "ca_address_status": "Owned",
    "pa_house_number": "456",
    "pa_street_name": "Secondary St",
    "pa_barangay": "Diliman",
    "pa_municipality": "Quezon City",
    "pa_province": "Metro Manila",
    "pa_country": "Philippines",
    "pa_zip_code": 1101,
    "pa_address_status": "Rental"
  }
  ```
- **Response**: Created address object

#### Update Address
- **PUT** `/addresses/{full_name}`
- **Response**: Updated address object

#### Delete Address
- **DELETE** `/addresses/{full_name}`
- **Response**: Success message

---

### Enrollment Medical Info

#### Get Medical Info
- **GET** `/medical/{full_name}`
- **Response**: Medical information

#### Create Medical Info
- **POST** `/medical`
- **Request Body**:
  ```json
  {
    "fk_full_name_bd": "John Doe",
    "mf_a_medicine": "Penicillin",
    "mf_a_pollen": "Dust",
    "mf_a_food": "Shellfish",
    "mf_o_medical_conditions": "ASTHMA",
    "mf_tm_type": "Inhaler",
    "mf_tm_dosage_schedule": "As needed",
    "mf_exposure_c_v": 0
  }
  ```
- **Response**: Created medical record

#### Update Medical Info
- **PUT** `/medical/{full_name}`
- **Response**: Updated medical record

#### Delete Medical Info
- **DELETE** `/medical/{full_name}`
- **Response**: Success message

---

### Enrollment Parents/Guardians

#### Get Parents Info
- **GET** `/parents/{full_name}`
- **Response**: Array of parent records

#### Create Parent Info
- **POST** `/parents`
- **Request Body**:
  ```json
  {
    "fk_full_name_bd": "John Doe",
    "fi_last_name": "Doe",
    "fi_first_name": "Jane",
    "fi_middle_name": "M",
    "fi_contact_number": "+639123456789",
    "fi_occupation": "Teacher",
    "fi_relationship_status": "Married",
    "fi_communication": "SMS",
    "ec_to_contact": "FATHER"
  }
  ```
- **Response**: Created parent record

#### Update Parent Info
- **PUT** `/parents/{full_name}`
- **Response**: Updated parent records

#### Delete Parent Info
- **DELETE** `/parents/{full_name}`
- **Response**: Success message

---

### Special Needs Info

#### Get Special Needs
- **GET** `/special-needs/{full_name}`
- **Response**: Special needs information

#### Create Special Needs
- **POST** `/special-needs`
- **Request Body**:
  ```json
  {
    "fk_full_name_bd": "John Doe",
    "snep_a1_diagnosis": "HEARING IMPAIRMENT",
    "snep_a2_manifestations": "DIFFICULTY IN HEARING",
    "snep_pwd_id": 1
  }
  ```
- **Response**: Created special needs record

#### Update Special Needs
- **PUT** `/special-needs/{full_name}`
- **Response**: Updated special needs record

#### Delete Special Needs
- **DELETE** `/special-needs/{full_name}`
- **Response**: Success message

---

### Reference Data (Lookup Tables)

#### Mother Tongue Languages

**List All**
- **GET** `/mother-tongue`
- **Response**: Array of all mother tongue languages

**Get Single**
- **GET** `/mother-tongue/{id}`

**Create**
- **POST** `/mother-tongue`
- **Request Body**: `{"name": "Ilocano"}`

**Update**
- **PUT** `/mother-tongue/{id}`
- **Request Body**: `{"name": "Ilokano"}`

**Delete**
- **DELETE** `/mother-tongue/{id}`

---

#### Religions

**List All**
- **GET** `/religions`
- **Get Single**: **GET** `/religions/{id}`
- **Create**: **POST** `/religions` with `{"name": "Religion Name"}`
- **Update**: **PUT** `/religions/{id}`
- **Delete**: **DELETE** `/religions/{id}`

---

#### Indigenous Groups

**List All**
- **GET** `/indigenous-groups`
- **Get Single**: **GET** `/indigenous-groups/{id}`
- **Create**: **POST** `/indigenous-groups` with `{"name": "Group Name"}`
- **Update**: **PUT** `/indigenous-groups/{id}`
- **Delete**: **DELETE** `/indigenous-groups/{id}`

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": {},
  "timestamp": "2026-05-29 08:32:00"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {},
  "timestamp": "2026-05-29 08:32:00"
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [],
  "pagination": {
    "total": 100,
    "page": 1,
    "limit": 10,
    "pages": 10
  },
  "timestamp": "2026-05-29 08:32:00"
}
```

---

## HTTP Status Codes

- `200 OK` - Successful GET, PUT, DELETE
- `201 Created` - Successful POST
- `400 Bad Request` - Missing or invalid parameters
- `401 Unauthorized` - Authentication failed
- `403 Forbidden` - Permission denied
- `404 Not Found` - Resource not found
- `409 Conflict` - Resource already exists (e.g., duplicate username)
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error

---

## Example Usage with cURL

### Login
```bash
curl -X POST http://localhost/WEBSYST1_FINAL/ams/api/auth \
  -H "Content-Type: application/json" \
  -d '{"username":"john_doe","password":"password123"}'
```

### Get All Enrollments
```bash
curl -X GET "http://localhost/WEBSYST1_FINAL/ams/api/enrollments?page=1&limit=10" \
  -H "Authorization: Bearer token_here"
```

### Create Enrollment
```bash
curl -X POST http://localhost/WEBSYST1_FINAL/ams/api/enrollments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer token_here" \
  -d '{
    "fk_full_name_bd":"John Doe",
    "ed_grade_level":"6",
    "ed_lrn":123456789012,
    "ed_school_year":"2025-2026",
    "pi_first_name":"John",
    "pi_last_name":"Doe"
  }'
```

### Update User
```bash
curl -X PUT http://localhost/WEBSYST1_FINAL/ams/api/users/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer token_here" \
  -d '{"email":"newemail@example.com","role":"TEACHER"}'
```

### Delete Enrollment
```bash
curl -X DELETE http://localhost/WEBSYST1_FINAL/ams/api/enrollments/John%20Doe \
  -H "Authorization: Bearer token_here"
```

---

## Notes

1. **URL Encoding**: Full names and IDs with spaces should be URL-encoded (space = `%20`)
2. **Pagination**: Default limit is 10, maximum is 100
3. **Date Format**: Use `YYYY-MM-DD` format for dates
4. **CORS**: API accepts requests from all origins for now (development only)
5. **Security**: Update authentication to use JWT tokens for production
6. **Validation**: All required fields are validated server-side

---

## Future Enhancements

- [ ] JWT token authentication
- [ ] Role-based access control (RBAC)
- [ ] Request logging and audit trails
- [ ] Rate limiting
- [ ] API key management
- [ ] Webhook support
- [ ] GraphQL endpoint
