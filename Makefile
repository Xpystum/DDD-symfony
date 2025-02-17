CONTAINER = @docker exec -it online-store_php-fpm
COMPOSE = @docker compose -f ./docker/docker-compose.yml

init: dc.build dc.up # Сделать полную инициализацию приложения
	php bin/console doctrine:migrations:migrate;
	php bin/console doctrine:database:create --env=test
	php bin/console lexik:jwt:generate-keypair

check: cs test

###< Composer ###
test: # Запуск тестов
	$(CONTAINER) composer test
cs: # Исправление стиля кода
	$(CONTAINER) composer cs
###</ Composer ###

###< Docker compose v2 ###
dc.ps:
	$(COMPOSE) ps
dc.logs:
	$(COMPOSE) logs

dc.restart: dc.down dc.up

dc.rebuild: dc.down dc.build dc.up

dc.build:
	$(COMPOSE) build

dc.start:
	$(COMPOSE) start
dc.stop:
	$(COMPOSE) stop

dc.up:
	$(COMPOSE) up -d --remove-orphans
dc.down:
	$(COMPOSE) down --remove-orphans

dc.drop:
	@echo "WARNING: Эта команда удалит все контейнеры и тома! Продолжить? (y/n)"
	@read answer && [ $$answer = y ] && docker compose down -v --remove-orphans || echo "Отмена."
dc.full_drop:
	@echo "WARNING: Эта команда удалит все контейнеры, образы и тома! Продолжить? (y/n)"
	@read answer && [ $$answer = y ] && docker compose down -v --rmi=all --remove-orphans || echo "Отмена."
###</ Docker compose ###
