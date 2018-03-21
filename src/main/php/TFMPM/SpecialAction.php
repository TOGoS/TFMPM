<?php

/**
 * Base class for actions that know how to #__invoke themselves
 * and know whether or not they #isAllowed.
 * 
 * SpecialActions extend Component, and thereby keep a reference to a Registry,
 * as a matter of convenience.  Also, old code relies on it.
 * 
 * For these to be more pure representations of actions,
 * it might be better to not keep a reference to the registry,
 * and instead use the registry from the actioncontext.
 * Since the action context gets passed into __invoke,
 * you might rely on that instead of $this->registry to future-proof your code.
 * 
 * #jsonSerialize explicitly removes the registry
 * from the object properties for this reason.
 */
abstract class TFMPM_SpecialAction extends TFMPM_Component implements TOGoS_Action
{
	public function jsonSerialize() {
		$props = ['phpClassName' => get_class($this)] + get_object_vars($this);
		unset($props['registry']);
		return $props;
	}
	
	public function __toString() {
		try {
			return EarthIT_JSON::prettyEncode(TFMPM_Util::jsonSerializeRecursively($this));
		} catch( Exception $e ) {
			return get_class($this).":(error while encoding properties: ".$e->getMessage().")";
		}
	}
	
	/**
	 * Get standard template variables from the action context (logged in user, etc)
	 */
	protected function contextTemplateVars( TFMPM_ActionContext $actx, array &$into=array() ) {
		$userId = $actx->getLoggedInUserId();
		$into['actionContext'] = $actx;
		$into['loggedInUser'] = $userId === null ? null : $this->storageHelper->getItem('user', array('ID'=>$userId));
		return $into;
	}
	
	protected function templateResponse( $statusCode=200, $viewName, $vars=array(), $typeOrHeaders=null, TFMPM_ActionContext $actx=null ) {
		if( $actx !== null ) $vars += $this->contextTemplateVars($actx);
		$pageUtil = new TFMPM_PageUtil($this->registry, $vars);
		$blob = $pageUtil->viewBlob($viewName);
		if( $typeOrHeaders === null ) $typeOrHeaders = array('content-type'=>'text/html; charset=utf-8');
		return Nife_Util::httpResponse( $statusCode, $blob, $typeOrHeaders );
	}
	
	protected function jsonResponse($statusCode, $thing) {
		return Nife_Util::httpResponse( $statusCode, new EarthIT_JSON_PrettyPrintedJSONBlob($thing), "application/json" );
	}
	
	protected function redirect( $statusCode, $location ) {
		return Nife_Util::httpResponse($statusCode, "Redirecting to $location...", ['location'=>$location]);
	}
	
	protected function getErrorMessage( $messageId, TFMPM_ActionContext $actx ) {
		$message = $actx->getSessionVariable('errorMessage');
		return hash('sha1',$message) == $messageId ? $message : null;
	}
	
	protected function redirectWithErrorMessage( $location, $message, TFMPM_ActionContext $actx ) {
		if( is_array($message) ) $message = implode("\x1e", $message);
		$actx->setSessionVariable('errorMessage', $message);
		$messageHash = hash('sha1', $message);
		return $this->redirect(303, "{$location}?error-message-id={$messageHash}");
	}
	
	public function isAllowed( TFMPM_ActionContext $actx, &$status, array &$notes=[] ) {
		return true;
	}
	
	/**
	 * Given a path relative to '/' (including the '/'), make a
	 * relative URL from the current path (in $actx) to that location.
	 */
	protected function relativePath($to, TFMPM_ActionContext $actx) {
		// TODO: Fix when $actx->getPath() works
		return substr($to,1);
	}
	
	protected function blobUrl($ref, $filenameHint='?', TFMPM_ActionContext $actx) {
		list($urn,$blobId) = TFMPM_BlobIDUtil::parseRef($ref);
		$filename = str_replace('?',substr($blobId,0,12),$filenameHint);
		return $this->relativePath("/uri-res/raw/$urn/$filename", $actx);
	}
	
	/** Return a Nife_HTTP_Response */
	public abstract function __invoke( TFMPM_ActionContext $actx );
}
