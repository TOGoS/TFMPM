<?php

class TFMPM_GitCheckoutUtil
{
	protected $systemUtil;
	
	public function __construct( TFMPM_SystemUtil $systemUtil ) {
		$this->systemUtil = $systemUtil;
	}
	
	public function gitCheckoutCopy($sourceGitDir, $commitId, $checkoutDir, array $options=array()) {
		$sparsenessConfig = isset($options['sparsenessConfig']) ? $options['sparsenessConfig'] : '';
		$paths = isset($options['paths']) ? $options['paths'] : null;
		
		$checkoutGitDir = $checkoutDir."/.git";
		$this->systemUtil->mkdir($checkoutDir);
		$this->systemUtil->runCommand("cp -al ".escapeshellarg($sourceGitDir)." ".escapeshellarg($checkoutGitDir));
		$git = "git --git-dir=".escapeshellarg($checkoutGitDir)." --work-tree=".escapeshellarg($checkoutDir);
		$this->systemUtil->runCommand("$git config core.sparseCheckout true");
		if( $sparsenessConfig ) {
			$sparseCheckoutConfigFile = "$checkoutGitDir/info/sparse-checkout";
			file_put_contents($sparseCheckoutConfigFile, $sparsenessConfig); // Don't bother checking out 10GB of crap
		}
		if( $paths === array() ) return;

		$resetCmd = "$git reset ".escapeshellarg($commitId);
		$this->systemUtil->runCommand($resetCmd);
		
		$checkoutCmd = "$git checkout ".escapeshellarg($commitId);
		if( $paths !== null ) foreach( $paths as $p ) {
				$checkoutCmd .= " ".escapeshellarg($p);
		} else $checkoutCmd .= " .";
		$this->systemUtil->runCommand($checkoutCmd);
	}
}
