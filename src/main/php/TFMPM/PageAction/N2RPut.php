<?php

class TFMPM_PageAction_N2RPut extends TFMPM_PageAction
{
	protected $urn;
	protected $req;
	
	public function __construct( TFMPM_Registry $reg, $urn, TFMPM_Request $req ) {
		parent::__construct($reg);
		$this->urn = $urn;
		$this->req = $req;
	}
	
	public function isAllowed( TFMPM_ActionContext $actx, &$status, array &$notes=[] ) {
		if( $actx->getLoggedInUserId() === null ) {
			$status = 403;
			$notes[] = "You must be logged in to upload stuff.";
			return false;
		}
		return true;
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		$stream = fopen('php://input', 'rb');
		try {
			$this->primaryBlobRepository->putStream( $stream, 'uploaded', $this->urn );
		} catch( TOGoS_PHPN2R_IdentifierFormatException $e ) {
			return Nife_Util::httpResponse(409, $e->getMessage());
		} catch( TOGoS_PHPN2R_HashMismatchException $e ) {
			return Nife_Util::httpResponse(409, $e->getMessage());
		}
		$blobId = TOGoS_PHPN2R_FSSHA1Repository::urnToBasename($this->urn);
		$responseObject = array(
			'urn' => $this->urn,
			'blobId' => $blobId
		);
		return Nife_Util::httpResponse("201 Blob Stored", new EarthIT_JSON_PrettyPrintedJSONBlob($responseObject), [
			'content-type'=>'application/json', 'etag'=>'"'.$blobId.'"']);
	}
}
