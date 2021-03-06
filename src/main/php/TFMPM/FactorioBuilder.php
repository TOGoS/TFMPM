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
				'!license*.txt', // Filenames with spaces cause problems for my Makefiles.
				'!data/base/sound',
				'!data/core/sound',
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
				'!license*.txt', // Filenames with spaces cause problems for my Makefiles.
				'!data/base/sound',
				'!data/core/sound',
			),
			'shouldExist' => array(
				'docker/Makefile',
				'src/Main.cpp',
			),
			'checkoutConfirmationFile' => true
		));
	}

	/**
	 * Checks that the docker image ID file exists, reads it,
	 * invalidates the docker image metadata cache for the ID and tag,
	 * and returns array of 'id' => ... and 'tag' => ...
	 */
	protected function newDockerImageAndTagFromIdFile($file, $tag) {
		$id = file_get_contents($file);
		$id = trim($id);
		if( empty($id) ) {
			throw new Exception("Failed to read docker image ID from $file");
		}
		$this->dockerImageMetadataCache->invalidate($id);
		$this->dockerImageMetadataCache->invalidate($tag);
		return array('id' => $id, 'tag' => $tag);
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
			fwrite(STDERR, $dir."/docker/factorio-headless"." doesn't exist!");
			throw new Exception("Version $commitId doesn't have a docker/factorio-headless directory; we'll need some extra smarts in order to build it...");
		}
		$buildId = "{$commitId}-headless";
		$this->systemUtil->runCommand(array('make',"build_id={$buildId}",'-C',$dir."/docker/factorio-headless"));
		return $this->newDockerImageAndTagFromIdFile($dir."/docker/factorio-headless/docker-image-id", "factorio/factorio:{$buildId}");
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
		return $this->newDockerImageAndTagFromIdFile($dir."/docker/factorio-test/docker-image-id", "factorio/factorio:{$buildId}");
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
