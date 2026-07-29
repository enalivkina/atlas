include .env

DOCKER_BEFORE_UP_ARGS=# не применимо в окружении production
DOCKER_AFTER_UP_ARGS=# не применимо в окружении production

up:
	@docker-compose -p ${DOCKER_PROJECT} \
	${DOCKER_BEFORE_UP_ARGS} up -d ${DOCKER_AFTER_UP_ARGS}

down:
	@docker-compose -p ${DOCKER_PROJECT} \
	${DOCKER_BEFORE_UP_ARGS} down --remove-orphans

restart: down up

install: \
	 down \
	 docker-build \
	 up

shell:
	@docker exec -ti atlas-php sh

docker-build: \
	docker-build-php

docker-build-php:
	@docker build --target=cli \
	--build-arg USER=1000 \
	--build-arg GROUP=1000 \
	-t ${DOCKER_REGISTRY}/${DOCKER_PHP_IMAGE_NAME}:${DOCKER_IMAGE_VERSION} -f ./.docker/Dockerfile .

docker-ps:
	@docker-compose -p ${DOCKER_PROJECT} ${DOCKER_BEFORE_UP_ARGS} ps

composer-install:
	docker compose run --rm php composer install

composer-require:
	docker compose run --rm php composer require $(pack)

fix-cs-fixer:
	docker compose run --rm php vendor/bin/php-cs-fixer fix --config=.csFixer/.php-cs-fixer.php

codecept-build:
	docker compose run --rm php vendor/bin/codecept build

test:
	docker compose run --rm php vendor/bin/codecept run

test-unit:
	docker compose run --rm php vendor/bin/codecept run Unit

test-functional:
	docker compose run --rm php vendor/bin/codecept run Functional