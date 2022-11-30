<?php
class entidades Extends TRecord
{	
	const TABLENAME  = 'ENTIDADES';
	const PRIMARYKEY = 'COD_INT';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $Ent_Cod_Desc;
	private $Org;
	
	public function __construct($COD_INT = NULL)
	{
		parent::__construct($COD_INT);
		
		parent::addAttribute('RAZAO_SOCIAL');
		parent::addAttribute('CNPJ');
		parent::addAttribute('ENDERECO');
		parent::addAttribute('BAIRRO');
		parent::addAttribute('CIDADE');
		parent::addAttribute('CEP');
		parent::addAttribute('ESTADO');
		parent::addAttribute('TELEFONE');
		parent::addAttribute('FAX');
		parent::addAttribute('OBS');
		parent::addAttribute('COD_FEDERAL');
		parent::addAttribute('PERC_DESC');
		parent::addAttribute('TIPO');
		parent::addAttribute('COD_EXT');
		parent::addAttribute('COD_IPE');
		parent::addAttribute('INSC_ESTADUAL');
		parent::addAttribute('RESPONSAVEL');
		parent::addAttribute('PRODUTO');
		parent::addAttribute('REAJ');
		parent::addAttribute('CARENCIA');
		parent::addAttribute('INST');
		parent::addAttribute('ATIVO');
		parent::addAttribute('STATUS');
		parent::addAttribute('TIPO_COBRANCA');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	/*
		Salva uma entidade e os agregados 'entidades_cod_desc' e 'org'    
	*/
	public function store()
    {
        parent::store();
       
             //tabela relacional ,  FK  , id do obj    , vetor   
		parent::saveComposite('entidades_cod_desc',  'ENT_COL', $this->COD_INT, $this->Ent_Cod_Desc);
		
		parent::saveComposite('org',  'ENT_COL', $this->COD_INT, $this->Org);
		
		
    }//function store
	
	/*
		Trás as entidade e os agregados 'entidades_cod_desc' e 'org'    
	*/
	public function load($COD_INT)//ID do próprio 'Obj'
	{
		//                 classe meio de campo   Fk da classe    ID 'obj'   
		$this->Ent_Cod_Desc = parent::loadComposite('entidades_cod_desc', 'ENT_COL', $COD_INT);
		$this->Org          = parent::loadComposite('org', 'ENT_COL', $COD_INT);
		
		return parent::load($COD_INT);
		
	}//Load
	
	/*
	retorna as 'entidades_cod_desc' em forma de array
	*/
	public function getEnt_Cod_Desc()
    {
        return $this->Ent_Cod_Desc;
		
    }//getEnt_Cod_Desc
	
	/*
	Add os 'entidades_cod_desc's no array @$this->Ent_Cod_Desc 
	*/
	public function addEnt_Cod_Desc(entidades_cod_desc $object)
    {
        $this->Ent_Cod_Desc[] = $object;
		
    }//addEnt_Cod_Desc
	
	/*
	retorna as 'org' em forma de array
	*/
	public function getOrg()
    {
        return $this->Org;
		
    }//getOrg
	
	/*
	Add os 'org's no array @$this->org 
	*/
	public function addOrg(Org $object)
    {
        $this->Org[] = $object;
		
    }//addOrg
	
	
}//TRecord

?>