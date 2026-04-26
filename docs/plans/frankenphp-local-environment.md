# FrankenPHP Local Dev Environment

## Context

The project previously used `composer run dev` to start four concurrent host processes (`php artisan serve`, queue listener, Pail log viewer, Vite). This was replaced with FrankenPHP running inside Docker (standard mode, HTTP only), with Vite in a companion container for HMR. `docker compose up` is now the single entry point.

---

## Architecture

Two Docker Compose services:

| Service | Image | Purpose |
|---------|-------|---------|
| `app` | Custom (FrankenPHP PHP 8.5) | Serves PHP via FrankenPHP standard mode on port 8000 |
| `vite` | `node:22-alpine` | Runs `npm run dev` with HMR on port 5173 |

Both services mount the project root so changes on the host are reflected immediately. No database container — SQLite was removed; add a DB service later when needed.

---

## Files Created

### `Dockerfile`

```dockerfile
FROM dunglas/frankenphp:latest-php8.5

WORKDIR /app

# PHP extensions required by Laravel
RUN install-php-extensions \
    mbstring \
    xml \
    ctype \
    fileinfo \
    openssl \
    zip \
    bcmath \
    pcntl \
    opcache

# Pinned to a specific digest to prevent silent breakage on future image updates.
# To refresh: docker pull composer:2 && docker inspect composer:2 --format '{{index .RepoDigests 0}}'
COPY --from=composer@sha256:dc292c5c0f95f526b051d4c341bf08e7e2b18504c74625e3203d7f123050e318 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --optimize-autoloader

COPY .. .

RUN php artisan storage:link --no-interaction || true

EXPOSE 80
```

### `docker-compose.yml`

```yaml
services:
  app:
    build: .
    ports:
      - "8000:80"
    volumes:
      - .:/app
      - /app/vendor       # preserve container's vendor/ from the build
    environment:
      - APP_ENV=local
      - APP_URL=http://localhost:8000
    depends_on:
      - vite

  vite:
    image: node:22-alpine
    working_dir: /app
    volumes:
      - .:/app
      - vite_node_modules:/app/node_modules  # musl-compiled binaries, isolated from host
    ports:
      - "5173:5173"
    command: sh -c "npm install && npm run dev -- --host"

volumes:
  vite_node_modules:
```

> **Why the named volume?** `node:22-alpine` uses musl libc, but host `node_modules` contain glibc native bindings (e.g. rolldown). The named volume lets the container run its own `npm install` with the correct musl binaries, keeping them isolated from the host.

### `Caddyfile`

```caddyfile
{
	admin off
	frankenphp
}

:80 {
	root * /app/public
	encode zstd br gzip
	php_server
}
```

### `.dockerignore`

```
node_modules
vendor
.git
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
public/hot
public/build
```

---

## Files Modified

### `vite.config.js`

Added Docker HMR config so the Vite container is reachable from the host browser:

```js
server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    hmr: {
        host: 'localhost',
        port: 5173,
    },
    watch: {
        ignored: ['**/storage/framework/views/**'],
    },
},
```

---

## Day-to-Day Workflow

```bash
# Start everything (first run builds the image)
docker compose up --build

# Subsequent starts (no rebuild needed)
docker compose up

# Rebuild after adding PHP dependencies
docker compose build app

# Run Artisan commands inside the container
docker compose exec app php artisan migrate

# Run tests
docker compose exec app php artisan test --compact

# Stop
docker compose down
```

---

## Verification

1. `docker compose up --build` — both services start without errors.
2. Visit `http://localhost:8000` — Laravel welcome page loads with styles (Vite-compiled assets served via HMR).
3. Edit a Blade/CSS/JS file on the host — browser hot-reloads without a full page refresh.
4. `docker compose exec app php artisan test --compact` — all Pest tests pass.
5. `docker compose exec app php artisan --version` — confirms PHP 8.5 and Laravel are running correctly.
