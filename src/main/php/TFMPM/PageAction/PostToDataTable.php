<?php

class TFMPM_PageAction_PostToDataTable extends TFMPM_PageAction
{
	protected $rc;
	protected $formData;
	
	public function __construct( TFMPM_Registry $reg, EarthIT_Schema_ResourceClass $rc, array $formData ) {
		parent::__construct($reg);
		$this->rc = $rc;
		$this->formData = $formData;
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		$formInfo = $this->dataTableFormModel->getMultiPostFormInfo($this->rc);
		// TODO: Should probably just iterate over form data
		// and match each one to a single-post form.
		// That way the number of items doesn't have to be limited to however
		// many the form showed.
		$this->formModel->populateInputValuesFromParameters( $formInfo, $this->formData );
		if( $this->formModel->validate($formInfo) ) {
			$data = $this->formModel->extractInputData($formInfo);
			$schemaData = EarthIT_CMIPREST_RESTItemCodec::getInstance()->decodeItems($data, $this->rc);
			$this->storage->saveItems( $schemaData, $this->rc, [
				// Only time we'll get duplicate keys is if they're content-based,
				// in which case we don't care that what we saved alreay existed.
				EarthIT_Storage_ItemSaver::ON_DUPLICATE_KEY => EarthIT_Storage_ItemSaver::ODK_KEEP
			] );
			return $this->redirect(303, '#');
		} else {
			$forwardTo = new TFMPM_PageAction_ShowDataTable($this->registry, $this->rc, null, $formInfo);
			return call_user_func($forwardTo, $actx);
		}
	}
}
