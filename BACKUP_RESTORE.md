# MySQL Backup and Restore Guide

## Automatic Backups

This project automatically creates MySQL backups every hour during business hours (8 AM – 11 PM IST).

Backups are stored in:

```
db-backups branch
```

inside:

```
backups/
```

Example:

```
backups/
├── backup-2026-06-21-08-30.sql.gz
├── backup-2026-06-21-09-30.sql.gz
├── backup-2026-06-21-10-30.sql.gz
```

Only the latest 10 backups are kept.

---

# Restore Procedure

## Step 1: Download the backup

Download:

```
backup-YYYY-MM-DD-HH-MM.sql.gz
```

from the `db-backups` branch.

---

## Step 2: Extract the backup

### Linux / macOS

```bash
gunzip backup-2026-06-21-10-30.sql.gz
```

This produces:

```text
backup-2026-06-21-10-30.sql
```

### Windows

Use:

- WinRAR
- 7-Zip

to extract the file.

---

# Restore to Existing Database

```bash
mysql \
-h HOST \
-P 3306 \
-u USER \
-p DATABASE_NAME < backup-2026-06-21-10-30.sql
```

Example:

```bash
mysql \
-h 65.xxx.xxx.xxx \
-P 3306 \
-u root \
-p family_db < backup-2026-06-21-10-30.sql
```

---

# Restore to New Database

## Create database

```sql
CREATE DATABASE family_restore;
```

or:

```bash
mysql -u root -p
```

then:

```sql
CREATE DATABASE family_restore;
EXIT;
```

---

## Restore

```bash
mysql \
-h HOST \
-P 3306 \
-u USER \
-p family_restore < backup-2026-06-21-10-30.sql
```

---

# Verify

Connect:

```bash
mysql -u root -p
```

Select database:

```sql
USE family_restore;
```

Show tables:

```sql
SHOW TABLES;
```

Count records:

```sql
SELECT COUNT(*) FROM users;
```

---

# Emergency Recovery

1. Download latest backup.
2. Extract:

```bash
gunzip backup.sql.gz
```

3. Create database:

```sql
CREATE DATABASE restore_db;
```

4. Restore:

```bash
mysql -u root -p restore_db < backup.sql
```

5. Verify tables:

```sql
SHOW TABLES;
```

---

# Backup Schedule

- Every hour during business hours.
- Latest 10 backups retained.
- Old backups automatically removed.
