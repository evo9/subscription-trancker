.PHONY: install up down api-sh web-sh migrate fresh test stan pint

## First-run bootstrap: create Laravel into apps/api, build, start, install, migrate.
install:
	@test -f apps/api/artisan || docker run --rm -v "$(PWD)/apps:/app" -w /app composer:2 \
		create-project laravel/laravel api
	@test -f .env || cp .env.example .env
	docker compose build
	docker compose up -d
	docker compose exec -T api composer install
	docker compose exec -T api php artisan key:generate
	docker compose exec -T api php artisan migrate --seed
	@echo "Ready — app: http://localhost  |  API: http://localhost/api"

up:
	docker compose up -d

down:
	docker compose down

api-sh:
	docker compose exec api sh

web-sh:
	docker compose exec web sh

migrate:
	docker compose exec api php artisan migrate --seed

fresh:
	docker compose exec api php artisan migrate:fresh --seed

test:
	docker compose exec api php artisan test

stan:
	docker compose exec api ./vendor/bin/phpstan analyse

pint:
	docker compose exec api ./vendor/bin/pint
