<?php

class TFMPM_PageUtil extends TFMPM_Component
{
	protected $params;
	
	public function __construct( TFMPM_Registry $reg, array $params=[] ) {
		parent::__construct($reg);
		$this->params = $params;
	}
	
	public static function jsonResponse($status, $data, $headers=[]) {
		$headers += ['content-type'=>'application/json'];
		return Nife_Util::httpResponse($status, new EarthIT_JSON_PrettyPrintedJSONBlob($data), $headers);
	}
	
	public function emitHtmlBoilerplate($title, array $params=array()) {
		$this->emitView('html-boilerplate', array('title'=>$title) + $params);
	}
	public function emitHtmlFooter( array $params=array() ) {
		$this->emitView('html-footer', $params);
	}
	public function emitMessageBlock( array $params=array() ) {
		$this->emitView('success-message-block', $params);
		$this->emitView('error-message-block', $params);
	}
	
	protected function fortifyViewParams(array $params) {
		return $params + $this->params + ['PU' => $this];
	}
	
	protected function getActionContext() {
		if( !isset($this->params['actionContext']) ) {
			throw new Exception("No 'actionContext' available.");
		}
		return $this->params['actionContext'];
	}
	
	public function relativeUrl($target) {
		if( strlen($target) > 0 && $target[0] == '/' ) {
			$target = $this->getActionContext()->relativeUrl($target);
		}
		return $target;
	}
	
	public function linkHtml($target, $text) {
		$target = $this->relativeUrl($target);
		return "<a href=\"".htmlspecialchars($target)."\">".htmlspecialchars($text)."</a>";
	}
	
	protected function templateFile($viewName) {
		$file = $this->registry->viewTemplateDirectory.'/'.$viewName.".php";
		if( !file_exists($file) ) {
			throw new Exception("Template file for '$viewName', '$file', does not exist");
		}
		return $file;
	}
	
	public function viewBlob($viewName, $params=array()) {
		$params = $this->fortifyViewParams($params);
		$params['params'] = $params;
		return new EarthIT_FileTemplateBlob($this->templateFile($viewName), $params);
	}
	
	public function emitView($viewName, $params=array()) {
		$params = $this->fortifyViewParams($params);
		extract($params);
		include $this->templateFile($viewName);;
	}
	
	public function emitSelectOptions( array $options, $selectedValues ) {
		$selectedValues = TFMPM_Util::toSet($selectedValues);
		foreach( $options as $k => $v ) {
			echo "<option value=\"", htmlspecialchars($k), "\"",
				(isset($set[$k]) ? ' selected' : ''), ">",
				htmlspecialchars($v), "</option>\n";
		}
	}
}
