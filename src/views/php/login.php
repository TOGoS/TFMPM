<?php $PU->emitHtmlBoilerplate("Log In", $params); ?>

<h2>Log In</h2>

<?php $PU->emitErrorMessageBlock($params); ?>

<form method="POST" action="login" class="tabby">
<div>
  <label for="username-box">Username</label>
  <input id="username-box" name="username" type="text" placeholder="david@bowie.net"/>
</div>
<div>
  <label for="password-box">Password</label>
  <input id="username-box" name="password" type="password" placeholder="Asdf1234"/>
</div>
<div><input type="submit" value="Log In"/></div>
</form>

<?php $PU->emitHtmlFooter(); ?>
