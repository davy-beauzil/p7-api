.PHONY: rector
rector: vendor
	vendor/bin/rector process --ansi --dry-run --xdebug --config=./config/lib/rector.php

.PHONY: phpstan
phpstan: vendor
	vendor/bin/phpstan analyse --configuration=./config/lib/phpstan.neon

.PHONY: ecs
ecs: vendor
	vendor/bin/ecs --fix --config=./config/lib/ecs.php

.PHONY: static-analyze
static-analyze: vendor
	make phpstan && make rector && make ecs