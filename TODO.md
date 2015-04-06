- Separate ComponentGears out of Component class
- Rename Dispatcher to Router
- Refactor Dispatcher/Router to create/invoke actions as separate steps
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