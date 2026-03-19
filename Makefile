# Variables
DC_DEV = docker compose -f docker-compose.dev.yml --env-file .env.dev
DC_PROD = docker compose -f docker-compose.yml --env-file .env
APP_DEV = organisasi_app_dev
APP_PROD = organisasi_app_prod

# Fetch current user UID and GID to prevent permission issues
export USER_ID := $(shell id -u)
export GROUP_ID := $(shell id -g)

.PHONY: help dev-up dev-down dev-restart dev-build dev-logs dev-ps dev-shell artisan composer npm migrate seed fresh \
        prod-up prod-down prod-restart prod-build prod-logs prod-ps prod-shell prod-migrate prod-optimize prod-perms

help: ## Menampilkan bantuan
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# --- ENVIRONMENT DEVELOPMENT ---
dev-up: ## Menjalankan environment dev (background)
	$(DC_DEV) up -d

dev-down: ## Menghentikan environment dev
	$(DC_DEV) down

dev-restart: ## Restart environment dev
	$(DC_DEV) down && $(DC_DEV) up -d

dev-build: ## Build ulang image docker dev
	$(DC_DEV) up -d --build

dev-logs: ## Melihat log semua kontainer dev
	$(DC_DEV) logs -f

dev-ps: ## Melihat status kontainer dev
	$(DC_DEV) ps

dev-shell: ## Masuk ke dalam terminal kontainer app dev
	$(DC_DEV) exec app bash

# --- ENVIRONMENT PRODUCTION ---
prod-up: ## Menjalankan environment prod (background)
	$(DC_PROD) up -d

prod-down: ## Menghentikan environment prod
	$(DC_PROD) down

prod-restart: ## Restart environment prod
	$(DC_PROD) down && $(DC_PROD) up -d

prod-build: ## Build ulang image docker prod dengan UID/GID user saat ini
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) $(DC_PROD) build --no-cache
	$(DC_PROD) up -d

prod-logs: ## Melihat log semua kontainer prod
	$(DC_PROD) logs -f

prod-ps: ## Melihat status kontainer prod
	$(DC_PROD) ps

prod-shell: ## Masuk ke dalam terminal kontainer app prod
	$(DC_PROD) exec app bash

prod-migrate: ## Menjalankan migrasi database prod
	$(DC_PROD) exec app php artisan migrate --force

prod-build-assets: ## Build frontend assets (NPM) di dalam container prod
	$(DC_PROD) exec app npm install
	$(DC_PROD) exec app npm run build

prod-optimize: ## Jalankan optimasi Laravel untuk production
	$(DC_PROD) exec app php artisan optimize
	$(DC_PROD) exec app php artisan view:cache
	$(DC_PROD) exec app php artisan config:cache

prod-perms: ## Set permission untuk folder storage & bootstrap/cache
	sudo chown -R $(USER_ID):$(GROUP_ID) backend/storage backend/bootstrap/cache
	chmod -R 775 backend/storage backend/bootstrap/cache

# --- TOOLS & UTILITIES ---
artisan: ## Menjalankan php artisan (contoh: make artisan c="migrate")
	$(DC_DEV) exec app php artisan $(c)

composer: ## Menjalankan composer (contoh: make composer c="install")
	$(DC_DEV) exec app composer $(c)

npm: ## Menjalankan npm (contoh: make npm c="install")
	$(DC_DEV) exec app npm $(c)

migrate: ## Menjalankan migrasi database dev
	$(DC_DEV) exec app php artisan migrate

seed: ## Menjalankan seeder database dev
	$(DC_DEV) exec app php artisan db:seed

fresh: ## Reset database dan migrasi ulang + seed
	$(DC_DEV) exec app php artisan migrate:fresh --seed

tinker: ## Masuk ke Laravel Tinker
	$(DC_DEV) exec app php artisan tinker
