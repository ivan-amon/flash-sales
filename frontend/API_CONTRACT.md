# Flash Sales API Contract (MVP)

## Base URL

```
http://localhost/api
```

## Authentication

The API uses **Laravel Sanctum** personal access tokens (Bearer tokens). There are two distinct principals — **regular users** (token ability `is_user`) and **organizers** (token ability `is_organizer`) — issued by separate auth endpoints. Send the token on every authenticated request:

```
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

> Tokens are scoped by ability. A user token cannot access organizer-only routes and vice versa (enforced via the `abilities:is_user` / `abilities:is_organizer` middleware and policies/gates on resource routes).

---

## 1. User Authentication

### Register — `POST /register`

**Request body**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```
Validation: `name` required string max 255 · `email` required, valid email, unique in `users` · `password` required, min 8, must be `confirmed` (i.e. matching `password_confirmation`).

**Response `201 Created`**
```json
{
  "user": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "email_verified_at": null,
    "created_at": "2026-06-07T10:00:00.000000Z",
    "updated_at": "2026-06-07T10:00:00.000000Z"
  },
  "token": "1|abcdef123456..."
}
```

### Login — `POST /login`

**Request body**
```json
{
  "email": "jane@example.com",
  "password": "password123"
}
```
Validation: `email` required, valid email · `password` required.

**Response `200 OK`**
```json
{
  "user": { "id": 1, "name": "Jane Doe", "email": "jane@example.com", "...": "..." },
  "token": "2|abcdef123456..."
}
```

**Error `422 Unprocessable Entity`** — invalid credentials
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": { "email": ["The provided credentials are incorrect."] }
}
```

### Logout — `POST /logout` 🔒 *(user token, ability `is_user`)*

**Response `200 OK`**
```json
{ "message": "Logged out successfully." }
```

### Get authenticated user — `GET /user` 🔒 *(user token, ability `is_user`)*

**Response `200 OK`** — returns the raw `User` model (same shape as `user` above).

---

## 2. Organizer Authentication

### Register — `POST /organizer/register`

**Request body**
```json
{
  "official_name": "Acme Events Ltd",
  "phone": "+1234567890",
  "email": "organizer@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```
Validation: `official_name` required string max 255 · `phone` optional/nullable string, unique in `organizers` · `email` required, valid email, unique in `organizers` · `password` required, min 8, `confirmed`.

**Response `201 Created`**
```json
{
  "organizer": {
    "id": 1,
    "official_name": "Acme Events Ltd",
    "email": "organizer@example.com",
    "phone": "+1234567890",
    "created_at": "2026-06-07T10:00:00.000000Z",
    "updated_at": "2026-06-07T10:00:00.000000Z"
  },
  "token": "3|abcdef123456..."
}
```

### Login — `POST /organizer/login`

**Request body**
```json
{
  "email": "organizer@example.com",
  "password": "password123"
}
```

**Response `200 OK`**
```json
{
  "organizer": { "id": 1, "official_name": "Acme Events Ltd", "...": "..." },
  "token": "4|abcdef123456..."
}
```

**Error `422 Unprocessable Entity`** — same shape as user login (`"The provided credentials are incorrect."`).

### Logout — `POST /organizer/logout` 🔒 *(organizer token, ability `is_organizer`)*

**Response `200 OK`**
```json
{ "message": "Logged out successfully." }
```

---

## 3. Events

> **What changed since the previous contract**
> - Events now have an optional **`description`** field (free text, up to 65535 characters; `null` when omitted).
> - Events now require a **`price`** on creation — an integer **in cents** (e.g. `4999` = €49.99). This price is applied to **every ticket** generated for the event; it is *not* stored on the event itself but surfaces as `ticket.price` (and as `order.amount` once a ticket is reserved). See [Money fields](#money-fields-cents).
> - Events now belong to a **city** (`city_id`, required). List/detail responses embed the full nested `city` → `country` objects.
> - Events have an **event start date** (`event_starts_at`, required) in addition to `sale_starts_at`.
> - `sale_starts_at` is now **required and non-nullable** (it used to be nullable).
> - Events support a **cover image**. The response always includes a `cover_image_url` (absolute URL or `null`) plus the raw `cover_image_path`.
> - Because of the image upload, **create & update are now `multipart/form-data`, not JSON** (see those endpoints below).

> <a id="money-fields-cents"></a>**💶 Money fields are integers in cents.** `ticket.price` and `order.amount` are non-null integers representing the smallest currency unit (cents) to avoid floating-point errors. Divide by 100 for display (`4999` → `49.99`) and multiply by 100 when sending `price`. Never send a decimal.

### Event object shape

A fully serialized event (as returned by list/detail) looks like this:
```json
{
  "id": 1,
  "title": "Summer Music Festival",
  "description": "Three days of live music across five stages.",
  "total_tickets": 500,
  "organizer_id": 1,
  "city_id": 7,
  "sale_starts_at": "2026-07-01T09:00:00.000000Z",
  "event_starts_at": "2026-08-15T18:00:00.000000Z",
  "cover_image_path": "events/abc123.webp",
  "cover_image_url": "http://localhost/storage/events/abc123.webp",
  "created_at": "2026-06-01T10:00:00.000000Z",
  "updated_at": "2026-06-01T10:00:00.000000Z",
  "city": {
    "id": 7,
    "country_id": 3,
    "name": "Barcelona",
    "created_at": "...",
    "updated_at": "...",
    "country": {
      "id": 3,
      "name": "Spain",
      "iso_code": "ES",
      "created_at": "...",
      "updated_at": "..."
    }
  },
  "available_tickets": 350
}
```
Notes:
- `description` is `null` when the organizer didn't provide one.
- The event's per-ticket `price` is **not** part of the event object — read it from `ticket.price` (orders embed `ticket.event`, and the ticket carries the price).
- `cover_image_url` is `null` when no image was uploaded; `cover_image_path` is then also `null`.
- `city` (and its nested `country`) is eager-loaded on the **list** and **detail** endpoints. It is **not** included on the create/update responses (see below).
- `available_tickets` is a computed field present only on the **list** and **detail** endpoints.

### List events — `GET /events` *(public)*

> **⚠️ Breaking change — this endpoint is now paginated.** It used to return a flat array of events. It now returns Laravel's standard length-aware paginator object: the events live under `data`, with pagination metadata at the top level. Use `?page=N` to navigate pages.

**Response `200 OK`** — a paginated payload. Each item in `data` is shaped as above (with `city.country` and `available_tickets`).
```json
{
  "current_page": 1,
  "data": [ /* array of event objects */ ],
  "first_page_url": "http://localhost/api/events?page=1",
  "from": 1,
  "last_page": 4,
  "last_page_url": "http://localhost/api/events?page=4",
  "next_page_url": "http://localhost/api/events?page=2",
  "path": "http://localhost/api/events",
  "per_page": 15,
  "prev_page_url": null,
  "to": 15,
  "total": 52
}
```

### Get a single event — `GET /events/{event}` *(public)*

**Response `200 OK`** — a single event object (same shape as above, with `city.country` and `available_tickets`).

**Error `404 Not Found`** — event doesn't exist:
```json
{ "message": "No query results for model [App\\Models\\Event] {id}" }
```

### Create an event — `POST /events` 🔒 *(organizer only)*

**⚠️ Content type: `multipart/form-data`** (because of the optional file upload). Do **not** set `Content-Type: application/json` for this request — send a `FormData` body and let the browser set the boundary. Keep `Accept: application/json` and the `Authorization` header.

**Form fields**

| Field | Type | Rules |
|---|---|---|
| `title` | string | required, unique in `events` |
| `description` | string | optional/nullable, max 65535 characters |
| `total_tickets` | integer | required, min 1 |
| `price` | integer | **required, min 0 — in cents** (applied to every generated ticket) |
| `city_id` | integer | required, must exist in `cities` |
| `sale_starts_at` | date/datetime | required |
| `event_starts_at` | date/datetime | required |
| `cover_image` | file | optional — image, mime `jpeg`/`png`/`webp`, max 2048 KB |

Authorization: caller must be an organizer (policy `create` on `Event`); `organizer_id` is set automatically from the authenticated user.

> **Note:** `price` is consumed to set each ticket's `price` and is **not** echoed back on the event object. The response below has no `price` field — confirm pricing via the event's tickets/orders.

**Response `201 Created`** — the created `Event` object. Includes `cover_image_path` / `cover_image_url`, but **not** the nested `city` object and **not** `available_tickets`.
```json
{
  "id": 2,
  "title": "Summer Music Festival",
  "description": "Three days of live music across five stages.",
  "total_tickets": 500,
  "city_id": 7,
  "sale_starts_at": "2026-07-01T09:00:00.000000Z",
  "event_starts_at": "2026-08-15T18:00:00.000000Z",
  "cover_image_path": "events/abc123.webp",
  "cover_image_url": "http://localhost/storage/events/abc123.webp",
  "organizer_id": 1,
  "created_at": "2026-06-07T10:00:00.000000Z",
  "updated_at": "2026-06-07T10:00:00.000000Z"
}
```

### Update an event — `PUT /events/{event}` or `PATCH /events/{event}` 🔒 *(owning organizer only)*

**⚠️ Content type: `multipart/form-data`** (same as create). All fields are optional (`sometimes`) — only send what you want to change.

> **Tip:** HTML/`fetch` `FormData` only sends as a real `POST`. To update with a file via `multipart/form-data`, POST to the update URL with a `_method=PUT` (or `PATCH`) field, e.g. `POST /events/{event}` with `_method=PUT` in the FormData body (Laravel method spoofing).

| Field | Type | Rules |
|---|---|---|
| `title` | string | sometimes required, unique in `events` (ignoring current event) |
| `description` | string | sometimes/nullable, max 65535 characters |
| `total_tickets` | integer | sometimes required, min 1 |
| `city_id` | integer | sometimes required, must exist in `cities` |
| `sale_starts_at` | date/datetime | sometimes required |
| `event_starts_at` | date/datetime | sometimes required |
| `cover_image` | file | sometimes — image, mime `jpeg`/`png`/`webp`, max 2048 KB. Uploading a new image deletes the previous one. |

> **Heads-up:** `price` is **not** updatable here — ticket prices are fixed at event creation. Don't send `price` to the update endpoint (it is ignored).

Authorization: `Gate::authorize('update', $event)` — only the organizer that owns the event.

**Response `200 OK`** — the updated `Event` object (same shape as the create response).

**Error `403 Forbidden`** — not the owning organizer:
```json
{ "message": "This action is unauthorized." }
```

### Delete an event — `DELETE /events/{event}` 🔒 *(owning organizer only)*

Authorization: `Gate::authorize('delete', $event)`. The stored cover image (if any) is deleted along with the event.

**Response `204 No Content`** — empty body.

---

## 3a. Cities & Countries

Events reference a **city** via `city_id`, and each city belongs to a **country**. The data shapes are:

```json
// Country
{ "id": 3, "name": "Spain", "iso_code": "ES", "created_at": "...", "updated_at": "..." }

// City
{ "id": 7, "country_id": 3, "name": "Barcelona", "created_at": "...", "updated_at": "..." }
```

### List countries — `GET /countries` *(public)*

**Response `200 OK`** — array of all countries, ordered by `name`:
```json
[
  { "id": 1, "name": "Andorra", "iso_code": "AD", "created_at": "...", "updated_at": "..." },
  { "id": 3, "name": "Spain", "iso_code": "ES", "created_at": "...", "updated_at": "..." }
]
```

`iso_code` is the country's ISO 3166-1 alpha-2 code (2 uppercase letters, unique).

### List cities — `GET /cities` *(public)*

Optional query param `country_id` filters cities to a single country — use it to populate a city dropdown after the user picks a country.

**Request** — `GET /cities?country_id=3`

**Response `200 OK`** — array of cities, ordered by `name`. Returns all cities when `country_id` is omitted:
```json
[
  { "id": 12, "name": "Barcelona", "country_id": 3, "created_at": "...", "updated_at": "..." },
  { "id": 7, "name": "Madrid", "country_id": 3, "created_at": "...", "updated_at": "..." }
]
```

> **City picker flow:** call `GET /countries` to fill the country selector, then `GET /cities?country_id={id}` to fill the city selector, and submit the chosen `city_id` when creating/updating an event.

---

## 4. Orders

> All order routes require authentication and are intended for **regular users** (organizers are blocked by the `OrderPolicy`).

### List my orders — `GET /orders` 🔒

**Response `200 OK`** — array of the authenticated user's orders, each with `ticket` and `ticket.event` eager-loaded:
```json
[
  {
    "id": 10,
    "user_id": 1,
    "ticket_id": 55,
    "amount": 4999,
    "status": "pending",
    "expires_at": "2026-06-07T10:15:00.000000Z",
    "created_at": "2026-06-07T10:00:00.000000Z",
    "updated_at": "2026-06-07T10:00:00.000000Z",
    "ticket": {
      "id": 55,
      "event_id": 2,
      "status": "reserved",
      "price": 4999,
      "created_at": "...",
      "updated_at": "...",
      "event": {
        "id": 2,
        "title": "Summer Music Festival",
        "description": "Three days of live music across five stages.",
        "total_tickets": 500,
        "organizer_id": 1,
        "city_id": 7,
        "sale_starts_at": "2026-07-01T09:00:00.000000Z",
        "event_starts_at": "2026-08-15T18:00:00.000000Z",
        "cover_image_path": "events/abc123.webp",
        "cover_image_url": "http://localhost/storage/events/abc123.webp",
        "created_at": "...",
        "updated_at": "..."
      }
    }
  }
]
```

`status` is one of: `pending`, `confirmed`, `cancelled`, `expired`.
`ticket.status` is one of: `available`, `reserved`, `sold`.
`amount` (on the order) and `ticket.price` are integers **in cents** — `amount` is snapshotted from the ticket's `price` at reservation time, so they match. Divide by 100 for display.

### Get a single order — `GET /orders/{order}` 🔒

Authorization: `Gate::authorize('view', $order)` — only the owning user.

**Response `200 OK`** — same shape as a single item above (with `ticket.event` loaded).

**Error `403 Forbidden`** — not the owning user:
```json
{ "message": "This action is unauthorized." }
```

### Create an order (reserve a ticket) — `POST /orders` 🔒

**Request body**
```json
{ "event_id": 2 }
```
Validation: `event_id` required integer, must exist in `events`.
Authorization: `Gate::authorize('create', Order::class)` — caller must be a regular user, not an organizer.

**Response `201 Created`** — the newly created `Order` (status `pending`, with an `expires_at` reservation deadline). `amount` (in cents) is taken from the reserved ticket's `price`:
```json
{
  "id": 11,
  "user_id": 1,
  "ticket_id": 56,
  "amount": 4999,
  "status": "pending",
  "expires_at": "2026-06-07T10:15:00.000000Z",
  "created_at": "2026-06-07T10:00:00.000000Z",
  "updated_at": "2026-06-07T10:00:00.000000Z"
}
```

**Error `403 Forbidden`** — ticket sales haven't started yet:
```json
{ "error": "Ticket sales for event 2 have not started yet." }
```

**Error `409 Conflict`** — no tickets available:
```json
{ "error": "No available tickets for event 2." }
```

### Pay for an order — `POST /orders/{order}/pay` 🔒

**Request body**
```json
{ "payment_method": "credit_card" }
```
Validation: `payment_method` required string, must be one of `credit_card` or `paypal`.
Authorization: `Gate::authorize('update', $order)` — only the owning user.

**Response `200 OK`**
```json
{
  "message": "Order processed successfully",
  "data": {
    "order_id": 11,
    "status": "confirmed",
    "payment_method": "credit_card",
    "updated_at": "2026-06-07T10:05:00.000000Z"
  }
}
```

**Error `409 Conflict`** — order is not in `pending` status:
```json
{ "error": "Order 11 is not in pending status." }
```

**Error `410 Gone`** — order's reservation window has expired (order auto-cancelled):
```json
{ "error": "Order 11 has expired and has been cancelled." }
```

---

## 5. Images (Uploads & Serving)

This section consolidates everything the frontend needs to know about images. Right now the **only** image in the system is an **event cover image**; the same rules will apply if more image fields are added later.

### What the backend expects on upload

- Images are uploaded as **`multipart/form-data`**, never as JSON or base64. Send a real file in a `FormData` body and let the browser set the `Content-Type`/boundary — do **not** manually set `Content-Type: application/json`.
- The cover image is sent under the form field name **`cover_image`** (this is the *input* field; it is different from the stored value, see below).
- Validation rules applied by the backend:

| Rule | Value |
|---|---|
| Optional? | Yes — the field is `nullable`/`sometimes`; an event can exist with no image |
| Must be an image | Yes (`image` rule) |
| Allowed formats | `jpeg`, `png`, `webp` |
| Max size | **2048 KB (2 MB)** |

If validation fails, the backend returns **`422 Unprocessable Entity`** with the error keyed under `cover_image`:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "cover_image": ["The cover image field must be an image."]
  }
}
```

### How the backend stores & handles images

- On a successful upload the file is stored on the **`public` disk** inside the **`events/`** directory, with an auto-generated unique filename (e.g. `events/9aZ3...webp`).
- The **relative storage path** is persisted on the event as **`cover_image_path`** (e.g. `"events/9aZ3...webp"`).
- The backend also exposes a computed, read-only **`cover_image_url`** attribute — the **absolute, publicly accessible URL** built from that path (`{APP_URL}/storage/{cover_image_path}`). Use this field directly as the `src` of an `<img>`.
- When an event is **updated with a new `cover_image`**, the previous file is **deleted** automatically before the new one is stored.
- When an event is **deleted**, its stored image file is **deleted** too.
- If no image was ever uploaded, both `cover_image_path` and `cover_image_url` are **`null`**.

### Fields summary

| Field | Direction | Type | Meaning |
|---|---|---|---|
| `cover_image` | **request only** (write) | file (multipart) | The image file you upload when creating/updating an event |
| `cover_image_path` | response (read) | string \| null | Relative path on the server's storage disk |
| `cover_image_url` | response (read) | string \| null | Absolute URL to render the image — **use this one in the UI** |

### Displaying an image

Always render `cover_image_url` and guard against `null`:
```html
<img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title" />
```

### Uploading from the frontend (example)

```ts
const form = new FormData()
form.append('title', title)
form.append('total_tickets', String(totalTickets))
form.append('price', String(priceInCents)) // integer in cents, e.g. 4999
form.append('city_id', String(cityId))
if (description) {
  form.append('description', description)
}
form.append('sale_starts_at', saleStartsAt)
form.append('event_starts_at', eventStartsAt)
if (file) {
  form.append('cover_image', file) // a File from <input type="file">
}

await fetch('http://localhost:8000/api/events', {
  method: 'POST',
  headers: {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
    // ⚠️ Do NOT set Content-Type — the browser sets it for FormData
  },
  body: form,
})
```

For **updating** an event with a new image, browsers can't send a real `PUT`/`PATCH` with `FormData`, so use Laravel method spoofing — POST to the update URL and add a `_method` field:
```ts
form.append('_method', 'PUT')
await fetch(`http://localhost:8000/api/events/${eventId}`, {
  method: 'POST',
  headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
  body: form,
})
```

> **Backend note:** serving images requires the storage symlink (`php artisan storage:link`). If `cover_image_url` returns a 404, that symlink is missing on the server — flag it to the backend team.

---

## Standard Error Responses

### `401 Unauthorized` — missing/invalid token
```json
{ "message": "Unauthenticated." }
```

### `403 Forbidden` — authenticated but not authorized (wrong token ability or failed policy/gate check)
```json
{ "message": "This action is unauthorized." }
```

### `404 Not Found` — route-model binding failed (resource doesn't exist)
```json
{ "message": "No query results for model [App\\Models\\Event] {id}" }
```

### `422 Unprocessable Entity` — validation failure (`FormRequest` rules)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field must be at least 8 characters."]
  }
}
```

### `500 Internal Server Error`
```json
{ "message": "Server Error" }
```
