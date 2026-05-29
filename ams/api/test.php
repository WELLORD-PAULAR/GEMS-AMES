<?php
/**
 * API Testing Helper
 * Quick reference for testing API endpoints
 * 
 * Usage: Access this file in browser to see example curl commands
 * or use the PHP functions to test programmatically
 */

define('API_BASE_URL', 'http://localhost/WEBSYST1_FINAL/ams/api');

/**
 * Make API request
 */
function apiRequest($method, $endpoint, $data = null, $token = null)
{
    $url = API_BASE_URL . $endpoint;
    
    $options = [
        'http' => [
            'method' => $method,
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            'timeout' => 10
        ]
    ];
    
    if ($token) {
        $options['http']['header'][] = 'Authorization: Bearer ' . $token;
    }
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    return json_decode($response, true);
}

// ============================================================
// TEST EXAMPLES
// ============================================================

$examples = [];

// 1. LOGIN TEST
$examples['login'] = [
    'name' => 'User Login',
    'method' => 'POST',
    'endpoint' => '/auth',
    'data' => [
        'username' => 'admin',
        'password' => 'admin123'
    ],
    'curl' => 'curl -X POST ' . API_BASE_URL . '/auth ' .
             '-H "Content-Type: application/json" ' .
             '-d \'{
               "username": "admin",
               "password": "admin123"
             }\''
];

// 2. CREATE USER TEST
$examples['create_user'] = [
    'name' => 'Create User',
    'method' => 'POST',
    'endpoint' => '/users',
    'data' => [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'Test123!@#',
        'role' => 'PARENT'
    ],
    'curl' => 'curl -X POST ' . API_BASE_URL . '/users ' .
             '-H "Content-Type: application/json" ' .
             '-d \'{
               "username": "testuser",
               "email": "test@example.com",
               "password": "Test123!@#",
               "role": "PARENT"
             }\''
];

// 3. LIST USERS TEST
$examples['list_users'] = [
    'name' => 'List Users (Paginated)',
    'method' => 'GET',
    'endpoint' => '/users?page=1&limit=10',
    'curl' => 'curl -X GET "' . API_BASE_URL . '/users?page=1&limit=10"'
];

// 4. GET USER TEST
$examples['get_user'] = [
    'name' => 'Get User by ID',
    'method' => 'GET',
    'endpoint' => '/users/1',
    'curl' => 'curl -X GET ' . API_BASE_URL . '/users/1'
];

// 5. CREATE ENROLLMENT TEST
$examples['create_enrollment'] = [
    'name' => 'Create Enrollment',
    'method' => 'POST',
    'endpoint' => '/enrollments',
    'data' => [
        'fk_full_name_bd' => 'John Michael Santos',
        'ed_grade_level' => '6',
        'ed_lrn' => 123456789012,
        'ed_school_year' => '2025-2026',
        'pi_first_name' => 'John',
        'pi_last_name' => 'Santos',
        'pi_middle_name' => 'Michael',
        'pi_birth_date' => '2015-06-15',
        'pi_sex' => 'MALE',
        'pi_mother_tongue_id' => 2,
        'pi_religion_id' => 1,
        'ac_indigenous_group_id' => 1,
        'user_account_id' => 1
    ],
    'curl' => 'curl -X POST ' . API_BASE_URL . '/enrollments ' .
             '-H "Content-Type: application/json" ' .
             '-d \'{
               "fk_full_name_bd": "John Michael Santos",
               "ed_grade_level": "6",
               "ed_lrn": 123456789012,
               "ed_school_year": "2025-2026",
               "pi_first_name": "John",
               "pi_last_name": "Santos",
               "pi_birth_date": "2015-06-15",
               "pi_sex": "MALE",
               "pi_mother_tongue_id": 2,
               "pi_religion_id": 1,
               "user_account_id": 1
             }\''
];

// 6. LIST ENROLLMENTS TEST
$examples['list_enrollments'] = [
    'name' => 'List Enrollments',
    'method' => 'GET',
    'endpoint' => '/enrollments?page=1&limit=10&grade_level=6',
    'curl' => 'curl -X GET "' . API_BASE_URL . '/enrollments?page=1&limit=10&grade_level=6"'
];

// 7. GET ENROLLMENT TEST
$examples['get_enrollment'] = [
    'name' => 'Get Enrollment Details',
    'method' => 'GET',
    'endpoint' => '/enrollments/John%20Michael%20Santos',
    'curl' => 'curl -X GET ' . API_BASE_URL . '/enrollments/John%20Michael%20Santos'
];

// 8. UPDATE ENROLLMENT TEST
$examples['update_enrollment'] = [
    'name' => 'Update Enrollment',
    'method' => 'PUT',
    'endpoint' => '/enrollments/John Michael Santos',
    'data' => [
        'ed_grade_level' => '7',
        'ed_school_year' => '2026-2027'
    ],
    'curl' => 'curl -X PUT ' . API_BASE_URL . '/enrollments/John%20Michael%20Santos ' .
             '-H "Content-Type: application/json" ' .
             '-d \'{
               "ed_grade_level": "7",
               "ed_school_year": "2026-2027"
             }\''
];

// 9. CREATE ADDRESS TEST
$examples['create_address'] = [
    'name' => 'Create Enrollment Address',
    'method' => 'POST',
    'endpoint' => '/addresses',
    'data' => [
        'fk_full_name_bd' => 'John Michael Santos',
        'ca_house_number' => '123',
        'ca_street_name' => 'Main Street',
        'ca_barangay' => 'San Antonio',
        'ca_municipality' => 'Quezon City',
        'ca_provice' => 'Metro Manila',
        'ca_country' => 'Philippines',
        'ca_zipcode' => 1109,
        'ca_address_status' => 'Owned'
    ],
    'curl' => 'curl -X POST ' . API_BASE_URL . '/addresses ' .
             '-H "Content-Type: application/json" ' .
             '-d \'{...}\''
];

// 10. GET REFERENCE DATA TEST
$examples['get_languages'] = [
    'name' => 'Get Mother Tongue Languages',
    'method' => 'GET',
    'endpoint' => '/mother-tongue',
    'curl' => 'curl -X GET ' . API_BASE_URL . '/mother-tongue'
];

$examples['get_religions'] = [
    'name' => 'Get Religions',
    'method' => 'GET',
    'endpoint' => '/religions',
    'curl' => 'curl -X GET ' . API_BASE_URL . '/religions'
];

$examples['get_groups'] = [
    'name' => 'Get Indigenous Groups',
    'method' => 'GET',
    'endpoint' => '/indigenous-groups',
    'curl' => 'curl -X GET ' . API_BASE_URL . '/indigenous-groups'
];

// 11. DELETE TEST
$examples['delete_user'] = [
    'name' => 'Delete User',
    'method' => 'DELETE',
    'endpoint' => '/users/1',
    'curl' => 'curl -X DELETE ' . API_BASE_URL . '/users/1'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMS API Testing Helper</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .api-base {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .api-base strong {
            color: #667eea;
        }
        
        .examples-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .example-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .example-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .example-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        
        .method {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
            margin-bottom: 10px;
            color: white;
        }
        
        .method.GET {
            background: #61affe;
        }
        
        .method.POST {
            background: #49cc90;
        }
        
        .method.PUT {
            background: #fca130;
        }
        
        .method.DELETE {
            background: #f93e3e;
        }
        
        .endpoint {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            margin: 10px 0;
            word-break: break-all;
            color: #333;
        }
        
        .curl-cmd {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            overflow-x: auto;
            margin: 10px 0;
            line-height: 1.4;
        }
        
        .copy-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            margin-top: 10px;
            transition: background 0.3s ease;
        }
        
        .copy-btn:hover {
            background: #764ba2;
        }
        
        .data-section {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 0.85em;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .data-section code {
            display: block;
            font-family: 'Courier New', monospace;
            color: #333;
            white-space: pre-wrap;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            color: #1565c0;
        }
        
        .success {
            background: #c8e6c9;
            border-left-color: #4caf50;
            color: #2e7d32;
        }
        
        .warning {
            background: #fff3e0;
            border-left-color: #ff9800;
            color: #e65100;
        }
        
        footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 AMS REST API Testing Helper</h1>
        
        <div class="api-base">
            <strong>API Base URL:</strong> <?php echo API_BASE_URL; ?>
        </div>
        
        <div class="info-box success">
            ✓ All endpoints support JSON request/response format
        </div>
        
        <div class="info-box warning">
            ⚠ Replace IDs and names with actual values from your database
        </div>
        
        <div class="examples-grid">
            <?php foreach ($examples as $key => $example): ?>
            <div class="example-card">
                <h3><?php echo htmlspecialchars($example['name']); ?></h3>
                
                <span class="method <?php echo $example['method']; ?>">
                    <?php echo $example['method']; ?>
                </span>
                
                <div class="endpoint">
                    <?php echo htmlspecialchars($example['endpoint']); ?>
                </div>
                
                <?php if (isset($example['data'])): ?>
                <div class="data-section">
                    <strong>Request Body:</strong>
                    <code><?php echo json_encode($example['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?></code>
                </div>
                <?php endif; ?>
                
                <strong>cURL Command:</strong>
                <div class="curl-cmd">
                    <?php echo htmlspecialchars($example['curl']); ?>
                </div>
                
                <button class="copy-btn" onclick="copyToClipboard(this)">
                    📋 Copy Command
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        
        <footer>
            <p>AMS REST API v1.0 | Documentation: <code>API_DOCUMENTATION.md</code></p>
        </footer>
    </div>
    
    <script>
        function copyToClipboard(button) {
            const curl = button.previousElementSibling.textContent;
            navigator.clipboard.writeText(curl).then(() => {
                const originalText = button.textContent;
                button.textContent = '✓ Copied!';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            }).catch(err => {
                alert('Failed to copy: ' + err);
            });
        }
    </script>
</body>
</html>
