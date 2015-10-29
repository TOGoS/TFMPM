<?php

interface PHPTemplateProjectNS_ActionContext
{
	public function getLoggedInUserId();
	public function sessionExists();
	public function getSessionVariable($key, $default=null);
	public function setSessionVariable($key, $value);
	public function unsetSessionVariable($key);
	public function destroySession();
	/**
	 * Return the path part of the URL (http://foo/app/foo/x?123 -> /foo/x)
	 * that was used to request this page.
	 */
	public function getPath();
	/**
	 * Given a path from the document root, e.g. 'hello', returns a
	 * relative path to that page from the user's current location.
	 * @param $path a relative (to the document root) URL.  May include query string.
	 */
	public function relativeUrl($path);
	/**
	 * Given a path from the document root, e.g. 'hello', returns a
	 * URL to that page
	 * @param $path a relative (to the document root) URL.  May include query string.
	 */
	public function absoluteUrl($path);
}
