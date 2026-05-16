# Chat App — Agent Guide

## Stack

Laravel 12 + SQLite + Reverb (WebSocket) + Tailwind CSS 4 + Alpine.js + Vite.  
SPA chat UI rendered via Blade + Alpine, real-time via Laravel Echo.

## Setup

```bash
composer setup              # install + .env + key + migrate + npm build
```

Dev servers (runs all four concurrently):

```bash
composer dev                # artisan serve + queue:listen + pail + npm run dev
```

Start WebSocket separately:

```bash
php artisan reverb:start    # required for real-time broadcasting
```

## Test

```bash
composer test               # config:clear + artisan test (PHPUnit via Pest)
php artisan test --filter=RoomController
```

Tests use SQLite in-memory (see `phpunit.xml`). No external services needed.  
Unit tests in `tests/Unit/`, feature tests in `tests/Feature/`.

## Lint / Format

```bash
vendor/bin/pint             # Laravel Pint (PSR-12)
```

## Architecture

| Layer | Location |
|---|---|
| Routes | `routes/web.php` (auth + chat), `routes/channels.php` (broadcast auth) |
| Models | `app/Models/` — User, Room (group/private), Message, RoomUser (pivot) |
| Controllers | `app/Http/Controllers/` — Auth, Room, Message |
| Events | `app/Events/` — MessageSent (PresenceChannel), UserPresenceUpdated (Channel) |
| Policy | `app/Policies/RoomPolicy.php` — only `view` is allowed (checks room membership) |
| Frontend | `resources/views/chat/index.blade.php` (SPA), `resources/js/app.js` (Alpine) |
| Broadcasting | `resources/js/echo.js` — Reverb via Pusher protocol; uses `VITE_REVERB_*` env vars |

## Broadcasting Flow

- `MessageSent` → `PresenceChannel('room.{roomId}')` — broadcast to others only (`->toOthers()`)
- `UserPresenceUpdated` → `Channel('presence')` — sent on login/logout
- Channels authorized in `routes/channels.php`: user must be room member

## Known Issues

- `app/Http/Controllers/MessageController.php:5` — `use App\events\MessageSent` should be `App\Events\MessageSent` (wrong case). Fix before adding new event imports.
- `MessageController::send()` loads all room users via relationship (`$room->users->contains(...)`) on every request; prefer a DB query for high-traffic.

## Env

Copy `.env.example` → `.env`, generate key (`php artisan key:generate`).  
Default DB is SQLite (`database/database.sqlite`, already committed).  
Session, cache, queue all default to `database` driver.
