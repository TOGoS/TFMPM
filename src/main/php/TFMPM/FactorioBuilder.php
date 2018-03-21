<?php

class TFMPM_FactorioBuilder
{
	protected $factorioGitDir;
	protected $checkoutsRootDir;
	protected $gitCheckoutUtil;
	protected $systemUtil;

	public function __construct($factorioGitDir, $checkoutRootDir, TFMPM_GitCheckoutUtil $gitCheckoutUtil, TFMPM_SystemUtil $systemUtil) {
		$this->factorioGitDir = $factorioGitDir;
		$this->checkoutRootDir = $checkoutRootDir;
		$this->gitCheckoutUtil = $gitCheckoutUtil;
		$this->systemUtil = $systemUtil;
	}
		
	public function checkOutFactorioDataOnly($commitId) {
		$checkoutDir = "factorio-checkouts/$commitId.data-only";
		$checkoutConfirmationFile = "{$checkoutDir}/.checkout-completed";
		if( !file_exists($checkoutConfirmationFile) ) {
			$this->gitCheckoutUtil->gitCheckoutCopy($this->factorioGitDir, $commitId, $checkoutDir, array(
				'sparsenessConfig' => '!*.png',
				'files' => 'data'
			));
			file_put_contents($checkoutConfirmationFile, "ok");
		}
		return $checkoutDir;
	}

	public function checkOutFactorioHeadless($commitId) {
		$checkoutDir = "factorio-checkouts/$commitId.headless";
		$checkoutConfirmationFile = "{$checkoutDir}/.checkout-completed";
		if( !file_exists($checkoutConfirmationFile) ) {
			$this->gitCheckoutUtil->gitCheckoutCopy($this->factorioGitDir, $commitId, $checkoutDir, array(
				'sparsenessConfig' => '!*.png'
			));
			file_put_contents($checkoutConfirmationFile, "ok");
		}
		return $checkoutDir;
	}

	// to build factorio
	// make regenerate_build_version_file
	// ...some other stuff
	// is there a docker/factorio-headless?  Use that.

	public function buildFactorioHeadlessDockerImage($commitId) {
		$dir = $this->checkOutFactorioHeadless($commitId);
		$this->systemUtil->symlink($this->factorioGitDir,"$dir/.git"); // Needed to regenerate the build version file
		$this->systemUtil->runCommand(array('make','-C',$dir,'regenerate_build_version_file'));
		$this->systemUtil->unlink("$dir/.git"); // Make sure it doesn't get abused
		if( !is_dir($dir."/docker/factorio-headless") ) {
			throw new Exception("Version $commitId doesn't have a docker/factorio-headless directory; we'll need some extra smarts in order to build it...");
		}
		$this->systemUtil->runCommand(array('make','-C',$dir."/docker/factorio-headless"));
		return array(
			'id' => trim(file_get_contents($dir."/docker/factorio-headless/docker-image-id")),
			'tag' => "factorio/factorio:{$commitId}-headless"
		);
	}
}
