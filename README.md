# TOGoS's Factorio Map Preview Manager

- ```make rebuild-database``` - create the map SQLite database.
- ```util/generate-map-preview``` - generate a preview.
  Information will be stored into ```logs/(current date in Y_m_d format).jsonl```,
  and also into the SQLite database.
- ```make dev_www_server=0.0.0.0:6061 run-web-server``` - run the web server,
  which you can use to view your map collection.

## Viewer

Viewer will automatically pick dimensions for available data.

First for left-right, then for up-down.  Zoom in/out always changes scale.

- Factorio commit ID / data commit ID
- Map seed
- Water level
- ...other controls...
- Map offset X
- Map offset Y
- Map width

## Other stuff

This is a PHPTemplateProject-based project,
which means it's got a lot of boilerplate stuff built in for doing JSON web services.
Cruft is crufty.
