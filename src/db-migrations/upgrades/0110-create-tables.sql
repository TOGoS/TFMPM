CREATE TABLE "tfmpm"."user" (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"username" VARCHAR(126) NOT NULL,
	"passhash" VARCHAR(126),
	"emailaddress" VARCHAR(126),
	PRIMARY KEY ("id")
);
CREATE TABLE "tfmpm"."tokenaction" (
	"tokenhash" VARCHAR(126) NOT NULL,
	"halfuserid" BIGINT NOT NULL,
	"actionscript" TEXT NOT NULL,
	"reuseable" BOOLEAN NOT NULL,
	"expirationtime" TIMESTAMP,
	"usagetime" TIMESTAMP,
	PRIMARY KEY ("tokenhash"),
	FOREIGN KEY ("halfuserid") REFERENCES "tfmpm"."user" ("id")
);
CREATE TABLE "tfmpm"."postaladdress" (
	"id" CHAR(32) NOT NULL,
	"streetaddress" VARCHAR(126),
	"unitaddress" VARCHAR(126),
	"cityname" VARCHAR(126),
	"regioncode" CHAR(2),
	"postalcode" VARCHAR(126),
	"countrycode" CHAR(3),
	PRIMARY KEY ("id")
);
CREATE TABLE "tfmpm"."organization" (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"name" VARCHAR(126) NOT NULL,
	"parentid" BIGINT,
	PRIMARY KEY ("id"),
	FOREIGN KEY ("parentid") REFERENCES "tfmpm"."organization" ("id")
);
CREATE TABLE "tfmpm"."entitypostaladdress" (
	"entityid" BIGINT NOT NULL,
	"postaladdressid" CHAR(32) NOT NULL,
	PRIMARY KEY ("entityid", "postaladdressid"),
	FOREIGN KEY ("postaladdressid") REFERENCES "tfmpm"."postaladdress" ("id")
);
CREATE TABLE "tfmpm"."userrole" (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"name" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("id")
);
CREATE TABLE "tfmpm"."resourceclass" (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"name" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("id")
);
CREATE TABLE "tfmpm"."actionclass" (
	"name" VARCHAR(126) NOT NULL,
	"description" TEXT,
	PRIMARY KEY ("name")
);
CREATE TABLE "tfmpm"."userrolepermission" (
	"roleid" BIGINT NOT NULL,
	"resourceclassid" BIGINT NOT NULL,
	"actionclassname" VARCHAR(126) NOT NULL,
	"appliessystemwide" BOOLEAN NOT NULL DEFAULT FALSE,
	"appliesatattachmentpoint" BOOLEAN NOT NULL DEFAULT FALSE,
	"appliesaboveattachmentpoint" BOOLEAN NOT NULL DEFAULT FALSE,
	"appliesbelowattachmentpoint" BOOLEAN NOT NULL DEFAULT FALSE,
	PRIMARY KEY ("roleid", "resourceclassid", "actionclassname"),
	FOREIGN KEY ("roleid") REFERENCES "tfmpm"."userrole" ("id"),
	FOREIGN KEY ("resourceclassid") REFERENCES "tfmpm"."resourceclass" ("id"),
	FOREIGN KEY ("actionclassname") REFERENCES "tfmpm"."actionclass" ("name")
);
CREATE TABLE "tfmpm"."userorganizationattachment" (
	"userid" BIGINT NOT NULL,
	"roleid" BIGINT NOT NULL,
	"organizationid" BIGINT NOT NULL,
	PRIMARY KEY ("userid", "roleid", "organizationid"),
	FOREIGN KEY ("userid") REFERENCES "tfmpm"."user" ("id"),
	FOREIGN KEY ("roleid") REFERENCES "tfmpm"."userrole" ("id"),
	FOREIGN KEY ("organizationid") REFERENCES "tfmpm"."organization" ("id")
);
CREATE TABLE "tfmpm"."defaultuserrole" (
	"roleid" BIGINT NOT NULL,
	"requirelogin" BOOLEAN NOT NULL,
	PRIMARY KEY ("roleid"),
	FOREIGN KEY ("roleid") REFERENCES "tfmpm"."userrole" ("id")
);
CREATE TABLE "tfmpm"."computationstatus" (
	"statuscode" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("statuscode")
);
CREATE TABLE "tfmpm"."computation" (
	"expression" VARCHAR(126) NOT NULL,
	"statuscode" VARCHAR(126) NOT NULL,
	"result" VARCHAR(126),
	PRIMARY KEY ("expression"),
	FOREIGN KEY ("statuscode") REFERENCES "tfmpm"."computationstatus" ("statuscode")
);
CREATE TABLE "tfmpm"."product" (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"upc" VARCHAR(126) NOT NULL,
	"title" VARCHAR(126) NOT NULL,
	"descriptionhtml" TEXT NOT NULL,
	PRIMARY KEY ("id")
);
CREATE TABLE "tfmpm"."order" (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"userid" BIGINT NOT NULL,
	"shippingaddressid" CHAR(32) NOT NULL,
	"billingaddressid" CHAR(32) NOT NULL,
	PRIMARY KEY ("id"),
	FOREIGN KEY ("userid") REFERENCES "tfmpm"."user" ("id"),
	FOREIGN KEY ("shippingaddressid") REFERENCES "tfmpm"."postaladdress" ("id"),
	FOREIGN KEY ("billingaddressid") REFERENCES "tfmpm"."postaladdress" ("id")
);
CREATE TABLE "tfmpm"."orderitem" (
	"orderid" BIGINT NOT NULL,
	"productid" BIGINT NOT NULL,
	"quantity" INT NOT NULL,
	FOREIGN KEY ("orderid") REFERENCES "tfmpm"."order" ("id"),
	FOREIGN KEY ("productid") REFERENCES "tfmpm"."product" ("id")
);
