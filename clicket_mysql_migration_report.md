# CLICKET JSON to MySQL Migration Report

Date: 2026-06-20

## Source of Truth

- Database schema: `clicket_final_database.sql`
- Database name: `clicket`
- Runtime connection layer: `includes/database.php`

## Files Modified or Added

- `includes/database.php` - centralized PDO connection and shared MySQL helpers.
- `includes/catalog-db.php` - seeds and reads venues, layouts, events, and performances from MySQL.
- `includes/data.php` - loads the existing catalog from MySQL after using the old arrays only as seed input.
- `includes/log.php` - authenticates customers from `users` and staff from `staff_accounts`.
- `includes/favorite-data.php` - stores favorites in `favorites`.
- `includes/reservation.php` - stores active seat holds in `seat_holds` and `seat_hold_items`.
- `includes/order-history-data.php` - stores orders, seats, payments, payment proofs, and tickets in MySQL.
- `includes/staff-panel-data.php` - reads reservations and favorites from MySQL-backed helpers.
- `includes/ticket-selection-api.php` - sends full selected-seat details to the MySQL reservation layer.
- `clicket_migrate_json_to_mysql.php` - one-time legacy JSON import script.
- `clicket_mysql_migration_report.md` - this report.

## Migrated Runtime Areas

- Customer authentication: `users`
- Admin/organizer authentication: `staff_accounts`
- Event catalog and schedules: `events`, `event_performances`, `venues`, `venue_layouts`
- Seat holds and checkout reservation conflicts: `seat_holds`, `seat_hold_items`, `seats`
- Orders and selected seats: `orders`, `order_seats`
- Payments and proof metadata: `payments`, `payment_proofs`
- Issued tickets: `tickets`
- Favorites/bookmarks: `favorites`
- Staff dashboard analytics: database-backed helper outputs

## Import Result

Imported through XAMPP PHP using:

```powershell
& 'C:\xampp\php\php.exe' clicket_migrate_json_to_mysql.php
```

Current MySQL row counts:

- `users`: 18
- `staff_accounts`: 10
- `venues`: 7
- `venue_layouts`: 10
- `events`: 23
- `event_performances`: 47
- `orders`: 10
- `order_seats`: 17
- `payments`: 10
- `payment_proofs`: 5
- `tickets`: 17
- `favorites`: 0

## Validation

- Final schema is present with exactly 30 tables.
- Foreign key count: 59.
- PHP lint passed for the changed PHP files.
- MySQL catalog smoke check: 11 concerts, 8 theater events, 4 sports events, 7 featured events.
- Order-history smoke check: 10 orders loaded from MySQL.
- Booked-seat lookup returned migrated seats for `concerts-1`.
- Local Apache smoke checks returned HTTP 200 for:
  - `index.php`
  - `events.php`
  - `show.php?event=concerts-1`
  - `auth.php?mode=admin`

## Legacy JSON Files Safe to Remove After Backup

Do not remove these until you are satisfied with the migrated MySQL data and have a backup:

- `storage/users.json`
- `storage/staff.json`
- `storage/orders.json`
- `storage/favorites.json`
- `storage/reservations.json`

The one-time importer still references these files by design. Runtime application helpers no longer read or write them.
