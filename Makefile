config_files := \
	config/ccouch-repos.lst \
	config/dbc.json \
	config/email-transport.json

generated_resources := \
	build/db/all-tables.sql \
	build/db/create-database.sql \
	build/db/drop-database.sql \
	util/phptemplateprojectdatabase-psql \
	util/phptemplateprojectdatabase-pg_dump \
	util/SchemaSchemaDemo.jar \
	schema/schema.php \
	vendor

build_resources := ${generated_resources} ${config_files}

runtime_resources := \
	config/dbc.json \
	config/email-transport.json \
	schema/schema.php \
	vendor

resources := ${build_resources} ${runtime_resources}

schemaschemademo := java -jar util/SchemaSchemaDemo.jar schema/schema.txt

fetch := vendor/bin/fetch -repo @config/ccouch-repos.lst

default: runtime-resources run-tests

.DELETE_ON_ERROR:

.PHONY: \
	build-resources \
	create-database \
	default \
	drop-database \
	everything \
	empty-database \
	rebuild-database \
	redeploy \
	resources \
	runtime-resources \
	run-tests \
	run-unit-tests \
	run-web-server \
	upgrade-database \
	clean \
	everything

build-resources: ${build_resources}
runtime-resources: ${runtime_resources}
resources: ${resources}

clean:
	rm -rf ${generated_resources}

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
	${schemaschemademo} -o-create-tables-script "$@"

schema/schema.php: schema/schema.txt util/SchemaSchemaDemo.jar
	${schemaschemademo} -o-schema-php "$@" -php-schema-class-namespace EarthIT_Schema

build/db/create-database.sql: config/dbc.json vendor
	vendor/bin/generate-create-database-sql "$<" >"$@"
build/db/drop-database.sql: config/dbc.json vendor
	vendor/bin/generate-drop-database-sql "$<" >"$@"

#www/images/head.png:
#	${fetch} -o "$@" "urn:bitprint:HYWPXT25DHVRV4BXETMRZQY26E6AQCYW.33QDQ443KBXZB5F5UGYODRN2Y34DOZ4GILDI7ZA"

create-database drop-database: %: build/db/%.sql
	sudo -u postgres psql <"$<"

empty-database: build/db/empty-database.sql util/phptemplateprojectdatabase-psql
	cat "$<" | util/phptemplateprojectdatabase-psql

upgrade-database: resources
	vendor/bin/upgrade-database -upgrade-table 'phptemplateprojectdatabasenamespace.schemaupgrade'

rebuild-database: empty-database upgrade-database

run-unit-tests: runtime-resources upgrade-database
	vendor/bin/phpunit --bootstrap init-environment.php test

run-tests: run-unit-tests

run-web-server:
	cd www && php -S localhost:6061 bootstrap.php

redeploy: runtime-resources upgrade-database

everything: \
	config/dbc.json \
	drop-database \
	create-database \
	upgrade-database \
	run-tests \
	run-web-server
