<?php $PU->emitHtmlBoilerplate($collectionName, $params); ?>

<?php if(count($items) === 0): ?>
<p>No data!</p>
<?php else: ?>
<?php EarthIT_PAXML::emit($tablePaxml); ?>
<?php endif; ?>

<?php $PU->emitHtmlFooter(); ?>
