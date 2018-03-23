<?php

class TFMPM_SnakeCaseDBObjectNamer implements EarthIT_DBC_Namer
{
	public function getTableName( EarthIT_Schema_ResourceClass $c ) {
		return EarthIT_Schema_WordUtil::toSnakeCase($c->getName());
	}	
	public function getColumnName( EarthIT_Schema_ResourceClass $c, EarthIT_Schema_Field $f ) {
		return EarthIT_Schema_WordUtil::toSnakeCase($f->getName());
	}
	public function getIndexName( EarthIT_Schema_ResourceClass $c, EarthIT_Schema_Index $i ) {
		return EarthIT_Schema_WordUtil::toSnakeCase($i->getName());
	}
	public function getForeignKeyName( EarthIT_Schema_ResourceClass $c, EarthIT_Schema_Reference $r ) {
		return EarthIT_Schema_WordUtil::toSnakeCase($c->getName()." ".$r->getName());
	}
}
