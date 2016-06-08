# Phrebar Page tutorial

## Simple page

First, let's make a simple template-based HTML page.

We'll need to:

- Create a template file in ```src/views/php/```
- Create a PageAction class
- Add a rule to our router that maps a request to that PageAction.

### The view template

Let's make our page include the standard header and footer,
plus some hard-coded text, and maybe the time of day,
just to have something dynamic in there.

Create the view template file, ```src/views/php/my-new-page.php```

```php
<?php $PU->emitHtmlBoilerplate("Hi!", $params); ?>

<p>Hello, welcome to my new page!</p>

<p>The current date is <?php eht( date('Y-m-d', $currentTime) ); ?>

<?php $PU->emitHtmlFooter(); ?>
```

Things to note:

```$PU``` is an instance of PageUtil.
It's got methods for including other views,
calculating relative URLs,
and generating simple HTML.

```eht($x)``` is equivalent to ```echo htmlspecialchars($x)```.
Use it whenever you want to emit plain text into an HTML page
and have it be escaped properly.

```$PU```, ```$params```, and ```$currentTime```
are all variables passed in from the page action.
```$PU``` and ```$params``` are standard,
but we'll have to define ```$currentTime``` ourselves.

### The PageAction

Create a new PHP class
```src/main/php/PHPTemplateProjectNS/PageAction/ShowMyNewPage.php```:

```php
<?php

class PHPTemplateProjectNS_PageAction_ShowMyNewPage extends PHPTemplateProjectNS_PageAction
{
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		return $this->templateResponse( 200, 'my-new-page', [
			'currentTime' => time()
		], null, $actx);
	}
}
```

Explanation:

An instance of this class represents an action that the user is trying to do.
PageActions are named using a verb phrase describing that.
"Show" is the idiomatic prefix for an action that results in showing an HTML page.
(for REST services, "Get" is used).

If you follow the inheritance tree upward,
you'll eventually find yourself at TOGoS_Action,
which is an empty interface with a long explanation about what it means to be an action.
I'll leave reading said explanation as an exercise for the reader.

Actions are eventually invoked by the Router.
In the case of PageActions and RESTActions,
the action is invoked as a function with a single 'ActionContext' parameter.

The ActionContext encapsulates 'session state'.
i.e. the context in which the action is being run.
This includes information about which user is invoking the action,
what path they used to get to it (helpful for creating relative URLs),
and access to session (as in ```$_SESSION```) variables.

### Router

We need to add a couple of lines to ```src/main/php/PHPTemplateProjectNS/Router.php```
so that our action gets used when a certain URL is requested.

Add this somewhere in the big if-else chain
in ```requestToAction```
(suggested placement: just before ```if( $path === '/register' )```):

```php
		} else if( $path == '/my-new-page' and $method == 'GET' ) {
			return $this->createPageAction('ShowMyNewPage');
```

Now you should be able to browse to your deployment ```/my-new-page```
and see something like:

> Hello, welcome to my new page!
> The current date is 2016-06-08
