# Class: Wf\DockerWorkflowBundle\Skeleton\FileType\MakefileSkeletonFile
# HandleExisting: 0
PROJECT_WORKING_DIRECTORY := $${PWD}
WF_TARGET_DIRECTORY := .wf
# Export the debug value
export WF_DEBUG

# Makefiles
include test1/makefile \
        test2/makefile

# Docker files
DOCKER_CONFIG_FILES := $(PROJECT_WORKING_DIRECTORY)/test2/docker-compose.yml

ORIGINAL_CMD_DOCKER_ENV := $(CMD_DOCKER_ENV)
define CMD_DOCKER_ENV
    $(ORIGINAL_CMD_DOCKER_ENV) \
    WF_TARGET_DIRECTORY=$(WF_TARGET_DIRECTORY)
endef

define CMD_DOCKER_BASE
    $(CMD_DOCKER_ENV) $(FILE_ENVS) $(COMMAND_ENVS) docker-compose \
        -p $(DOCKER_BASENAME) \
        $(foreach file,$(DOCKER_CONFIG_FILES),-f $(file)) \
        --project-directory $(CURDIR)
endef
# Added environments variables too!
#   - $(foreach env,$(FILE_ENVS),-e $(env))     From .wf.env file
#   - $(foreach env,$(COMMAND_ENVS),-e $(env))  From wf command: wf -e KEY1=VALUE1 -e KEY2=VALUE2 [...]
define CMD_DOCKER_RUN
    DOCKER_RUN=1 $(CMD_DOCKER_BASE) run --rm $(DOCKER_PSEUDO_TTY) $(foreach env,$(FILE_ENVS),-e $(env)) $(foreach env,$(COMMAND_ENVS),-e $(env))
endef
# If you want to run without user (as root), use the: `$(CMD_DOCKER_RUN) $(DOCKER_CLI_NAME) <cmd>` instead of `$(CMD_DOCKER_RUN_CLI) <cmd>`
define CMD_DOCKER_RUN_CLI
    $(CMD_DOCKER_RUN) $(DOCKER_CLI_NAME)
endef
# Added environments variables too!
#   - $(foreach env,$(FILE_ENVS),-e $(env))     From .wf.env file
#   - $(foreach env,$(COMMAND_ENVS),-e $(env))  From wf command: wf -e KEY1=VALUE1 -e KEY2=VALUE2 [...]
define CMD_DOCKER_EXEC
    $(CMD_DOCKER_BASE) exec $(DOCKER_PSEUDO_TTY) $(foreach env,$(FILE_ENVS),-e $(env)) $(foreach env,$(COMMAND_ENVS),-e $(env))
endef
# If you want to run without user (as root), use the: `$(CMD_DOCKER_EXEC) $(DOCKER_CLI_NAME) <cmd>` instead of `$(CMD_DOCKER_EXEC_CLI) <cmd>`
define CMD_DOCKER_EXEC_CLI
    $(CMD_DOCKER_EXEC) --user $(DOCKER_USER) $(DOCKER_CLI_NAME)
endef
