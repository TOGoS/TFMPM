<?php if( !empty($errorMessages) ): ?>
<div class="error">
<p>Oh no!</p>
<ul>
<?php foreach( $errorMessages as $msg ): ?>
<li><?php eht($msg); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
