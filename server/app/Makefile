# TO BE USED FROM INSIDE THE CONTAINER

internal_reset_db_command:
	bin/console doctrine:database:drop --force --env=${ENV} | true \
	&& bin/console doctrine:database:create --env=${ENV}

internal_migrate_db_command:
	bin/console doctrine:migration:migrate -n --env=${ENV}

internal_load_fixtures_command:
	bin/console doctrine:fixtures:load --append --env=${ENV}
