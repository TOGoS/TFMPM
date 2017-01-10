INSERT INTO phptemplateprojectdatabasenamespace.userrole
("id", "name") VALUES
(1000060, 'user (not necessarily logged-in)'),
(1000063, 'logged-in user');

INSERT INTO phptemplateprojectdatabasenamespace.defaultuserrole
("roleid", "requirelogin") VALUES
(1000060, FALSE),
(1000063, TRUE);

INSERT INTO phptemplateprojectdatabasenamespace.userrolepermission
(roleid, resourceclassid, actionclassname, appliessystemwide) VALUES
(1000060, 1000026, 'read'     , true),
(1000060, 1000027, 'read'     , true),
(1000060, 1000030, 'read'     , true),
(1000060, 1000031, 'read'     , true),
(1000060, 1000032, 'read'     , true),
(1000060, 1000036, 'read'     , true);
