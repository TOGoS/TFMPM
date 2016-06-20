<?php $PU->emitHtmlBoilerplate("Log In", $params); ?>

<h2>Reset your password</h2>

<?php $PU->emitMessageBlock($params); ?>

<form method="POST" action="reset-password" class="tabby">
<input type="hidden" name="token" value="<?php eht($token); ?>"/>
<div>
  <label for="username-box">Password</label>
  <input id="username-box" name="password" type="password" />
</div>
<div>
	<label for="password-box">New password (type it again!)</label>
  <input id="username-box" name="password2" type="password" />
</div>
<div><input type="submit" value="Reset"/></div>
</form>

<?php $PU->emitHtmlFooter(); ?>
