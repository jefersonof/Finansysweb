<?php
class fornec_resp Extends TRecord
{	
	const TABLENAME  = 'FORNEC_RESP';
	const PRIMARYKEY = 'CONTROLE';
	const IDPOLICY   = 'max';
	
	const CREATEDAT = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($CONTROLE = NULL)
	{
		parent::__construct($CONTROLE);
		
		parent::addAttribute('NOME');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	
}//TRecord

?>