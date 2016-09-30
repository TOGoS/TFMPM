config_files := \
	config/ccouch-repos.lst \
	config/dbc.json \
	config/email-addresses.json \
	config/email-transport.json \
	www/.htaccess

generated_resources := \
	build/db/all-tables.sql \
	build/db/create-database.sql \
	build/db/drop-database.sql \
	util/phptemplateprojectdatabase-psql \
	util/phptemplateprojectdatabase-pg_dump \
	util/SchemaSchemaDemo.jar \
	schema/schema.php \
	vendor

# Include .git-object-urns.txt in the above list if
# you like deployments to take forever.

build_resources := ${generated_resources} ${config_files}

runtime_resources := \
	config/dbc.json \
	config/email-addresses.json \
	config/email-transport.json \
	schema/schema.php \
	vendor

resources := ${build_resources} ${runtime_resources}

schemaschemademo := java -jar util/SchemaSchemaDemo.jar

fetch := vendor/bin/fetch -repo @config/ccouch-repos.lst

default: redeploy

.DELETE_ON_ERROR:

.PHONY: \
	build-resources \
	clean \
	create-database \
	default \
	drop-database \
	everything \
	empty-database \
	realclean \
	rebuild-database \
	rebuild-database-with-test-data \
	redeploy \
	redeploy-without-upgrading-the-database \
	resources \
	runtime-resources \
	run-tests \
	run-unit-tests \
	run-web-server \
	test-db-connection \
	upgrade-database \
	upgrade-database-with-test-data

build-resources: ${build_resources}
runtime-resources: ${runtime_resources}
resources: ${resources}

clean:
	rm -rf ${generated_resources}
realclean:
	rm -rf ${generated_resources} ${config_files}

vendor: composer.lock
	composer install
	touch "$@"

${config_files}: %: | %.example
	cp "$|" "$@"

# If composer.lock doesn't exist at all,
# this will 'composer install' for the first time.
# After that, it's up to you to 'composer update' to get any
# package updates or apply changes to composer.json.
composer.lock: | composer.json
	composer install

util/phptemplateprojectdatabase-psql: config/dbc.json
	vendor/bin/generate-psql-script -psql-exe psql "$<" >"$@"
	chmod +x "$@"
util/phptemplateprojectdatabase-pg_dump: config/dbc.json
	vendor/bin/generate-psql-script -psql-exe pg_dump "$<" >"$@"
	chmod +x "$@"

util/SchemaSchemaDemo.jar: \
%: %.urn | vendor config/ccouch-repos.lst
	${fetch} -o "$@" `cat "$<"`

build/db/all-tables.sql: schema/schema.txt util/SchemaSchemaDemo.jar
	${schemaschemademo} -o-create-tables-script "$@" "$<"

build/db/rc-inserts.sql: schema/schema.php
	util/generate-rc-inserts >"$@"

schema/schema.php: schema/schema.txt util/SchemaSchemaDemo.jar
	${schemaschemademo} schema/schema.txt -o-schema-php "$@" -php-schema-class-namespace EarthIT_Schema
schema/test.schema.php: schema/test.schema.txt schema/schema.txt util/SchemaSchemaDemo.jar
	${schemaschemademo} schema/schema.txt schema/test.schema.txt -o-schema-php "$@" -php-schema-class-namespace EarthIT_Schema

.git-object-urns.txt: .git/HEAD
	vendor/earthit/php-project-utils/bin/generate-git-urn-map -i "$@"

build/db/create-database.sql: config/dbc.json vendor
	vendor/bin/generate-create-database-sql "$<" >"$@"
build/db/drop-database.sql: config/dbc.json vendor
	vendor/bin/generate-drop-database-sql "$<" >"$@"

#www/images/head.png:
#	${fetch} -o "$@" "urn:bitprint:HYWPXT25DHVRV4BXETMRZQY26E6AQCYW.33QDQ443KBXZB5F5UGYODRN2Y34DOZ4GILDI7ZA"

create-database: build/db/create-database.sql
	sudo -u postgres psql <"$<"
	sudo -u postgres psql $$(util/get-db-name) <build/db/enable-extensions.sql
drop-database: build/db/drop-database.sql
	sudo -u postgres psql <"$<"

test-db-connection: config/dbc.json
	util/test-db-connection

empty-database: build/db/empty-database.sql util/phptemplateprojectdatabase-psql
	cat "$<" | util/phptemplateprojectdatabase-psql

upgrade-database: resources
	vendor/bin/upgrade-database -upgrade-table 'phptemplateprojectdatabasenamespace.schemaupgrade'
upgrade-database-with-test-data: resources
	vendor/bin/upgrade-database -upgrade-table 'phptemplateprojectdatabasenamespace.schemaupgrade' \
		-upgrade-script-dir build/db/upgrades \
		-upgrade-script-dir build/db/test-data

fix-entity-id-sequence: resources config/entity-id-sequence.json
	util/fix-entity-id-sequence

rebuild-database: empty-database upgrade-database
rebuild-database-with-test-data: empty-database upgrade-database-with-test-data

run-unit-tests: runtime-resources schema/test.schema.php upgrade-database-with-test-data
	vendor/bin/phpunit --bootstrap init-test-environment.php src/test/php

run-tests: run-unit-tests

run-web-server:
	cd www && php -S localhost:6061 bootstrap.php

redeploy-without-upgrading-the-database: runtime-resources

redeploy: redeploy-without-upgrading-the-database upgrade-database fix-entity-id-sequence

everything: \
	config/dbc.json \
	drop-database \
	create-database \
	upgrade-database \
	run-tests \
	run-web-server
