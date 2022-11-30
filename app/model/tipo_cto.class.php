<?php
class tipo_cto Extends TRecord
{	
	const TABLENAME  = 'TIPO_CTO';
	const PRIMARYKEY = 'ID';
	const IDPOLICY   = 'max';
	
	const CREATEDEAT = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($ID = NULL)
	{
		parent::__construct($ID);
		
		parent::addAttribute('CODIGO');
		parent::addAttribute('DESCRICAO');
		parent::addAttribute('TIPO');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
		
	}//function __construct
	
}//TRecord

?>