<?php
class REG_FIN Extends TRecord
{	
	const TABLENAME  = 'REG_FIN';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('REGIME');
		parent::addAttribute('TABELA');
		parent::addAttribute('PRAZO_PGTO');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
}//TRecord

?>