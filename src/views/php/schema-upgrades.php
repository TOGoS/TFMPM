<?php $PU->emitHtmlBoilerplate("Schema Upgrades", $params); ?>

<style>
table.upgrades td.upgrade-script-content {
	background: rgba(255,255,0,0.2);
	border: 1px solid rgba(255,255,0,0.5);
	padding-left: 16px;
}
table.upgrades {
	box-sizing: border-box;
	width: 100%;
	border-collapse: collapse;
}
table.upgrades td:not(:first-child) {
	padding-left: 16px;
}
table.upgrades td {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
table.upgrades tr:nth-child(4n+1) {
	background: rgba(0,217,109,0.15);
}
table.upgrades tr:nth-child(4n+3) {
	background: rgba(0,217,109,0.2);
}
table.upgrades td {
	/**
	 * This is a bit of a hack to allow the table cells to shink
	 * below the width that would normally be imposed on them
	 * by the text they contain.
	 * The goal is to allow all the text in all the columns to show
	 * so long as they fit on the screen, and then ellipse them
	 * when they don't.
	 * This doesn't accomplish that perfectly, but it's the best
	 * I've been able to do entirely with CSS.
    */
	max-width: 0;
}
</style>

<p style="float:right"><a href="?mode=list">list</a> | <a href="?mode=full">full</a></p>

<h2>Schema Upgrades</h2>

<p><b><?php eht(count($schemaUpgrades)); ?></b> upgrades have been run.
<br />Fingerprint: <b><?php eht($fingerprint); ?></b></p>

<table class="upgrades">
<tr><th>Time</th><th>Filename</th><th>Hash</th></tr>
<?php foreach( $schemaUpgrades as $upgrade ): ?>
<tr>
    <td><?php eht($upgrade['time']); ?></td>
    <td><?php eht($upgrade['script filename']); ?></td>
    <td><?php echo $PU->linkHtml(
      "/uri-res/raw/".$upgrade['script file URN']."/".urlencode($upgrade['script filename']).'?type=text/plain',
      $upgrade['script file hash']); ?></td>
</tr>
<?php if(isset($upgrade['script content'])): ?>
<tr><td colspan="3" class="upgrade-script-content"><pre class="upgrade-script"><?php eht($upgrade['script content']); ?></pre></td></tr>
<?php endif; ?>
<?php endforeach; ?>
</table>

<?php $PU->emitHtmlFooter(); ?>
