<?php
	$PU->emitHtmlBoilerplate("Welcome to TFMPM!", $params + array('extra'=>array(
		'Hit ? for help',
		count($maps)." maps loaded"
	)));
?>

<script type="text/javascript" src="map-comparison.js"></script>

<div id="help" class="overlay" style="display:none">
<h3>Halp!</h3>
</div>

<div id="info-overlay" class="overlay">
<table id="map-info-table">
<tbody id="map-info-tbody">
<tr id="cursor-position-tr">
  <td>Cursor Position</td>
  <td id="cursor-position">&nbsp;</td>
</tr>
</tbody>
</table>
</div>

<div id="map-container">
<img id="map-image" width="1024" height="1024"/>
</div>

<script type="text/javascript">//<![CDATA[
(function() {
	var mcUi = new window.tfmpm.MapComparisonUI({
		maps: <?php EarthIT_JSON::prettyPrint($maps, Nife_Util::getEchoFunction(), "\n\t\t"); ?>,
		mapContainer: document.getElementById('map-container'),
		mapImageElement: document.getElementById('map-image'),
		cursorPositionElement: document.getElementById('cursor-position'),
		mapInfoTable: document.getElementById('map-info-table'),
		mapInfoTbody: document.getElementById('map-info-tbody'),
		backgroundElement: document.getElementById('main-content-div'),
	});
	window.addEventListener('mousemove', mcUi.onMouseMove.bind(mcUi));
	window.addEventListener('keydown', mcUi.onKey.bind(mcUi));
	mcUi.mapImageElement.addEventListener('wheel', mcUi.onWheel.bind(mcUi));
	mcUi.start();
})();
//]]></script>

<?php $PU->emitHtmlFooter(); ?>
