# Symfony REST API Boilerplate

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue.svg)](https://php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-7.3-green.svg)](https://symfony.com/)
[![Docker](https://img.shields.io/badge/docker-enabled-blue.svg)](https://www.docker.com/)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

> A production-ready, Docker-based boilerplate for building secure and scalable REST APIs with Symfony 7.3. Features modern PHP practices, comprehensive security measures, and developer-friendly tooling.  
> Docker configuration based on [Symfony Docker](https://github.com/dunglas/symfony-docker)

## 🚀 Features

### 🔐 Security & Authentication
- **JWT Authentication** with refresh token support
- **Rate Limiting** with attribute-based rate limiting
- **CORS Configuration** with environment-based origin control
- **Enhanced Email Validation** with strict mode and duplicate checking
- **Password Strength Validation** with configurable security levels
- **Password Reset** functionality with secure token management

### 🏗️ Architecture & Code Quality
- **Modern Symfony 7.3** with PHP 8.4+ support
- **Clear module** separation
- **PHP configuration** with `.env` file
- **FrankenPHP** runtime for enhanced performance
- **Docker containerization** with production-ready configuration
- **OpenAPI/Swagger** documentation (`/api/doc` and `/api/doc.json`)
- **Comprehensive testing** with PHPUnit
- **Code quality tools**: PHPStan, PHP CS Fixer, Rector

### 📡 API Features
- **Consistent API responses** with `ApiResponse` and `ApiErrorResponse`
- **Pagination support** with Pagerfanta integration
- **Role-based serialization** groups
- **Exception handling** with proper HTTP status codes
- **Request validation** with Symfony Validator
- **Automated API documentation** with success response attribute `#[SuccessResponse(User::class)` or `#[SuccessResponse(User::class, isList: true)]` for paginated responses
- **Sentry integration**

## 🛠️ Quick Start

### Prerequisites
- Docker & Docker Compose

### Installation

1. **Clone and build**
   ```bash
   git clone <repository-url>
   cd symfony-api-boilerplate
   docker compose build --pull --no-cache
   ```

2. **Start the application**
   ```bash
   docker compose up --wait
   ```

3. **Access the application**
    - API: `https://localhost`
    - Documentation: `https://localhost/api/doc`
    - Accept the auto-generated TLS certificate when prompted

4. **Stop the application**
   ```bash
   docker compose down --remove-orphans
   ```

## 📋 Environment Configuration

Create `.env.local` file for local development:

```bash
JWT_PASSPHRASE='YourSecretPassphrase'
APP_SECRET='YourAppSecret'

# Mailer
MAILER_DSN=smtp://localhost:1025
```

## 🔧 Configuration Details

### Rate Limiting

The application includes comprehensive rate limiting with attribute-based rule: `#[RateLimiting('limiter name')]`:

Rate limiting can be disabled by setting parameter `rate_limiter.enabled` to `false` in `config/packages/rate_limiter.php`.

### CORS Policy

- **Origins**: Configurable via `CORS_ALLOW_ORIGIN` environment variable
- **Methods**: GET, POST, PUT, PATCH, DELETE, OPTIONS
- **Headers**: Content-Type, Authorization
- **Preflight Cache**: 3600 seconds

### Email Validation

Enhanced email validation includes:
- **Strict RFC compliance** validation

## 📚 API Documentation

### Authentication Flow

1. **Register a new user**
   ```bash
   curl -X POST https://localhost/api/auth/register \
     -H "Content-Type: application/json" \
     -d '{"email": "user@example.com", "password": "SecurePass123!"}'
   ```

2. **Login to get tokens**
   ```bash
   curl -X POST https://localhost/api/auth/token \
     -H "Content-Type: application/json" \
     -d '{"username": "user@example.com", "password": "SecurePass123!"}'
   ```

3. **Use the JWT token**
   ```bash
   curl -X GET https://localhost/api/protected-endpoint \
     -H "Authorization: Bearer YOUR_JWT_TOKEN"
   ```

4. **Refresh expired token**
   ```bash
   curl -X POST https://localhost/api/auth/token/refresh \
     -H "Content-Type: application/json" \
     -d '{"refresh_token": "YOUR_REFRESH_TOKEN"}'
   ```

### API Response Format

**Success Response:**
```json
{
  "data": {
    "id": 1,
    "email": "user@example.com",
    "roles": ["ROLE_USER"]
  }
}
```

**Paginated Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Example Item"
    }
  ],
  "total": 25,
  "has_next_page": true,
  "has_previous_page": false
}
```

**Validation Response:**
```json
{
  "code": "UUID",
  "violations": [
      {
      "field": "id",
      "message": "This value should be a valid UUID."
    } 
  ]
}
```

**Error Response:**
```json
{
  "message": "Validation failed",
  "code": "UUID"
}
```

## 🧪 Development

### Code Quality Commands

```bash
# Run all tests
docker compose exec php composer test

# Check code quality
docker compose exec php composer check

# Fix code style issues
docker compose exec php composer fix
```

### Database Operations

```bash
# Create migration
docker compose exec php bin/console make:migration

# Run migrations
docker compose exec php bin/console doctrine:migrations:migrate

# Load fixtures (if available)
docker compose exec php bin/console doctrine:fixtures:load
```

## 🚀 Deployment

### Production Environment

1. **Environment Variables**
   ```bash
   APP_ENV=prod
   APP_DEBUG=false
   JWT_PASSPHRASE='YourSecretPassphrase'
   APP_SECRET='YourAppSecret'
   CORS_ALLOW_ORIGIN=https://yourdomain.com
   DATABASE_URL=postgresql://user:pass@host:5432/dbname
   SENTRY_DSN=https://your-sentry-dsn
   ```

2. **Build Production Image**
   ```bash
   docker compose -f compose.prod.yaml build
   docker compose -f compose.prod.yaml up -d
   ```

3. **SSL/TLS Configuration**
    - The application includes auto-generated certificates for development
    - For production, configure proper SSL certificates
    - Update CORS origins to match your domain

### Performance Considerations

- **FrankenPHP** provides excellent performance out of the box
- **Rate limiting** helps protect against abuse
- **JWT tokens** are stateless and scalable

## 🏗️ Architecture

### Project Structure

```
src/
├── Auth/           # Authentication & authorization
│   ├── Action/     # HTTP controllers
│   ├── Entity/     # Doctrine entities
│   └── Model/      # DTOs and value objects
├── User/           # User management
├── Shared/         # Shared utilities and services
│   ├── RateLimiter/ # Rate limiting implementation
│   └── EventListener/ # Global event listeners
└── OpenApi/        # API documentation utilities
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Run tests and quality checks (`composer check`)
4. Commit your changes (`git commit -m 'Add amazing feature'`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

## 🔮 Roadmap

- [ ] Fix problem with multiple `SuccessResponse` attribute in multiple controllers
- [ ] Ratelimiter for Reset password endpoints and login endpoint
- [ ] Implement API versioning strategy
- [ ] Add caching layer for improved performance
- [ ] Add health check endpoint
- [ ] Add support for Notifier component
- [ ] Add sending email for password reset
- [ ] Add fixtures for local development
- [ ] Multifactor authentication

*This README was reviewed and improved with the assistance of AI.*
