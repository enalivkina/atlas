Учебный фреймворк atlas
============================
Индекс пакета: ATLAS

## Описание

Фреймворк для разработки backend-приложений (веб/консоль) в курсе внутреннего обучения ЭФКО. Является платформой для изучения базового поведения приложения созданного на PHP. Фреймворк не является production-ready реализацией и не предназначен для коммерческого использования.

## Окружение

* PHP ^8.5
* psr/http-message ^2.0
* psr/http-factory ^1.1
* psr/container ^2.0

### Переменные окружения

```
#Режим окружения production/development/testing
ENV_MODE=development
APP_ENV=dev
APP_DEBUG=1
#Регистр
INDEX_NAME=ATLAS
# Имя проекта для docker compose
DOCKER_PROJECT=atlas
# Хранилище контейнеров
DOCKER_REGISTRY=localhost
# Версия контейнеров
DOCKER_IMAGE_VERSION=latest
#Имя образа для php-fpm
DOCKER_PHP_IMAGE_NAME=atlas-php
```

## Установка

1. устанавливаем пакет `composer require framework-education/atlas`

2. копируем `.env.dist` из директории фреймворка в `.env` корневой директории вашего проекта

3. настраиваем переменные окружения `.env`