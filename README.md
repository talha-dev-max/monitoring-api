# 🚀 Supplier API Monitoring System

A comprehensive Laravel-based monitoring system for tracking the health, performance, and uptime of external supplier APIs. Built with modern design patterns including Repository Pattern, Service Layer, and Data Transfer Objects (DTOs).

## 📋 Table of Contents

- [Features](#-features)
- [Architecture](#-architecture)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [API Documentation](#-api-documentation)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Contributing](#-contributing)

## ✨ Features

### Core Monitoring Capabilities
- **Real-time Health Checks**: Monitor API endpoints with customizable intervals
- **Performance Metrics**: Track response times, error rates, and availability
- **Automated Alerting**: Email notifications for failures and incidents
- **Uptime Reports**: Generate comprehensive SLA compliance reports
- **Automatic Failover**: Seamlessly switch to backup suppliers when primary fails

### Technical Features
- **Repository Pattern**: Clean separation of data access logic
- **Service Layer**: Business logic abstraction with dependency injection
- **DTOs**: Type-safe data transfer between layers
- **Comprehensive Testing**: Unit tests, feature tests, and integration tests
- **Laravel 12**: Built on the latest Laravel framework
- **Queue Support**: Background processing for monitoring and alerts
- **Database Migrations**: Version-controlled database schema

## 🏗️ Architecture

### Design Patterns Used

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Controllers   │    │    Services     │    │  Repositories   │
│                 │◄──►│                 │◄──►│                 │
│ - API Endpoints │    │ - Business      │    │ - Data Access   │
│ - Commands      │    │   Logic         │    │ - Query Logic   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                       ┌─────────────────┐
                       │      DTOs       │
                       │                 │
                       │ - HealthStatus  │
                       │ - Performance   │
                       │ - UptimeReport  │
                       └─────────────────┘
```

### Core Components

- **ApiMonitoringService**: Main service for health monitoring operations
- **Repository Interfaces**: Clean abstractions for data operations
- **DTOs**: Type-safe data transfer objects
- **Eloquent Models**: Database entity representations
- **Console Commands**: CLI tools for monitoring operations
- **Notifications**: Alert system for failures

## 🚀 Installation

### Prerequisites

- PHP 8.2+
- Composer 2.0+
- Laravel 12
- MySQL/PostgreSQL/SQLite

### Step 1: Clone and Install Dependencies

```bash
git clone <repository-url>
cd monitoring-api
composer install
```

### Step 2: Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Database Setup

```bash
# Configure your database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitoring_api
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 4: Run Migrations and Seeders

```bash
php artisan migrate
php artisan db:seed
```

## ⚙️ Configuration

### Environment Variables

```bash
# Mail Configuration for Alerts
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password

# Monitoring Configuration
MONITORING_ALERT_EMAIL=admin@yourcompany.com
MONITORING_DEFAULT_TIMEOUT=30
MONITORING_CHECK_INTERVAL=60
```

### Adding New Suppliers

You can add suppliers via seeder, database direct insert, or programmatically:

```php
Supplier::create([
    'name' => 'new_payment_provider',
    'base_url' => 'https://api.provider.com',
    'health_endpoint' => '/health',
    'status' => 'active',
    'timeout_seconds' => 30,
    'retry_count' => 3,
    'headers' => ['Authorization' => 'Bearer token'],
    'is_backup' => false,
    'priority' => 1
]);
```

## 💻 Usage

### Command Line Interface

#### 🔍 **Monitor All Active Suppliers**
```bash
php artisan monitor:suppliers
```
**Purpose:** Performs health checks on all active suppliers and logs results.

**Example Output:**
```
Monitoring suppliers...
✅ Stripe Payment API: Healthy (Response: 142ms)
✅ PayPal Payment API: Healthy (Response: 89ms)
❌ Shopify API: Unhealthy (Error: Connection timeout)
Monitoring completed. 2/3 suppliers healthy.
```

#### 👤 **Monitor Specific Supplier**
```bash
php artisan monitor:suppliers --supplier="Stripe Payment API"
```
**Purpose:** Monitor a single supplier by name for targeted troubleshooting.

**Example:**
```bash
# Monitor by exact name
php artisan monitor:suppliers --supplier="PayPal Payment API"

# Monitor multiple specific suppliers
php artisan monitor:suppliers --supplier="Stripe Payment API" --supplier="PayPal Payment API"
```

#### 🚨 **Monitor with Email Alerts**
```bash
php artisan monitor:suppliers --alert
```
**Purpose:** Monitor all suppliers and send email notifications for failures.

#### 📊 **Detailed Monitoring with Reports**
```bash
php artisan monitor:suppliers --verbose --alert
```
**Purpose:** Monitor with detailed output and alerts enabled.

#### 🔄 **Force Monitor (Including Inactive)**
```bash
php artisan monitor:suppliers --force
```
**Purpose:** Monitor all suppliers including inactive ones for testing.

#### 📈 **Generate Performance Report**
```bash
php artisan monitor:suppliers --report --days=7
```
**Purpose:** Generate performance and uptime report for the last 7 days.

#### ⏰ **Scheduled Monitoring Examples**
```bash
# Every minute monitoring (production)
* * * * * cd /var/www/monitoring-api && php artisan monitor:suppliers --alert >> /var/log/monitoring.log 2>&1

# Every 5 minutes monitoring (development)
*/5 * * * * cd /var/www/monitoring-api && php artisan monitor:suppliers >> storage/logs/monitoring.log 2>&1

# Hourly detailed reports
0 * * * * cd /var/www/monitoring-api && php artisan monitor:suppliers --report --alert >> storage/logs/reports.log 2>&1

# Daily summary reports
0 8 * * * cd /var/www/monitoring-api && php artisan monitor:suppliers --report --days=1 --alert
```

#### 🛠️ **Advanced Command Options**

| Option | Purpose | Example |
|--------|---------|---------|
| `--supplier=NAME` | Monitor specific supplier | `--supplier="Stripe API"` |
| `--alert` | Enable email alerts | `--alert` |
| `--verbose` | Show detailed output | `--verbose` |
| `--force` | Include inactive suppliers | `--force` |
| `--report` | Generate performance report | `--report` |
| `--days=N` | Report period in days | `--days=30` |
| `--timeout=N` | Custom timeout seconds | `--timeout=45` |
| `--retry=N` | Custom retry count | `--retry=5` |

#### 💡 **Useful Command Combinations**

```bash
# Full monitoring suite for production
php artisan monitor:suppliers --alert --report --days=1 --verbose

# Quick health check for all active suppliers
php artisan monitor:suppliers --verbose

# Emergency check with extended timeout
php artisan monitor:suppliers --timeout=60 --retry=3 --alert

# Performance analysis for specific supplier
php artisan monitor:suppliers --supplier="Critical API" --report --days=7 --verbose

# Maintenance mode - check all suppliers including inactive
php artisan monitor:suppliers --force --verbose
```

### Programmatic Usage

```php
use App\Services\Monitoring\ApiMonitoringService;

// Inject the service via Laravel's container
public function __construct(ApiMonitoringService $monitoringService)
{
    $this->monitoringService = $monitoringService;
}

// Monitor API health
$healthStatus = $this->monitoringService->monitorApiHealth('supplier_name');

// Get performance metrics
$metrics = $this->monitoringService->trackResponseTimes('supplier_name');

// Generate uptime report
$report = $this->monitoringService->generateUptimeReport('supplier_name');

// Handle failures
$failures = [
    ['type' => 'timeout', 'timestamp' => now(), 'error' => 'Request timeout'],
    ['type' => 'error', 'timestamp' => now(), 'error' => 'Connection failed']
];
$this->monitoringService->alertOnFailures('supplier_name', $failures);
```

### Scheduled Monitoring

Add to your `routes/console.php` or `app/Console/Kernel.php`:

```php
Schedule::command('monitor:suppliers --alert')
    ->everyMinute()
    ->withoutOverlapping();
```

## 📊 API Documentation

### REST API Endpoints

#### Base URL: `http://your-domain.com/api/v1`

### **Supplier Management API**

#### 📋 **List All Suppliers**
```http
GET /api/v1/suppliers
```

**Example URLs:**
- `GET /api/v1/suppliers` - Get all suppliers
- `GET /api/v1/suppliers?paginate=true&per_page=10` - Get paginated suppliers

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Stripe Payment API",
      "base_url": "https://api.stripe.com",
      "health_endpoint": "/v1/health",
      "status": "active",
      "timeout_seconds": 30,
      "retry_count": 3,
      "headers": {"Authorization": "Bearer sk_test_..."},
      "is_backup": false,
      "priority": 1,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "message": "Suppliers retrieved successfully"
}
```

#### ➕ **Create New Supplier**
```http
POST /api/v1/suppliers
Content-Type: application/json
```

**Example URL:** `POST /api/v1/suppliers`

**Request Body:**
```json
{
  "name": "PayPal Payment API",
  "base_url": "https://api.paypal.com",
  "health_endpoint": "/v1/health",
  "status": "active",
  "timeout_seconds": 30,
  "retry_count": 3,
  "headers": {
    "Authorization": "Bearer ACCESS_TOKEN",
    "Content-Type": "application/json"
  },
  "is_backup": false,
  "priority": 2
}
```

**Response (201):**
```json
{
  "status": "success",
  "data": {
    "id": 2,
    "name": "PayPal Payment API",
    "base_url": "https://api.paypal.com",
    // ... other fields
  },
  "message": "Supplier created successfully"
}
```

#### 📖 **Get Single Supplier**
```http
GET /api/v1/suppliers/{id}
```

**Example URLs:**
- `GET /api/v1/suppliers/1` - Get supplier with ID 1
- `GET /api/v1/suppliers/stripe-api` - Get supplier with name/slug

#### ✏️ **Update Supplier**
```http
PUT /api/v1/suppliers/{id}
Content-Type: application/json
```

**Example URL:** `PUT /api/v1/suppliers/1`

**Request Body (partial update):**
```json
{
  "status": "inactive",
  "timeout_seconds": 45
}
```

#### 🗑️ **Delete Supplier**
```http
DELETE /api/v1/suppliers/{id}
```

**Example URL:** `DELETE /api/v1/suppliers/1`

#### 🟢 **Get Active Suppliers**
```http
GET /api/v1/suppliers/active
```

**Example URL:** `GET /api/v1/suppliers/active`

**Purpose:** Retrieve only suppliers with status="active" for monitoring operations.

#### 🔄 **Get Backup Suppliers**
```http
GET /api/v1/suppliers/backup
```

**Example URL:** `GET /api/v1/suppliers/backup`

**Purpose:** Retrieve backup suppliers ordered by priority for failover scenarios.

#### 🔄 **Toggle Supplier Status**
```http
PATCH /api/v1/suppliers/{id}/toggle-status
```

**Example URL:** `PATCH /api/v1/suppliers/1/toggle-status`

**Purpose:** Switch supplier status between "active" and "inactive" for maintenance.

### **Testing API with cURL**

#### 📋 **List Suppliers**
```bash
# Get all suppliers
curl -X GET "http://localhost:8000/api/v1/suppliers" \
  -H "Accept: application/json"

# Get paginated suppliers
curl -X GET "http://localhost:8000/api/v1/suppliers?paginate=true&per_page=5" \
  -H "Accept: application/json"

# Get active suppliers only
curl -X GET "http://localhost:8000/api/v1/suppliers/active" \
  -H "Accept: application/json"

# Get backup suppliers
curl -X GET "http://localhost:8000/api/v1/suppliers/backup" \
  -H "Accept: application/json"
```

#### ➕ **Create Supplier**
```bash
curl -X POST "http://localhost:8000/api/v1/suppliers" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test Payment Gateway",
    "base_url": "https://api.testgateway.com",
    "health_endpoint": "/health",
    "status": "active",
    "timeout_seconds": 30,
    "retry_count": 3,
    "headers": {
      "Authorization": "Bearer test-token",
      "Content-Type": "application/json"
    },
    "is_backup": false,
    "priority": 1
  }'
```

#### 📖 **Get Single Supplier**
```bash
curl -X GET "http://localhost:8000/api/v1/suppliers/1" \
  -H "Accept: application/json"
```

#### ✏️ **Update Supplier**
```bash
curl -X PUT "http://localhost:8000/api/v1/suppliers/1" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "inactive",
    "timeout_seconds": 45
  }'
```

#### 🔄 **Toggle Supplier Status**
```bash
curl -X PATCH "http://localhost:8000/api/v1/suppliers/1/toggle-status" \
  -H "Accept: application/json"
```

#### 🗑️ **Delete Supplier**
```bash
curl -X DELETE "http://localhost:8000/api/v1/suppliers/1" \
  -H "Accept: application/json"
```

### **Error Responses**

#### Validation Error (422)
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "name": ["The supplier name is required."],
    "base_url": ["The base URL must be a valid URL."]
  }
}
```

#### Not Found Error (404)
```json
{
  "status": "error",
  "message": "Supplier not found"
}
```

### **Core Service Methods**

#### `monitorApiHealth(string $supplier): HealthStatus`
Performs real-time health check on a supplier API.

**Returns:**
```php
HealthStatus {
    +supplier: string
    +status: string ('healthy'|'unhealthy'|'timeout'|'error')
    +responseTimeMs: int
    +statusCode: int
    +timestamp: DateTime|null
    +errorMessage: string|null
    +responseHeaders: array|null
}
```

#### `trackResponseTimes(string $supplier): PerformanceMetrics`
Calculates performance metrics from historical data.

**Returns:**
```php
PerformanceMetrics {
    +supplier: string
    +avgResponseTimeMs: float
    +minResponseTimeMs: int
    +maxResponseTimeMs: int
    +percentile95Ms: float
    +percentile99Ms: float
    +errorRate: float
    +totalRequests: int
    +successfulRequests: int
    +failedRequests: int
    +availability: float
}
```

#### `generateUptimeReport(string $supplier): UptimeReport`
Generates comprehensive uptime and incident reports.

**Returns:**
```php
UptimeReport {
    +supplier: string
    +uptimePercentage: float
    +totalDowntimeMinutes: int
    +incidentCount: int
    +mttr: float  // Mean Time To Recovery
    +mtbf: float  // Mean Time Between Failures
    +slaStatus: string
    +incidents: array
}
```

## 🚀 Quick Start Guide

### 1. **Start the Development Server**
```bash
php artisan serve
# Server will be available at http://localhost:8000
```

### 2. **Create Your First Supplier (API)**
```bash
curl -X POST "http://localhost:8000/api/v1/suppliers" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "GitHub API",
    "base_url": "https://api.github.com",
    "health_endpoint": "/",
    "status": "active",
    "timeout_seconds": 30,
    "retry_count": 3
  }'
```

### 3. **Monitor All Suppliers (Command)**
```bash
php artisan monitor:suppliers --verbose
```

### 4. **List All Suppliers (API)**
```bash
curl -X GET "http://localhost:8000/api/v1/suppliers" \
  -H "Accept: application/json"
```

## 📋 API Quick Reference

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `GET` | `/api/v1/suppliers` | List all suppliers |
| `GET` | `/api/v1/suppliers?paginate=true` | Paginated suppliers |
| `POST` | `/api/v1/suppliers` | Create new supplier |
| `GET` | `/api/v1/suppliers/{id}` | Get supplier by ID |
| `PUT` | `/api/v1/suppliers/{id}` | Update supplier |
| `DELETE` | `/api/v1/suppliers/{id}` | Delete supplier |
| `GET` | `/api/v1/suppliers/active` | Get active suppliers |
| `GET` | `/api/v1/suppliers/backup` | Get backup suppliers |
| `PATCH` | `/api/v1/suppliers/{id}/toggle-status` | Toggle status |

## 🎯 Command Quick Reference

| Command | Purpose |
|---------|---------|
| `php artisan monitor:suppliers` | Monitor all active suppliers |
| `php artisan monitor:suppliers --supplier="Name"` | Monitor specific supplier |
| `php artisan monitor:suppliers --alert` | Monitor with email alerts |
| `php artisan monitor:suppliers --verbose` | Monitor with detailed output |
| `php artisan monitor:suppliers --force` | Monitor all (including inactive) |
| `php artisan test` | Run all tests |
| `php artisan test --filter=SupplierApiTest` | Run API tests only |

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Unit Tests
php artisan test --testsuite=Unit

# Feature Tests
php artisan test --testsuite=Feature

# Specific Test Class
php artisan test --filter=ApiMonitoringServiceTest
```

### Test Coverage

The test suite includes:
- **Unit Tests**: Service logic, DTOs, repositories
- **Feature Tests**: Console commands, integration flows
- **HTTP Tests**: API endpoints (if implemented)

### Example Test Usage

```php
// Testing health monitoring
Http::fake([
    'https://api.example.com/health' => Http::response('OK', 200)
]);

$result = $this->monitoringService->monitorApiHealth('test_supplier');
$this->assertTrue($result->isHealthy());
```

## 🚀 Deployment

### Production Setup

1. **Environment Configuration**
```bash
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack
```

2. **Database Optimization**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Queue Configuration**
```bash
# For production use Redis or database queues
QUEUE_CONNECTION=database

# Start queue workers
php artisan queue:work --daemon --tries=3
```

4. **Cron Setup**
```bash
# Add to your crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Docker Setup (Optional)

```dockerfile
FROM php:8.2-fpm
# Add your Docker configuration here
```

## 📈 Monitoring Dashboard (Future Enhancement)

While not included in this implementation, consider adding:
- Web-based dashboard for real-time monitoring
- Charts and graphs for performance trends
- Alert management interface
- Supplier configuration UI

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Commit changes: `git commit -am 'Add new feature'`
4. Push to branch: `git push origin feature/new-feature`
5. Submit a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for all new features
- Update documentation for any API changes
- Use semantic commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Check existing documentation
- Review test cases for usage examples

## 🔧 Troubleshooting

### Common Issues

1. **Facade Root Not Set Error**
   - Ensure you're using `Tests\TestCase` instead of `PHPUnit\Framework\TestCase`

2. **Database Connection Issues**
   - Check `.env` database configuration
   - Ensure database exists and credentials are correct

3. **Queue Jobs Not Processing**
   - Run `php artisan queue:work`
   - Check queue configuration in `.env`

4. **Supplier Health Checks Failing**
   - Verify supplier URLs and endpoints
   - Check network connectivity
   - Review timeout configurations
