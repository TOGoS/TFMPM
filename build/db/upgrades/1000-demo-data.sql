-- IDs:
-- 1000009,1000000000,1000011,1000012,1000013,1000014,1000015,1000016,1000017,1000018,1000019,1000020

INSERT INTO "phptemplateprojectdatabasenamespace"."user"
("id", "username", "passhash", "emailaddress") VALUES
(1000001, 'Freddie Mercury', 'blah', 'freddie@mercury.net'),
(1000002, 'David Bowie', 'blah', 'david@bowie.net');

INSERT INTO "phptemplateprojectdatabasenamespace"."organization"
("id", "name") VALUES
(1000003, 'Queen'),
(1000004, 'The Konrads'),
(1000005, 'Riot Squad');

INSERT INTO "phptemplateprojectdatabasenamespace"."userrole"
("id", "name") VALUES
(1000006, 'Demo person'),
(1000007, 'Different demo person');

INSERT INTO "phptemplateprojectdatabasenamespace"."userorganizationattachment"
("userid", "roleid", "organizationid") VALUES
(1000001, 1000006, 1000003),
(1000002, 1000006, 1000004),
(1000002, 1000007, 1000005);
