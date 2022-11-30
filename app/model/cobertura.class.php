<?php
class cobertura Extends TRecord
{	
	const TABLENAME  = 'COBERTURAS2';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $entgarantidora;
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('COBERTURA');
		parent::addAttribute('REAJUSTE');
		parent::addAttribute('MES_BASE_REAJUSTE');
		parent::addAttribute('PARCELAS_FAIXAS');
		parent::addAttribute('JUROS');
		parent::addAttribute('TAC');
		parent::addAttribute('TIPO');
		parent::addAttribute('OBS');
		parent::addAttribute('CONTA');
		parent::addAttribute('PROCESSO_SUSEP');
		parent::addAttribute('ENT_GAR');//FK ent_gar
		parent::addAttribute('REG_FIN');
		parent::addAttribute('NAOCALC');
		parent::addAttribute('NCOMIS');
		parent::addAttribute('COB2');
		parent::addAttribute('MSG_DOC');
		parent::addAttribute('APC');
		parent::addAttribute('DESC1');
		parent::addAttribute('DESC2');
		parent::addAttribute('SIGLA');
		parent::addAttribute('TARIFACAO');
		parent::addAttribute('GRUPO');
		parent::addAttribute('RAMO');
		parent::addAttribute('COD_MOR');
		parent::addAttribute('COD_INV');
		parent::addAttribute('COD');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	public function get_entgarantidora()
	{
		if(empty($this->entgarantidora))
		{
			$this->entgarantidora = new ent_gar($this->ENT_GAR);//FK ent_gar
		}
		
		return $this->entgarantidora;	
	}
	
	/*
	public function get_nome_grupo()
	{
		if(empty($this->nome_grupo))
		{
			$this->nome_grupo = new seg_grupos($this->GRUPO);//fk 'GRUPO'
		}
		return $this->nome_grupo;	
		
	}//get_grupo
	
	
	*/
	
}//TRecord

?>