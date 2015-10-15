<?php

class PHPTemplateProjectNS_PageAction_ShowDataTable extends PHPTemplateProjectNS_PageAction_TemplatePageAction
{
	protected $rc;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, EarthIT_Schema_ResourceClass $rc ) {
		parent::__construct($reg);
		$this->rc = $rc;
	}
	
	public function getTemplateName() { return 'data-table'; }
	public function getTemplateParameters() {
		$items = $this->storageHelper->getItems($this->rc);
		
		$collectionName = ucfirst(
			$this->rc->getFirstPropertyValue(EarthIT_CMIPREST_NS::COLLECTION_NAME) ?:
			EarthIT_Schema_WordUtil::pluralize($this->rc->getName()));
		
		$ths = [];
		$trs = [];
		$fields = $this->rc->getFields();
		foreach( $fields as $fn=>$f ) {
			$ths[] = ['th', $fn];
		}
		foreach( $items as $item ) {
			$tr = ['tr'];
			foreach( $fields as $fn=>$f ) {
				$td = ['td'];
				// TODO: Better way of determining if a field is numeric
				if( $f->getType()->getName() == 'entity ID' ) {
					$td['align'] = 'right';
				}
				$td[] = $item[$fn];
				$tr[] = $td;
			}
			$trs[] = $tr;
		}
		
		$tablePaxml = ['table', 'class'=>'bolly',
			['thead', array_merge(['tr'],$ths)],
			array_merge(['tbody'], $trs)
		];
		
		return [
			'rc' => $this->rc,
			'collectionName' => $collectionName,
			'items' => $items,
			'tablePaxml' => $tablePaxml
		];
	}
}
