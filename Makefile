.PHONY: generate autoload verify-generated test build check

generate:
	./scripts/generate-parser.sh

autoload:
	composer dump-autoload

verify-generated:
	./scripts/check-generated.sh

test: verify-generated
	./vendor/bin/phpunit

build: generate autoload

check: build test
