-- IDs:
-- 1109,1110,1111,1112,1113,1114,1115,1116,1117,1118,1119,1120

INSERT INTO "phptemplateprojectdatabasenamespace"."user"
("id", "username", "passhash", "emailaddress") VALUES
(1101, 'Freddie Mercury', 'blah', 'freddie@mercury.net'),
(1102, 'David Bowie', 'blah', 'david@bowie.net');

INSERT INTO "phptemplateprojectdatabasenamespace"."organization"
("id", "name") VALUES
(1103, 'Queen'),
(1104, 'The Konrads'),
(1105, 'Riot Squad');

INSERT INTO "phptemplateprojectdatabasenamespace"."userrole"
("id", "name") VALUES
(1106, 'Facility administrator'),
(1107, 'Like a janitor or something');

INSERT INTO "phptemplateprojectdatabasenamespace"."resourceclass"
("id", "name") VALUES
(1108, 'organization');

INSERT INTO "phptemplateprojectdatabasenamespace"."userorganizationattachment"
("userid", "roleid", "organizationid") VALUES
(1101, 1106, 1103),
(1102, 1106, 1104),
(1102, 1107, 1105);
