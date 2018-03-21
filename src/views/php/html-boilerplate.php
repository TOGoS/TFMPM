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
</ul>

<ul class="nav">
<?php if( $loggedInUser ): ?>
<li>Logged in as <?php eht($loggedInUser['username']); ?> <?php echo $PU->linkHtml('./logout','Log Out'); ?></li>
<?php else: ?>
<li><?php echo $PU->linkHtml('/login','Log In'); ?></li>
<li><?php echo $PU->linkHtml('/register','Register'); ?></li>
<?php endif; ?>
</ul>
</div>

<div class="content">
