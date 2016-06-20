<?php

class PHPTemplateProjectNS_AS_Context
{
	public $actx;
	public $dataStack = [];
	public $pc = 0;
	public $returnStack = [];
	
	public function __construct( PHPTemplateProjectNS_ActionContext $actx ) {
		$this->actx = $actx;
	}
	
	public function push($v) {
		$this->dataStack[] = $v;
	}
	public function pop() {
		return array_pop($this->dataStack);
	}

	public function peek() {
		if( count($this->dataStack) == 0 ) return null;
		return $this->dataStack[count($this->dataStack)-1];
	}
}
