generate:
	./scripts/generate-parser.sh

autoload:
	composer dump-autoload

test:
	./vendor/bin/phpunit

build: generate autoload

check: build test