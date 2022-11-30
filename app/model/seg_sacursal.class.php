<?php
class seg_sacursal Extends TRecord
{	
	const TABLENAME  = 'SEG_SACURSAL';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDEAT = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('SACURSAL');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
}//TRecord

?>