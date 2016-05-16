<?php

use PHPTemplateProjectNS_FormBlob as FB;

class PHPTemplateProjectNS_PageAction_ShowDataTable extends PHPTemplateProjectNS_PageAction_TemplatePageAction
{
	protected $rc;
	protected $items;
	protected $postFormInfo;
	
	public static function getInstance( PHPTemplateProjectNS_Registry $reg, EarthIT_Schema_ResourceClass $rc, $items=null ) {
		$collectionName =
			$rc->getFirstPropertyValue(EarthIT_CMIPREST_NS::COLLECTION_NAME) ?:
			EarthIT_Schema_WordUtil::pluralize($rc->getName());
		
		$className = 'PHPTemplateProjectNS_PageAction_Show'.EarthIT_Schema_WordUtil::toPascalCase($collectionName);
		if( !class_exists($className) or !is_subclass_of($className, 'PHPTemplateProjectNS_PageAction_ShowDataTable') ) {
			$className = 'PHPTemplateProjectNS_PageAction_ShowDataTable';
		}
		return new $className( $reg, $rc, $items );
	}
	
	/**
	 * In addition to fetching the items itself,
	 * this action can be used to dump out a predefined set of items
	 * by passing a list of schema-form items to $items.
	 * 
	 * (The original use isn't even used anymore; the only reason this
	 * is a PageAction is to get its protected methods for making
	 * template responses)
	 */
	public function __construct( PHPTemplateProjectNS_Registry $reg, EarthIT_Schema_ResourceClass $rc, $items=null, array $postFormInfo=null ) {
		parent::__construct($reg);
		$this->rc = $rc;
		$this->items = $items;
		$this->postFormInfo = $postFormInfo;
	}
	
	protected function getItems() {
		return $this->items ?: $this->storageHelper->getItems($this->rc);
	}
	
	public function getTemplateName() { return 'data-table'; }
	public function getTemplateParameters() {
		$items = $this->getItems();
		
		$collectionName =
			$this->rc->getFirstPropertyValue(EarthIT_CMIPREST_NS::COLLECTION_NAME) ?:
			EarthIT_Schema_WordUtil::pluralize($this->rc->getName());
		
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
				if( $item[$fn] === null ) {
					$td['class'] = 'null';
					$dataString = '';					
				} else if( $item[$fn] === true ) {
					$dataString = 'true';
				} else if( $item[$fn] === false ) {
					$dataString = 'false';
				} else {
					$dataString = (string)$item[$fn];
				}
				$td[] = $dataString;
				$tr[] = $td;
			}
			$trs[] = $tr;
		}
		
		$tablePaxml = ['table', 'class'=>'bolly',
			['thead', array_merge(['tr'],$ths)],
			array_merge(['tbody'], $trs)
		];
		
		$formInfo = $this->postFormInfo ?: $this->dataTableFormModel->getMultiPostFormInfo($this->rc);
		
		$postForm = new PHPTemplateProjectNS_FormBlob($formInfo, array(
			FB::INCLUDE_FORM_ELEMENT => true,
			FB::FORM_ID => 'post-form',
			FB::FORM_METHOD => 'POST',
			FB::FORM_ACTION => '#post-form',
			FB::SUBMIT_TITLE => "Create new {$collectionName}",
		));
		
		return [
			'rc' => $this->rc,
			'collectionName' => $collectionName,
			'items' => $items,
			'tablePaxml' => $tablePaxml,
			'postForm' => $postForm
		];
	}
}
