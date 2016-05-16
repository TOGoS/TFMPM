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
				$this->schema, $this->restNameFormatter, 'cmip'));
		
		if( ($request = $requestParser->parse( $method, $path, $queryString, $content )) !== null ) {
			return $requestParser->toAction($request);
		}
		
		return null;
	}
	
	/**
	 * Same API as for REST API requests, but returning HTML!
	 */
	public function restPageRequestToAction( $method, $path, $queryString, Nife_Blob $content=null ) {
		$requestParser = new EarthIT_CMIPREST_RequestParser_CMIPRequestParser(
			$this->schema, $this->restSchemaObjectNamer, $this->dataTableResultAssemblerFactory );
		
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
		} else if( $path == '/schema-upgrades' ) {
			switch( $req->getRequestMethod() ) {
			case 'GET':
				return $this->createPageAction('ShowSchemaUpgrades', $req->getParam('mode', 'list'));
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
			preg_match('#^/api([;/].*)#',$path,$bif) and
			($restAction = $this->apiRequestToAction(
				$req->getRequestMethod(),
				$bif[1], $req->queryString,
				$req->getRequestContentBlob())
			 ) !== null
		) {
			return $restAction;
		} else if(
			($restAction = $this->restPageRequestToAction(
				$req->getRequestMethod(),
				$path, $req->queryString,
				$req->getRequestContentBlob())
			) !== null
		) {
			return $restAction;
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
			return $this->rester->doActionAndGetHttpResponse($action, $actx);
		} else {
			throw new Exception("I don't know how to run ".PHPTemplateProjectNS_Debug::describe($action)." as an action");
		}
	}
	
	/**
	 * Check a few different ways that a user could be authenticated
	 * and return a new ActionContext that reflects any logged-in-ness
	 * 
	 * @return PHPTemplateProjectNS_ActionContext with loggedInUserId set, if someone was authenticated
	 * @throws PHPTemplateProjectNS_AuthenticationFailure
	 */
	public function authenticate( PHPTemplateProjectNS_Request $req, PHPTemplateProjectNS_ActionContext $actx ) {
		$auth = $req->getAuthUserPw();
		if( $auth['username'] !== null ) {
			$loginResult = $this->userModel->checkLogin( $auth['username'], $auth['password'] );
			if( $loginResult['success'] ) {
				return $actx->with(array('loggedInUserId'=>$loginResult['userId']));
			} else {
				throw new PHPTemplateProjectNS_AuthenticationFailure($loginResult['message']);
			}
		}
		
		if( ($sessionUserId = $actx->getSessionVariable('userId')) !== null ) {
			return $actx->with(array('loggedInUserId'=>$sessionUserId));
		}
		
		return $actx;
	}
	
	public function handleRequest( PHPTemplateProjectNS_Request $req, PHPTemplateProjectNS_ActionContext $actx ) {
		try {
			$actx = $this->authenticate( $req, $actx );
		} catch( PHPTemplateProjectNS_AuthenticationFailure $f ) {
			return Nife_Util::httpResponse( 401, $f->getMessage() );
		}
		
		$action = $this->requestToAction($req);
		return $this->doAction($action, $actx);
	}
}