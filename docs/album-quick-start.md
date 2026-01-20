# Quick Start Guide - Album Management

## Running Migrations

Since this is a Docker-based project, you need to run migrations inside the Docker container:

```bash
# Start Docker containers (if not already running)
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Or if you need to run a specific migration
docker-compose exec app php artisan migrate --path=/database/migrations/2026_01_20_081800_create_albums_table.php
docker-compose exec app php artisan migrate --path=/database/migrations/2026_01_20_081801_create_photos_table.php
```

## Verify Implementation

```bash
# Check if tables were created
docker-compose exec mysql mysql -u organisasi_user -porganisasi_pass organisasi_manajemen -e "SHOW TABLES;"

# Check albums table structure
docker-compose exec mysql mysql -u organisasi_user -porganisasi_pass organisasi_manajemen -e "DESCRIBE albums;"

# Check photos table structure
docker-compose exec mysql mysql -u organisasi_user -porganisasi_pass organisasi_manajemen -e "DESCRIBE photos;"
```

## Create Storage Link

Make sure the storage link is created for public file access:

```bash
docker-compose exec app php artisan storage:link
```

## File Permissions

Ensure proper permissions for storage directories:

```bash
docker-compose exec app chmod -R 775 storage
docker-compose exec app chmod -R 775 bootstrap/cache
```

## Testing Routes

Once migrations are complete, you can test the routes:

**Public Routes (no authentication required):**

- http://localhost:8888/gallery
- http://localhost:8888/gallery/{slug}

**Authenticated Routes (login required):**

- http://localhost:8888/albums
- http://localhost:8888/albums/create
- http://localhost:8888/albums/{id}
- http://localhost:8888/albums/{id}/edit

## Next Development Steps

1. **Create Vue Components** (Phase 6 - Next Task)
   - Albums/Index.vue
   - Albums/Create.vue
   - Albums/Edit.vue
   - Albums/Show.vue

2. **Test Backend Functionality**
   - Use Postman or similar tool to test API endpoints
   - Verify file uploads work correctly
   - Test authorization policies

3. **Build Frontend Components**
   - Photo upload component with preview
   - Gallery grid component
   - Lightbox/modal for photo viewing
   - Photo management interface

## Troubleshooting

### Issue: Migrations fail

```bash
# Check if database is running
docker-compose ps

# Check database connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Issue: File uploads don't work

```bash
# Verify storage link exists
docker-compose exec app ls -la public/storage

# If not, create it
docker-compose exec app php artisan storage:link

# Check permissions
docker-compose exec app ls -la storage/app/public
```

### Issue: Routes not found

```bash
# Clear route cache
docker-compose exec app php artisan route:clear

# List all routes
docker-compose exec app php artisan route:list | grep album
```

## API Endpoints Reference

### Album Management

- `GET /albums` - List all albums (with filters)
- `POST /albums` - Create new album
- `GET /albums/{album}` - Show album details
- `PUT /albums/{album}` - Update album
- `DELETE /albums/{album}` - Delete album

### Photo Management

- `POST /albums/{album}/photos` - Upload photos
- `DELETE /albums/{album}/photos/{photo}` - Delete photo
- `PATCH /albums/{album}/photos/order` - Reorder photos
- `PATCH /albums/{album}/photos/{photo}/description` - Update photo description
- `GET /photos/{photo}` - Get photo details
- `GET /photos/{photo}/download` - Download photo

### Public Gallery

- `GET /gallery` - List public albums
- `GET /gallery/{slug}` - Show public album
