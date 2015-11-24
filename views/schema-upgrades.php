<?php $PU->emitHtmlBoilerplate("Schema Upgrades", $params); ?>

<style>
td.upgrade-script-content {
    background: rgba(255,255,0,0.2);
    border: 1px solid rgba(255,255,0,0.5);
    padding-left: 16px;
}
</style>

<p style="float:right"><a href="?mode=list">list</a> | <a href="?mode=full">full</a></p>

<h2>Schema Upgrades</h2>

<p><b><?php eht(count($schemaUpgrades)); ?></b> upgrades have been run.
<br />Fingerprint: <b><?php eht($fingerprint); ?></b></p>

<table>
<tr><th>Time</th><th>Filename</th><th>Hash</th></tr>
<?php foreach( $schemaUpgrades as $upgrade ): ?>
<tr>
    <td><?php eht($upgrade['time']); ?></td>
    <td><?php eht($upgrade['script filename']); ?></td>
    <td><?php echo $PU->linkHtml(
      "/uri-res/raw/".$upgrade['script file URN']."/".urlencode($upgrade['script filename']),
      $upgrade['script file hash']); ?></td>
</tr>
<?php if(isset($upgrade['script content'])): ?>
<tr><td colspan="3" class="upgrade-script-content"><pre class="upgrade-script"><?php eht($upgrade['script content']); ?></pre></td></tr>
<?php endif; ?>
<?php endforeach; ?>
</table>

<?php $PU->emitHtmlFooter(); ?>
