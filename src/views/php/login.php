<?php $PU->emitHtmlBoilerplate("Log In", $params); ?>

<style scoped>
.login-stuffs {
}
.login-method {
	margin-top: 32px;
	margin-bottom: 32px;
}
</style>

<div class="login-stuffs">
<div class="login-method">
<h2>Log In</h2>

<?php $PU->emitMessageBlock($params); ?>

<form method="POST" action="login" class="tabby">
<div>
  <label for="username-box">Username</label>
  <input id="username-box" name="username" type="text" placeholder="david@bowie.net"/>
</div>
<div>
  <label for="password-box">Password</label>
  <input id="password-box" name="password" type="password" placeholder="Asdf1234"/>
</div>
<div><input type="submit" value="Log In"/></div>
</form>
</div>


<div class="login-method">
<h3>Forgot your password?</h3>

<p>Enter your e-mail address and we'll send you a link to reset it.</p>

<form method="POST" action="forgot-password" class="tabby">
<div>
  <label for="email-address-box">E-mail Address</label>
  <input id="email-address-box" name="email-address" type="text" placeholder="david@bowie.net"/>
</div>
<div><input type="submit" value="Send Password Reset Link"/></div>
</form>
</div>


<div class="login-method">
<h3>Don't want to horse with passwords?</h3>

<p>Enter your e-mail address and we'll send you a link to log in.</p>

<form method="POST" action="send-login-link" class="tabby">
<div>
  <label for="email-address-box">E-mail Address</label>
  <input id="email-address-box" name="email-address" type="text" placeholder="david@bowie.net"/>
</div>
<div><input type="submit" value="Send Login Link"/></div>
</form>
</div>
</div>

<?php $PU->emitHtmlFooter(); ?>
