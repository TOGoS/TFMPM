<?php

class PHPTemplateProjectNS_DataTableFormModel extends PHPTemplateProjectNS_Component
{
	protected function isAutogeneratedId(EarthIT_Schema_Field $f) {
		return (
			$f->getFirstPropertyValue('http://ns.nuke24.net/Schema/RDB/defaultValueSequence') !== null or
			$f->getFirstPropertyValue('http://ns.nuke24.net/Schema/RDB/isAutoIncremented') or
			$f->getFirstPropertyValue('http://ns.nuke24.net/Schema/RDB/isContentHashBasedId')
		);
	}
	
	public function getMultiPostFormInfo($rc) {
		$rc = $this->rc($rc);
		
		$postForm = null;
		$itemInfo = ['dataTypeName'=>'complex', 'onEmpty'=>'void'];
		foreach( $rc->getFields() as $fn=>$f ) {
			if( $this->isAutoGeneratedId($f) ) continue;
			$ffn = EarthIT_Schema_WordUtil::toCamelCase($fn);
			$fieldDataType = 'text'; // TODO: Not always
			$fieldInfo = [
				'dataType' => $fieldDataType,
				'title' => ucfirst($f->getName()),
			];
			$itemInfo['fields'][$ffn] = $fieldInfo;
		}
		
		$rcName = $rc->getName();
		$collectionName =
			$rc->getFirstPropertyValue('http://ns.earthit.com/CMIPREST/collectionName') ?:
			EarthIT_Schema_WordUtil::pluralize($rcName);
		
		$formInfo = [ 'fields' => [] ];
		for( $i=0; $i<5; ++$i ) $formInfo['fields']["item{$i}"] = $itemInfo + ['title' => "New {$rcName} #{$i}" ];
		
		return $formInfo;
	}
}
