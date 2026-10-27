# Courier API

A small Laravel REST API for managing courier master data (CRUD, pagination, search, filter and sort).

## Applicant details

I built this repository for the programming test in my application to Gradin as a Backend Developer.

| | |
|---|---|
| Applicant | Muhammad Rafli Sahrul Ramadhan |
| Position | Backend Developer |
| Company | Gradin |
| Test | Backend programming test (Laravel, Courier module) |

## Requirements

- PHP 8.3 or newer with `pdo_sqlite` (or another database driver)
- Composer

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The `--seed` flag adds 20 sample couriers. You can run `php artisan db:seed` again at any time without creating duplicates.

The API runs at `http://127.0.0.1:8000/api/couriers`.

## Run the tests

```bash
php artisan test
```

Tests run on an in-memory SQLite database and leave your local data alone.

## Courier fields

| Field           | Type    | Rules                                              |
|-----------------|---------|----------------------------------------------------|
| `name`          | string  | required, max 255                                  |
| `email`         | string  | required, valid email, unique                      |
| `phone`         | string  | required, 8 to 15 digits, optional leading `+`     |
| `vehicle_type`  | string  | required, one of `bicycle`, `motorcycle`, `car`, `van` |
| `license_plate` | string  | optional, max 15                                   |
| `level`         | integer | required, 1 to 5                                   |
| `is_active`     | boolean | optional, defaults to `true`                       |

## Endpoints

| Method         | URL                  | Description                  |
|----------------|----------------------|------------------------------|
| GET            | `/api/couriers`      | List couriers (paginated)    |
| GET            | `/api/couriers/{id}` | Show one courier             |
| POST           | `/api/couriers`      | Create a courier (201)       |
| PUT / PATCH    | `/api/couriers/{id}` | Update a courier             |
| DELETE         | `/api/couriers/{id}` | Delete a courier (204)       |

Send the `Accept: application/json` header to get errors as JSON.

### List query parameters

| Parameter  | Example              | Description                                                                 |
|------------|----------------------|-----------------------------------------------------------------------------|
| `search`   | `?search=budi+agung` | Every word must appear in the name, in any order. Matches "Budiono Hadi Agung". |
| `level`    | `?level=2,3`         | Only couriers with these levels.                                            |
| `sort`     | `?sort=-created_at`  | `name` (default), `created_at`. Prefix with `-` for descending.             |
| `per_page` | `?per_page=25`       | Page size. Default 15, max 100.                                             |
| `page`     | `?page=2`            | Page number.                                                                |

Parameters can be combined: `/api/couriers?search=budi&level=2,3&sort=-created_at`.

### Example

```bash
curl -X POST http://127.0.0.1:8000/api/couriers \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"Budiono Hadi Agung","email":"budiono@example.com","phone":"081234567890","vehicle_type":"motorcycle","level":3}'
```

## Design decisions

- The controller stays thin. Form Requests handle validation and an API Resource shapes the output.
- Search and the level filter are Eloquent query scopes on the `Courier` model, so other queries can reuse them.
- Search escapes `%` and `_` and matches them as plain text.
- Ordering falls back to `id` for equal values, so pages never repeat or skip rows.
- Delete removes the row from the database. There is no soft delete.
- The API has no authentication because the brief does not ask for it. Add something like Laravel Sanctum before real use.
