# Plan: Subscriber Count on List Index Page

## Context

The list index page currently shows each list's ID and name but not the number of subscribers. There's already a TODO comment in `ListController::index()` for this. The `listtotalcount/{list_id}` endpoint provides this data but uses basic auth (username + password) instead of the existing JWT bearer token, and has a different base URL (no `/v2` prefix).

User decisions: extend `ApiClient` to support basic auth, fetch counts sequentially, fail the whole page on any error.

---

## Changes

### 1. Extend `ApiClient` — `app/Services/ApiClient.php`
Add optional `username` and `password` constructor params. In `request()`, apply `->withBasicAuth()` when credentials are present (instead of `->withToken()`). Logic: if `username` is set, use basic auth; else if `token` is set, use bearer; else no auth.

### 2. Config — `config/services.php`
Add basic auth credentials and the old API base URL to the `salesautopilot` block:
```php
'salesautopilot' => [
    'url'         => 'https://api.salesautopilot.com/v2',
    'token'       => env('SALESAUTOPILOT_JWT_TOKEN'),
    'old_api_url' => env('SALESAUTOPILOT_OLD_API_URL', 'https://api.salesautopilot.com'),
    'username'    => env('SALESAUTOPILOT_USERNAME'),
    'password'    => env('SALESAUTOPILOT_PASSWORD'),
],
```

### 3. Env files — `.env` and `.env.example`
Add three new keys:
```
SALESAUTOPILOT_OLD_API_URL=https://api.salesautopilot.com
SALESAUTOPILOT_USERNAME=
SALESAUTOPILOT_PASSWORD=
```

### 4. Service container — `app/Providers/AppServiceProvider.php`
Register a second named binding and use `bindMethod` to inject both clients into `ListController`:
```php
$this->app->bind('salesautopilot.count', fn () => new ApiClient(
    baseUrl: config('services.salesautopilot.old_api_url'),
    username: config('services.salesautopilot.username'),
    password: config('services.salesautopilot.password'),
));

$this->app->bindMethod([ListController::class, '__construct'], fn ($app) =>
    new ListController(
        client: $app->make(ApiClient::class),
        countClient: $app->make('salesautopilot.count'),
    )
);
```

### 5. Controller — `app/Http/Controllers/ListController.php`
- Add `private readonly ApiClient $countClient` constructor param.
- Remove the TODO comment.
- After fetching `$lists`, loop sequentially and call `$this->countClient->get("/listtotalcount/{$list['listId']}")` for each list, building a `$counts` map keyed by list ID.
- Catch `RequestException` / `ConnectionException` from any count call and return the error view.
- Pass `$counts` to the view alongside `$lists`.

### 6. View — `resources/views/lists.blade.php`
- Add a "Subscribers" `<th>` column header.
- In each `<tr>`, add a `<td>` rendering `$counts[$list['listId']] ?? '—'`.

---

## Critical Files
- `app/Services/ApiClient.php`
- `app/Providers/AppServiceProvider.php`
- `app/Http/Controllers/ListController.php`
- `resources/views/lists.blade.php`
- `config/services.php`
- `.env` / `.env.example`

---

## Verification
1. Set `SALESAUTOPILOT_USERNAME` and `SALESAUTOPILOT_PASSWORD` in `.env`.
2. Load the home page (`/`) and confirm a "Subscribers" column appears with numeric counts.
3. Temporarily set invalid credentials and confirm the page shows the full-page error message.
4. Run `php artisan test --compact` — existing tests should still pass.
