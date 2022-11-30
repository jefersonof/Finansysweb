<?php
class entidades_cod_desc Extends TRecord
{	
	const TABLENAME  = 'ENTIDADES_COD_DESC';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $nome_entidade;
	private $nome_tipo_cto;
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('ENT_COL');//FK ENTIDADES
		parent::addAttribute('TIPO_CTO');//FK TIPO_CTO
		parent::addAttribute('COD_DESC');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	/*
	Retorna o nome da entidades ,
	quando instanciado um obj entidades_cod_desc
	*/
	public function get_nome_entidade()
	{
		if(empty($this->nome_entidade))
		{
			$this->nome_entidade = new entidades($this->ENT_COL);//FK entidades
		}
		
		return $this->nome_entidade;
		
	}//get_nome_entidade
	
	/*
	Retorna o nome dO tipo de contrato ,
	quando instanciado um obj plano
	*/
	public function get_nome_tipo_cto()
	{
		if(empty($this->nome_tipo_cto))
		{
			$this->nome_tipo_cto = new tipo_cto($this->TIPO_CTO);//FK entidades
		}
		
		return $this->nome_tipo_cto;
		
	}//get_nome_tipo_cto
	
}//TRecord

?>