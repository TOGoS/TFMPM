<?php

class PHPTemplateProjectNS_PageAction extends EarthIT_Component
{
	protected function queryRows( $sql, $params=array() ) {
		return $this->registry->getDbAdapter()->fetchAll($sql,$params);
	}
	protected function queryRow( $sql, $params=array() ) {
		foreach( $this->queryRows($sql,$params) as $row ) return $row;
		return null;
	}
	
	protected function makeTemplateResponse( $statusCode=200, $viewName, $vars=array(), $typeOrHeaders='text/html' ) {
		$vars = $this->registry->getComponent('PHPTemplateProjectNS_PageUtil')->fortifyViewParams($vars);
		$templateFile = PHPTemplateProjectNS_ROOT_DIR.'/views/'.$viewName.".php";
		if( !file_exists($templateFile) ) {
			throw new Exception("View template file '{$templateFile}' does not exist!");
		}
		$blob = new EarthIT_FileTemplateBlob($templateFile, $vars);
		return Nife_Util::httpResponse( $statusCode, $blob, $typeOrHeaders );
	}
}
