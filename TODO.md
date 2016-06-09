## TODO

- Re-enable compound request test
- Use Nife_HTTP_FancyRequests
- Include in Vagrant machine:
  - screen (and a decent .screenrc)
  - emacs
  - Make database created match config/dbc.json 
  - Database initialization
- Make sure 'passhash' field doesn't get included in data from /api/users
- Support Basic authorization
- Make a To-Do list app
- Comment stuff in schema.txt
- Tutorials
  - Defining classes
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
* Replace puppet with something not stupid
  - provision.sh
* Replace 'import everything's in schema.txt with importing the specific stuffs
* Tutorials (tut/)
  * Making PageActions
* Make address saving work (need to generate ID)
