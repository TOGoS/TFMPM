# High-level architecture the PHP application

## Components

At the center of the system is a Registry object.
Multiple registry objects may exist at once,
but normally there will only be one.
This object's primary responsibility is to create other objects, called 'components'.
Components have a reference back to the registry so that
if, during the course of their work, they need to use some other component,
they can request it from the registry,
which will either return a cached instance or create a new one.

Given that components reference the registry, and the registry caches
component instances, there's a cicular reference between registry and
components.  This set of objects comprises the core machinery of our
system.

```
  ╭────────────╮
  │            │ reg ╭──────────────╮
  │            │◀────│              │
  │  Registry  │  a  │  ComponentA  │
  │            │────▶│              │
  │            │     ╰──────────────╯
  │            │ reg ╭──────────────╮
  │            │◀────│              │
  │            │  b  │  ComponentB  │
  │            │────▶│              │
  │            │     ╰──────────────╯
  │            │ reg ╭──────────────╮
  │            │◀────│              │
  │            │  c  │  ComponentC  │
  │            │────▶│              │
  │            │     ╰──────────────╯
  ╰────────────╯
```

You must always think of the registy as being to the left of other components.
Otherwise you are not Phrebaring correctly.

This isn't to say that components can't create and use other components.
That's fine if it's useful for some particular case.
(e.g. Storage objects will often reference the one directly underneath themselves)
But normally,
putting component construction in the registry keeps things simple and unsurprising.

Examples of components:

- mailer
- database connection
- object storage system
- REST service handler

Components are usually lazily loaded by the registry.
The procedure for doing so is encoded in Registry#__get.

Components should be named after what they are.  e.g. for a component that manages users...

- ```Userify``` <- Wrong!  Should be a noun.
- ```User``` <- Wrong!  This class doesn't represent a user.
- ```UserUtil``` <- Okay!  Although ```Util``` connotates static methods, so maybe not perfect.
- ```UserModel``` <- Also okay!
- ```UserStuff``` <- Also okay!  Not descriptive, but not misleading, either.

## Loading config files

The registry is also responsible for loading configuration data.
This is usually from JSON files in a 'config/' directory.
e.g. to get the value "Wally World" from config/foo.json containing

```json
  {
    "bar": {
      "baz": "Wally World"
    }
  }
```

you could call $registry->getConfig('foo/bar/baz').

## Bootstrapping

init-environment.php is responsible for constructing the registry.
Registry is then responsible for constructing everything else.
The www bootstrapt script loads init-environment.php to construct the registry
and then asks it for a router to fulfill web requests,
but any other script (such as command line utilities)
can also load it and have access to all the same facilities.

## Data

Data is the stuff that's not components.
We'll often pass around data objects as simple arrays of property name => value.
To avoid the 'ask for a banana, get the gorilla and the jungle it's living in' problem,
you should avoid creating data objects/arrays
that references system objects
or objects 'above' them in a hierarchy.

You may be used to systems in which you define 'model' classes that both contain and process data.
In Phrebar you do no such thing.
A component that processes data is entirely separate from the data itself.
Sometimes data may be encapsulated in objects (a la EarthIT_Schema_SchemaObject),
but these objects are 'dumb'.
Their methods are limited to accessors.

(Occasionally objects will be passed around that blur the lines
between the system and the data on which it operates.  A callback
function that fetches something by name, for example (regardless of
purpose, things called "callback functions" tend to be this kind of
thing).  In this case the object is relatively transient, like a data
object, but may do complex tasks and reference component objects.
Because they are transient, the Registry should not cache references
to these.  Otherwise they can be treated as components that are passed
around rather than being hard-wired.)

To summarize:

- System components = machinery that does the processing - think of these as
  complex machines bolted in place but wired together to form larger machines
- Data objects = the stuff being processed - think of these as
  independent packets [of data] that move around between the complex machines
