-- This test data uses entity IDs 1000041-1000050

INSERT INTO phptemplateprojectdatabasenamespace.organization
(id, name, parentid) VALUES
(1000041, 'ACL Test Org', NULL),
(1000042, 'ACL Test Facility West', 1000041),
(1000043, 'ACL Test Facility East', 1000041),
(1000044, 'ACL Test Facility East Garage', 1000043);

INSERT INTO phptemplateprojectdatabasenamespace.facility
(id, curtaincolor) VALUES
(1000041, 'red'),
(1000042, 'white'),
(1000043, 'blue'),
(1000044, 'brown');

INSERT INTO phptemplateprojectdatabasenamespace.userrole
(id, name) VALUES
(1000045, 'Organization Administrator'),
(1000046, 'Facilty Administrator'),
(1000047, 'Facility Visitor');

INSERT INTO phptemplateprojectdatabasenamespace.userrolepermission
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
-- Facility admins manage the facility at their attachment point
-- and see the organization structure above them
(1000046, 1000024, 'read'     , false,  true,  true, false),
(1000046, 1000035, 'read'     , false,  true, false, false),
(1000046, 1000035, 'update'   , false,  true, false, false),
-- Visitors can look at stuff at their attachment point
-- and see the organization structure above them
(1000047, 1000024, 'read'     , false,  true,  true, false),
(1000047, 1000035, 'read'     , false,  true, false, false);

INSERT INTO phptemplateprojectdatabasenamespace.user
(id, username) VALUES
(1000048, 'Test Organization Administrator'),
(1000049, 'Test Facility Administrator'),
(1000050, 'Test Some Guy');

INSERT INTO phptemplateprojectdatabasenamespace.userorganizationattachment
(userid, roleid, organizationid) VALUES
(1000048, 1000045, 1000041),
(1000049, 1000046, 1000043),
(1000050, 1000047, 1000043);