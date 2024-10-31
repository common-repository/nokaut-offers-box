=== Composer update ===

    docker run --rm -it -v "$PWD":/app -w /app $(docker build --build-arg PHP_VERSION=7.2 -q .) /bin/bash -c "rm -rf ./vendor composer.lock && composer update"
