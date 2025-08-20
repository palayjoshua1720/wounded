# Laravel Backend API

This backend API provides the necessary endpoints for the frontend application.

## Prerequisites

Before you begin, ensure you have the following installed:
- PHP >= 8.1
- Composer
- MySQL or any other database of your choice
- Node.js and NPM (for frontend development)

## Installation

1. Clone the repository and navigate to the backend directory:
```bash
cd api-backend
```

2. Install PHP dependencies:
```bash
composer install
```

3. Create a copy of the environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in the `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

6. Run database migrations:
```bash
php artisan migrate
```

7. (Optional) Seed the database with sample data:
```bash
php artisan db:seed
```

## Running the Application

1. Start the Laravel development server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## API Endpoints

### Sample Endpoint
- `GET /api/sample`
  - Returns a test message to verify API connectivity
  - Response: `{ "message": "API is working!" }`

### Version Endpoint
- `GET /api/version`
  - Returns Laravel and PHP version information
  - Response: 
    ```json
    {
      "laravel_version": "x.x.x",
      "php_version": "x.x.x"
    }
    ```

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
The project follows PSR-12 coding standards. To check your code style:
```bash
composer run lint
```

### Database Migrations
To create a new migration:
```bash
php artisan make:migration migration_name
```

To run migrations:
```bash
php artisan migrate
```

To rollback migrations:
```bash
php artisan migrate:rollback
```

## Environment Variables

Key environment variables in `.env`:

- `APP_ENV`: Application environment (local, production, etc.)
- `APP_DEBUG`: Debug mode (true/false)
- `DB_*`: Database configuration
- `CORS_ALLOWED_ORIGINS`: Allowed origins for CORS (comma-separated)

## Troubleshooting

1. **Permission Issues**
   - Ensure storage and bootstrap/cache directories are writable:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. **Composer Issues**
   - Clear composer cache:
   ```bash
   composer clear-cache
   ```

3. **Cache Issues**
   - Clear application cache:cd 
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

## Contributing

1. Create a new branch for your feature
2. Make your changes
3. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
