<?php
class uf Extends TRecord
{	
	const TABLENAME  = 'UF';
	const PRIMARYKEY = 'COD';
	const IDPOLICY   = 'max';
	
	const CREATEDEAT = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $nome_grupo;
	
	public function __construct($COD = NULL)
	{
		parent::__construct($COD);
		
		parent::addAttribute('UF');
		parent::addAttribute('ESTADO');
		parent::addAttribute('REGIAO');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
		
	}//function __construct
	
	
}//TRecord

?>