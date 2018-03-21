<?php

class TFMPM_HashUtil extends TFMPM_Component
{
	protected function getHjsPkFields( $rc ) {
		$rc = $this->rc($rc);
		$keys = $rc->getIndexes();
		$hjsPkFields = array();
		if( isset($keys['primary']) ) {
			foreach( $keys['primary']->getFieldNames() as $fn ) {
				$field = $rc->getField($fn);
				if( $field->getFirstPropertyValue('http://ns.nuke24.net/Schema/RDB/isContentHashBasedId',false) === true ) {
					$hjsPkFields[$fn] = $field;
				}
			}
		}
		return $hjsPkFields;
	}
	
	public function canonicalizeItems( array $items, $rc ) {
		$rc = $this->rc($rc);
		
		$fieldTransforms = array();
		foreach( $rc->getFields() as $fn=>$f ) {
			$xf = array();
			if( $f->getFirstPropertyValue('http://ns.nuke24.net/Schema/Application/usesUserTextCanonicalizationRules',false) === true ) {
				$xf[] = 'utcr';
			}
			$fieldTransforms[$fn] = $xf;
		}
		
		$fixedItems = array();
		foreach( $items as $k=>$item ) {
			$fixedItem = array();
			foreach( $fieldTransforms as $fn=>$xfs ) {
				$storeNull = true;
				$v = isset($item[$fn]) ? $item[$fn] : null;
				foreach( $xfs as $xf ) {
					switch( $xf ) {
					case 'utcr':
						$storeNull = false;
						break;
					default:
						throw new Exception("Bad field transform: $xf");
					}
				}
				if( $storeNull or $v !== null ) {
					$fixedItem[$fn] = $v;
				}
			}
			$fixedItems[$k] = $fixedItem;
		}
		return $fixedItems;
	}
	
	protected function reallyFixItemsForSaving( array $items, $rc, array $hjsPkFields ) {
		$items = $this->canonicalizeItems($items, $rc);
		$fnMap = array();
		foreach( $rc->getFields() as $fn=>$f ) {
			if( isset($hjsPkFields[$fn]) ) continue; // We will generate it!
			$fnMap[$fn] = EarthIT_Schema_WordUtil::toCamelCase($fn);
		}
		asort($fnMap);
		
		$jsoItems = array();
		foreach( $items as $k=>$item ) {
			$jsoItem = array();
			foreach( $fnMap as $sfn=>$jfn ) {
				if( array_key_exists($sfn,$item) ) {
					$jsoItem[$jfn] = $item[$sfn];
				}
			}
			$serialized = EarthIT_JSON::prettyEncode($jsoItem)."\n";
			$id = TOGoS_Base32::encode(sha1($serialized,true));
			// At this point we might actually save the serialized item to some blobstore
			// if we wanted.
			foreach( $hjsPkFields as $fn=>$f ) {
				// There shouldn't be more than one of these,
				// but if there are, whatever.
				$items[$k][$fn] = $id;
			}
		}
		return $items;
	}
	
	public function fixItemsForSaving( array $items, $rc ) {
		$rc = $this->rc($rc);
		$hjsPkFields = $this->getHjsPkFields($rc);
		if( count($hjsPkFields) > 0 ) {
			$items = $this->reallyFixItemsForSaving($items, $rc, $hjsPkFields);
		}
		return $items;
	}
}
