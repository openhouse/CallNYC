# CallNYC (Archived Demo)

## Local development (Docker Compose)

1. Copy env defaults (optional):
   ```sh
   cp .env.example .env
   ```
2. Build + start the stack:
   ```sh
   docker compose up --build
   ```
3. Open the app at: http://localhost:8080

Docker Compose mounts the repo into the container so edits are reflected on refresh.

## Seed data (deterministic)

The seed script builds the schema and loads `data/sample.csv`.

```sh
docker compose exec web php bin/seed.php
```

This script is idempotent and safe to re-run.

## Archive mode

Set `CALLNYC_ARCHIVED=1` to disable mutating endpoints like `getCSV.php`.
This is enabled by default in `docker-compose.yml` and recommended for production.

## Dokku deploy checklist

- Create app and MySQL service:
  ```sh
  dokku apps:create callnyc
  dokku mysql:create callnyc-db
  dokku mysql:link callnyc-db callnyc
  ```
- Ensure config vars (example):
  ```sh
  dokku config:set callnyc CALLNYC_ARCHIVED=1
  ```
- Dokku ports:
  - Dockerfile listens on `5000` (Dokku default). If using a different port, set:
    ```sh
    dokku proxy:ports-set callnyc http:80:5000
    ```
- Enable HTTPS (Let’s Encrypt plugin):
  ```sh
  dokku letsencrypt:enable callnyc
  ```

## Environment variables

- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_PORT`
- `DATABASE_URL` (optional, overrides DB_* when set)
- `CALLNYC_ARCHIVED=1` to harden legacy write endpoints
