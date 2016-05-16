<?php

class PHPTemplateProjectNS_FormModel extends PHPTemplateProjectNS_Component
{
	public function dataTypeName( array $formInfo ) {
		if( isset($formInfo['dataTypeName']) ) return $formInfo['dataTypeName'];
		return isset($formInfo['fields']) ? 'complex' : 'text';
	}
	
	protected function inputIsEmpty( array $formInfo ) {
		switch( $this->dataTypeName($formInfo) ) {
		case 'complex':
			foreach( $formInfo['fields'] as $fn=>$fi ) {
				if( !$this->inputIsEmpty($fi) ) return false;
			}
			return true;
		default:
			return !isset($formInfo['inputValue']) || trim($formInfo['inputValue']) == '';
		}
	}
	
	/**
	 * Returns true if valid, false otherwise.
	 * Updates $formInfo in-place to add validation errors,
	 * remove empty records.
	 */
	public function validate( array &$formInfo ) {
		$okay = true;
		// If an outer form has onEmpty = 'null'
		// and an inner one has onEmpty = 'error',
		// then as long as the entire thing is empty, there is no error
		if( $this->inputIsEmpty($formInfo) ) {
			if( isset($formInfo['onEmpty']) ) {
				switch( $formInfo['onEmpty'] ) {
				case 'blank': case 'null': case 'void':
					// Is explicitly valid even if sub-items have onEmpty = error
					return true;
				case 'error':
					$formInfo['errors'][] = "May not be empty";
					$okay = false;
					break;
				default:
					throw new Exception("Unrecognized 'onEmpty' behavior: '{$formInfo['onEmpty']}'");
				}
			}
		}
		
		switch( ($dt = $this->dataTypeName($formInfo)) ) {
		case 'complex':
			foreach( $formInfo['fields'] as $fn=>$_ ) {
				$okay &= $this->validate($formInfo['fields'][$fn]);
			}
			break;
		case 'text':
			// TODO: match regex
			break;
		case 'boolean':
			// TODO: Ensure valid boolean representation, convert to true/false
			break;
		default:
			// Don't know how to validate!
			throw new Exception("Don't know how to validate form field of type '{$dt}'");
		}
		return $okay;
	}
	
	public function extractInputData( $formInfo, &$removeKey=false ) {
		if( $this->inputIsEmpty($formInfo) ) {
			if( isset($formInfo['onEmpty']) ) {
				switch( $formInfo['onEmpty'] ) {
				case 'blank':
					// Do nothing!
					break;
				case 'null':
					return null;
				case 'void':
					$removeKey = true;
					return null;
				case 'error':
					// Should have been caught by validate()
					throw new Exception("onEmpty = error (should have been caught by validation)");
				default:
					throw new Exception("Unrecognized 'onEmpty' behavior: '{$formInfo['onEmpty']}'");
				}
			}
		}
		if( $this->dataTypeName($formInfo) == 'complex' ) {
			$data = array();
			foreach( $formInfo['fields'] as $k=>$v ) {
				$removeKey = false;
				$data[$k] = $this->extractInputData($v, $removeKey);
				if( $removeKey ) unset($data[$k]);
			}
			return $data;
		} else {
			return isset($formInfo['inputValue']) ? $formInfo['inputValue'] : '';
		}
	}
	
	public function populateInputValuesFromParameters( array &$formInfo, $parameters ) {
		if( is_array($parameters) ) {
			foreach( $parameters as $k=>$v ) {
				if( isset($formInfo['fields'][$k]) ) {
					$this->populateInputValuesFromParameters($formInfo['fields'][$k], $v);
				}
			}
		} else {
			$formInfo['inputValue'] = $parameters === null ? '' : trim((string)$parameters);
		}
	}
}
