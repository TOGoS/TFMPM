build_target_dir := target

config_files := \
	config/ccouch-repos.lst \
	config/dbc.json \
	config/email-addresses.json \
	config/email-transport.json \
	www/.htaccess

generated_resources := \
	src/db-migrations/all-tables.sql \
	src/db-migrations/create-database.sql \
	src/db-migrations/drop-database.sql \
	util/tfmpm-psql \
	util/tfmpm-pg_dump \
	util/SchemaSchemaDemo.jar \
	${build_target_dir}/schema/schema.php \
	vendor

# Include .git-object-urns.txt in the above list if
# you like deployments to take forever.

build_resources := ${generated_resources} ${config_files}

runtime_resources := \
	config/dbc.json \
	config/email-addresses.json \
	config/email-transport.json \
	${build_target_dir}/schema/schema.php \
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
	run-integration-tests \
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

util/tfmpm-psql: config/dbc.json
	vendor/bin/generate-psql-script -psql-exe psql "$<" >"$@"
	chmod +x "$@"
util/tfmpm-pg_dump: config/dbc.json
	vendor/bin/generate-psql-script -psql-exe pg_dump "$<" >"$@"
	chmod +x "$@"

util/SchemaSchemaDemo.jar: \
%: %.urn | vendor config/ccouch-repos.lst
	${fetch} -o "$@" `cat "$<"`

src/db-migrations/all-tables.sql: src/schema/schema.txt util/SchemaSchemaDemo.jar
	${schemaschemademo} -o-create-tables-script "$@" "$<"

src/db-migrations/rc-inserts.sql: ${build_target_dir}/schema/schema.php
	util/generate-rc-inserts >"$@"

${build_target_dir}/schema/schema.php: src/schema/schema.txt util/SchemaSchemaDemo.jar
	${schemaschemademo} src/schema/schema.txt -o-schema-php "$@" -php-schema-class-namespace EarthIT_Schema
${build_target_dir}/schema/test.schema.php: src/schema/test.schema.txt src/schema/schema.txt util/SchemaSchemaDemo.jar
	${schemaschemademo} src/schema/schema.txt src/schema/test.schema.txt -o-schema-php "$@" -php-schema-class-namespace EarthIT_Schema

.git-object-urns.txt: .git/HEAD
	vendor/earthit/php-project-utils/bin/generate-git-urn-map -i "$@"

src/db-migrations/create-database.sql: config/dbc.json vendor
	vendor/bin/generate-create-database-sql "$<" >"$@"
src/db-migrations/drop-database.sql: config/dbc.json vendor
	vendor/bin/generate-drop-database-sql "$<" >"$@"

#www/images/head.png:
#	${fetch} -o "$@" "urn:bitprint:HYWPXT25DHVRV4BXETMRZQY26E6AQCYW.33QDQ443KBXZB5F5UGYODRN2Y34DOZ4GILDI7ZA"

create-database: src/db-migrations/create-database.sql
	sudo -u postgres psql <"$<"
	sudo -u postgres psql $$(util/get-db-name) <src/db-migrations/enable-extensions.sql
drop-database: src/db-migrations/drop-database.sql
	sudo -u postgres psql <"$<"

test-db-connection: config/dbc.json
	util/test-db-connection

empty-database: src/db-migrations/empty-database.sql util/tfmpm-psql
	cat "$<" | util/tfmpm-psql

upgrade-database: resources
	vendor/bin/upgrade-database -upgrade-table 'tfmpm.schemaupgrade' \
		-upgrade-script-dir src/db-migrations/upgrades	
upgrade-database-with-test-data: resources
	vendor/bin/upgrade-database -upgrade-table 'tfmpm.schemaupgrade' \
		-upgrade-script-dir src/db-migrations/upgrades \
		-upgrade-script-dir src/db-migrations/test-data

fix-entity-id-sequence: resources config/entity-id-sequence.json
	util/fix-entity-id-sequence

rebuild-database: empty-database upgrade-database
rebuild-database-with-test-data: empty-database upgrade-database-with-test-data

# PHPSimplerTest doesn't know about groups!  D:
#run-unit-tests: runtime-resources ${build_target_dir}/schema/test.schema.php
#	vendor/bin/phpsimplertest --bootstrap init-test-environment.php src/test/php --colorful-output
#
# run-integration-tests: run-unit-tests
#run-integration-tests: runtime-resources ${build_target_dir}/schema/test.schema.php upgrade-database-with-test-data
#	vendor/bin/phpsimplertest --bootstrap init-test-environment.php --group integration src/test/php
#
#run-tests: run-unit-tests run-integration-tests
# So for now all the tests are combined:
run-tests run-unit-tests run-integration-tests: runtime-resources ${build_target_dir}/schema/test.schema.php upgrade-database-with-test-data
	vendor/bin/phpsimplertest --colorful-output --bootstrap init-test-environment.php src/test/php

run-web-server:
	php -S localhost:6061 -t www bootstrap.php

redeploy-without-upgrading-the-database: runtime-resources

redeploy: redeploy-without-upgrading-the-database upgrade-database fix-entity-id-sequence

everything: \
	config/dbc.json \
	drop-database \
	create-database \
	upgrade-database \
	run-tests \
	run-web-server
