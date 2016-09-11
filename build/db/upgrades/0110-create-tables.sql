CREATE TABLE "phptemplateprojectdatabasenamespace"."user" (
	"id" BIGINT NOT NULL DEFAULT nextval('phptemplateprojectdatabasenamespace.newentityid'),
	"username" VARCHAR(126) NOT NULL,
	"passhash" VARCHAR(126),
	"emailaddress" VARCHAR(126),
	PRIMARY KEY ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."tokenaction" (
	"tokenhash" VARCHAR(126) NOT NULL,
	"halfuserid" BIGINT NOT NULL,
	"actionscript" TEXT NOT NULL,
	"reuseable" BOOLEAN NOT NULL,
	"expirationtime" TIMESTAMP,
	"usagetime" TIMESTAMP,
	PRIMARY KEY ("tokenhash"),
	FOREIGN KEY ("halfuserid") REFERENCES "phptemplateprojectdatabasenamespace"."user" ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."postaladdress" (
	"id" CHAR(32) NOT NULL,
	"streetaddress" VARCHAR(126),
	"unitaddress" VARCHAR(126),
	"cityname" VARCHAR(126),
	"regioncode" CHAR(2),
	"postalcode" VARCHAR(126),
	"countrycode" CHAR(3),
	PRIMARY KEY ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."organization" (
	"id" BIGINT NOT NULL DEFAULT nextval('phptemplateprojectdatabasenamespace.newentityid'),
	"name" VARCHAR(126) NOT NULL,
	"parentid" BIGINT,
	PRIMARY KEY ("id"),
	FOREIGN KEY ("parentid") REFERENCES "phptemplateprojectdatabasenamespace"."organization" ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."facility" (
	"id" BIGINT NOT NULL DEFAULT nextval('phptemplateprojectdatabasenamespace.newentityid'),
	"curtaincolor" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("id"),
	FOREIGN KEY ("id") REFERENCES "phptemplateprojectdatabasenamespace"."organization" ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."entitypostaladdress" (
	"entityid" BIGINT NOT NULL,
	"postaladdressid" CHAR(32) NOT NULL,
	PRIMARY KEY ("entityid", "postaladdressid"),
	FOREIGN KEY ("postaladdressid") REFERENCES "phptemplateprojectdatabasenamespace"."postaladdress" ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."userrole" (
	"id" BIGINT NOT NULL DEFAULT nextval('phptemplateprojectdatabasenamespace.newentityid'),
	"name" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."resourceclass" (
	"id" BIGINT NOT NULL DEFAULT nextval('phptemplateprojectdatabasenamespace.newentityid'),
	"name" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."actionclass" (
	"name" VARCHAR(126) NOT NULL,
	"description" TEXT,
	PRIMARY KEY ("name")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."userrolepermission" (
	"roleid" BIGINT NOT NULL,
	"resourceclassid" BIGINT NOT NULL,
	"actionclassname" VARCHAR(126) NOT NULL,
	"appliessystemwide" BOOLEAN NOT NULL,
	"appliesatattachmentpoint" BOOLEAN NOT NULL,
	"appliesaboveattachmentpoint" BOOLEAN NOT NULL,
	"appliesbelowattachmentpoint" BOOLEAN NOT NULL,
	PRIMARY KEY ("roleid", "resourceclassid", "actionclassname"),
	FOREIGN KEY ("roleid") REFERENCES "phptemplateprojectdatabasenamespace"."userrole" ("id"),
	FOREIGN KEY ("resourceclassid") REFERENCES "phptemplateprojectdatabasenamespace"."resourceclass" ("id"),
	FOREIGN KEY ("actionclassname") REFERENCES "phptemplateprojectdatabasenamespace"."actionclass" ("name")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."userorganizationattachment" (
	"userid" BIGINT NOT NULL,
	"roleid" BIGINT NOT NULL,
	"organizationid" BIGINT NOT NULL,
	PRIMARY KEY ("userid", "roleid", "organizationid"),
	FOREIGN KEY ("userid") REFERENCES "phptemplateprojectdatabasenamespace"."user" ("id"),
	FOREIGN KEY ("roleid") REFERENCES "phptemplateprojectdatabasenamespace"."userrole" ("id"),
	FOREIGN KEY ("organizationid") REFERENCES "phptemplateprojectdatabasenamespace"."organization" ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."computationstatus" (
	"statuscode" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("statuscode")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."computation" (
	"expression" VARCHAR(126) NOT NULL,
	"statuscode" VARCHAR(126) NOT NULL,
	"result" VARCHAR(126),
	PRIMARY KEY ("expression"),
	FOREIGN KEY ("statuscode") REFERENCES "phptemplateprojectdatabasenamespace"."computationstatus" ("statuscode")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."product" (
	"id" BIGINT NOT NULL DEFAULT nextval('phptemplateprojectdatabasenamespace.newentityid'),
	"upc" VARCHAR(126) NOT NULL,
	"title" VARCHAR(126) NOT NULL,
	"descriptionhtml" TEXT NOT NULL,
	PRIMARY KEY ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."order" (
	"id" BIGINT NOT NULL DEFAULT nextval('phptemplateprojectdatabasenamespace.newentityid'),
	"userid" BIGINT NOT NULL,
	"shippingaddressid" CHAR(32) NOT NULL,
	"billingaddressid" CHAR(32) NOT NULL,
	PRIMARY KEY ("id"),
	FOREIGN KEY ("userid") REFERENCES "phptemplateprojectdatabasenamespace"."user" ("id"),
	FOREIGN KEY ("shippingaddressid") REFERENCES "phptemplateprojectdatabasenamespace"."postaladdress" ("id"),
	FOREIGN KEY ("billingaddressid") REFERENCES "phptemplateprojectdatabasenamespace"."postaladdress" ("id")
);
CREATE TABLE "phptemplateprojectdatabasenamespace"."orderitem" (
	"orderid" BIGINT NOT NULL,
	"productid" BIGINT NOT NULL,
	"quantity" INT NOT NULL,
	FOREIGN KEY ("orderid") REFERENCES "phptemplateprojectdatabasenamespace"."order" ("id"),
	FOREIGN KEY ("productid") REFERENCES "phptemplateprojectdatabasenamespace"."product" ("id")
);
