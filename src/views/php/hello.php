<?php $PU->emitHtmlBoilerplate("Welcome to TFMPM!", $params); ?>

<h1>Welcome to TOGoS's Factorio Map Preview Manager!</h1>

<p>There are <?php eht($mapDatabaseSummary['mapGenerationCount']); ?> generated maps</p>

<p>Let's check some maps!</p>

<!-- pre><?php eht(print_r($mapFilterMetadata,true)); ?></pre -->

<form class="filter-form" action="compare-maps">
<div>
  <label><input
    type="checkbox" name="dataCommitMustMatchFactorioCommit"
    <?php if($mapFilterMetadata['dataCommitMustMatchFactorioCommit']['selectedValue']) echo "checked"; ?>
    onchange="document.getElementById('dataCommitIdFieldset').style.display = this.checked ? 'none' : ''"
  />Data commit must match Factorio commit</label>
</div>
<div class="filter-set">
<?php foreach($mapFilterMetadata as $fieldCode=>$filter): ?>
 <?php if( isset($filter['filterability']['exact-match']) and count($filter['values']) ): ?>
  <fieldset class="filter" id="<?php eht($fieldCode.'Fieldset') ?>">
    <legend><?php eht(ucfirst($filter['fieldName'])); ?></legend>
    <select id="<?php eht($fieldCode."SelectBox");?>" name="<?php eht($fieldCode); ?>[]" multiple size="20">
    <?php $PU->emitSelectOptions($filter['values'], $filter['selectedValues']); ?>
    </select>
    <a onclick="<?php eht("\$('#{$fieldCode}SelectBox option:selected').prop('selected',false);"); ?>">clear</a>
  </fieldset>
 <?php endif; ?>
<?php endforeach; ?>

<?php if($mapFilterMetadata['dataCommitMustMatchFactorioCommit']['selectedValue']): ?>
<!-- HACK HACK HACK -->
<script type="text/javascript">document.getElementById('dataCommitIdFieldset').style.display = 'none';</script>
<?php endif; ?>

</div>

<div>
<input type="submit" formaction="" value="Narrow Filters"/>
<input type="submit" formaction="compare-maps" value="Compare Maps"/>
</div>

</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<?php $PU->emitHtmlFooter(); ?>
