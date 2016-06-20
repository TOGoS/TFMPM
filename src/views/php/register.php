<?php $PU->emitHtmlBoilerplate("Register!", $params); ?>

<h2>Registration form!</h2>

<?php $PU->emitMessageBlock($params); ?>

<form method="POST" action="register">
<table>
<tr>
  <td><label>Your name</label></td>
  <td><input type="text" name="name" placeholder="Foo McBar"/></td>
</tr>
<tr>
  <td><label>Your e-mail address</label></td>
  <td><input type="text" name="e-mail-address" placeholder="foo@bar.com"/></td>
</tr>
<tr>
  <td colspan="2">
    <input id="send-login-link-box" type="checkbox" name="send-login-link"/>
    <label for="send-login-link-box">Send a login link so you don't have to fool with passwords</label>
  </td>
</tr>
<tr>
  <td colspan="2"><input type="submit" value="Register!"/></td>
</tr>
</table>
</form>

<?php $PU->emitHtmlFooter(); ?>
