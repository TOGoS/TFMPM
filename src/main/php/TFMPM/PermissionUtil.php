<?php

class TFMPM_PermissionUtil
{
	public static function max( /* $permissionValue0, $permissionValue1, ... */ ) {
		$vals = func_get_args();
		foreach( array(true, EarthIT_CMIPREST_RESTActionAuthorizer::AUTHORIZED_IF_RESULTS_VISIBLE) as $v ) {
			foreach( $vals as $vX ) if( $v === $vX ) return $v;
		}
		return false;
	}
}
