# TOGoS's Factorio Map Preview Manager

```util/generate-map-preview``` - generate a preview.  Information will be stored into logs/<current date in Y_m_d format>.jsonl.

## Viewer

```make dev_www_server=0.0.0.0:6061 run-web-server```

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
I expect that there will be a web component for visually comparing maps.
In the meantime, cruft is crufty.
