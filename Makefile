# Variables
DC_DEV = docker compose -f docker-compose.dev.yml --env-file .env.dev
APP_DEV = organisasi_app_dev

.PHONY: help dev-up dev-down dev-restart dev-build dev-logs dev-ps dev-shell artisan composer npm migrate seed fresh

help: ## Menampilkan bantuan
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

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

# Laravel & Tools
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
