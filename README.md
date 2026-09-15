# Event Management API

A Laravel-based RESTful API for managing events and attendees. Built with Laravel 13, PHP 8.3+, and Laravel Sanctum for authentication.

## Features

- **Event Management**: Create, read, update, and delete events
- **Attendee Management**: Register attendees for events and list event attendees
- **API Authentication**: Token-based authentication using Laravel Sanctum
- **Relationship Loading**: Flexible eager loading via `include` query parameter
- **Email Notifications**: Automated event reminder notifications
- **Scheduled Reminders**: Console command to send reminders for upcoming events
- **Database Seeding**: Factories and seeders for development/testing
- **API Resources**: Clean, consistent JSON responses

## Tech Stack

- **Framework**: Laravel 13.x
- **PHP**: 8.3+
- **Authentication**: Laravel Sanctum
- **Database**: SQLite (default), supports MySQL/PostgreSQL
- **Testing**: PHPUnit
- **Frontend**: Vite (for asset compilation)

## Installation

### Prerequisites

- PHP 8.3 or higher
- Composer
- Node.js & NPM

### Setup

```bash
# Clone the repository
git clone <repository-url>
cd event-manage

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# (Optional) Seed the database
php artisan db:seed

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

Or use the provided setup script:

```bash
composer run setup
```

## Environment Configuration

Key environment variables in `.env`:

```env
APP_NAME="Event Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# For MySQL/PostgreSQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=event_manage
# DB_USERNAME=root
# DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=localhost
```

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login and receive API token |
| POST | `/api/auth/logout` | Logout (revoke token) |
| GET | `/api/user` | Get authenticated user |

### Events

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/events` | List all events (paginated) | No |
| POST | `/api/events` | Create a new event | Yes |
| GET | `/api/events/{id}` | Get a specific event | No |
| PUT/PATCH | `/api/events/{id}` | Update an event | Yes |
| DELETE | `/api/events/{id}` | Delete an event | Yes |

### Attendees

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/events/{event}/attendees` | List attendees for an event | No |
| POST | `/api/events/{event}/attendees` | Register attendee for event | Yes |
| GET | `/api/events/{event}/attendees/{id}` | Get specific attendee | No |
| DELETE | `/api/events/{event}/attendees/{id}` | Remove attendee from event | Yes |

### Query Parameters

**Include Relationships** (for Events):
```
GET /api/events?include=user,attendees,attendees.user
```

Available relations: `user`, `attendees`, `attendees.user`

**Pagination**:
```
GET /api/events?page=2&per_page=15
```

## Authentication

### Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
```

Response:
```json
{
  "token": "1|abc123..."
}
```

### Using the Token

Include the token in the Authorization header:

```bash
curl -X GET http://localhost:8000/api/events \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Accept: application/json"
```

## Data Models

### Event

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Primary key |
| user_id | integer | Foreign key to User |
| name | string | Event name (max 255) |
| description | text | Event description |
| start_time | datetime | Event start time |
| end_time | datetime | Event end time |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

**Relationships**: `user` (belongsTo), `attendees` (hasMany)

### Attendee

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Primary key |
| user_id | integer | Foreign key to User |
| event_id | integer | Foreign key to Event |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

**Relationships**: `user` (belongsTo), `event` (belongsTo)

### User

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Primary key |
| name | string | User name |
| email | string | Unique email |
| password | string | Hashed password |
| email_verified_at | timestamp | Email verification timestamp |

## Authorization Policies

The application includes policies for Events and Attendees (`EventPolicy`, `AttendeePolicy`). Currently all methods return `false` - implement your authorization logic:

```php
// app/Policies/EventPolicy.php
public function update(User $user, Event $event): bool
{
    return $user->id === $event->user_id; // Only creator can update
}

public function delete(User $user, Event $event): bool
{
    return $user->id === $event->user_id; // Only creator can delete
}
```

Register policies in `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    Event::class => EventPolicy::class,
    Attendee::class => AttendeePolicy::class,
];
```

## Event Reminders

### Notification

The `EventReminderNotification` sends email reminders to attendees for events starting within 24 hours.

### Console Command

Send reminders manually:

```bash
php artisan app:send-event-reminders
```

Schedule in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('app:send-event-reminders')->dailyAt('09:00');
}
```

## Database Seeding

Seed the database with sample data:

```bash
# Full seed (1000 users, 200 events, attendees)
php artisan db:seed

# Or run specific seeders
php artisan db:seed --class=EventSeeder
php artisan db:seed --class=AttendeeSeeder
```

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

## Development

### Code Style

```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check code style
./vendor/bin/pint --test
```

### Logs

```bash
# View Laravel logs
php artisan pail

# Or tail directly
tail -f storage/logs/laravel.log
```

### Database

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migrate and seed
php artisan migrate:fresh --seed
```

## Project Structure

```
app/
├── Console/Commands/SendEventReminders.php  # Reminder command
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php              # Login/logout
│   │   ├── EventController.php             # Event CRUD
│   │   └── AttendeeController.php          # Attendee CRUD
│   ├── Resources/
│   │   ├── EventResource.php               # Event API resource
│   │   ├── AttendeeResource.php            # Attendee API resource
│   │   └── UserResource.php                # User API resource
│   └── Traits/CanLoadRelationships.php     # Relationship loading trait
├── Models/
│   ├── Event.php
│   ├── Attendee.php
│   └── User.php
├── Notifications/EventReminderNotification.php
└── Policies/
    ├── EventPolicy.php
    └── AttendeePolicy.php

database/
├── factories/                              # Model factories
├── migrations/                             # Database migrations
└── seeders/                                # Database seeders

routes/
├── api.php                                 # API routes
└── web.php                                 # Web routes
```

## API Response Format

### Success Response

```json
{
  "id": 1,
  "name": "Laravel Conference",
  "user_id": 1,
  "description": "Annual Laravel conference",
  "start_time": "2026-10-15T09:00:00.000000Z",
  "end_time": "2026-10-15T18:00:00.000000Z",
  "created_at": "2026-09-15T10:00:00.000000Z",
  "updated_at": "2026-09-15T10:00:00.000000Z",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "attendees": [
    {
      "id": 1,
      "user_id": 2,
      "event_id": 1,
      "event": {...}
    }
  ]
}
```

### Error Response

```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

## License

This project is open-sourced software licensed under the [MIT license](LICENSE.md).