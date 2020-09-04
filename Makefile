.DEFAULT_GOAL := help

.PHONY: help
help: ## Справка по командам
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: build
build: prebuild migrate migrate-test ## Билд приложения

prebuild:
	@docker-compose up -d
	@docker-compose exec php composer install

.PHONY: run-tests
run-tests: ## Запуск юнит-тестов
	@docker-compose exec php bin/phpunit

.PHONY: import-movies
import-movies: ## Импорт фильмов
	@docker-compose exec php bin/console app:fetch:trailers

.PHONY: migrate
migrate: ## Выполнение миграций БД
	@docker-compose exec php bin/console phinx:migrate

.PHONY: rollback
rollback: ## Откат последней миграции БД
	@docker-compose exec php bin/console phinx:rollback

.PHONY: migrate-test
migrate-test: ## Выполнение миграций тестовой БД
	@docker-compose exec php bin/console phinx:migrate -e test

.PHONY: rollback-test
rollback-test: ## Откат последней миграции тестовой БД
	@docker-compose exec php bin/console phinx:rollback -e test
