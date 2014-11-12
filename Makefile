generated_files = \
	build/db/create-database.sql \
	build/db/drop-database.sql \
	build/db/upgrades/0110-create-tables.sql \
	build/db/upgrades/0097-drop-tables.sql \
	util/phptemplateprojectdatabase-psql \
	util/phptemplateprojectdatabase-pg_dump \
	util/SchemaSchemaDemo.jar \
	schema/schema.php

run_schema_processor = \
	java -jar util/SchemaSchemaDemo.jar \
	-o-create-tables-script build/db/upgrades/0110-create-tables.sql \
	-o-drop-tables-script build/db/upgrades/0097-drop-tables.sql \
	-o-schema-php schema/schema.php -php-schema-class-namespace EarthIT_Schema \
	schema/schema.txt

all: ${generated_files}

clean:
	rm -f ${generated_files}

.DELETE_ON_ERROR:

.PHONY: \
	all \
	rebuild-database \
	run-service-tests \
	clean

util/phptemplateprojectdatabase-psql: config/dbc.json
	vendor/bin/generate-psql-script -psql-exe psql "$<" >"$@"
	chmod +x "$@"
util/phptemplateprojectdatabase-pg_dump: config/dbc.json
	vendor/bin/generate-psql-script -psql-exe pg_dump "$<" >"$@"
	chmod +x "$@"

%: %.urn
	cp -n config/ccouch-repos.lst.example config/ccouch-repos.lst
	vendor/bin/fetch -repo @config/ccouch-repos.lst -o "$@" `cat "$<"`

build/db/upgrades/0110-create-tables.sql: schema/schema.txt util/SchemaSchemaDemo.jar
	${run_schema_processor}

build/db/upgrades/0097-drop-tables.sql: schema/schema.txt util/SchemaSchemaDemo.jar
	${run_schema_processor}

schema/schema.php: schema/schema.txt util/SchemaSchemaDemo.jar
	${run_schema_processor}

build/db/create-database.sql: config/dbc.json
	vendor/bin/generate-create-database-sql "$<" >"$@"
build/db/drop-database.sql: config/dbc.json
	vendor/bin/generate-drop-database-sql "$<" >"$@"

create-database drop-database: %: build/db/%.sql
	sudo su postgres -c "cat '$<' | psql"

rebuild-database: ${generated_files}
	cat build/db/upgrades/*.sql | util/phptemplateprojectdatabase-psql -q

run-service-tests:
	${MAKE} -C service-tests
