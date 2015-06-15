<?php

interface PHPTemplateProjectNS_ActionContext
{
	public function getLoggedInUserId();
	public function sessionExists();
	public function getSessionVariable($key, $default=null);
	public function setSessionVariable($key, $value);
	public function unsetSessionVariable($key);
	public function destroySession();
}
