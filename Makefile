help:
	@echo "Please use \`make <target>' where <target> is one of"
	@echo "  test           to perform unit tests."
	@echo "  coverage       to perform unit tests with code coverage."
	@echo "  coverage-show  to show code coverage reports."
	@echo "  tag            to tag a new release."

coverage:
	vendor/bin/phpunit --coverage-html=build/coverage

coverage-show:
	open build/coverage/index.html

test:
	vendor/bin/phpunit && vendor/bin/phpunit --group performance

tag:
	$(if $(TAG),,$(error TAG is not defined. Pass via "make tag TAG=4.2.1"))
	@echo Tagging $(TAG)
	sed -i '' -e "s/VERSION = '.*'/VERSION = '$(TAG)'/" src/Application.php
	php -l src/Application.php
	git add src/Application.php
	git commit -m "$(TAG) release"
	git tag "$(TAG)"

.PHONY: coverage coverage-show