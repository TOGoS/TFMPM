-- Allocated entity IDs 1151 to 1159 for these,
-- but then I decided not to use entity IDs.

INSERT INTO phptemplateprojectdatabasenamespace.actionclass
(name, description) VALUES
('create'   , 'allows creation of new records'),
('read'     , 'allows reding of records'),
('update'   , 'allows basic modification (that do not change ownership) of records'),
('delete'   , 'allows deletion of records'),
('move-to'  , 'allows changing ownership of existing records to a location controlled by the user'),
('move-from', 'allows changing ownership of existing records from a location controlled by the user');
