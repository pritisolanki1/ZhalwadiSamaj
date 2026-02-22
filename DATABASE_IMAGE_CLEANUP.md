# Database Image Path Cleanup Guide

This document lists all database tables and fields that store image/video file paths. Since the actual image files are missing from the server, you need to clean up these fields in your database.

## Important Notes

- **JSON Fields**: These store arrays of image filenames. Set them to `NULL` or `'[]'` (empty JSON array).
- **TEXT/STRING Fields**: These store single image filenames. Set them to `NULL` or `''` (empty string).
- **Recommendation**: Use `NULL` for all fields as it's cleaner and more standard.

---

## Database Tables and Fields to Clean

### 1. `members` Table
**Table Name**: `members`

| Field Name | Data Type | Description | Set To |
|------------|-----------|-------------|--------|
| `avatar` | TEXT | Single member avatar image filename | `NULL` |
| `slider` | JSON | Array of slider image filenames | `NULL` |

**SQL Commands**:
```sql
UPDATE members SET avatar = NULL WHERE avatar IS NOT NULL;
UPDATE members SET slider = NULL WHERE slider IS NOT NULL;
```

---

### 2. `businesses` Table
**Table Name**: `businesses`

| Field Name | Data Type | Description | Set To |
|------------|-----------|-------------|--------|
| `logo` | JSON | Array of logo image filenames | `NULL` |
| `slider` | JSON | Array of slider image filenames | `NULL` |
| `gallery` | JSON | Array of gallery image filenames | `NULL` |

**SQL Commands**:
```sql
UPDATE businesses SET logo = NULL WHERE logo IS NOT NULL;
UPDATE businesses SET slider = NULL WHERE slider IS NOT NULL;
UPDATE businesses SET gallery = NULL WHERE gallery IS NOT NULL;
```

---

### 3. `teams` Table
**Table Name**: `teams`

| Field Name | Data Type | Description | Set To |
|------------|-----------|-------------|--------|
| `avatar` | TEXT | Single team avatar image filename | `NULL` |

**SQL Commands**:
```sql
UPDATE teams SET avatar = NULL WHERE avatar IS NOT NULL;
```

---

### 4. `jobs` Table
**Table Name**: `jobs`

| Field Name | Data Type | Description | Set To |
|------------|-----------|-------------|--------|
| `avatar` | TEXT | Single job avatar image filename | `NULL` |

**SQL Commands**:
```sql
UPDATE jobs SET avatar = NULL WHERE avatar IS NOT NULL;
```

---

### 5. `user_galleries` Table (Member Gallery)
**Table Name**: `user_galleries`

| Field Name | Data Type | Description | Set To |
|------------|-----------|-------------|--------|
| `images` | JSON | Array of image filenames | `NULL` |
| `videos` | JSON | Array of video filenames | `NULL` |

**SQL Commands**:
```sql
UPDATE user_galleries SET images = NULL WHERE images IS NOT NULL;
UPDATE user_galleries SET videos = NULL WHERE videos IS NOT NULL;
```

---

### 6. `gallery_images` Table
**Table Name**: `gallery_images`

| Field Name | Data Type | Description | Set To |
|------------|-----------|-------------|--------|
| `images` | JSON | Array of image filenames | `NULL` |
| `videos` | JSON | Array of video filenames | `NULL` |

**SQL Commands**:
```sql
UPDATE gallery_images SET images = NULL WHERE images IS NOT NULL;
UPDATE gallery_images SET videos = NULL WHERE videos IS NOT NULL;
```

---

### 7. `game_results` Table
**Table Name**: `game_results`

| Field Name | Data Type | Description | Set To |
|------------|-----------|-------------|--------|
| `image` | STRING | Single game result image filename | `NULL` |

**SQL Commands**:
```sql
UPDATE game_results SET image = NULL WHERE image IS NOT NULL;
```

---

### 8. `announcements` Table
**Table Name**: `announcements`

| Field Name | Data Type | Description | Set To |
|------------|-----------|-------------|--------|
| `image` | STRING(100) | Single announcement image filename | `NULL` |

**SQL Commands**:
```sql
UPDATE announcements SET image = NULL WHERE image IS NOT NULL;
```

---

### 9. `reports` Table
**Table Name**: `reports`

| Field Name | Data Type | Description | Set To |
|------------|-----------|-------------|--------|
| `image` | STRING | Single report image filename | `NULL` |

**SQL Commands**:
```sql
UPDATE reports SET image = NULL WHERE image IS NOT NULL;
```

---

## Complete SQL Script for All Tables

You can run this complete script to clean up all image fields at once:

```sql
-- Members table
UPDATE members SET avatar = NULL WHERE avatar IS NOT NULL;
UPDATE members SET slider = NULL WHERE slider IS NOT NULL;

-- Businesses table
UPDATE businesses SET logo = NULL WHERE logo IS NOT NULL;
UPDATE businesses SET slider = NULL WHERE slider IS NOT NULL;
UPDATE businesses SET gallery = NULL WHERE gallery IS NOT NULL;

-- Teams table
UPDATE teams SET avatar = NULL WHERE avatar IS NOT NULL;

-- Jobs table
UPDATE jobs SET avatar = NULL WHERE avatar IS NOT NULL;

-- User galleries table (Member Gallery)
UPDATE user_galleries SET images = NULL WHERE images IS NOT NULL;
UPDATE user_galleries SET videos = NULL WHERE videos IS NOT NULL;

-- Gallery images table
UPDATE gallery_images SET images = NULL WHERE images IS NOT NULL;
UPDATE gallery_images SET videos = NULL WHERE videos IS NOT NULL;

-- Game results table
UPDATE game_results SET image = NULL WHERE image IS NOT NULL;

-- Announcements table
UPDATE announcements SET image = NULL WHERE image IS NOT NULL;

-- Reports table
UPDATE reports SET image = NULL WHERE image IS NOT NULL;
```

---

## Alternative: Set to Empty Values Instead of NULL

If you prefer to use empty values instead of NULL:

### For JSON Fields (arrays):
```sql
-- Set JSON fields to empty array '[]'
UPDATE members SET slider = '[]' WHERE slider IS NOT NULL;
UPDATE businesses SET logo = '[]', slider = '[]', gallery = '[]' WHERE logo IS NOT NULL OR slider IS NOT NULL OR gallery IS NOT NULL;
UPDATE user_galleries SET images = '[]', videos = '[]' WHERE images IS NOT NULL OR videos IS NOT NULL;
UPDATE gallery_images SET images = '[]', videos = '[]' WHERE images IS NOT NULL OR videos IS NOT NULL;
```

### For TEXT/STRING Fields:
```sql
-- Set text/string fields to empty string ''
UPDATE members SET avatar = '' WHERE avatar IS NOT NULL;
UPDATE teams SET avatar = '' WHERE avatar IS NOT NULL;
UPDATE jobs SET avatar = '' WHERE avatar IS NOT NULL;
UPDATE game_results SET image = '' WHERE image IS NOT NULL;
UPDATE announcements SET image = '' WHERE image IS NOT NULL;
UPDATE reports SET image = '' WHERE image IS NOT NULL;
```

---

## Verification Queries

After cleaning up, verify that all image fields are empty:

```sql
-- Check members
SELECT COUNT(*) as count_with_avatar FROM members WHERE avatar IS NOT NULL AND avatar != '';
SELECT COUNT(*) as count_with_slider FROM members WHERE slider IS NOT NULL AND slider != '[]' AND slider != 'null';

-- Check businesses
SELECT COUNT(*) as count_with_images FROM businesses WHERE logo IS NOT NULL OR slider IS NOT NULL OR gallery IS NOT NULL;

-- Check all tables
SELECT 
    (SELECT COUNT(*) FROM members WHERE avatar IS NOT NULL) as members_avatar,
    (SELECT COUNT(*) FROM members WHERE slider IS NOT NULL) as members_slider,
    (SELECT COUNT(*) FROM businesses WHERE logo IS NOT NULL OR slider IS NOT NULL OR gallery IS NOT NULL) as businesses_images,
    (SELECT COUNT(*) FROM teams WHERE avatar IS NOT NULL) as teams_avatar,
    (SELECT COUNT(*) FROM jobs WHERE avatar IS NOT NULL) as jobs_avatar,
    (SELECT COUNT(*) FROM user_galleries WHERE images IS NOT NULL OR videos IS NOT NULL) as user_galleries_media,
    (SELECT COUNT(*) FROM gallery_images WHERE images IS NOT NULL OR videos IS NOT NULL) as gallery_images_media,
    (SELECT COUNT(*) FROM game_results WHERE image IS NOT NULL) as game_results_image,
    (SELECT COUNT(*) FROM announcements WHERE image IS NOT NULL) as announcements_image,
    (SELECT COUNT(*) FROM reports WHERE image IS NOT NULL) as reports_image;
```

---

## Summary

**Total Tables to Update**: 9 tables
**Total Fields to Update**: 13 fields

- **JSON Array Fields** (7 fields): `members.slider`, `businesses.logo`, `businesses.slider`, `businesses.gallery`, `user_galleries.images`, `user_galleries.videos`, `gallery_images.images`, `gallery_images.videos`
- **Single Value Fields** (6 fields): `members.avatar`, `teams.avatar`, `jobs.avatar`, `game_results.image`, `announcements.image`, `reports.image`

**Recommended Action**: Set all fields to `NULL` for consistency and cleaner data.

---

## Notes

1. **Backup First**: Always backup your database before running these UPDATE queries.
2. **Test Environment**: Test these queries on a development/staging environment first.
3. **API Behavior**: After cleanup, the API will return empty arrays or empty strings for image fields, which is handled gracefully by the updated code.
4. **Future Uploads**: When new images are uploaded, they will be stored properly and the API will work correctly.

