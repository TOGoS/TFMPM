<?php

class TFMPM_PageAction_ShowHello extends TFMPM_PageAction_TemplatePageAction
{
	public function getTemplateName() { return 'hello'; }
	public function getTemplateParameters() {
		$helloUri = "hello/".rawurlencode("TOGoS's Factorio Map Preview Manager");

		$dataTableRows = [];
		foreach( $this->schema->getResourceClasses() as $rc ) {
			if( !$rc->hasRestService() ) continue;
			$collectionName = ucfirst(
				$rc->getFirstPropertyValue(EarthIT_CMIPREST_NS::COLLECTION_NAME) ?:
				EarthIT_Schema_WordUtil::pluralize($rc->getName()));
			$dashName = str_replace(' ','-',strtolower($collectionName));
			$restServiceLinks[] = "<li><a href=\"api/".htmlspecialchars($dashName)."\">".htmlspecialchars($collectionName)."</a></li>";
			$tableLinks[] = "<li><a href=\"".htmlspecialchars($dashName)."\">".htmlspecialchars($collectionName)."</a></li>";
			$dataTableRows[] = ['tr',
				['td', ['a', 'href'=>$dashName, $collectionName]],
				['td', ['a', 'href'=>"api/{$dashName}", 'JSON']],
			];
		}
		$dataTablePaxml = array_merge( ['table', 'class'=>'bolly'], $dataTableRows );
		
		$otherStuff = [
			'Number of users' => $this->storageHelper->queryValue("SELECT COUNT(*) FROM tfmpm.user"),
			'Something from the ABC decoder' => $this->abcDecoder->getAbc(),
		];
		
		$otherLinks = [
			'Do square roots really slowly' => 'computations',
			'Register awn are sight!' => 'register'
		];
		
		return [
			'dataTablePaxml' => $dataTablePaxml,
			'helloUri' => $helloUri,
			'otherStuff' => $otherStuff,
			'otherLinks' => $otherLinks
		];
	}
}
