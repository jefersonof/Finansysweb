<?php
class ent_gar Extends TRecord
{	
	const TABLENAME  = 'ENT_GAR';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('NOME');
		parent::addAttribute('RESPONSAVEL');
		parent::addAttribute('BAIRRO');
		parent::addAttribute('CIDADE');
		parent::addAttribute('UF');
		parent::addAttribute('CEP');
		parent::addAttribute('TELEFONE');
		parent::addAttribute('FAX');
		parent::addAttribute('CELULAR');
		parent::addAttribute('OBS');
		parent::addAttribute('CNPJ');
		parent::addAttribute('IE');
		parent::addAttribute('ENDERECO');
		parent::addAttribute('CONTATO');
		parent::addAttribute('GRUPO');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
		
	}//function __construct
	
}//TRecord

?>