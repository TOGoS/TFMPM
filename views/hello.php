<?php $PU->emitHtmlBoilerplate("Welcome!", $params); ?>

<h1>Welcome to PHP Template Project!</h1>

<p>This code was generated by PHP Project Initializer.
You probably want to make some modifications.</p>

<p>See also: <a href="<?php echo htmlspecialchars($helloUri); ?>"><?php echo htmlspecialchars($helloUri); ?></a></p>

<h4>Data Tables</h4>
<?php EarthIT_PAXML::emit($dataTablePaxml); ?>

<style>
dl.tabby { display: table; }
dl.tabby div { display: table-row; }
dl.tabby dt {
	display: table-cell;
	padding: 2px 8px 2px 4px;
}
dl.tabby dt:after {
	content: ":";
}
dl.tabby dd {
	display: table-cell;
	padding: 2px 4px 2px 8px;
	text-align: right;
}
</style>

<h4>File upload</h4>

<form action="blobs" method="POST" enctype="multipart/form-data">
<input type="file" name="files[]" multiple="multiple">
<input type="submit" name="Go!"/>
</form>

<h4>Other stuff</h4>

<dl class="tabby">
<?php foreach($otherStuff as $thing=>$stuff): ?>
<?php EarthIT_PAXML::emit( ['div', ['dt', $thing], ['dd', $stuff]] ); ?>
<?php endforeach; ?>
</dl>

<ul>
<?php foreach($otherLinks as $title=>$href): ?>
<?php EarthIT_PAXML::emit( ['li', ['a', 'href'=>$href, $title]] ); ?>
<?php endforeach; ?>
</ul>

<?php $PU->emitHtmlFooter(); ?>
