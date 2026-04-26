# Clear Up Default Laravel Template

## Context

Starting from a fresh Laravel 13 install, we stripped out database-related boilerplate that won't be used, and set up a clean foundation for a full-stack Blade app that pulls data from an external REST API and uses the file-based cache. No auth, no database reads/writes.

---

## Changes

### 1. Clean up .env
- Set `CACHE_STORE=file`
- Remove all unused variables: `DB_*`, `BCRYPT_ROUNDS`, `SESSION_*`, `MEMCACHED_HOST`, `QUEUE_CONNECTION`, `BROADCAST_CONNECTION`, `MAIL_*`, `REDIS_*`, `AWS_*`
- Keep: `APP_*`, `CACHE_STORE`, `LOG_*`, `VITE_*`

### 2. Remove unused database boilerplate
Deleted:
- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/0001_01_01_000001_create_cache_table.php`
- `database/migrations/0001_01_01_000002_create_jobs_table.php`
- `database/factories/UserFactory.php`
- `database/seeders/DatabaseSeeder.php`
- `app/Models/User.php`

### 3. Set up a shared Blade layout
Created `resources/views/layouts/app.blade.php` — a minimal HTML shell with `@yield('content')` that includes Tailwind CSS via Vite.

### 4. Replace the welcome view with a clean home view
- Removed `resources/views/welcome.blade.php`
- Created `resources/views/home.blade.php` extending the layout
- Updated `routes/web.php` to point `/` to `HomeController@index`

### 5. Create a HomeController
- `app/Http/Controllers/HomeController.php`
- `index()` returns the `home` view

### 6. Create an HTTP client service for the external REST API
- `app/Services/ApiClient.php`
- Wraps Laravel's `Http` facade with a configurable base URL
- Caches responses using `Cache::remember()` with a configurable TTL

### 7. Clean up routes/console.php
- Removed the default `Artisan::command('inspire', ...)` entry

### 8. Replace example tests with real ones
- Deleted `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php`
- Created `tests/Feature/HomeTest.php` — asserts GET `/` returns 200 and the correct view
- Created `tests/Unit/ApiClientTest.php` — unit tests for `ApiClient` (Http facade mocking)

---

## Files Modified
| File | Change |
|------|--------|
| `.env` | `CACHE_STORE=file`, removed ~30 unused variables |
| `routes/web.php` | Replaced closure with `HomeController@index` |
| `routes/console.php` | Removed inspire command |

## Files Created
| File | Purpose |
|------|---------|
| `resources/views/layouts/app.blade.php` | Shared HTML layout |
| `resources/views/home.blade.php` | Home page view |
| `app/Http/Controllers/HomeController.php` | Returns home view |
| `app/Services/ApiClient.php` | Wraps Http facade for external API |
| `tests/Feature/HomeTest.php` | Smoke test for home route |
| `tests/Unit/ApiClientTest.php` | Unit tests for ApiClient |

## Files Deleted
- `resources/views/welcome.blade.php`
- `app/Models/User.php`
- `database/factories/UserFactory.php`
- `database/seeders/DatabaseSeeder.php`
- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/0001_01_01_000001_create_cache_table.php`
- `database/migrations/0001_01_01_000002_create_jobs_table.php`
- `tests/Feature/ExampleTest.php`
- `tests/Unit/ExampleTest.php`

---

## Verification
1. `php artisan test --compact` — all tests pass (HomeTest + ApiClientTest)
2. `php artisan route:list` — only `/` and `/up` routes exist
3. `php artisan config:show cache.default` — shows `file`
4. Browse to `/` — renders the clean home view with no errors
