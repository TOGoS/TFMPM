CREATE SCHEMA IF NOT EXISTS phptemplateprojectdatabasenamespace;

CREATE TABLE "phptemplateprojectdatabasenamespace"."schemaupgrade" (
	"time" TIMESTAMP,
	"scriptfilename" VARCHAR(255),
	"scriptfilehash" CHAR(40)
);
