<?php if( !empty($successMessages) ): ?>
<div class="success">
<ul>
<?php foreach($successMessages as $msg): ?>
<li><?php eht($msg); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
