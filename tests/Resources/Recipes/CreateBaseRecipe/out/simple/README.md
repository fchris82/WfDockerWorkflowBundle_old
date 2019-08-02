# Class: Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile
# HandleExisting: 0
Docker Workflow
===============

This is an auto generated directory. Please don't edit these files and don't commit to version control system!

## Base

> You have to add `.wf` and `.wf.yml` to the global `.gitignore` file!

The program try to find the `.wf.yml`. If it doesn't exist it will check the `.wf.yml.dist`.

## Configure: `.wf.yml`

There are lot of recipes with a lot of different configuration options. For help to you there is a command which can list all available options:

    wizard --config-dump

If you want to create a custom init file:

    wizard --config-dump > .wf.yml.dist

> Use the `.wf.yml.dist` by default! You may to put into version control repository. If somebedy want to use its own config, it can create a copy with `.wf.yml` name.

## Commands

### Always available "commands" (arguments)

> **Except the `--help` they work only in a project directory which contains config file!**

| Command | Description |
|:------- |:----------- |
| `wf --help` | You can list a simple help |
| `wf help` | You can list a **project** help |
| `wf list` | List the available project commands! |
| `wf info` | Show basic informations about the project! |
| `wf reconfigure` | It can rebuild the "framework" from config yml. |

Docker (compose) commands:

| Command | Description |
|:------- |:----------- |
| `wf debug-docker-config` | Show the current "combined" `docker-compose.yml` file |
| `wf docker-compose <args>` | Universal ~`docker-compose` command |
| `wf logs <service>` | ~`docker-compose logs` |
| `wf up` | ~`docker-compose up` |
| `wf down` | ~`docker-compose down` |
| `wf restart` | `down` + `up` |
| `wf enter [<service>]` | Open a `bash` with **your own** user. The default service is the `$(DOCKER_CLI_NAME)` makefile variable. It should be set by a recipe! |
| `wf debug-enter [<service>]` | Open a `bash` with **root** user. The default service is the `$(DOCKER_CLI_NAME)` makefile variable. It should be set by a recipe! |
| `wf exec <args>` | ~`docker-compose exec` |
| `wf run <args>` | ~`docker-compose run` (as **current user**!) |
| `wf sudo-run <args>` | ~`docker-compose run` (as **root**)|
| `wf rebuild` | Rebuild/refresh the containers |
| `wf ps` | ~`docker-compose ps` |


### Project/Recipe "commands"

> Use the `wf list` command to show all available commands.

You can debug a project command with the `-v` and `-vvv` argument:

    wf -v [command] [command-args]

 Eg:

    wf -v sf doctrine:migrations:migrate -vvv
       ^^                                ^^^^
       Debug mode                        Symfony command verbose! Not this!

### Custom commands

You can define custom bash commands in the `commands` place:

```yaml
commands:
    # single lines
    init:
        # Make a copy from dist file for the user. If file exists then it will be unchanged!
        - cp -n .wf.yml.dist .wf.yml
        - echo "Finish!"

    # Multiple lines
    install: |
        wf composer install && \
        echo "Installed!"
```

> The program will generate `.sh` files and call them.

## Environments (`.wf.env`)

You can create an `env` file to all wf container. The autloaded env file name: .wf.env. There are some limitations: you can't use special characters (like UTF-8).

## Recpies

You can list the all configuration option:

    wizard --config-dump

### Extend recpies

You have two ways: create a custom `docker-compose.yml` file or create a docker compose configuration in `.wf.yml`.
The program use the docker componse inheritance function. Use the `wf debug-docker-config` command to discover the current settings.

> **Warning!** If you use a custom configuration file, look at the correct Docker Compose API version!
> You can change it in the `config.docker_compose.version` configuration.
>
> The "inline" configuration solution handles this problem.

#### "Inline" configuration example \[recommended\]

```yaml
# .wf.yml or .wf.yml.dist
# [...]
docker_compose:
    extension:
        # The docker-config syntax starts here
        services:
            web:
                environment:
                    TEST: test
            my_own:
                image: my_own/service
```

#### Custom file example

```yaml
# Your own file: .docker/docker-compose.extra.yml
version:

services:
    web:
        environment:
            TEST: test
    my_own:
        image: my_own/service
```

```yaml
# .wf.yml or .wf.yml.dist
# [...]
docker_compose:
    include:
        - '.docker/docker-compose.extra.yml'
```

#### Mix

You can mix the different solutions.

```yaml
# .wf.yml or .wf.yml.dist
# [...]
docker_compose:
    include:
        - '.docker/docker-compose.extra.yml'
        - '~/.docker/docker-compose.my.yml'
    extension:
        # The docker-config syntax starts here
        services:
            web:
                # Override the original image
                image: new_image/service
                environment:
                    TEST: test
                    FOO:  bar
            my_own:
                image: my_own/service
```

### Create a Recipe

