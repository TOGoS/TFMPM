<?php $PU->emitHtmlBoilerplate("Welcome!"); ?>

<style>/* <!-- */

table.computations {
	border-collapse: collapse;
   margin: 10px;
}
table.computations th, table.computations td {
   padding: 3px 6px;
}
table.computations tr td:nth-child(even) {
	background-color: rgba(50,50,0,0.2);
}
td.status-queued { color: gray; }
td.status-processing { color: yellow; }
td.status-complete { color: green; }

/* --> */</style>

<h2>Computations!</h2>

<p>I will calculate square roots for you really really slowly!</p>

<form method="POST">
<input type="text" name="square"/>
</form>

<?php if(count($computations) > 0): ?>

<h3>Previous computations</h3>

<p>Reload once in a while to see progress.</p>

<table class="computations">
<tr><th>Expression</th>
    <th>Status</th>
    <th>Result</th></tr>
<?php foreach($computations as $comp): ?>
<tr><td class="expression"><?php eht($comp['expression']); ?></td>
    <td class="status status-<?php eht($comp['statuscode']); ?>"><?php eht($comp['statuscode']); ?></td>
    <td class="result"><?php eht($comp['result']); ?></td></tr>
<?php endforeach; ?>
</table>

<?php endif; ?>

<?php $PU->emitHtmlFooter(); ?>
