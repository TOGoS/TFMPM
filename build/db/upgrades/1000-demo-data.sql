INSERT INTO "phptemplateprojectdatabasenamespace"."user"
("username", "passhash", "emailaddress") VALUES
('Freddie Mercury', 'blah', 'freddie@mercury.net'),
('David Bowie', 'blah', 'david@bowie.net');

INSERT INTO "phptemplateprojectdatabasenamespace"."organization"
("name") VALUES
('Queen'),
('The Konrads'),
('Riot Squad');

INSERT INTO "phptemplateprojectdatabasenamespace"."userorganizationattachment"
("userid", "organizationid") VALUES
(1001, 1003),
(1002, 1004),
(1002, 1005);
