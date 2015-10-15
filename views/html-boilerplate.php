<html>
<head>
<title><?php echo htmlspecialchars("{$title} - PHP Template Project"); ?></title>
<!-- TODO: [Relative!] link to CSS instead of including inline -->
<style>
table.bolly {
	border-collapse: collapse;
}
table.bolly tbody tr:nth-child(2n+1) {
	background-color: rgba(0,0,255,0.15);
}
table.bolly tbody td {
	padding-right: 16px;
}
td.null {
	background-color: rgba(128,128,0,0.2);
}

.tabby {
	display: table;
}
.tabby > div {
	display: table-row;
}
.tabby > div > * {
	display: table-cell;
}

.tabby > div > label:nth-child(1) {
	padding-right: 10px;
}

.nav-bar {
	border-bottom: 1px solid silver;
	overflow: auto;
	padding: 0;
}
ul.nav1 { float: left; }
ul.nav2 { float: right; }
ul.nav1, ul.nav2 {
	margin: 0;
	display: table;
}
ul.nav1 > li, ul.nav2 li {
	display: table-cell;
	padding: 4px 8px;
}
.footer {
	text-align: center;
	border-top: 1px solid silver;
	color: rgba(128,192,128,0.5);
}

.error-messages {
	color: red;
}
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

<div class="nav-bar">
<ul class="nav1">
<li><?php echo $PU->linkHtml('./','Home'); ?></li>
<li class="devtool" style="display:none">Hi there!</li>
</ul>

<ul class="nav2">
<?php if( $loggedInUser ): ?>
<li>Logged in as <?php eht($loggedInUser['username']); ?> <?php echo $PU->linkHtml('./logout','Log Out'); ?></li>
<?php else: ?>
<li><?php echo $PU->linkHtml('./login','Log In'); ?></li>
<li><?php echo $PU->linkHtml('./register','Register'); ?></li>
<?php endif; ?>
</ul>
</div>
