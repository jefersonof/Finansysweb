<?php
class formaspag Extends TRecord
{	
	const TABLENAME  = 'FORMASPAG';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('FORMA');
		parent::addAttribute('LIB');
		parent::addAttribute('CAD');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
}//TRecord

?>