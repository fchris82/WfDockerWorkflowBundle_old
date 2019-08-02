.PHONY: test csfix shell rootshell

test:
	CURRENT_UID=$$(id -u):$$(stat -c '%g' /var/run/docker.sock) docker-compose run --rm test

csfix:
	CURRENT_UID=$$(id -u):$$(stat -c '%g' /var/run/docker.sock) docker-compose run --rm test php vendor/bin/php-cs-fixer fix

shell:
	CURRENT_UID=$$(id -u):$$(stat -c '%g' /var/run/docker.sock) docker-compose run --rm shell

rootshell:
	CURRENT_UID=$$(id -u root):$$(id -g root) docker-compose run --rm shell
