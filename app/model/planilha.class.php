<?php
class planilha Extends TRecord
{	
	const TABLENAME  = 'PLANILHA';
	const PRIMARYKEY = 'ID_PLANILHA';
	const IDPOLICY   = 'max';
	
	public function __construct($ID_PLANILHA = NULL)
	{
		parent::__construct($ID_PLANILHA);
		
		parent::addAttribute('NOME');		
		parent::addAttribute('SEXO');
		parent::addAttribute('DATA_NASCIMENTO');
		parent::addAttribute('CPF');
		parent::addAttribute('MATRICULA');
		parent::addAttribute('VALOR');
		
		
	}//function __construct
	
	
}//TRecord

?>