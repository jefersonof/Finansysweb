<?php
class cm_af Extends TRecord
{	
	const TABLENAME  = 'CM_AF';
	const PRIMARYKEY = 'CONTROLE';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($CONTROLE = NULL)
	{
		parent::__construct($CONTROLE);
		
		parent::addAttribute('AGENTE');//FK fornecedores
		parent::addAttribute('PI');
		parent::addAttribute('PF');
		parent::addAttribute('CM');
		parent::addAttribute('MX');
		parent::addAttribute('DI');
		parent::addAttribute('DF');
		parent::addAttribute('ENT_COL');//FK entidade_coletiva
		parent::addAttribute('USER');
		parent::addAttribute('DESCRI');
		parent::addAttribute('REP');
		parent::addAttribute('TP');
		parent::addAttribute('CB');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
}//TRecord

?>