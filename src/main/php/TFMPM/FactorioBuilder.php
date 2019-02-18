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
		return $this->gitCheckoutUtil->gitCheckoutCopy($this->factorioGitDir, $commitId, "factorio-checkouts/$commitId.headless-data", array(
			'sparsenessConfig' => array(
				'data/*',
				'!*.png',
				'!*.ttf',
			),
			'checkoutConfirmationFile' => true,
		));
	}

	public function checkOutFactorioHeadless($commitId) {
		return $this->gitCheckoutUtil->gitCheckoutCopy($this->factorioGitDir, $commitId, "factorio-checkouts/$commitId.headless", array(
			'sparsenessConfig' => array(
				'*',
				'!data/**.png', // This is also used by unit tests, for which tests/**.png are still needed.
				'!data/**.ttf',
			),
			'shouldExist' => array(
				'docker/Makefile',
				'src/Main.cpp',
			),
			'checkoutConfirmationFile' => true
		));
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

	public function buildFactorioTestDockerImage($commitId) {
		$dir = $this->checkOutFactorioHeadless($commitId);
		if( file_exists("$dir/Makefile") ) {
			$this->systemUtil->runCommand(array('make', '-C', $dir, 'regenerate_build_version_file'));
		} else {
			// Presumably this is a version that uses CMake
			// and the CMake scripts will take care of it
		}
		if( !is_dir($dir."/docker/factorio-test") ) {
			throw new Exception("Version $commitId doesn't have a docker/factorio-test directory; we'll need some extra smarts in order to build it...");
		}
		$buildId = "{$commitId}";
		$this->systemUtil->runCommand(array('make',"build_id={$buildId}",'-C',$dir."/docker/factorio-test"));
		return array(
			'id' => trim(file_get_contents($dir."/docker/factorio-test/docker-image-id")),
			'tag' => "factorio/factorio:{$buildId}"
		);
	}

	public function ensureFactorioTestDockerImageExists($commitId) {
		$tag = "factorio/factorio-test:{$commitId}";
		if( $this->dockerImageMetadataCache->doesDockerImageExist($tag) ) return $tag;
		// TODO: I suppose we could try pulling from dockerhub
		$this->log("Oh no, $tag doesn't seem to exist.  I'll have to build it...");
		$this->buildFactorioTestDockerImage($commitId);
		return $tag;
	}
}
