<?php

class TFMPM_FactorioBuilder extends TFMPM_Component
{
	protected $factorioGitDir;
	protected $checkoutsRootDir;
	protected $gitCheckoutUtil;
	protected $systemUtil;
	protected $dockerImageMetadataCache;

	public function __construct(
		$factorioGitDir, $checkoutRootDir,
		TFMPM_GitCheckoutUtil $gitCheckoutUtil,
		TFMPM_SystemUtil $systemUtil,
		TFMPM_DockerImageMetadataCache $dimc
	) {
		$this->factorioGitDir = $factorioGitDir;
		$this->checkoutRootDir = $checkoutRootDir;
		$this->gitCheckoutUtil = $gitCheckoutUtil;
		$this->systemUtil = $systemUtil;
		$this->dockerImageMetadataCache = $dimc;
	}
	
	public function checkOutFactorioHeadlessData($commitId) {
		$checkoutDir = "factorio-checkouts/$commitId.headless-data";
		$checkoutConfirmationFile = "{$checkoutDir}/.checkout-completed";
		if( !file_exists($checkoutConfirmationFile) ) {
			$this->gitCheckoutUtil->gitCheckoutCopy($this->factorioGitDir, $commitId, $checkoutDir, array(
				'sparsenessConfig' => array(
					'data/*',
					'!*.png'
				)
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
				'sparsenessConfig' => array(
					'*',
					'!*.png'
				),
				'shouldExist' => array(
					'docker/Makefile',
					'src/Main.cpp',
				),
			));
			file_put_contents($checkoutConfirmationFile, "ok");
		}
		return $checkoutDir;
	}

	public function buildFactorioHeadlessDockerImage($commitId) {
		$dir = $this->checkOutFactorioHeadless($commitId);
		if( file_exists("$dir/Makefile") ) {
			$this->systemUtil->runCommand(array('make', '-C', $dir, 'regenerate_build_version_file'));
		} else {
			// Presumably this is a version that uses CMake
			// and the CMake scripts will take care of it
		}
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

	public function ensureFactorioHeadlessDockerImageExists($commitId) {
		$tag = "factorio/factorio:{$commitId}-headless";
		if( $this->dockerImageMetadataCache->doesDockerImageExist($tag) ) return $tag;
		// TODO: I suppose we could try pulling from dockerhub
		$this->log("Oh no, $tag doesn't seem to exist.  I'll have to build it...");
		$this->buildFactorioHeadlessDockerImage($commitId);
		return $tag;
	}
}
