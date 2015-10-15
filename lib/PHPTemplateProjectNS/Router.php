<?php

class PHPTemplateProjectNS_Router extends PHPTemplateProjectNS_Component
{
	/**
	 * If the indicated request can be interpreted as a CMIPREST_RESTAction, parse and return said action.
	 * Otherwise return null.
	 */
	public function apiRequestToAction( $method, $path, $queryString, Nife_Blob $content=null ) {
		$requestParser = new EarthIT_CMIPREST_RequestParser_FancyRequestParser(
			EarthIT_CMIPREST_RequestParser_FancyRequestParser::buildStandardParsers(
				$this->schema, function($name, $plural=false) {
					if($plural) $name = EarthIT_Schema_WordUtil::pluralize($name);
					return EarthIT_Schema_WordUtil::toCamelCase($name);
				}, 'cmip'));
		
		if( ($request = $requestParser->parse( $method, $path, $queryString, $content )) !== null ) {
			return $requestParser->toAction($request);
		}
		
		return null;
	}
	
	protected function createPageAction( $actionName /* followed by action-specific arguments */ ) {
		$args = func_get_args();
		/* $actionName = */ array_shift($args);
		array_unshift($args, $this->registry);
		$className = "PHPTemplateProjectNS_PageAction_{$actionName}";
		$rc = new ReflectionClass($className);
		return $rc->newInstanceArgs($args);
	}
	
	public function requestToAction( PHPTemplateProjectNS_Request $req ) {
		$path = $req->getPathInfo();
		if( $path == '/' ) {
			return $this->createPageAction('ShowHello');
		} else if( preg_match('<^/uri-res(/.*)>', $path, $bif) ) {
			switch($req->requestMethod) {
			case 'PUT':
				if( $bif[1] == '/N2R' and ($urn = $req->getQueryString()) ) {
					return $this->createPageAction('N2RPut', $urn, $req);
				}
				break;
			case 'GET': case 'HEAD':
				return $this->createPageAction('N2RGet', $bif[1], $req);
			}
		} else if( $path == '/login' ) {
			switch( $req->requestMethod ) {
			case 'GET' : return $this->createPageAction('ShowLoginForm', $req->getParam('error-message-id'));
			case 'POST': return $this->createPageAction('LogIn', $req->getParam('username'), $req->getParam('password'));
			}
		} else if( $path == '/logout' ) {
			return function(PHPTemplateProjectNS_ActionContext $actx) {
				if( $actx->sessionExists() ) $actx->destroySession();
				return Nife_Util::httpResponse(303, 'Log you out!', ['location'=>'./']);
			};
		} else if( preg_match('<^/hello/(.*)$>', $path, $bif) ) {
			return $this->createPageAction('SayHelloTo',$bif[1]);
		} else if( $path == '/register' ) {
			switch( $req->getRequestMethod() ) {
			case 'GET':
				return $this->createPageAction('ShowRegistrationForm', $req->getParam('error-message-id'));
			case 'POST':
				return $this->createPageAction('Register', $req->getParams());
			}
		} else if( $path == '/computations' ) {
			switch( $req->getRequestMethod() ) {
			case 'GET':
				return $this->createPageAction('ShowComputations');
			case 'POST':
				$input = (float)$req->getParam('square');
				return $this->createPageAction('EnqueueComputation', "sqrt($input)");
			}
		} else if( $path === '/blobs' && $req->requestMethod === 'POST' ) {
			return $this->createPageAction('FileUpload', $req);
		} else if(
			preg_match('#^/([^/]+)$#',$path,$bif) and
			($rc = EarthIT_CMIPREST_Util::getResourceClassByCollectionName($this->schema, $bif[1])) !== null
		) {
			return $this->createPageAction('ShowDataTable', $rc);
		} else if(
			preg_match('#^/api([;/].*)#',$path,$bif) and
			($cmipUserAction = $this->apiRequestToAction(
				$req->getRequestMethod(),
				$bif[1], $req->queryString,
				$req->getRequestContentBlob())
			 ) !== null
		) {
			return $cmipUserAction;
		}
		
		return function(PHPTemplateProjectNS_ActionContext $actx) use ($req, $path) {
			// These are here because they haven't yetb been converted to
			// the newer interpret-then-execute style, though it'd be
			// easy to do.
			if( preg_match('<^/hello/(.*)$>', $path, $matchData) ) {
				return Nife_Util::httpResponse( 200, "Hello, ".rawurldecode($matchData[1]).'!' );
			} else if( $path == '/error' ) {
				trigger_error( "An error occurred for demonstrative porpoises.", E_USER_ERROR );
			} else if( $path == '/exception' ) {
				throw new Exception( "You asked for an exception and this is it." );
			} else {
				return Nife_Util::httpResponse( 404, "I don't know about $path!" );
			}
		};
	}
	
	public function doAction($action, PHPTemplateProjectNS_ActionContext $actx) {
		if( is_callable($action) ) {
			return call_user_func($action, $actx);
		} else if( $action instanceof EarthIT_CMIPREST_RESTAction ) {
			$rez = $this->rester->doAction($action, $actx);
			if( $rez instanceof Nife_HTTP_Response ) return $rez;
			return PHPTemplateProjectNS_PageUtil::jsonResponse(200, $rez);
		} else {
			throw new Exception("I don't know how to run ".PHPTemplateProjectNS_Debug::describe($action)." as an action");
		}
	}
	
	public function handleRequest( PHPTemplateProjectNS_Request $req, PHPTemplateProjectNS_ActionContext $actx ) {
		// TODO: Any per-request authentication (basic, etc)
		$action = $this->requestToAction($req);
		return $this->doAction($action, $actx);
	}
}
