<?php

class TFMPM_JohnResultAssemblyUtil
{
	public static function collectJohns( $branches, $prefix, array $johns=array(), array &$paths=array() ) {
		$paths[$prefix] = $johns;
		foreach( $branches as $k=>$johnTreeNode ) {
			$johns2 = $johns;
			$johns2[] = $johnTreeNode->getJohn();
			self::collectJohns( $johnTreeNode->getBranches(), $prefix.'.'.$k, $johns2, $paths );
		}
		return $paths;
	}
	
	protected static function _q45( EarthIT_Schema_ResourceClass $rc, array $items ) {
		$restObjects = array();
		foreach( $items as $item ) {
			if( ($itemId = EarthIT_CMIPREST_Util::itemId($rc, $item)) !== null ) {
				$restObjects[$itemId] = $item;
			} else {
				$restObjects[] = $item;
			}
		}
		return $restObjects;
	}
	
	public static function assembleMultiItemResult( EarthIT_Schema_ResourceClass $rootRc, array $johnCollections, array $relevantObjects ) {
		$relevantRestObjects = array();
		foreach( $johnCollections as $path => $johns ) {
			// Figure out what resource class of items we got, here
			$targetRc = count($johns) == 0 ? $rootRc : $johns[count($johns)-1]->targetResourceClass;
			$relevantRestObjects[$path] = self::_q45( $targetRc, $relevantObjects[$path] );
		}
		
		$assembled = array();
		
		// Assemble!
		foreach( $johnCollections as $path => $johns ) {
			$pathParts = explode('.',$path);
			if( count($pathParts) == 1 ) {
				foreach( $relevantRestObjects[$path] as $k=>$obj ) {
					$assembled[$k] =& $obj;
				}
			} else {
				$lastPathPart = $pathParts[count($pathParts)-1];
				$originPath = implode('.',array_slice($pathParts,0,count($pathParts)-1));
				$j = $johns[count($johns)-1];
				$plural = $j->targetIsPlural();
				$originRc = $j->originResourceClass;
				$targetRc = $j->targetResourceClass;
				$matchFields = array(); // target field rest name => origin field rest name
				for( $li=0; $li<count($j->originLinkFields); ++$li ) {
					$targetFieldName = $j->targetLinkFields[$li]->getName();
					$originFieldName = $j->originLinkFields[$li]->getName();
					$matchFields[$targetFieldName] = $originFieldName;
				}
				foreach( $relevantRestObjects[$originPath] as $ok=>$ov ) {
					$relations = array();
					foreach( $relevantRestObjects[$path] as $tk=>$tv ) {
						$matches = true;
						foreach( $matchFields as $trf=>$orf ) {
							if( $tv[$trf] != $ov[$orf] ) $matches = false;
						}
						if( $matches ) {
							$relations[$tk] =& $relevantRestObjects[$path][$tk];
						}
					}
					if( $plural ) {
						$relevantRestObjects[$originPath][$ok][$lastPathPart] = $relations;
					} else {
						$relevantRestObjects[$originPath][$ok][$lastPathPart] = null;
						foreach( $relations as $k=>$_ ) {
							$relevantRestObjects[$originPath][$ok][$lastPathPart] =& $relations[$k];
							break;
						}
					}
				}
			}
		}
		
		return $relevantRestObjects['root'];
	}
}
