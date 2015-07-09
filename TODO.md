## TODO

- Examples of:
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
- Explicitly $this->registry->thing instead of $this->thing from components
  to make code easier for new people to follow
- Convention for naming/instantiating PageActions so rules don't have to be
  hardcoded
- How-to guides
  - Defining classes
  - Making PageActions
  - Altering authorization rules

## Done

* Que for jobs to be executed after the response has been written/closed
* Separate ComponentGears out of Component class
* Rename Dispatcher to Router
* Refactor Dispatcher/Router to create/invoke actions as separate steps
