<?php

class TFMPM_PageAction_FileUpload extends TFMPM_PageAction
{
	protected $req;
	protected $sector;
	protected $allowSectorOverride;
	
	public function __construct( TFMPM_Registry $reg, TFMPM_Request $req, array $options=array() ) {
		parent::__construct($reg);
		$this->req = $req;
		$this->sector = isset($options['sector']) ? $options['sector'] : null;
		$this->allowSectorOverride = !empty($options['allowSectorOverride']);
	}
	
	public function isAllowed( TFMPM_ActionContext $actx, &$status, array &$notes=[] ) {
		if( $actx->getLoggedInUserId() === null ) {
			$status = 403;
			$notes[] = "You must be logged in to upload stuff.";
			return false;
		}
		return true;
	}
	
	protected function isMultipart() {
		return substr($this->req->getRequestContentType(),0,19) === 'multipart/form-data';
	}
	
	protected function getFiles() {
		$files = array();
		foreach( $this->req->FILES as $file ) {
			if( is_array($file['name']) ) {
				$fixed = [];
				foreach( $file as $k=>$arr ) {
					foreach( $arr as $idx=>$v ) {
						$fixed[$idx][$k] = $v;
					}
				}
				foreach( $fixed as $file ) $files[] = $file;
			} else {
				$files[] = $file;
			}
		}
		return $files;
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		$repo = $this->primaryBlobRepository;
		$uploaded = array();
		$notes = array();
		$errors = array();
		$sector = $this->sector;
		if(
			$this->allowSectorOverride &&
			isset($this->req->SERVER['HTTP_X_CCOUCH_SECTOR']) &&
			preg_match('/^[a-zA-Z0-9-]+$/', $this->req->SERVER['HTTP_X_CCOUCH_SECTOR'])
		) {
			$sector = $this->req->SERVER['HTTP_X_CCOUCH_SECTOR'];
		}
		if( $this->isMultipart() ) {
			foreach( $this->getFiles() as $k=>$f ) {
				try {
					$tempFile = $f['tmp_name'];
					
					if( !$tempFile ) {
						$errors[] = "Failed to upload '{$f['name']}'; this is usually because the file is bigger than some limit in php.ini.";
						continue;
					}
					
					$size = filesize($tempFile);
					$urn = $repo->putTempFile( $tempFile, $sector );
					$blobId = TFMPM_BlobIDUtil::urnToBasename($urn);
					
					$filenameHint = $f['name'];
					
					$uploaded[$k] = array(
						'URN' => $urn,
						'filename' => $filenameHint,
						'URL' => $this->blobUrl($urn, $filenameHint, $actx),
						'blob ID' => $blobId,
						'size' => $size,
					);
				} catch( Exception $e ) {
					$errors[] = "Error receiving {$f['name']}: ".$e->getMessage();
				}
			}
		} else {
			$stream = fopen('php://input', 'rb');
			try {
				$urn = $this->primaryBlobRepository->putStream( $stream, $sector );
			} catch( TOGoS_PHPN2R_IdentifierFormatException $e ) {
				return Nife_Util::httpResponse(409, $e->getMessage());
			}
			$blobId = TFMPM_BlobIDUtil::urnToBasename($urn);

			// Conceptually this should include most of the same
			// information (maybe minus filename), but blob ID/URN are
			// the important parts.

			$uploaded[] = array(
				'URN' => $urn,
				'blob ID' => $blobId,
				'URL' => $this->blobUrl($urn, null, $actx),
			);
		}
		
		$responseObject = ['uploaded' => [], 'errors' => [], 'notes' => []];
		
		foreach( $uploaded as $uploaded ) {
			$ccUpload = [];
			foreach( $uploaded as $k=>$v ) {
				$ccUpload[EarthIT_Schema_WordUtil::toCamelCase($k)] = $v;
			}
			$responseObject['uploaded'][] = $ccUpload;
		}
		
		return Nife_Util::httpResponse(
			"201 Blobs Stored",
			new EarthIT_JSON_PrettyPrintedJSONBlob($responseObject),
			['content-type'=>'application/json']);
	}
}
