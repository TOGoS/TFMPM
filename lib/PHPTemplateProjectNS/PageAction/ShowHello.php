<?php

class PHPTemplateProjectNS_PageAction_ShowHello extends PHPTemplateProjectNS_PageAction
{
	public function __invoke() {
		$helloUri = "hello/".rawurlencode("PHP Template Project");
		
		$classLinks = array();
		foreach( $this->schema->getResourceClasses() as $rc ) {
			if( !$rc->hasRestService() ) continue;
			$collectionName = ucfirst(EarthIT_Schema_WordUtil::pluralize($rc->getName()));
			$dashName = str_replace(' ','-',strtolower($collectionName));
			$classLinks[] = "<li><a href=\"api/".htmlspecialchars($dashName)."\">".htmlspecialchars($collectionName)."</a></li>";
		}
		
		return $this->makeTemplateResponse(200, 'hello', array('classLinks'=>$classLinks, 'helloUri'=>$helloUri));
	}
}
