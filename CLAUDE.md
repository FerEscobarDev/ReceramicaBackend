# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11 application for Receramica, a product management system with image handling capabilities. The application uses:
- **Backend**: Laravel 11 with PHP 8.2+
- **Frontend**: Inertia.js + Vue 3 + Tailwind CSS
- **Authentication**: Laravel Jetstream with Sanctum
- **Database**: MySQL
- **Image Processing**: Intervention Image library
- **Development Environment**: Laravel Sail (Docker)

## Development Commands

### Starting the Application

With Docker (Laravel Sail):
```bash
./vendor/bin/sail up -d
```

Access the application at `http://localhost` (port 80).

### Frontend Development

```bash
# Start Vite development server (hot reload)
npm run dev

# Build for production
npm run build
```

### Database Operations

```bash
# Run migrations
./vendor/bin/sail artisan migrate

# Rollback migrations
./vendor/bin/sail artisan migrate:rollback

# Fresh migration (warning: drops all tables)
./vendor/bin/sail artisan migrate:fresh

# Seed database
./vendor/bin/sail artisan db:seed
```

### Testing

```bash
# Run all tests
./vendor/bin/sail artisan test

# Run specific test file
./vendor/bin/sail artisan test --filter=TestName

# Run with PHPUnit directly
./vendor/bin/sail composer test
```

### Code Quality

```bash
# Run Laravel Pint (code formatter)
./vendor/bin/sail composer pint

# Clear caches
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear
```

### Artisan Commands

```bash
# Common artisan commands (prefix with ./vendor/bin/sail)
php artisan tinker              # Interactive REPL
php artisan route:list          # List all routes
php artisan make:model ModelName -m  # Create model with migration
php artisan make:controller ControllerName  # Create controller
php artisan storage:link        # Create storage symlink for public files
```

## Architecture

### Core Data Models

**Product Model** (`app/Models/Product.php`):
- Represents ceramic product "creaciones" (creations)
- Fields: name, description, price (default 5000000), quantity (default 1), landing (boolean)
- Has many Images (one-to-many relationship)
- Uses mass assignment with `$guarded = []`

**Image Model** (`app/Models/Image.php`):
- Belongs to Product
- Fields: product_id, url, alt, main (boolean flag for primary image)
- Images stored in `storage/app/public/creaciones_images/`
- Cascade deletes with parent product

### Image Processing Pipeline

All uploaded images are automatically optimized using Intervention Image:
- Resized to 400x600 using `coverDown()` (maintains aspect ratio, crops if needed)
- JPEG quality set to 75 for compression
- Processing happens in `ProductsController::store()` and `ProductsController::update()`
- Manual batch optimization available via `ProductsController::optimizeImages()`

### Routes Structure

**Web Routes** (`routes/web.php`):
- Root `/` redirects to `/login`
- All routes require authentication (`auth:sanctum`, `verified`)
- Product CRUD under `/creaciones` namespace:
  - GET `/creaciones` - List all products
  - GET `/creaciones/create` - Create form
  - POST `/creaciones` - Store new product
  - GET `/creaciones/update/{product}` - Edit form
  - POST `/creaciones/update/{product}` - Update product
  - DELETE `/creaciones/delete/{product}` - Delete product
  - GET `/creaciones/images` - View all images
  - POST `/creaciones/images/optimize` - Batch optimize images

**API Routes** (`routes/api.php`):
- All routes require `auth:sanctum`
- GET `/creaciones` - Returns all products with images (JSON)
- GET `/images` - Returns main images for landing products
- POST `/uploadImages` - Upload images endpoint

### Frontend Stack

**Inertia.js Pages** (`resources/js/Pages/Product/`):
- `Creaciones.vue` - Product listing page
- `Create.vue` - Product creation form
- `Update.vue` - Product editing form
- `ShowImages.vue` - Image gallery view

**Layout**: Uses `AppLayout.vue` with Jetstream components

### Storage and Public Files

- Images stored in: `storage/app/public/creaciones_images/`
- Public access requires: `php artisan storage:link` (creates symlink `public/storage -> storage/app/public`)
- Access images via: `Storage::url($image->url)`

### Authentication

- Laravel Jetstream provides user authentication
- Sanctum for API token authentication
- Two-factor authentication supported
- User actions in `app/Actions/Fortify/` and `app/Actions/Jetstream/`

## Important Validation Rules

When creating/updating products:
- `name` and `description` are required
- `landing` field is required (boolean)
- `images` must be an array
- If `landing` is true, must have at least one image with `isMain: true`

## Docker Services

Defined in `docker-compose.yml`:
- `laravel.test` - Main application (ports 80, 5173 for Vite)
- `mysql` - Database (port 3306)
- `redis` - Cache/sessions (port 6379)
- `meilisearch` - Search engine (port 7700)
- `mailpit` - Email testing (ports 1025, 8025)
- `selenium` - Browser testing

## Environment Configuration

Key environment variables (see `.env.example`):
- Database uses `mysql` host (Docker service name)
- Session and cache drivers set to `database`
- Queue connection uses `database` driver
- File storage uses `local` disk by default
