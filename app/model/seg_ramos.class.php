<?php
class seg_ramos Extends TRecord
{	
	const TABLENAME  = 'SEG_RAMOS';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDEAT = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $nome_grupo;
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('RAMO');
		parent::addAttribute('GRUPO');//fk 'GRUPO'
		parent::addAttribute('GRUPORAMO');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	
	
	public function get_nome_grupo()
	{
		if(empty($this->nome_grupo))
		{
			$this->nome_grupo = new seg_grupos($this->GRUPO);//fk 'GRUPO'
		}
		return $this->nome_grupo;	
		
	}//get_grupo
	
	
	/*
	public function set_grupo(seg_grupo $grupo)
	{
		$this->grupo        = $grupo;// armazena o objeto
		$this->grupo_CODIGO = $grupo->CODIGO;// armazena o objeto
		
	}//get_grupo
	*/
	
	
	
	
	
}//TRecord

?>