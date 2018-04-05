<?php

class TFMPM_FactorioBuilder extends TFMPM_Component
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

	public function buildFactorioHeadlessDockerImage($commitId) {
		$dir = $this->checkOutFactorioHeadless($commitId);
		$this->systemUtil->runCommand(array('make', '-C', $dir, 'regenerate_build_version_file'));
		if( !is_dir($dir."/docker/factorio-headless") ) {
			throw new Exception("Version $commitId doesn't have a docker/factorio-headless directory; we'll need some extra smarts in order to build it...");
		}
		$buildId = "{$commitId}-headless";
		$this->systemUtil->runCommand(array('make',"build_id={$buildId}",'-C',$dir."/docker/factorio-headless"));
		return array(
			'id' => trim(file_get_contents($dir."/docker/factorio-headless/docker-image-id")),
			'tag' => "factorio/factorio:{$buildId}"
		);
	}

	protected $dockerImageExistenceCache = array();
	public function doesDockerImageExist($tag) {
		if( isset($this->dockerImageExistenceCache[$tag]) ) return true;
		
		if( $this->systemUtil->runCommand(array('docker','inspect',$tag), array(
			'outputFile'=>'/dev/null', 'onNz'=>'return'
		)) == 0 ) {
			return $this->dockerImageExistenceCache[$tag] = true;
		}
		return false;
	}

	public function ensureFactorioHeadlessDockerImageExists($commitId) {
		$tag = "factorio/factorio:{$commitId}-headless";
		if( $this->doesDockerImageExist($tag) ) return $tag;
		// TODO: I suppose we could try pulling from dockerhub
		$this->log("Oh no, $tag doesn't seem to exist.  I'll have to build it...");
		$this->buildFactorioHeadlessDockerImage($commitId);
		return $tag;
	}
}
