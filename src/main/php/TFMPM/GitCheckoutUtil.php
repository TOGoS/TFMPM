<?php

class TFMPM_GitCheckoutUtil
{
	protected $systemUtil;
	
	public function __construct( TFMPM_SystemUtil $systemUtil ) {
		$this->systemUtil = $systemUtil;
	}
	
	public function gitCheckoutCopy($sourceGitDir, $commitId, $checkoutDir, array $options=array()) {
		$sparsenessConfig = isset($options['sparsenessConfig']) ? $options['sparsenessConfig'] : null;
		if( isset($options['paths']) ) {
			throw new Exception("'paths' option to gitCheckoutCopy no longer supported - use sparsenessConfig instead");
		}
		
		$checkoutGitDir = $checkoutDir."/.git";
		$this->systemUtil->mkdir($checkoutDir);
		$this->systemUtil->runCommand("cp -al ".escapeshellarg($sourceGitDir)." ".escapeshellarg($checkoutGitDir));
		$git = "git --git-dir=".escapeshellarg($checkoutGitDir)." --work-tree=".escapeshellarg($checkoutDir);
		$this->systemUtil->runCommand("$git config core.sparseCheckout true");
		if( $sparsenessConfig !== null ) {
			if( is_array($sparsenessConfig) ) {
				$a = $sparsenessConfig;
				$sparsenessConfig = "";
				foreach( $a as $l ) $sparsenessConfig .= "$l\n";
			}
			$sparseCheckoutConfigFile = "$checkoutGitDir/info/sparse-checkout";
			file_put_contents($sparseCheckoutConfigFile, $sparsenessConfig); // Don't bother checking out 10GB of crap
		}

		$checkoutCmd = "$git checkout ".escapeshellarg($commitId);
		$this->systemUtil->runCommand($checkoutCmd);
	}
}
