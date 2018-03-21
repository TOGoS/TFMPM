<?php

abstract class TFMPM_TestCase extends TOGoS_SimplerTest_TestCase
{
	protected $registry;
	public function __construct() {
		global $TFMPM_Registry;
		$this->registry = $TFMPM_Registry;
	}

	/**
	 * Shortcut to get objects from the registry.  This has to be
	 * public because that's how __get works, but it's not intended to
	 * be used from outside.
	 */
	public function __get($attrName) {
		return $this->registry->$attrName;
	}
}
