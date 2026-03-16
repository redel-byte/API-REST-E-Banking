# Laravel JWT API - Swagger Documentation

This project includes comprehensive Swagger/OpenAPI documentation for the JWT Authentication API.

## 📚 Documentation Endpoints

### 1. Swagger UI (Interactive Documentation)
- **URL**: `http://localhost:8000/api/docs/ui`
- **Description**: Interactive API documentation interface where you can test endpoints directly
- **Features**: 
  - Try out API endpoints
  - View request/response schemas
  - Authentication testing with JWT tokens

### 2. OpenAPI JSON Specification
- **URL**: `http://localhost:8000/api/docs/json`
- **Description**: Raw OpenAPI 3.0 specification in JSON format
- **Usage**: Can be imported into other API documentation tools

### 3. Documentation Test Endpoint
- **URL**: `http://localhost:8000/api/docs/test`
- **Description**: Simple test endpoint to verify Swagger controller is working

## 🔐 API Endpoints Documented

### Authentication Endpoints

#### POST /api/register
- **Description**: Register a new user account
- **Request Body**:
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }
  ```
- **Responses**:
  - `201`: User registered successfully
  - `422`: Validation error

#### POST /api/login
- **Description**: Login user and get JWT token
- **Request Body**:
  ```json
  {
    "email": "john@example.com",
    "password": "password123"
  }
  ```
- **Responses**:
  - `200`: Login successful (returns JWT token)
  - `401`: Invalid credentials
  - `422`: Validation error

#### POST /api/logout
- **Description**: Logout user and invalidate JWT token
- **Authentication**: Required (Bearer token)
- **Responses**:
  - `200`: Logout successful
  - `401`: Unauthenticated

#### GET /api/me
- **Description**: Get current user profile information
- **Authentication**: Required (Bearer token)
- **Responses**:
  - `200`: User profile retrieved successfully
  - `401`: Unauthenticated

## 🛠️ How to Use the Documentation

### 1. Start the Laravel Server
```bash
php artisan serve
```

### 2. Access Swagger UI
Open your browser and navigate to: `http://localhost:8000/api/docs/ui`

### 3. Testing Authenticated Endpoints
1. First, register a user or login to get a JWT token
2. In Swagger UI, click the "Authorize" button
3. Enter `Bearer YOUR_JWT_TOKEN` in the input field
4. Click "Authorize" to apply the token
5. Now you can test protected endpoints like `/api/me` and `/api/logout`

## 📋 Request/Response Examples

### Registration Success Response (201)
```json
{
  "message": "Account created successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2023-01-01T12:00:00Z",
    "updated_at": "2023-01-01T12:00:00Z"
  }
}
```

### Login Success Response (200)
```json
{
  "message": "Login successfully",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### Validation Error Response (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

### Authentication Error Response (401)
```json
{
  "message": "Unauthenticated."
}
```

## 🔧 Configuration

The Swagger documentation is configured through:

1. **Annotations**: Controller methods use `@OA\*` annotations for documentation
2. **SwaggerController**: Handles documentation generation and UI serving
3. **OpenAPI Config**: Configuration file at `config/openapi.php`

## 📁 File Structure

```
app/
├── Http/Controllers/
│   ├── AuthController.php          # API endpoints with OpenAPI annotations
│   └── SwaggerController.php       # Documentation endpoints
├── Http/Requests/
│   ├── RegisterRequest.php         # Registration validation
│   └── loginRequest.php            # Login validation
├── Models/
│   └── User.php                    # User model with schema documentation
config/
└── openapi.php                     # OpenAPI configuration
routes/
└── api.php                         # API routes including documentation routes
```

## 🚀 Getting Started

1. **Install Dependencies**:
   ```bash
   composer install
   ```

2. **Environment Setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**:
   ```bash
   php artisan migrate
   ```

4. **Start Server**:
   ```bash
   php artisan serve
   ```

5. **Access Documentation**:
   - Swagger UI: `http://localhost:8000/api/docs/ui`
   - OpenAPI JSON: `http://localhost:8000/api/docs/json`

## 🔒 Authentication

The API uses JWT (JSON Web Tokens) for authentication:

1. **Register**: Create a new account
2. **Login**: Get a JWT token
3. **Use Token**: Include `Authorization: Bearer YOUR_TOKEN` header in subsequent requests

## 📊 API Features

- ✅ Complete OpenAPI 3.0 specification
- ✅ Interactive Swagger UI
- ✅ JWT Bearer authentication
- ✅ Request/response validation
- ✅ Comprehensive error handling
- ✅ Schema documentation for models
- ✅ Security scheme definitions

## 🐛 Troubleshooting

### Common Issues

1. **Server Not Starting**: Ensure port 8000 is available
2. **404 Errors**: Check that routes are properly registered
3. **Authentication Issues**: Verify JWT configuration
4. **Database Errors**: Ensure migrations have been run

### Debug Commands

```bash
# Check routes
php artisan route:list

# Test Swagger controller
curl http://localhost:8000/api/docs/test

# Check OpenAPI JSON
curl http://localhost:8000/api/docs/json
```

## 📝 Notes

- The documentation automatically updates when you modify controller annotations
- All API endpoints are properly documented with request/response examples
- Validation rules are reflected in the documentation
- Security schemes are configured for JWT Bearer authentication
