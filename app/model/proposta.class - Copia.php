<?php
class proposta Extends TRecord
{	
	const TABLENAME  = 'PROPOSTA';
	const PRIMARYKEY = 'ID_PROPOSTA';
	const IDPOLICY   = 'max';
	
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
		
	}//function __construct
	
	public function store()
    {
        parent::store();
       
		//composição entre proposta e proposta_foto
                        //tabela relacional ,  FK  		  , id do obj          , vetor   
		//parent::saveComposite('proposta_foto',  'PROPOSTA_ID', $this->ID_PROPOSTA, $this->proposta_foto);
		
    }//function store
	
	public function load($ID_PROPOSTA)
	{
		$this->proposta_foto   = parent::loadComposite('proposta_foto',  'PROPOSTA_ID', $ID_PROPOSTA);
		
		return parent::load($ID_PROPOSTA);
		
	}//load
	
	public function delete($ID_PROPOSTA = NULL)
    {
        $ID_PROPOSTA = isset($ID_PROPOSTA) ? $ID_PROPOSTA: $this->ID_PROPOSTA;
       
        //parent::deleteComposite('Finanr3', 'CONTRATO_OLD', $CONTRATO);
        parent::deleteComposite('proposta_foto', 'PROPOSTA_ID', $ID_PROPOSTA);
        
	   parent::delete( $ID_PROPOSTA );
		
    }//function delete
	
	/*
	Add os 'proposta_foto's no array @$this->proposta_foto 
	*/
	public function addproposta_foto(proposta_foto $object)
    {
        $this->proposta_foto[] = $object;
		
    }//addproposta_foto
	
	/*
	retorna os 'proposta_foto' em forma de array
	*/
	public function getproposta_foto()
    {
        return $this->proposta_foto;
		
    }//getproposta_foto
	
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