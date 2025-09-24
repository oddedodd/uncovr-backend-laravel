# Uncovr API

A Laravel-based API platform for managing music artists, releases, and interactive content pages. Built with Laravel 12, Filament admin panel, and role-based access control.

## Features

### 🎵 Music Management
- **Artist Profiles**: Create and manage artist profiles with bios, links, and user associations
- **Release Management**: Handle music releases with cover images, rich content, and publication controls
- **Interactive Pages**: Create custom pages for releases with background colors and block-based content

### 🔐 Authentication & Authorization
- **Laravel Sanctum**: Token-based API authentication
- **Role-Based Access Control**: Three user roles (admin, label, artist) with different permissions
- **Policy Protection**: Secure access to resources based on ownership and roles

### 🎛️ Admin Panel
- **Filament v4**: Modern admin interface for content management
- **Role-Based Views**: Artists see only their own content, admins see everything
- **Rich Content Editing**: File uploads, rich text editors, and image management

### 🌐 Public API
- **Public Endpoints**: Access published releases and pages without authentication
- **Slug-Based URLs**: SEO-friendly URLs for releases and pages
- **Artist-Specific Content**: Browse releases by artist

## API Endpoints

### Public Endpoints (No Authentication Required)
```
GET /api/v1/releases                           # All published releases
GET /api/v1/artists/{id}/releases/public       # Releases by artist
GET /api/v1/releases/slug/{slug}               # Release by slug
GET /api/v1/releases/slug/{slug}/pages         # Pages for a release
```

### Authenticated Endpoints (Requires Token)
```
POST /api/v1/auth/login                        # Login
GET  /api/v1/me                                # Current user info
POST /api/v1/auth/logout                       # Logout

# Artist Management
GET    /api/v1/artists                         # List artists
POST   /api/v1/artists                         # Create artist
GET    /api/v1/artists/{id}                    # Show artist
PUT    /api/v1/artists/{id}                    # Update artist
DELETE /api/v1/artists/{id}                    # Delete artist

# Release Management
GET    /api/v1/artists/{id}/releases           # List releases for artist
POST   /api/v1/artists/{id}/releases           # Create release
GET    /api/v1/releases/{id}                   # Show release
PATCH  /api/v1/releases/{id}                   # Update release
DELETE /api/v1/releases/{id}                   # Delete release

# Page Management
GET    /api/v1/releases/{id}/pages             # List pages for release
POST   /api/v1/releases/{id}/pages             # Create page
GET    /api/v1/pages/{id}                      # Show page
PATCH  /api/v1/pages/{id}                      # Update page
DELETE /api/v1/pages/{id}                      # Delete page
```

## User Roles

### Admin
- Full access to all content
- Can manage artists, releases, and pages
- Access to admin panel at `/admin`

### Label
- Can create and manage artists
- Can manage releases for any artist
- Access to admin panel at `/admin`

### Artist
- Can only manage their own artist profile
- Can create and manage their own releases and pages
- Access to admin panel at `/admin`
- Dashboard endpoint: `/api/v1/artist/dashboard`

## Tech Stack

- **Framework**: Laravel 12
- **Admin Panel**: Filament v4
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **Database**: SQLite (development), PostgreSQL (production)
- **Frontend**: Laravel Breeze with Tailwind CSS

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy environment file: `cp .env.example .env`
4. Generate application key: `php artisan key:generate`
5. Create database: `touch database/database.sqlite`
6. Run migrations: `php artisan migrate`
7. Seed roles: `php artisan db:seed --class=RolesSeeder`
8. Link storage: `php artisan storage:link`

## Development

```bash
# Start development server with all services
composer run dev

# Or start individual services
php artisan serve          # Web server
php artisan queue:listen   # Queue worker
php artisan pail           # Log viewer
npm run dev               # Vite dev server
```

## Testing

```bash
composer run test
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
