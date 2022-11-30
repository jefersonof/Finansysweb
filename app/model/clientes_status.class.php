<?php
class clientes_status Extends TRecord
{	
	const TABLENAME  = 'CLIENTES_STATUS';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $nome_grupo;
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('STATUS');
		parent::addAttribute('NOVOS');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	
}//TRecord

?>