<?php

abstract class PHPTemplateProjectNS_PageAction extends PHPTemplateProjectNS_Component implements TOGoS_Action
{
	protected function makeTemplateResponse( $statusCode=200, $viewName, $vars=array(), $typeOrHeaders='text/html' ) {
		$vars = $this->pageUtil->fortifyViewParams($vars);
		$templateFile = PHPTemplateProjectNS_ROOT_DIR.'/views/'.$viewName.".php";
		if( !file_exists($templateFile) ) {
			throw new Exception("View template file '{$templateFile}' does not exist!");
		}
		$vars['params'] = $vars;
		$blob = new EarthIT_FileTemplateBlob($templateFile, $vars);
		return Nife_Util::httpResponse( $statusCode, $blob, $typeOrHeaders );
	}
	
	protected function redirect( $statusCode, $location ) {
		return Nife_Util::httpResponse($statusCode, "Redirecting to $location...", ['location'=>$location]);
	}
	
	protected function getErrorMessage( $messageId, PHPTemplateProjectNS_ActionContext $actx ) {
		$message = $actx->getSessionVariable('errorMessage');
		return hash('sha1',$message) == $messageId ? $message : null;
	}
	
	protected function redirectWithErrorMessage( $location, $message, PHPTemplateProjectNS_ActionContext $actx ) {
		if( is_array($message) ) $message = implode("\n", $message);
		$actx->setSessionVariable('errorMessage', $message);
		$messageHash = hash('sha1', $message);
		return $this->redirect(303, "{$location}?error-message-id={$messageHash}");
	}
	
	/** Return a Nife_HTTP_Response */
	public abstract function __invoke( PHPTemplateProjectNS_ActionContext $actx );
}
