<?php
class fornecedor Extends TRecord
{	
	const TABLENAME  = 'FORNECEDORES4';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $cm_af;
	private $cm_pec;
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('NOME');
		parent::addAttribute('NOME_FANTASIA');
		parent::addAttribute('RESPONSAVEL');
		parent::addAttribute('E_MAIL');
		parent::addAttribute('DATA_CADASTRAMENTO');
		parent::addAttribute('CPF_CNPJ');
		parent::addAttribute('ID_IE');
		parent::addAttribute('EMP');
		parent::addAttribute('TELEFONE');
		parent::addAttribute('TELEFONE2');
		parent::addAttribute('FAX');
		parent::addAttribute('CONTATO');
		parent::addAttribute('ENDERECO');
		parent::addAttribute('CEP');
		parent::addAttribute('BAIRRO');
		parent::addAttribute('CIDADE');
		parent::addAttribute('UF');//RET
		parent::addAttribute('RETPJ');
		parent::addAttribute('IRRF');
		parent::addAttribute('ISSQN');
		parent::addAttribute('BANCO');
		parent::addAttribute('USUARIO');
		parent::addAttribute('AGENCIA');
		parent::addAttribute('CONTA_CORRENTE');
		parent::addAttribute('TIPO');
		parent::addAttribute('STATUS');
		parent::addAttribute('FIS_JUR');
		parent::addAttribute('OBS');
		parent::addAttribute('USER_ID');
		parent::addAttribute('NOME_USER');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	public function store()
    {
        parent::store();
       
		//composição entre contrato e cobcontrato
                        //tabela relacional ,  FK  		  , id do obj          , vetor   
		parent::saveComposite('cm_af',  'AGENTE', $this->CODIGO, $this->cm_af);
		parent::saveComposite('cm_pec', 'AGENTE', $this->CODIGO, $this->cm_pec);
		
        //parent::saveAggregate('CobContrato', 'contrato_id', 'cobertura_id', $this->id, $this->coberturas);
        
    }//function store
	
	
	public function load($CODIGO)
	{
		$this->cm_af  = parent::loadComposite('cm_af',  'AGENTE', $CODIGO);
		$this->cm_pec = parent::loadComposite('cm_pec', 'AGENTE', $CODIGO);
		
		return parent::load($CODIGO);
		
	}//load
	
	
	/*
	retorna as AF's em forma de array
	*/
	public function getcm_af()
    {
        return $this->cm_af;
		
    }//getCm_af
	
	/*
	Add as AF's no array @$this->cm_af 
	*/
	public function addcm_af(cm_af $object)
    {
        $this->cm_af[] = $object;
		
    }//addAc_mf
	
	
	/*
	retorna as PEC's em forma de array
	*/
	public function getcm_pec()
    {
        return $this->cm_pec;
		
    }//getcm_pec
	
	/*
	Add as PEC's no array @$this->cm_pec 
	*/
	public function addcm_pec(cm_pec $object)
    {
        $this->cm_pec[] = $object;
		
    }//addcm_pec
	
	
	
	
}//TRecord

?>