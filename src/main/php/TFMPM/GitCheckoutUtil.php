<?php

class TFMPM_GitCheckoutUtil
{
	protected $systemUtil;
	
	use TFMPM_ComponentGears; // for log(...)
	
	public function __construct( TFMPM_SystemUtil $systemUtil ) {
		$this->systemUtil = $systemUtil;
	}
	
	protected static function smartSplitLines($text) {
		$lines = explode("\n",$text);
		$result = array();
		foreach( $lines as $line ) {
			$line = trim($line);
			if( !empty($line) and $line[0] != '#' ) $result[] = $line;
		}
		return $result;
	}
	
	protected function gitHasObject($gitDir, $objectId) {
		$git = "git --git-dir=".escapeshellarg($gitDir);
		$catCode = $this->systemUtil->runCommand("$git cat-file -t ".escapeshellarg($objectId)." >/dev/null 2>&1", array('onNz'=>'return'));
		return $catCode == 0;
	}
	
	protected function gitFetch($gitDir, $objectId) {
		$git = "git --git-dir=".escapeshellarg($gitDir);
		
		if( $this->gitHasObject($gitDir, $objectId) ) return;
		
		$remotes = self::smartSplitLines(`$git remote`);
		foreach( $remotes as $rem ) {
			$fetchCode = $this->systemUtil->runCommand(
				"$git fetch ".escapeshellarg($rem)." ".escapeshellarg($objectId),
				array('onNz' => 'return')
			);
			if( $fetchCode == 0 ) return;
		}
		
		$this->log("Doin a git fetch --all to try to acquire $objectId");
		// As a last resort, try fetch --all
		$this->systemUtil->runCommand(
			"$git fetch --all",
			array('onNz' => 'return')
		);
		if( $this->gitHasObject($gitDir, $objectId) ) return;
		
		throw new Exception("Oh no, we don't have git object $objectId!");
	}
	
	public function gitCheckoutCopy($sourceGitDir, $commitId, $checkoutDir, array $options=array()) {
		$checkoutConfirmationFile = isset($options['checkoutConfirmationFile']) ? $options['checkoutConfirmationFile'] : null;
		if( $checkoutConfirmationFile === true ) $checkoutConfirmationFile = "{$checkoutDir}/.checkout-completed";
		if( $checkoutConfirmationFile !== null and file_exists($checkoutConfirmationFile) ) return $checkoutDir;
		
		$sparsenessConfig = isset($options['sparsenessConfig']) ? $options['sparsenessConfig'] : null;
		if( isset($options['paths']) ) {
			throw new Exception("'paths' option to gitCheckoutCopy no longer supported - use sparsenessConfig instead");
		}
		
		$this->gitFetch($sourceGitDir, $commitId);
		
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
		$checkoutCmd2 = "$git checkout ".escapeshellarg($commitId)." .";
		$this->systemUtil->runCommand($checkoutCmd2);

		if( isset($options['shouldExist']) ) {
			$shouldExist = is_array($options['shouldExist']) ? $options['shouldExist'] : array($options['shouldExist']);
			$missing = array();
			foreach( $shouldExist as $f ) {
				if( !file_exists("$checkoutDir/$f") ) {
					$missing[] = $f;
				}
			}
			if( count($missing) > 0 ) {
				throw new Exception("Oh no, checkout failed; ".EarthIT_Schema_WordUtil::oxfordlyFormatList($missing)." are missing");
			}
		}

		if( $checkoutConfirmationFile !== null ) {
			file_put_contents($checkoutConfirmationFile, "ok\n");
		}

		return $checkoutDir;
	}
}
