<?php

class TFMPM_SystemUtil
{
	public function log($stuff) {
		fwrite(STDERR, "{$stuff}\n");
	}
	
	public function getHomeDir() {
		$d = getenv('HOME');
		if( empty($d) ) {
			throw new Exception("Failed to determine home directory");
		}
		return $d;
	}
	
	public function resolvePath($path, $options=array()) {
		if( preg_match('#^~(/.*|$)#', $path, $bif) ) {
			$resolved = $this->getHomeDir().$bif[1];
		} else if( preg_match('#^~([^/]+)(.*)#', $path, $bif) ) {
			$resolved = "/home/{$bif[1]}{$bif[2]}";
		} else {
			$resolved = $path;
		}
		$resolved = realpath($resolved);
		if( $resolved === false or $resolved == '' ) {
			if( !isset($options['onError']) or $options['onError'] == 'throw' ) {
				throw new Exception("Failed to resolve path '$path'");
			} else {
				return null;
			}
		}
		return $resolved;
	}


	public function unlink($whatever) {
		if( @unlink($whatever) === false ) {
			if( !file_exists($whatever) ) {
				// lol it's fine
				return;
			}
			$errInfo = error_get_last();
			throw new Exception("unlink(‹$whatever›) failed: {$errInfo['message']}");
		}
	}
	public function symlink($src,$dest) {
		if( @symlink($src, $dest) === false ) {
			$errInfo = error_get_last();
			throw new Exception("symlink(‹$src›, ‹$dest›) failed: {$errInfo['message']}");
		}
	}
	public function mkdir($dir, $mode=0755) {
		if( is_dir($dir) ) return;
		if( @mkdir($dir, $mode, true) === false ) {
			$errInfo = error_get_last();
			throw new Exception("Mkdir ‹{$dir}› failed: {$errInfo['message']}");
		}
	}
	public function mkParentDirs($file) {
		$dir = dirname($file);
		if( $dir == '' ) return;
		self::mkdir($dir);
	}
	public function buildShellCommand(array $argv, array $options=array()) {
		$cmdString = implode(" ", array_map('escapeshellarg', $argv));
		if( isset($options['outputFile']) ) {
			$cmdString .= " >".escapeshellarg($options['outputFile']);
		}
		if( isset($options['errorFile']) ) {
			$cmdString .= " 2>".escapeshellarg($options['errorFile']);
		} else if( isset($options['errorFd']) ) {
			$cmdString .= " 2>&{$options['errorFd']}";
		}
		if( isset($options['inputFile']) ) {
			$cmdString .= " <".escapeshellarg($options['inputFile']);
		}
		if( isset($options['teeOutputFile']) ) {
			$cmdString = "set -o pipefail; ".$cmdString;
			$cmdString .= " | tee ".escapeshellarg($options['teeOutputFile']);
		}
		return $cmdString;
	}
	public function runCommand($args, array $options=array()) {
		if( isset($options['teeOutputFile']) ) {
			throw new Exception("teeOutputFile not supported because 'set -o pipefail' isn't");
		}
		$cmdString = is_array($args) ? self::buildShellCommand($args, $options) : $args;
		$this->log("$ $cmdString");
		system($cmdString, $status);
		$onNz = isset($options['onNz']) ? $options['onNz'] : 'error';
		if( $status != 0 && $onNz == 'error' ) {
			throw new Exception("Non-zero exit status $status from command: $cmdString");
		}
		return $status;
	}
}
