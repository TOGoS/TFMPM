INSERT INTO "user"
("username", "passhash", "emailaddress") VALUES
('Freddie Mercury', 'blah', 'freddie@mercury.net'),
('David Bowie', 'blah', 'david@bowie.net');

INSERT INTO "organization"
("name") VALUES
('Queen'),
('The Konrads'),
('Riot Squad');

INSERT INTO "userorganizationattachment"
("userid", "organizationid") VALUES
(1001, 1003),
(1002, 1004),
(1002, 1005);
