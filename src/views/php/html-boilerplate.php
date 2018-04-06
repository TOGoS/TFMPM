<!DOCTYPE html>
<html>
<head>
<title><?php echo htmlspecialchars("{$title} - TOGoS's Factorio Map Preview Manager"); ?></title>
<link rel="stylesheet" type="text/css" href="<?php eht($PU->relativeUrl('/basek.css')); ?>"/>
<script>
var footerClickCount = 0;
function footerClicked() {
	++footerClickCount;
	if( footerClickCount > 5 ) alert("Teehee!  That tickles!");
}
</script>
</head>
<body>

<div class="nav-bar">
<ul class="nav">
<li><?php echo $PU->linkHtml('/','Home'); ?></li>
<li class="devtool" style="display:none">Hi there!</li>
<?php if(isset($extra)) foreach(is_array($extra) ? $extra : array($extra) as $ex) echo "<li>".htmlspecialchars($ex)."</li>"; ?>
</ul>

</div>

<div id="main-content-div" class="content">
