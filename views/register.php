<?php $PU->emitHtmlBoilerplate("Register!"); ?>

<h2>Registration form!</h2>

<p>This doesn't actually do anything yet except attempt to send you an e-mail.</p>

<form method="POST">
<input type="text" name="e-mail-address" placeholder="foo@bar.com"/>
<input type="submit" value="Register!"/>
</form>

<?php $PU->emitHtmlFooter(); ?>
