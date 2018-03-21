<?php

class TFMPM_PageAction_EnqueueComputation extends TFMPM_PageAction
{
	protected $expression;
	public function __construct( TFMPM_Registry $reg, $expression ) {
		parent::__construct($reg);
		$this->expression = $expression;
	}
	
	protected function updateComputation($expression,$statusCode,$result) {
		$this->storageHelper->doQuery(
			'UPDATE "tfmpm"."computation"'."\n".
			"SET statuscode = {statusCode}, result = {result}\n".
			"WHERE expression = {expression}",
			array('expression'=>$expression, 'statusCode'=>$statusCode, 'result'=>$result)
		);
	}
	
	public function __invoke( TFMPM_ActionContext $actx ) {
		$expression = $this->expression;
		TFMPM_PostResponseJobs::enqueue(function() use ($expression) {
			// Simulate slowness
			sleep(5);
			$this->updateComputation($expression,'processing',null);
			$result = eval('return '.$expression.';');
			if( $result === false ) {
				$this->updateComputation($expression,'errored',null);
			} else {
				sleep(5);
				$this->updateComputation($expression,'complete',(string)$result);
			}
		});
		$this->storageHelper->doQuery(
			'DELETE FROM "tfmpm"."computation"'."\n".
			"WHERE expression = {expression}",
			array('expression'=>$expression, 'statusCode'=>'queued')
		);
		$this->storageHelper->doQuery(
			'INSERT INTO "tfmpm"."computation"'."\n".
			"(expression, statuscode) VALUES ({expression},{statusCode})",
			array('expression'=>$expression, 'statusCode'=>'queued')
		);
		return Nife_Util::httpResponse(303, "Thanks for your submission!", array('location'=>'computations'));
		//return Nife_Util::httpResponse(200, "Thanks for your submission!");
	}
}
