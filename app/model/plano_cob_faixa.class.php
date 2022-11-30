<?php
class plano_cob_faixa Extends TRecord
{	
	const TABLENAME  = 'PLANOS_COB_FAIXAS';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $plano_cobertura;
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('PLANO');//FK Plano
		parent::addAttribute('COBERTURA');// FK Coberturas
		parent::addAttribute('FAIXA');
		parent::addAttribute('ID_INI');
		parent::addAttribute('ID_FIM');
		parent::addAttribute('INC');
		parent::addAttribute('PU_PURO');
		parent::addAttribute('PU_PURO2');
		parent::addAttribute('PU_PURO3');
		parent::addAttribute('CARREG');
		parent::addAttribute('VL_REL');
		parent::addAttribute('CARREG2');;
		parent::addAttribute('created_at');;
		parent::addAttribute('updated_at');;
		
		
	}//function __construct
	
	/*
	Retorna o nome da cobertura ,
	quando instanciado um obj plano
	*/
	public function get_plano_cobertura()
	{
		if(empty($this->plano_cobertura))
		{
			$this->plano_cobertura = new cobertura($this->COBERTURA);//FK plano
		}
		
		return $this->plano_cobertura;	
	}
	
}//TRecord

?>