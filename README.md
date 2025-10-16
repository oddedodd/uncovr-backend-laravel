# Uncovr API

A Laravel-based API platform for managing music artists, releases, and interactive content pages. Built with Laravel 12, Filament admin panel, and role-based access control.

## Features

### 🎵 Music Management
- **Label Management**: Create and manage record labels with owner associations
- **Artist Profiles**: Complete artist profiles with images, bios, links, and label associations
- **Release Management**: Handle music releases with types (single/EP/album), status (draft/published), cover images, rich content, Spotify URLs, and publication controls
- **Interactive Pages**: Create custom block-based pages for releases with rich content blocks (hero, text, image, video, gallery, spacer), background colors, and position management
- **Page Status Control**: Draft and publish pages independently from releases

### 🎨 Content Blocks
- **Hero Block**: Full-width hero sections with images, titles, and subtitles
- **Text Block**: Rich text content with HTML support
- **Image Block**: Single images with captions and alt text
- **Video Block**: Embedded YouTube videos with captions
- **Gallery Block**: Multi-image galleries with captions
- **Spacer Block**: Customizable vertical spacing (small/medium/large)

### 🔐 Authentication & Authorization
- **Laravel Sanctum**: Token-based API authentication
- **User Registration**: Public API endpoint for app user registration
- **Role-Based Access Control**: Four user roles (admin, label, artist, user) with different permissions
- **Policy Protection**: Secure access to resources based on ownership and roles
- **Admin Panel Protection**: User role restricted from accessing admin panel (API-only access)

### 🎛️ Admin Panel
- **Filament v4**: Modern admin interface for content management
- **Role-Based Views**: Artists see only their own content, labels see their artists, admins see everything
- **Rich Content Editing**: File uploads, rich text editors, image management, and visual block builders
- **Label Management**: Admin-only interface for managing record labels
- **Page Management**: Drag-and-drop page ordering with move up/down buttons, dedicated edit pages (no modals)
- **Image Uploads**: Artist images, release cover images, and page content images with built-in image editor
- **Performance Optimizations**: Deferred loading, auto-refresh, and optimized queries

### 🌐 Public API
- **Public Endpoints**: Access published releases and pages without authentication
- **Slug-Based URLs**: SEO-friendly URLs for releases and pages
- **Artist-Specific Content**: Browse releases by artist with artist images
- **Release Types**: Support for singles, EPs, and albums with filtering
- **Spotify Integration**: Direct links to Spotify albums, playlists, and tracks
- **Block-Based Content**: Rich content delivery with resolved background colors and structured data

## API Endpoints

### Public Endpoints (No Authentication Required)
```
# Releases
GET /api/v1/releases                           # All published releases (with pagination)
GET /api/v1/releases?type=single               # Filter by type (single, ep, album)
GET /api/v1/releases?artist_id={id}            # Filter by artist
GET /api/v1/artists/{artist}/releases/public   # Published releases by artist
GET /api/v1/releases/slug/{slug}               # Get release by slug

# Pages
GET /api/v1/releases/slug/{slug}/pages         # Get pages for a release (by slug)
```

### Authentication
```
POST /api/v1/auth/register                     # Register new user (user role)
  Body: { name, email, password, password_confirmation }
  Response: { token, user, message }

POST /api/v1/auth/login                        # Login
  Body: { email, password }
  Response: { token, user }

POST /api/v1/auth/logout                       # Logout (requires token)
GET  /api/v1/me                                # Current user info (requires token)
```

### Authenticated Endpoints (Requires Token)

#### Label Management (Admin only)
```
GET    /api/v1/labels                          # List all labels
POST   /api/v1/labels                          # Create label
GET    /api/v1/labels/{id}                     # Show label
PUT    /api/v1/labels/{id}                     # Update label
DELETE /api/v1/labels/{id}                     # Delete label
```

#### Artist Management (Admin, Label)
```
GET    /api/v1/artists                         # List artists
POST   /api/v1/artists                         # Create artist
  Body: { name, slug?, bio?, links?, artist_image? }

GET    /api/v1/artists/{id}                    # Show artist
PUT    /api/v1/artists/{id}                    # Update artist
DELETE /api/v1/artists/{id}                    # Delete artist
GET    /api/v1/artist/dashboard                # Artist dashboard (artist role)
```

#### Release Management (Admin, Label, Artist)
```
GET    /api/v1/artists/{artist}/releases       # List releases for artist
POST   /api/v1/artists/{artist}/releases       # Create release
  Body: { title, type, slug?, release_date?, status?, cover_image?, spotify_url?, content?, meta? }

GET    /api/v1/releases/{id}                   # Show release
PATCH  /api/v1/releases/{id}                   # Update release
DELETE /api/v1/releases/{id}                   # Delete release
```

#### Page Management (Admin, Label, Artist)
```
GET    /api/v1/releases/{release}/pages        # List pages for release
POST   /api/v1/releases/{release}/pages        # Create page
  Body: { title, slug?, page_type?, background_color?, blocks?, position?, status?, meta? }

GET    /api/v1/pages/{id}                      # Show page
PATCH  /api/v1/pages/{id}                      # Update page
DELETE /api/v1/pages/{id}                      # Delete page
```

## User Roles

### Admin
- Full access to all content and features
- Can manage labels, artists, releases, and pages
- Can view and edit all content regardless of ownership
- Full access to admin panel at `/admin`
- Can create users with any role

### Label
- Can create and manage their own record label
- Can create and manage artists under their label
- Can manage releases and pages for artists under their label
- Access to admin panel at `/admin`
- Cannot access other labels' content

### Artist
- Can only manage their own artist profile
- Can create and manage their own releases and pages
- Access to admin panel at `/admin`
- Dashboard endpoint: `/api/v1/artist/dashboard`
- Automatically assigned when created via admin/label

### User
- API-only access (no admin panel access)
- Can register via public API endpoint
- Can authenticate and access protected endpoints
- Intended for app users/consumers
- Blocked from accessing `/admin` panel

## Tech Stack

- **Framework**: Laravel 12
- **Admin Panel**: Filament v4
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **Database**: SQLite (development), PostgreSQL (production)
- **Frontend**: Laravel Breeze with Tailwind CSS

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd uncovr-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   # For SQLite (development)
   touch database/database.sqlite
   
   # Update .env
   DB_CONNECTION=sqlite
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed --class=RolesSeeder
   ```

6. **Link storage for file uploads**
   ```bash
   php artisan storage:link
   ```

7. **Create admin user** (optional)
   ```bash
   php artisan tinker
   >>> $user = User::create(['name' => 'Admin', 'email' => 'admin@uncovr.local', 'password' => Hash::make('secret123')]);
   >>> $user->assignRole('admin');
   ```

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

## API Response Structure

### Release Response
```json
{
  "id": 4,
  "artist": {
    "id": 7,
    "name": "Test Artist med Label",
    "slug": "test-artist-med-label",
    "artist_image": "https://example.com/storage/artists/images/xxxxx.jpg"
  },
  "title": "Artist 4 Release 01",
  "slug": "artist-4-release-01",
  "type": "album",
  "status": "published",
  "release_date": "2025-09-30",
  "published_at": "2025-09-28T08:43:02+00:00",
  "cover_image": "https://example.com/storage/releases/covers/xxxxx.jpg",
  "spotify_url": "https://open.spotify.com/album/xxxxx",
  "content": "<p>Release content</p>",
  "meta": null,
  "created_at": "2025-09-27T18:08:55+00:00",
  "updated_at": "2025-09-28T08:43:02+00:00"
}
```

### Page Response
```json
{
  "id": 5,
  "release_id": 4,
  "title": "Intro Page",
  "slug": "intro-page",
  "background_color": "#423a3a",
  "blocks": [
    {
      "type": "hero",
      "data": {
        "title": "Hero Title",
        "subtitle": "Hero Subtitle",
        "image": "pages/hero/xxxxx.jpg",
        "background_color": "#c24141"
      },
      "resolvedBackground": "#c24141"
    },
    {
      "type": "text",
      "data": {
        "html": "<p>Content here</p>",
        "background_color": "#693c3c"
      },
      "resolvedBackground": "#693c3c"
    }
  ],
  "created_at": "2025-09-27T18:09:24+00:00",
  "updated_at": "2025-10-11T13:36:31+00:00"
}
```

## Testing

### Run Tests
```bash
composer run test
```

### API Testing Script
A bash script is included for testing the API:

```bash
# Test against localhost (default)
./uncovr_api.sh status           # Show current configuration
./uncovr_api.sh login            # Login with admin
./uncovr_api.sh register "Name" "email@example.com" "password"
./uncovr_api.sh public-releases  # Get public releases
./uncovr_api.sh pages --slug my-album
./uncovr_api.sh user-flow        # Run complete user flow test

# Test against production
ENV=prod ./uncovr_api.sh status
ENV=prod ./uncovr_api.sh user-flow
```

## File Storage

Images and media files are stored in the following directories:
- **Artist Images**: `storage/app/public/artists/images/`
- **Release Covers**: `storage/app/public/releases/covers/`
- **Page Content**: 
  - Hero images: `storage/app/public/pages/hero/`
  - Page images: `storage/app/public/pages/images/`
  - Gallery images: `storage/app/public/pages/gallery/`

All files are publicly accessible via `/storage/` URL after running `php artisan storage:link`.

## Performance Optimizations

- **Eager Loading**: Artist relationships loaded with selective columns to reduce query overhead
- **Deferred Loading**: Admin tables use deferred loading for better initial page load
- **Query Optimization**: Selective column loading and indexed queries
- **Position Caching**: Page position data cached to reduce N+1 queries during reordering
- **Auto-refresh**: Admin tables auto-refresh every 30 seconds to show latest changes

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Rock on! 🎸