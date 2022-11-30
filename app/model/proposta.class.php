<?php
class proposta Extends TRecord
{	
	const TABLENAME  = 'PROPOSTA';
	const PRIMARYKEY = 'ID_PROPOSTA';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $proposta_foto;
	private $proposta_status;
	private $nome_corretor;
	
	public function __construct($ID_PROPOSTA = NULL)
	{
		parent::__construct($ID_PROPOSTA);
		
		parent::addAttribute('CORRETOR');//fk fornecedor
		parent::addAttribute('FUNCIONARIO');
		parent::addAttribute('DESCRICAO');
		parent::addAttribute('FOTO');
		parent::addAttribute('NOME');
		parent::addAttribute('CPF');
		parent::addAttribute('STATUS');
		parent::addAttribute('USER_ID');
		parent::addAttribute('USER_NAME');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	
	/*
	retorna o nome do corretor
	*/
	public function get_nome_corretor()
	{
		if(empty($this->nome_corretor))
		{
			$this->nome_corretor = new fornecedor($this->CORRETOR);//FK
		}
		return $this->nome_corretor;	
		
	}//get_nome_corretor
	
}//TRecord

?>