# Plan: ListController index — SalesAutoPilot API integration

## Context
The `ListController::index()` is a placeholder that returns an empty `lists` view. We need it to fetch lists from the SalesAutoPilot v2 API (`GET /lists`), display them with a Details button per row, and handle API errors inline.

User choices:
- **Auth**: Extend `ApiClient` with optional Bearer token support (rather than a new class)
- **Errors**: Inline message in the view (no redirect)
- **Config**: Add `salesautopilot` block to `config/services.php`

---

## Steps

### 1. `config/services.php` — add SalesAutoPilot block
```php
'salesautopilot' => [
    'url'   => 'https://api.salesautopilot.com/v2',
    'token' => env('SALESAUTOPILOT_JWT_TOKEN'),
],
```

### 2. `app/Services/ApiClient.php` — add optional Bearer token
- Add `private readonly ?string $token = null` to constructor
- In `request()`, conditionally chain `->withToken($this->token)` before the `->get()` call when token is set

### 3. `app/Providers/AppServiceProvider.php` — bind a SalesAutoPilot-configured ApiClient
```php
$this->app->bind('salesautopilot.client', fn() => new ApiClient(
    baseUrl: config('services.salesautopilot.url'),
    token:   config('services.salesautopilot.token'),
));
```

### 4. `app/Http/Controllers/ListController.php` — implement index()
- Constructor-inject `ApiClient` (resolved from the `salesautopilot.client` binding via `app()` or a typed binding)
- Call `$this->client->get('/lists')` inside a try/catch for `\Illuminate\Http\Client\RequestException`
- On success: pass `$lists` to view
- On failure: pass `$error` string (from exception message or status code) to view

### 5. `resources/views/lists.blade.php` — update template
- If `$error` is set: show an inline error alert
- Otherwise: render a table/list of records, each row has a "Details" button (disabled/placeholder `href="#"` — functionality TBD)

### 6. Add `show` route (placeholder, details later)
In `routes/web.php` add:
```php
Route::get('/lists/{id}', [ListController::class, 'show'])->name('lists.show');
```
Details button can link to `route('lists.show', $list['id'])`.

---

## Critical files
- `app/Services/ApiClient.php`
- `app/Providers/AppServiceProvider.php`
- `config/services.php`
- `app/Http/Controllers/ListController.php`
- `resources/views/lists.blade.php`
- `routes/web.php`

---

## Verification
1. Set `SALESAUTOPILOT_JWT_TOKEN` in `.env`
2. Visit `/` — should display the list table populated from the API
3. Remove/corrupt the token — should display an inline error message
4. Run `php artisan test --compact` to ensure no existing tests break
