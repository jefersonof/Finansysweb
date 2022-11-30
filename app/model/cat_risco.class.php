<?php
class cat_risco Extends TRecord
{	
	const TABLENAME  = 'CAT_RISCO';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('TIPO');
		parent::addAttribute('DESC');
		parent::addAttribute('INI');
		parent::addAttribute('FIM');
		parent::addAttribute('PERC');
		parent::addAttribute('CONTA');
		parent::addAttribute('USUARIO');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
}//TRecord

?>