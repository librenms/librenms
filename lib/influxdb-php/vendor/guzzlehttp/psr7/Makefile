all: clean test

test:
	vendor/bin/phpunit $(TEST)

coverage:
	vendor/bin/phpunit --coverage-html=artifacts/coverage $(TEST)

view-coverage:
	open artifacts/coverage/index.html

clean:
	rm -rf artifacts/*
