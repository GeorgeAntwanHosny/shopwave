.PHONY: up down dev-db fresh logs

## Full stack: postgres, redis, api, queue, reverb, web
up:
	docker compose up --build

down:
	docker compose down

## Just the data layer, for native dev against Postgres/Redis in containers
dev-db:
	docker compose --profile dev-db up -d

## Nukes all volumes (Postgres data, Redis data, the migration sentinel)
## and rebuilds from scratch — the real "start completely over" command.
fresh:
	docker compose down -v
	docker compose up --build

logs:
	docker compose logs -f