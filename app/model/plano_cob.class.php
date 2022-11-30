<?php
class plano_cob Extends TRecord
{	
	const TABLENAME  = 'PLANOS_COB';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $plano_cobertura;
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('COD_PLANO');//FK Plano
		parent::addAttribute('COD_COBERTURA');// FK Coberturas
		parent::addAttribute('created_at');// FK Coberturas
		parent::addAttribute('updated_at');// FK Coberturas
		
		
	}//function __construct
	
	/*
	Retorna o nome da cobertura ,
	quando instanciado um obj plano
	*/
	public function get_plano_cobertura()
	{
		if(empty($this->plano_cobertura))
		{
			$this->plano_cobertura = new cobertura($this->COD_COBERTURA);//FK plano
		}
		
		return $this->plano_cobertura;	
	}
	
	
}//TRecord

?>