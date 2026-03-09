#!/bin/sh

set -e

echo "Setting permissions..."
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "Waiting for rabbitmq to be ready..."
until nc -zv rabbitmq 5672; do
    echo "RabbitMQ is not ready yet..."
    sleep 1
done
echo "RabbitMQ is ready!"

echo "Declaring RabbitMQ exchange and queue..."
php artisan rabbitmq:exchange-declare hr.events rabbitmq --type=topic || true
php artisan rabbitmq:queue-declare hub.events rabbitmq || true
php artisan rabbitmq:queue-bind hub.events hr.events rabbitmq --routing-key='employees.#' || true

echo "Starting queue worker..."
exec php artisan rabbitmq:consume --verbose --tries=3
