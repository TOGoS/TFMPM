<?php $PU->emitHtmlBoilerplate("Welcome to TFMPM!", $params + array('extra'=>'Hit ? for help')); ?>

<script type="text/javascript" src="map-comparison.js"></script>

<div id="help" class="overlay" style="display:none">
<h3>Halp!</h3>
</div>

<table id="map-info-table" class="overlay">
<tbody id="map-info-tbody">
</tbody>
</table>

<div id="map-container">
<img id="map-image" width="1024" height="1024"/>
</div>

<script type="text/javascript">//<![CDATA[
(function() {
	var mcUi = new window.tfmpm.MapComparisonUI({
		maps: <?php EarthIT_JSON::prettyPrint($maps, Nife_Util::getEchoFunction(), "\n\t\t"); ?>,
		mapContainer: document.getElementById('map-container'),
		mapImageElement: document.getElementById('map-image'),
		mapInfoTable: document.getElementById('map-info-table'),
		mapInfoTbody: document.getElementById('map-info-tbody'),
	});
	mcUi.start();
})();
//]]></script>

<?php $PU->emitHtmlFooter(); ?>
