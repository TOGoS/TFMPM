<?php
	$PU->emitHtmlBoilerplate("Welcome to TFMPM!", $params + array('extra'=>array(
		count($maps)." maps loaded"
	)));
?>

<script type="text/javascript" src="map-comparison.js"></script>

<div id="help" class="overlay" style="display:none">
<h3>Halp!</h3>
</div>

<style scoped>/*<![CDATA[*/
#info-overlay {
	display: grid;
	grid-gap: 10px;
}
#map-info-table {
	grid-row: 1;
	grid-column: 1 / 2;
	border-right: var(--separator-width) solid var(--separator-color);
}
#map-resource-table {
	grid-row: 1;
	grid-column: 2 / 3;
}
#map-navigation-table {
	border-top: var(--separator-width) solid var(--separator-color);
	grid-row: 2;
	grid-column: 1 / 3;
}
/*]]>*/</style>

<div id="info-overlay" class="overlay" style="display: grid">

<table id="map-info-table">
<tbody id="map-info-tbody">
<tr id="cursor-position-tr">
  <td>Cursor Position</td>
  <td id="cursor-position">&nbsp;</td>
</tr>
</tbody>
</table>

<table id="map-resource-table">
<tbody id="map-resource-tbody">
</tbody>
</table>

<table id="map-navigation-table">
<tbody id="map-navigation-tbody">
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
		schema: <?php EarthIT_JSON::prettyPrint($schema, Nife_Util::getEchoFunction(), "\n\t\t"); ?>,
		mapContainer: document.getElementById('map-container'),
		mapImageElement: document.getElementById('map-image'),
		cursorPositionElement: document.getElementById('cursor-position'),
		mapInfoTable: document.getElementById('map-info-table'),
		mapInfoTbody: document.getElementById('map-info-tbody'),
		mapResourceTbody: document.getElementById('map-resource-tbody'),
		mapNavigationTbody: document.getElementById('map-navigation-tbody'),
		backgroundElement: document.getElementById('main-content-div'),
	});
	window.addEventListener('mousemove', mcUi.onMouseMove.bind(mcUi));
	window.addEventListener('keydown', mcUi.onKey.bind(mcUi));
	mcUi.mapImageElement.addEventListener('wheel', mcUi.onWheel.bind(mcUi));
	mcUi.start();
})();
//]]></script>

<?php $PU->emitHtmlFooter(); ?>
