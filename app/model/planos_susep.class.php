<?php
class planos_susep Extends TRecord
{	
	const TABLENAME  = 'PLANOS_SUSEP';
	const PRIMARYKEY = 'ID_PLANOS_SUSEP';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $nome_ramo;
	
	public function __construct($ID_PLANOS_SUSEP = NULL)
	{
		parent::__construct($ID_PLANOS_SUSEP);
		
		parent::addAttribute('PROCESSO');
		parent::addAttribute('DESCRICAO');
		parent::addAttribute('ATIVO');
		parent::addAttribute('REGIME_FINANCEIRO');
		parent::addAttribute('PLNCODIGO');
		parent::addAttribute('PROCESSO2');
		parent::addAttribute('CARREG');
		parent::addAttribute('TIPO');
		parent::addAttribute('GRUPO');//fk_grupo
		parent::addAttribute('RAMO');//fk_ramo
		parent::addAttribute('TIPO_PLANO');
		parent::addAttribute('TIPO_PRODUTO');
		parent::addAttribute('PRAZO_PAG');
		parent::addAttribute('TAXA_JUROS');
		parent::addAttribute('TAB_SERVICO');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	public function get_nome_ramo()
	{
		if(empty($this->nome_ramo))
		{
			$this->nome_ramo = new seg_ramos($this->RAMO);//fk 'RAMO'
		}
		return $this->nome_ramo;	
		
	}//get_grupo
	
}//TRecord

?>