# Forced App Update - Laravel Backend

This document explains the backend-controlled app version system and how to deploy/test it.

## What Was Added

- Database table: `app_versions`
- Model: `App\Models\AppVersion`
- Public API endpoint:

  ```text
  GET /api/app/version
  ```

- Admin web page:

  ```text
  GET /app-version
  PUT /app-version
  ```

- Dashboard/navbar links for app version management.

## Files Added

```text
app/Http/Controllers/Api/AppVersionController.php
app/Http/Controllers/AppVersionController.php
app/Models/AppVersion.php
database/migrations/2026_05_30_000000_create_app_versions_table.php
resources/views/app_versions/edit.blade.php
```

## Files Modified

```text
routes/api.php
routes/web.php
resources/views/layouts/app.blade.php
resources/views/dashboard.blade.php
```

Note: `app/Services/FirebaseNotificationService.php` already had local changes before this documentation pass. Review it separately before committing/deploying if needed.

## Database

New table:

```text
app_versions
```

Columns:

```text
id
latest_version
minimum_supported_version
force_update
update_message
play_store_url
created_at
updated_at
```

The migration inserts a default row:

```text
latest_version = 1.2
minimum_supported_version = 1.2
force_update = false
play_store_url = https://play.google.com/store/apps/details?id=com.zalawadi.app
```

## Deployment Steps

1. Deploy the backend code.
2. Run migrations:

   ```bash
   php artisan migrate
   ```

3. Clear caches if production uses cached routes/config/views:

   ```bash
   php artisan optimize:clear
   ```

4. Log in to the Laravel admin panel.
5. Open:

   ```text
   /app-version
   ```

6. Configure latest/minimum supported versions and Play Store URL.

## API Contract

Endpoint:

```text
GET /api/app/version
```

Authentication:

```text
Not required
```

Example response:

```json
{
  "status": "Success",
  "message": "App version fetched",
  "data": {
    "latest_version": "2.0.0",
    "minimum_supported_version": "1.8.0",
    "force_update": true,
    "update_message": "A new version is available. Please update to continue.",
    "play_store_url": "https://play.google.com/store/apps/details?id=com.zalawadi.app"
  }
}
```

## Admin Configuration Examples

### No Update Required

```text
latest_version = 1.2
minimum_supported_version = 1.2
force_update = false
```

### Optional Update

```text
latest_version = 1.3
minimum_supported_version = 1.2
force_update = false
```

Android behavior:

- Shows update prompt.
- User can tap `Later`.

### Forced Update

```text
latest_version = 1.3
minimum_supported_version = 1.3
force_update = true
```

Android behavior:

- Shows full-screen update page.
- User cannot dismiss it.
- User cannot access app features.

## Backend Testing

### 1. Verify Migration

```bash
php artisan migrate
```

Check database:

```sql
SELECT * FROM app_versions;
```

Expected:

- One row exists.
- Defaults are populated.

### 2. Verify API

```bash
curl https://YOUR_DOMAIN/api/app/version
```

Expected:

- HTTP 200.
- `status` is `Success`.
- `data.latest_version` and `data.minimum_supported_version` are present.

### 3. Verify Admin Page

Open:

```text
https://YOUR_DOMAIN/app-version
```

Expected:

- Login is required.
- Admin can edit:
  - Latest version
  - Minimum supported version
  - Force update
  - Update message
  - Play Store URL

### 4. Verify Validation

Try invalid values:

```text
latest_version = abc
minimum_supported_version = 1.x
play_store_url = not-a-url
```

Expected:

- Form validation errors are shown.
- Database is not updated with invalid values.

## Release Workflow

1. Upload the new APK/AAB to Google Play.
2. Wait until the release is available to users.
3. Update backend:

   ```text
   latest_version = new Android versionName
   minimum_supported_version = oldest allowed versionName
   force_update = true or false
   ```

4. Test with an older installed APK.
5. Keep future version changes in the admin panel. No code change is required for future force-update rules.
