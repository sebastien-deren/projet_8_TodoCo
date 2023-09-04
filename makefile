#--SYMFONY--#
SYMFONY = symfony
SYMFONY_CONSOLE = $(SYMFONY) console
SYMFONY_SERVER_START = $(SYMFONY) serve -d
SYMFONY_SERVER_STOP = $(SYMFONY) server:stop
SYMFONY_PROJECT_OPEN = $(SYMFONY) open:local

#--DOCKER--#
DOCKER =docker
DOCKER_COMPOSE = $(DOCKER) compose
DOCKER_COMPOSE_START = $(DOCKER_COMPOSE) up -d
DOCKER_COMPOSE_STOP = $(DOCKER_COMPOSE) down

#--DOCTRINE--#
DOCTRINE= $(SYMFONY_CONSOLE) doctrine
DOCTRINE_MAKE_MIGRATION = $(SYMFONY_CONSOLE) make:migration
DOCTRINE_MIGRATE= $(DOCTRINE):migration:migrate -n
DOCTRINE_MAKE_DB=$(DOCTRINE):database:create --if-not-exists
DOCTRINE_FIXTURES = $(DOCTRINE):fixtures:load -n

#--COMPOSER--#
COMPOSER = composer
COMPOSER_INSTALL = $(COMPOSER) install


docker-up:
	$(DOCKER_COMPOSE_START)
.PHONY:docker-up

docker-down:
	$(DOCKER_COMPOSE_STOP)
.PHONY:docker-down

sf-start:
	$(SYMFONY_SERVER_START)
.PHONY: sf-start

sf-stop:
	$(SYMFONY_SERVER_STOP)
.PHONY:sf-stop

sf-open:
	$(SYMFONY_PROJECT_OPEN)
.PHONY:sf-open

composer-install:
	$(COMPOSER_INSTALL)
.PHONY:composer-install

composer-update:
	$(COMPOSER) update
.PHONY:composer-update

dmakedb:
	$(DOCTRINE_MAKE_DB)
.PHONY:dmakedb

dmakemigration:
	$(DOCTRINE_MAKE_MIGRATION)
.PHONY: dmakemigration

dmm:
	$(DOCTRINE_MIGRATE)
.PHONY: dmm

doctrine-fixture:
	$(DOCTRINE_FIXTURES)
.PHONY: doctrine-fixture

sleep:
	echo 'waiting for docker database !'
	sleep 5
.PHONY: sleep



start: sf-start docker-up sf-open
.PHONY:start

stop: sf-stop docker-down
.PHONY: stop

install: docker-up composer-install sf-start sleep dmakedb dmm doctrine-fixture  sf-open
.PHONY: install
