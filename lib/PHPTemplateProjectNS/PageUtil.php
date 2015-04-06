<?php

class PHPTemplateProjectNS_PageUtil extends PHPTemplateProjectNS_Component
{
	public static function jsonResponse($status, $data, $headers=[]) {
		$headers += ['content-type'=>'application/json'];
		return Nife_Util::httpResponse($status, new EarthIT_JSON_PrettyPrintedJSONBlob($data), $headers);
	}
	
	public function emitHtmlBoilerplate($title) {
		$this->emitView('html-boilerplate', array('title'=>$title));
	}
	public function emitHtmlFooter() {
		$this->emitView('html-footer');
	}
	
	public function fortifyViewParams(array $params) {
		$params['PU'] = $this;
		return $params;
	}
	
	public function linkHtml($target, $text) {
		return "<a href=\"".htmlspecialchars($target)."\">".htmlspecialchars($text)."</a>";
	}
	
	public function emitView($viewName, $params=array()) {
		$params = $this->fortifyViewParams($params);
		extract($params);
		include PHPTemplateProjectNS_ROOT_DIR.'/views/'.$viewName.".php";
	}
	
	public function emitSelectOptions( array $options, $selectedValue ) {
		foreach( $options as $k => $v ) {
			echo "<option value=\"", htmlspecialchars($k), "\"",
				($selectedValue == $k ? ' selected' : ''), ">",
				htmlspecialchars($v), "</option>\n";
		}
	}
}
