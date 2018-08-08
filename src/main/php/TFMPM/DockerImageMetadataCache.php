<?php

class TFMPM_DockerImageMetadataCache
extends TFMPM_Component
{
	protected $imageMetadatas = array();
	public function getImageMetadata($tag) {
		if( isset($this->imageMetadatas[$tag]) ) return $this->imageMetadatas[$tag];

		ob_start();
		$status = $this->systemUtil->runCommand(array('docker','inspect',$tag), array(
			'onNz'=>'return'
		));
		$imageMetadataJson = ob_get_clean();

		if( $status == 0 ) {
			$mds = EarthIT_JSON::decode($imageMetadataJson);
			if( isset($mds[0]) ) {
				return $this->imageMetadatas[$tag] = $mds[0];
			}
		}
		return $this->imageMetadatas[$tag] = false;
	}
	
	public function doesDockerImageExist($tag) {
		$info = $this->getImageMetadata($tag);
		return !empty($info);
	}
}
