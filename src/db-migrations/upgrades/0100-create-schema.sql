CREATE SCHEMA IF NOT EXISTS tfmpm;

CREATE TABLE "tfmpm"."schemaupgrade" (
	"time" TIMESTAMP,
	"scriptfilename" VARCHAR(255),
	"scriptfilehash" CHAR(40)
);
