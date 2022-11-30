<?php
class org Extends TRecord
{	
	const TABLENAME  = 'ORG';
	const PRIMARYKEY = 'ID';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $nome_grupo;
	
	public function __construct($ID = NULL)
	{
		parent::__construct($ID);
		
		parent::addAttribute('CODIGO');
		parent::addAttribute('ORGAO');
		parent::addAttribute('UPAG');
		parent::addAttribute('ENT_COL');//FK ENTIDADES
		parent::addAttribute('COD_ORG');
		parent::addAttribute('SIGLA');
		parent::addAttribute('PREF_ORG');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
}//TRecord

?>