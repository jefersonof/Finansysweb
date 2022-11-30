<?php
class motivo_cancelamento Extends TRecord
{	
	const TABLENAME  = 'MOTIVOS_CANCELAMENTO';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('MOTIVO');
		parent::addAttribute('STATUS');
		parent::addAttribute('COM_FED');
		parent::addAttribute('OBS');
		parent::addAttribute('REINC');
		parent::addAttribute('STATUSA');
		parent::addAttribute('ALTPEC');
		parent::addAttribute('NA');
		parent::addAttribute('APO_SEG');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
}//TRecord

?>