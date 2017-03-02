<?php

class PHPTemplateProjectNS_Router extends PHPTemplateProjectNS_Component
{
	/**
	 * If the indicated request can be interpreted as a CMIPREST_RESTAction, parse and return said action.
	 * Otherwise return null.
	 */
	public function apiRequestToAction( $method, $path, $queryString, Nife_Blob $content=null ) {
		$requestParser = EarthIT_CMIPREST_RequestParser_FancyRequestParser::buildStandardFancyParser(
			$this->schema, $this->restSchemaObjectNamer, 'cmip');

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
		$method = $req->getRequestMethod();
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
		} else if( $path == '/do-action-script' ) {
			return $this->createPageAction('DoActionScript', $req->getParam('script'));
		} else if( $path == '/send-login-link' ) {
			switch( $method ) {
			case 'POST':
				return $this->createPageAction('SendLoginLink', $req->getParams());
			}
		} else if( $path == '/forgot-password' ) {
			switch( $method ) {
			case 'POST':
				return $this->createPageAction('SendPasswordResetLink', $req->getParams());
			}
		} else if( $path == '/reset-password' ) {
			switch( $method ) {
			case 'GET':
				return $this->createPageAction('ShowPasswordResetForm', $req->getParam('token'));
			case 'POST':
				return $this->createPageAction('ResetPassword', $req->getParams());
			}
		} else if( $path == '/register' ) {
			switch( $method ) {
			case 'GET':
				return $this->createPageAction('ShowRegistrationForm', $req->getParam('error-message-id'));
			case 'POST':
				return $this->createPageAction('Register', $req->getParams());
			}
		} else if( $path == '/do-token' ) {
			return $this->createPageAction('DoToken', $req->getParam('token'), $req->getParam('forward'));
		} else if( $path == '/schema-upgrades' ) {
			switch( $method ) {
			case 'GET':
				return $this->createPageAction('ShowSchemaUpgrades', $req->getParam('mode', 'list'));
			}
		} else if( $path == '/computations' ) {
			switch( $method ) {
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
				$method,
				$bif[1], $req->getQueryString(),
				$req->getRequestContentBlob())
			 ) !== null
		) {
			return $restAction;
		} else if(
			$method === 'POST' and preg_match('#^/([^/]+)$#', $path, $bif) and
			($rc = EarthIT_CMIPREST_Util::getResourceClassByCollectionName($this->schema, $bif[1])) !== null
		) {
			return new PHPTemplateProjectNS_PageAction_PostToDataTable($this->registry, $rc, $req->getParams());
		} else if( $path == '/exception' ) {
			return function(PHPTemplateProjectNS_ActionContext $actx) {
				throw new Exception( "You asked for an exception and this is it." );
			};
		} else if( $path == '/error' ) {
			return function(PHPTemplateProjectNS_ActionContext $actx) {
				trigger_error( "An error occurred for demonstrative porpoises.", E_USER_ERROR );
			};
		} else if(
			($restAction = $this->restPageRequestToAction(
				$method,
				$path, $req->getQueryString(),
				$req->getRequestContentBlob())
			) !== null
		) {
			return $restAction;
		}
		
		return function(PHPTemplateProjectNS_ActionContext $actx) use ($req, $path) {
			return Nife_Util::httpResponse( 404, "I don't know about $path!" );
		};
	}
	
	protected function actionNeedsTransaction($action) {
		// In theory this could return false
		// for really simple actions, especially ones
		// that don't even hit the database.
		return true;
	}
	
	protected function doAction2($action, PHPTemplateProjectNS_ActionContext $actx) {
		// TODO: Make it so all actions go through RESTer
		// so that our special actions can be part of CompoundActions
		if( is_callable($action) ) {
			// TODO: Check if action is allowed
			return call_user_func($action, $actx);
		} else if( $action instanceof EarthIT_CMIPREST_RESTAction ) {
			return $this->rester->doActionAndGetHttpResponse($action, $actx);
		} else {
			throw new Exception("I don't know how to run ".PHPTemplateProjectNS_Debug::describe($action)." as an action");
		}
	}
	
	public function doAction($action, PHPTemplateProjectNS_ActionContext $actx) {
		$this->storageHelper->beginTransaction();
		$success = false;
		try {
			$result = $this->doAction2($action, $actx);
			$success = true;
			return $result;
		} finally {
			$this->storageHelper->endTransaction($success);
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
