<?php
class proposta_foto Extends TRecord
{	
	const TABLENAME  = 'PROPOSTA_FOTO';
	const PRIMARYKEY = 'ID_PROPOSTA_FOTO';
	const IDPOLICY   = 'max';
	
	public function __construct($ID_PROPOSTA_FOTO = NULL)
	{
		parent::__construct($ID_PROPOSTA_FOTO);
		
		parent::addAttribute('PROPOSTA_ID');//FK PROPOSTA
		parent::addAttribute('FOTO');
		parent::addAttribute('DESCRICAO');
		
	}//function __construct
	
}//TRecord

?>