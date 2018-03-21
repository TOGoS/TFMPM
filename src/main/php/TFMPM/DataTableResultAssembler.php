<?php

class TFMPM_DataTableResultAssembler
extends TFMPM_Component
implements EarthIT_CMIPREST_ResultAssembler
{
	public function needsResult() { return true; }
	
	public function assembleResult( EarthIT_CMIPREST_ActionResult $result, TOGoS_Action $action=null, $ctx=null ) {
		$itemCols = $result->getItemCollections();
		return TFMPM_PageAction_ShowDataTable::getInstance($this->registry, $result->getRootResourceClass(), $itemCols['root']);
	}
	
	public function assembledResultToHttpResponse( $assembled, TOGoS_Action $action=null, $ctx=null ) {
		return call_user_func($assembled, $ctx);
	}
	
	public function exceptionToHttpResponse( Exception $e, TOGoS_Action $action=null, $ctx=null ) {
		// Could make a nice page,
		// but let's just let the root exception handler deal with it for now.
		throw $e;
	}
}
