## TODO

- Include in Vagrant machine:
  - screen (and a decent .screenrc)
  - emacs
  - Make database created match config/dbc.json 
  - Database initialization
- Replace puppet with something not stupid
- Replace 'import everything's in schema.txt with importing the specific stuffs
- Make sure 'passhash' field doesn't get included in data from /api/users
- Support Basic authorization
- Make a To-Do list app
- Comment stuff in schema.txt
- Explicitly $this->registry->thing instead of $this->thing from components
  to make code easier for new people to follow
- Convention for naming/instantiating PageActions so rules don't have to be
  hardcoded
- Write somehHow-to guides
  - Defining classes
  - Making PageActions
  - Altering authorization rules

Include examples of:

- PDF Generation
* Loading components with acronyms, (e.g. xmlProdder -> XMLProdder)
- Permission checking
- Transforming objects between REST/DB
  - Field renames
  - Field value transformations
  - Transformations over multiple fields
- Update filters
  - Modify data being stored
  - Abort or allow the transaction
  - Log changes (including user ID)
- N2R stuff
- A REST service that gets data weirdly (like by aggregating data from multiple tables or something)

## Done

* Que for jobs to be executed after the response has been written/closed
* Separate ComponentGears out of Component class
* Rename Dispatcher to Router
* Refactor Dispatcher/Router to create/invoke actions as separate steps
