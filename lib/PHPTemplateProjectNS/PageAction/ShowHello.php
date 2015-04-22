<?php

class PHPTemplateProjectNS_PageAction_ShowHello extends PHPTemplateProjectNS_PageAction_TemplatePageAction
{
	public function getTemplateName() { return 'hello'; }
	public function getTemplateParameters() {
		$helloUri = "hello/".rawurlencode("PHP Template Project");
		
		$classLinks = array();
		foreach( $this->schema->getResourceClasses() as $rc ) {
			if( !$rc->hasRestService() ) continue;
			$collectionName = ucfirst(EarthIT_Schema_WordUtil::pluralize($rc->getName()));
			$dashName = str_replace(' ','-',strtolower($collectionName));
			$classLinks[] = "<li><a href=\"api/".htmlspecialchars($dashName)."\">".htmlspecialchars($collectionName)."</a></li>";
		}
		
		$otherStuff = [
			'Number of users' => $this->storageHelper->queryValue("SELECT COUNT(*) FROM phptemplateprojectdatabasenamespace.user"),
			'Something from the ABC decoder' => $this->abcDecoder->getAbc(),
		];
		
		$otherLinks = [
			'Do square roots really slowly' => 'computations'
		];
		
		return ['classLinks'=>$classLinks, 'helloUri'=>$helloUri, 'otherStuff'=>$otherStuff, 'otherLinks'=>$otherLinks];
	}
}
