<?php

class PHPTemplateProjectNS_FormBlob extends Nife_AbstractBlob
{
	const INDENT = 'indent';
	const INDENT_DELTA = 'indentDelta';
	const INCLUDE_FORM_ELEMENT = 'includeFormElement';
	const FORM_ID = 'formId';
	const FORM_METHOD = 'formMethod';
	const FORM_ENCTYPE = 'formEncType';
	const FORM_ACTION = 'formAction';
	const SUBMIT_TITLE = 'submitTitle';
	
	protected $formInfo;
	protected $indent;
	protected $indentDelta;
	protected $idPrefix;
	protected $options;
	
	/** array value */
	protected static function av( array $options, $key, $defaultValue=null ) {
		return isset($options[$key]) ? $options[$key] : $defaultValue;
	}
	
	protected function optVal($key, $defaultValue=null) {
		return self::av($this->options, $key, $defaultValue);
	}
	
	public function __construct( array $formInfo, $options=array() ) {
		$this->formInfo    = $formInfo;
		$this->options     = $options;
		$this->indent      = self::av($options, self::INDENT, "");
		$this->indentDelta = self::av($options, self::INDENT_DELTA, "\t");
		$this->idPrefix    = self::av($options, self::FORM_ID, 'the-form').'-';
	}
	
	public function __get($k) {
		switch( $k ) {
		case 'formId': case 'formMethod': case 'formAction':
			return $this->optVal($k);
		}
		throw new Exception("No such property on FormBlob: $k");
	}
	
	protected static function formFieldName( $fieldName, $prefix='' ) {
		if( empty($prefix) ) return $fieldName;
		return "{$prefix}[{$fieldName}]";
	}
	
	protected function fieldsToPaxml( array $fields, $prefix='', array &$tableRows ) {
		foreach( $fields as $fieldName=>$fieldInfo ) {
			$inputName = self::formFieldName($fieldName, $prefix);
			$dtn = isset($fieldInfo['dataTypeNname']) ? $fieldInfo['dataTypeName'] :
				  (isset($fieldInfo['fields']) ? 'complex' : 'text');
			if( $dtn == 'complex' ) {
				$tr = ['tr', ['td', 'colspan'=>'3', $this->itemToPaxml($fieldInfo, $inputName)]];
			} else {
				$inputId = $this->idPrefix.$inputName;
				if( $dtn == 'boolean' ) {
					$inputPaxml = ['input', 'type'=>'checkbox', 'name'=>$inputName, 'id'=>$inputId];
					if( !empty($fieldInfo['inputValue']) ) $inputPaxml['checked'] = 'checked';
				} else if( isset($fieldInfo['possibleValues']) ) {
					$inputPaxml = ['select', 'name'=>$inputName, 'id'=>$inputId];
					foreach( $fieldInfo['possibleValues'] as $pv=>$pvText ) {
						$optionPaxml = ['option','value'=>$pv,$pvText];
						if( isset($fieldInfo['inputValue']) && $fieldInfo['inputValue'] == $pv ) {
							$optionPaxml['selected'] = 'selected';
						}
						$inputPaxml[] = $optionPaxml;
					}
				} else {
					$inputPaxml = ['input', 'type'=>'text', 'name'=>$inputName, 'id'=>$inputId];
					if( isset($fieldInfo['inputValue']) ) $inputPaxml['value'] = $fieldInfo['inputValue'];
					if( isset($fieldInfo['placeholderValue']) ) $inputPaxml['placeholder'] = $fieldInfo['placeholderValue'];
				}
				$tr = ['tr',
						 ['th', ['label', 'for'=>$inputId, isset($fieldInfo['title']) ? $fieldInfo['title'] : $fieldName]],
						 ['td', $inputPaxml]];
				$errorTd = null;
				if( !empty($fieldInfo['errors']) ) {
					$errorTd = ['td', 'class'=>'validation-error'];
					foreach( $fieldInfo['errors'] as $error ) {
						$errorTd[] = ['p', $error['message']];
					}
				}
				if( $errorTd ) $tr[] = $errorTd;
			}
			$tableRows[] = $tr;
		}
	}
	
	public function itemToPaxml( array $formInfo, $prefix='', array $opts=array() ) {
		$paxml = ['fieldset'];
		if( isset($formInfo['fieldsetClassName']) ) {
			$paxml['class'] = $formInfo['fieldsetClassName'];
		}
		if( isset($formInfo['title']) ) {
			$paxml[] = ['legend', $formInfo['title']];
		}
		if( isset($formInfo['fields']) ) {
			$tablePaxml = ['table', 'class'=>'form-fields'];
			$this->fieldsToPaxml( $formInfo['fields'], $prefix, $tablePaxml );
			if( self::av($opts,'includeSubmitButton') ) {
				$submit = ['input','type'=>'submit'];
				if( ($st = $this->optVal(self::SUBMIT_TITLE)) ) {
					$submit['value'] = $st;
				}
				$tablePaxml[] = ['tr','class'=>'submit-buttons',['td','colspan'=>'3',$submit]];
			}
			$paxml[] = $tablePaxml;
		}
		if( isset($formInfo['items']) ) {
			foreach( $formInfo['items'] as $itemKey=>$item ) {
				$paxml[] = $this->itemToPaxml( $item, self::formFieldName($itemKey, $prefix) );
			}
		}
		return $paxml;
	}
	
	public function toPaxml() {
		if( $this->optVal(self::INCLUDE_FORM_ELEMENT) ) {
			$formPaxml = ['form'];
			if( ($formId = $this->optVal(self::FORM_ID))     ) $formPaxml['id']      = $formId;
			if( ($method = $this->optVal(self::FORM_METHOD)) ) $formPaxml['method']  = $method;
			if( ($action = $this->optVal(self::FORM_ACTION)) ) $formPaxml['action']  = $action;
			if( ($eType  = $this->optVal(self::FORM_ENCTYPE))) $formPaxml['enctype'] = $eType;
			$itemPaxml = $this->itemToPaxml( $this->formInfo, '', array('includeSubmitButton'=>true) );
			$formPaxml[] = $itemPaxml;
			return $formPaxml;
		} else {
			return $this->itemToPaxml( $this->formInfo );
		}
	}
	
	//// Blob implementation
	
	public function getLength() { return null; }
	
	public function writeTo( $callback ) {
		$emitter = new EarthIT_PAXML_PAXMLEmitter();
		$emitter->emit( $this->toPaxml(), $this->indent, $this->indentDelta, $callback );
	}
}
