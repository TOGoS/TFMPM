<?php $PU->emitHtmlBoilerplate("Welcome to TFMPM!", $params); ?>

<h1>Welcome to TOGoS's Factorio Map Preview Manager!</h1>

<p>There are <?php eht($mapDatabaseSummary['mapGenerationCount']); ?> generated maps</p>

<p>Let's check some maps!</p>

<!-- pre><?php eht(print_r($mapFilterMetadata,true)); ?></pre -->

<form class="filter-form" action="">
<div class="filter-set">
<?php foreach($mapFilterMetadata as $fieldCode=>$filter): ?>
 <?php if( isset($filter['filterability']['exact-match']) and count($filter['values']) ): ?>
  <fieldset class="filter">
    <legend><?php eht($filter['fieldName']); ?></legend>
    <select id="<?php eht($fieldCode."SelectBox");?>" name="<?php eht($fieldCode); ?>[]" multiple size="20">
    <?php $PU->emitSelectOptions($filter['values'], $filter['selectedValues']); ?>
    </select>
    <a onclick="<?php eht("\$('#{$fieldCode}SelectBox option:selected').prop('selected',false);"); ?>">clear</a>
  </fieldset>
 <?php endif; ?>
<?php endforeach; ?>

</div>

<div>
<input type="submit" value="Narrow Filters"/>
<input type="submit" onclick="this.form.action = 'compare-maps'; return true" value="Compare Maps"/>
</div>

</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<?php $PU->emitHtmlFooter(); ?>
