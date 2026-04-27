# Plan: List Details Page

## Context

The app lists SalesAutoPilot mailing lists on the index page, but the "Details" button links to `#` and `show()` returns the wrong view. We need to wire up the details route to fetch and display up to 20 subscribers for the selected list.

## Changes Required

### 1. `app/Http/Controllers/ListController.php`

- Add `int $id` parameter to `show()`
- Call `$this->client->get("/newsletter/{$id}/subscribers", ['limit' => 20])` inside a try/catch (same exception pattern as `index()`)
- Pass `$subscribers` and `$id` to a new `list-details` view
- On error, pass `$error` and `$id` to the same view so the back link still renders

### 2. `resources/views/lists.blade.php`

- Update the Details `<a>` href from `#` to `route('lists.show', $list['listId'])`

### 3. `resources/views/list-details.blade.php` (new file)

- Extends `layouts.app`
- Heading: "List #{{ $id }} – Subscribers"
- Back link: `route('home')` styled like the existing Details button (dark pill)
- Error state: same red alert box pattern from `lists.blade.php`
- Empty state: "No subscribers found."
- Table columns: **ID**, **Email**, **First Name**, **Last Name**
- Iterate `$subscribers['data']` using keys `id`, `email`, `firstName`, `lastName` (verify against API response shape once wired up)

## Critical Files

| File                                      | Action           |
|-------------------------------------------|------------------|
| `app/Http/Controllers/ListController.php` | Edit `show()`    |
| `resources/views/lists.blade.php`         | Fix Details href |
| `resources/views/list-details.blade.php`  | Create new view  |

## API Endpoint

`GET https://api.salesautopilot.com/v2/newsletter/{newsletterId}/subscribers`  
Query param `limit=20` passed via `ApiClient::get()` second argument.

## Verification

1. Open `/` — confirm Details button now links to `/listDetails/{id}`
2. Click Details — confirm subscriber table renders with id, email, firstname, lastname
3. Click "Back to Lists" — confirm navigation returns to index
4. Simulate API error (bad token) — confirm error message renders with back link still present
