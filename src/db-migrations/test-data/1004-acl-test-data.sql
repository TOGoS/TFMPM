-- This test data uses entity IDs 1000041-1000050
-- Can also use 1000051-59, since not using those for action classes after all.
-- Next: 1000059

CREATE TABLE "tfmpm"."acltestfacility" (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"curtaincolor" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("id"),
	FOREIGN KEY ("id") REFERENCES "tfmpm"."organization" ("id")
);

CREATE TABLE tfmpm.acltestchair (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"facilityid" BIGINT NOT NULL,
	"color" VARCHAR(126),
	PRIMARY KEY ("id"),
	FOREIGN KEY ("facilityid") REFERENCES tfmpm.acltestfacility ("id")
);

CREATE TABLE tfmpm.globallyviewablething (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"name" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("id")
);
CREATE TABLE tfmpm.globallyeditablething (
	"id" BIGINT NOT NULL DEFAULT nextval('tfmpm.newentityid'),
	"name" VARCHAR(126) NOT NULL,
	PRIMARY KEY ("id")
);

INSERT INTO tfmpm.resourceclass
(id, name) VALUES
(1000035, 'ACL test facility'),
(1000056, 'ACL test chair'),
(1000061, 'globally viewable thing'),
(1000062, 'globally editable thing');

INSERT INTO tfmpm."organization"
(id, name, parentid) VALUES
(1000052, 'ACL Test Root Org', NULL),
(1000053, 'ACL Test Cousin Org', 1000052),
(1000041, 'ACL Test Org', 1000052),
(1000042, 'ACL Test Facility West', 1000041),
(1000043, 'ACL Test Facility East', 1000041),
(1000044, 'ACL Test Facility East Garage', 1000043);

INSERT INTO tfmpm.acltestfacility
(id, curtaincolor) VALUES
(1000053, 'orange'),
(1000041, 'red'),
(1000042, 'white'),
(1000043, 'blue'),
(1000044, 'brown');

INSERT INTO tfmpm.acltestchair
(id, facilityid, color) VALUES
(1000054, 1000043, 'brown'),
(1000055, 1000044, 'turquoise'),
-- 1000057 represents a non-existent chair
(1000058, 1000053, 'different orange');

INSERT INTO tfmpm.userrole
(id, name) VALUES
(1000045, 'Organization Administrator'),
(1000046, 'East Facilty Administrator'),
(1000047, 'East Facility Visitor');

INSERT INTO tfmpm.userrolepermission
(roleid, resourceclassid, actionclassname, appliessystemwide, appliesatattachmentpoint, appliesaboveattachmentpoint, appliesbelowattachmentpoint) VALUES
-- Org admins can do anything to everything at and below their attachment point
-- and see the organization structure above them
(1000045, 1000024, 'create'   , false,  true, false,  true),
(1000045, 1000024, 'read'     , false,  true,  true,  true),
(1000045, 1000024, 'update'   , false,  true, false,  true),
(1000045, 1000024, 'delete'   , false,  true, false,  true),
(1000045, 1000024, 'move-to'  , false,  true, false,  true),
(1000045, 1000024, 'move-from', false,  true, false,  true),
(1000045, 1000035, 'create'   , false,  true, false,  true),
(1000045, 1000035, 'read'     , false,  true, false,  true),
(1000045, 1000035, 'update'   , false,  true, false,  true),
(1000045, 1000035, 'delete'   , false,  true, false,  true),
(1000045, 1000035, 'move-to'  , false,  true, false,  true),
(1000045, 1000035, 'move-from', false,  true, false,  true),
(1000045, 1000056, 'create'   , false,  true, false,  true),
(1000045, 1000056, 'read'     , false,  true, false,  true),
(1000045, 1000056, 'update'   , false,  true, false,  true),
(1000045, 1000056, 'delete'   , false,  true, false,  true),
(1000045, 1000056, 'move-to'  , false,  true, false,  true),
(1000045, 1000056, 'move-from', false,  true, false,  true),
-- Facility admins manage the facility at their attachment point
-- and see the organization structure above them
(1000046, 1000024, 'read'     , false,  true,  true, false),
(1000046, 1000035, 'read'     , false,  true, false, false),
(1000046, 1000035, 'update'   , false,  true, false, false),
-- Facility admins have full control over the facility's chairs,
-- except to move chairs between facilities.
(1000046, 1000056, 'create'   , false,  true, false, false),
(1000046, 1000056, 'read'     , false,  true, false, false),
(1000046, 1000056, 'update'   , false,  true, false, false),
(1000046, 1000056, 'delete'   , false,  true, false, false),
-- Visitors can look at stuff at their attachment point
-- and see the organization structure above them
(1000047, 1000024, 'read'     , false,  true,  true, false),
(1000047, 1000035, 'read'     , false,  true, false, false),
(1000047, 1000056, 'read'     , false,  true, false, false),
-- Anyone can view the globally viewable/editable things,
(1000060, 1000061, 'read'     ,  true, false, false, false),
(1000060, 1000062, 'read'     ,  true, false, false, false),
-- And any logged in user can create and update the editable things.
(1000063, 1000062, 'create'   ,  true, false, false, false),
(1000063, 1000062, 'update'   ,  true, false, false, false),
(1000063, 1000062, 'delete'   ,  true, false, false, false);

INSERT INTO tfmpm.user
(id, username) VALUES
(1000048, 'Test Organization Administrator'),
(1000049, 'Test Facility Administrator'),
(1000050, 'Test Some Visitor'),
(1000051, 'Test Unattached User');

INSERT INTO tfmpm.userorganizationattachment
(userid, roleid, organizationid) VALUES
(1000048, 1000045, 1000041),
(1000049, 1000046, 1000043),
(1000050, 1000047, 1000043);

INSERT INTO tfmpm.globallyviewablething
(id, name) VALUES (1000064, 'everybody look at me!');

INSERT INTO tfmpm.globallyeditablething
(id, name) VALUES (1000065, 'everybody poke me!');
