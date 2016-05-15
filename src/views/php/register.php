<?php $PU->emitHtmlBoilerplate("Register!", $params); ?>

<h2>Registration form!</h2>

<?php $PU->emitErrorMessageBlock($params); ?>

<p>This doesn't actually do anything yet except attempt to send you an e-mail.</p>

<form method="POST" action="register" class="tabby">
<div>
  <label>Your name</label>
  <input type="text" name="name" placeholder="Foo McBar"/>
</div>
<div>
  <label>Your e-mail address</label>
  <input type="text" name="e-mail-address" placeholder="foo@bar.com"/>
</div>
<div>
  <input type="submit" value="Register!"/>
</div>
</form>

<?php $PU->emitHtmlFooter(); ?>
