<?php if($errorMessage): ?>
<div class="error-messages">
<p>Oh no!</p>
<ul><li><?php eht($errorMessage); ?></li></ul>
</div>
<?php endif; ?>
