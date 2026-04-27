# Plan: Replace JWT Token with Username/Password + Caching

## Context

The app currently stores a static JWT token in `.env` / `config/services.php` and passes it directly to `ApiClient`. The token expires (it has an `exp` claim), so it needs to be refreshed dynamically. The fix: store credentials instead, obtain a JWT via `POST /access-token` at runtime, cache it for 30 minutes, and reuse it across requests.

---

## Files to Change

| File                                   | What changes                                                                                  |
|----------------------------------------|-----------------------------------------------------------------------------------------------|
| `.env`                                 | Replace `SALESAUTOPILOT_JWT_TOKEN` with `SALESAUTOPILOT_USERNAME` + `SALESAUTOPILOT_PASSWORD` |
| `.env.example`                         | Same swap                                                                                     |
| `config/services.php`                  | Replace `token` key with `username` + `password`                                              |
| `app/Services/ApiClient.php`           | Add login + cache logic (see below)                                                           |
| `app/Providers/AppServiceProvider.php` | Pass `username`/`password` instead of `token`                                                 |

---

## Implementation

### `config/services.php`
```php
'salesautopilot' => [
    'url'      => 'https://api.salesautopilot.com/v2',
    'username' => env('SALESAUTOPILOT_USERNAME'),
    'password' => env('SALESAUTOPILOT_PASSWORD'),
],
```

### `app/Services/ApiClient.php`

- Remove `readonly` so we can inject `CacheRepository`
- Constructor: `baseUrl`, `username`, `password`, `CacheRepository $cache`
- Add private `getToken(): string` — checks `Cache::get('salesautopilot_jwt')`, if missing calls `login()`, stores result with `Cache::put(..., now()->addMinutes(30))`, returns token
- Add private `login(): string` — `POST /access-token` with `['username' => ..., 'password' => ...]`, returns `$response->json('token')` (field name TBD from live response — likely `token` or `access_token`)
- `request()` calls `$this->getToken()` instead of using `$this->token`
- Cache key: `salesautopilot_jwt` (string constant at top of class)

### `app/Providers/AppServiceProvider.php`
```php
$this->app->bind(ApiClient::class, fn () => new ApiClient(
    baseUrl:  config('services.salesautopilot.url'),
    username: config('services.salesautopilot.username'),
    password: config('services.salesautopilot.password'),
    cache:    app(\Illuminate\Contracts\Cache\Repository::class),
));
```

---

## Notes

- The login endpoint response field for the token needs to be verified against the live API (likely `token`). If it differs, `login()` needs to be adjusted.
- Cache driver is already configured as `database` in `config/cache.php` — no changes needed there.
- No changes to `ListController` — `ApiClient`'s public interface stays the same.

---

## Verification

1. Remove `SALESAUTOPILOT_JWT_TOKEN` from `.env`, add real credentials
2. Run `php artisan config:clear && php artisan cache:clear`
3. Visit `/` — first load should trigger a login request then cache the token
4. Visit `/` again — should use cached token (check with `php artisan tinker --execute 'dump(cache("salesautopilot_jwt"));'`)
5. Run `php artisan test --compact` to ensure no regressions
