<?php
class Cobcontrato Extends TRecord
{	
	const TABLENAME  = 'COBCONTRATO';
	const PRIMARYKEY = 'ID';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $cobertura;
	
	public function __construct($ID = NULL)
	{
		parent::__construct($ID);
		
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		parent::addAttribute('CONTRATO_ID');//FK CONTRATOS2
		parent::addAttribute('COBERTURA_ID');//FK COBERTURAS
		parent::addAttribute('VALOR');
		parent::addAttribute('TAXA');
		parent::addAttribute('VL_COBERTURA');
		parent::addAttribute('VL_REPASSE');
		parent::addAttribute('VL_FINANCIADO');
		parent::addAttribute('SITUACAO');
		parent::addAttribute('PARCELAS_COB');
		parent::addAttribute('DT_INICIO');
		parent::addAttribute('CARENCIA');
		parent::addAttribute('DT_CADASTRO');
		parent::addAttribute('TP_IOF');
		parent::addAttribute('IOF');
		parent::addAttribute('VL_PARCELA');
		parent::addAttribute('DT_SITUACAO');
				   
		
	}//function __construct
	
	public function set_cobertura(Cobertura $cobertura)
    {
	   $this->COBERTURA    = $cobertura; // armazena o objeto
	   $this->COBERTURA_ID = $cobertura->COBERTURA_ID; // armazena o id do objeto
	   
    }//set_cobertura
	
	public function get_cobertura()
    {
	    if(empty($this->cobertura))
		{
			$this->cobertura = new Cobertura($this->COBERTURA_ID);
		}
		
		return $this->cobertura; 
		
    }//get_cobertura
	
}//TRecord

?>


