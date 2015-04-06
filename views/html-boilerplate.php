<html>
<head>
<title><?php echo htmlspecialchars("{$title} - PHP Template Project"); ?></title>
<style>
ul.nav { display: table; border-bottom: 1px solid silver; width: 100%; }
ul.nav > li { display: table-cell; padding: 4px 8px; }
.footer { text-align: center; border-top: 1px solid silver; color: rgba(128,192,128,0.5); }
</style>
<script>
var footerClickCount = 0;
function footerClicked() {
	++footerClickCount;
	if( footerClickCount > 5 ) alert("Teehee!  That tickles!");
}
</script>
</head>
<body>

<ul class="nav">
<li><?php echo $PU->linkHtml('./','Home'); ?></li>
<li><?php echo $PU->linkHtml('./api/users','Users'); ?></li>
<li><?php echo $PU->linkHtml('./hello/Mr.%20Man','Hi!'); ?></li>
<li class="devtool" style="display:none">Hi there!</li>
</ul>
