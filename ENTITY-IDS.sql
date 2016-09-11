Spaces to be allocated from globally:

 [          0,        1000) : reserved for special values
 [       1000,        1100) : reserved for initial 'you need to intialize your entity ID sequence' space
 [       1100,     1000000) : reserved for projects that were created before the 1000000-4000000 ranges were defined
 [    1000000,     2000000) : template project IDs
 [    2000000,     3000000) : project IDs
 [    3000000,  1073741824) : reserved for future use
 [ 1073741824,  2147483648) : entity-like IDs that fit in 32 bits, allocated from top

The following spaces should be assigned one per deployment;
the list depends on what your deployments are:

 [10000000000, 20000000000) : non-global 'local development' IDs
 [20000000000, 30000000000) : shared deployment 1
 [20000000000, 30000000000) : shared deployment 2

...etc
