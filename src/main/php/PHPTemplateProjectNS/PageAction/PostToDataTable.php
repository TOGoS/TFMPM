<?php

class PHPTemplateProjectNS_PageAction_PostToDataTable extends PHPTemplateProjectNS_PageAction
{
	protected $rc;
	protected $formData;
	
	public function __construct( PHPTemplateProjectNS_Registry $reg, EarthIT_Schema_ResourceClass $rc, array $formData ) {
		parent::__construct($reg);
		$this->rc = $rc;
		$this->formData = $formData;
	}
	
	public function __invoke( PHPTemplateProjectNS_ActionContext $actx ) {
		$formInfo = $this->dataTableFormModel->getMultiPostFormInfo($this->rc);
		// TODO: Should probably just iterate over form data
		// and match each one to a single-post form.
		// That way the number of items doesn't have to be limited to however
		// many the form showed.
		$this->formModel->populateInputValuesFromParameters( $formInfo, $this->formData );
		if( $this->formModel->validate($formInfo) ) {
			$data = $this->formModel->extractInputData($formInfo);
			$schemaData = EarthIT_CMIPREST_RESTItemCodec::getInstance()->decodeItems($data, $this->rc);
			$this->storage->saveItems( $schemaData, $this->rc );
			return $this->redirect(303, '#');
		} else {
			$forwardTo = new PHPTemplateProjectNS_PageAction_ShowDataTable($this->registry, $this->rc, null, $formInfo);
			return call_user_func($forwardTo, $actx);
		}
	}
}
