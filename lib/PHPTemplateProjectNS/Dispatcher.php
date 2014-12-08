<?php

class PHPTemplateProjectNS_Dispatcher extends EarthIT_Component
{
	/**
	 * Return the object encoded by the request IFF
	 * It is JSON-encoded.  Otherwise returns null.
	 */
	protected static function getRequestContentObject() {
		static $requestRead;
		static $requestContentObject;
		if( !$requestRead ) {
			switch( $_SERVER['REQUEST_METHOD'] ) {
			case 'GET': case 'HEAD':
				$requestContentObject = null;
				break;
			default:
				// TODO: Check headers rather than assuming JSON
				$requestContent = eit_get_request_content();
				$requestContentObject = $requestContent == '' ? null : EarthIT_JSON::decode($requestContent);
			}
			$requestRead = true;
		}
		return $requestContentObject;
	}
	
	protected function getCurrentUserId() {
		return null;
	}
	
	/**
	 * Handle the request, returning a response if path seems to name some REST resource.
	 * Otherwise returns null.
	 */
	public function handleApiRequest( $method, $path, array $params=array(), $contentObject=null ) {
		if( ($crReq = EarthIT_CMIPREST_CMIPRESTRequest::parse( $method, $path, $params, $contentObject )) !== null ) {
			$crReq->userId = $this->getCurrentUserId();
			return $this->registry->getRester()->handle($crReq);
		} else {
			return null;
		}
	}
	
	protected function createPageAction( $actionName /* followed by action-specific arguments */ ) {
		$args = func_get_args();
		/* $actionName = */ array_shift($args);
		array_unshift($args, $this->registry);
		$className = "PHPTemplateProjectNS_PageAction_{$actionName}";
		$rc = new ReflectionClass($className);
		return $rc->newInstanceArgs($args);
	}
	
	protected function doPageAction( $actionName /* followed by action-specific arguments */ ) {
		return call_user_func( call_user_func_array(array($this,'createPageAction'), func_get_args()) );
	}
	
	/**
	 * Handles the request that's implied by various global variables.
	 */
	public function handleImplicitRequest( $path ) {
		$method = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : $_SERVER['REQUEST_METHOD'];
		$params = $_REQUEST;
		$contentObject = self::getRequestContentObject();
		return $this->handleRequest( $method, $path, $params, $contentObject );
	}
	
	public function handleRequest( $method, $path, array $params=array(), $contentObject=null ) {
		// Some demonstration routes; remove and replace with your own
		if( $path == '/' ) {
			return $this->doPageAction('ShowHello');
		} else if( preg_match('<^/hello/(.*)$>', $path, $matchData) ) {
			return Nife_Util::httpResponse( 200, "Hello, ".rawurldecode($matchData[1]).'!' );
		} else if( $path == '/error' ) {
			trigger_error( "An error occurred for demonstrative porpoises.", E_USER_ERROR );
		} else if( $path == '/exception' ) {
			throw new Exception( "You asked for an exception and this is it." );
		} else if(
			preg_match('#^/api([;/].*)#',$path,$bif) and
			($response = $this->handleApiRequest($method, $bif[1], $params, $contentObject)) !== null
		) {
			return $response;
		} else {
			return Nife_Util::httpResponse( 404, "I don't know about $path!" );
		}
	}
}
