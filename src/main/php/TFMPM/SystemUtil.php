<?php

class TFMPM_SystemUtil
{
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
		return $cmdString;
	}
	public function runCommand($args, array $options=array()) {
		$cmdString = is_array($args) ? self::buildShellCommand($args, $options) : '';
		system($cmdString, $status);
		$onNz = isset($options['onNz']) ? $options['onNz'] : 'error';
		if( $status != 0 && $onNz == 'error' ) {
			throw new Exception("Non-zero exit status $status from command: $cmdString");
		}
		return $status;
	}
}
