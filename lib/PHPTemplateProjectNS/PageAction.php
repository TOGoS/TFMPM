<?php

class PHPTemplateProjectNS_PageAction extends PHPTemplateProjectNS_Component
{
	protected function makeTemplateResponse( $statusCode=200, $viewName, $vars=array(), $typeOrHeaders='text/html' ) {
		$vars = $this->pageUtil->fortifyViewParams($vars);
		$templateFile = PHPTemplateProjectNS_ROOT_DIR.'/views/'.$viewName.".php";
		if( !file_exists($templateFile) ) {
			throw new Exception("View template file '{$templateFile}' does not exist!");
		}
		$blob = new EarthIT_FileTemplateBlob($templateFile, $vars);
		return Nife_Util::httpResponse( $statusCode, $blob, $typeOrHeaders );
	}
}
