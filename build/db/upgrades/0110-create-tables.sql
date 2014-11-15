CREATE TABLE "phptemplateprojectdatabasenamespace"."user" (
	"id" BIGINT NOT NULL DEFAULT nextval('phptemplateprojectdatabasenamespace.newentityid'),
	"username" VARCHAR(127) NOT NULL,
	"passhash" VARCHAR(127),
	"emailaddress" VARCHAR(127),
	PRIMARY KEY ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."organization" (
	"id" BIGINT NOT NULL DEFAULT nextval('phptemplateprojectdatabasenamespace.newentityid'),
	"name" VARCHAR(127) NOT NULL,
	PRIMARY KEY ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."userorganizationattachment" (
	"userid" BIGINT NOT NULL,
	"organizationid" BIGINT NOT NULL,
	PRIMARY KEY ("userid", "organizationid"),
	FOREIGN KEY ("userid") REFERENCES "phptemplateprojectdatabasenamespace"."user" ("id"),
	FOREIGN KEY ("organizationid") REFERENCES "phptemplateprojectdatabasenamespace"."organization" ("id")
);
