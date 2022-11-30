<?php
class plano Extends TRecord
{	
	const TABLENAME  = 'PLANOS';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $Plano_Cob;
	private $Plano_Cob_Faixa;
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('PLANO');
		parent::addAttribute('STATUS');
		parent::addAttribute('OBS');
		parent::addAttribute('COD_IPE');
		parent::addAttribute('TIPO');
		parent::addAttribute('COD_FED');
		parent::addAttribute('COD_EST');
		parent::addAttribute('COD_ULBRA');
		parent::addAttribute('TIPO2');
		parent::addAttribute('PROCESSO_SUSEP');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
		
	}//function __construct
	
	
	public function store()
    {
        parent::store();
       
		//composição entre PLANO e Plano_Cob ; plano_cob_faixa
                        //tabela relacional ,  FK  		  , id do obj          , vetor   
		parent::saveComposite('plano_cob',  'COD_PLANO', $this->CODIGO, $this->Plano_Cob);
		parent::saveComposite('plano_cob_faixa', 'PLANO', $this->CODIGO, $this->Plano_Cob_Faixa);
		
    }//function store
	
	public function load($CODIGO)//ID do próprio 'Obj'
	{
		//                            classe meio de campo   Fk da classe    ID 'obj'   
		$this->Plano_Cob       = parent::loadComposite('plano_cob', 'COD_PLANO', $CODIGO);
		$this->Plano_Cob_Faixa = parent::loadComposite('plano_cob_faixa', 'PLANO', $CODIGO);
		
		return parent::load($CODIGO);
		
	}//load
	
	/*
	retorna os 'Planos_cob' em forma de array
	*/
	public function getPlano_Cob()
    {
        return $this->Plano_Cob;
		
    }//getCm_af
	
	/*
	Add os 'Planos_cob's no array @$this->Plano_Cob 
	*/
	public function addPlano_Cob(Plano_Cob $object)
    {
        $this->Plano_Cob[] = $object;
		
    }//addPlano_Cob
	
	/*
	retorna os 'Planos_cob_Faixa' em forma de array
	*/
	public function getPlano_Cob_Faixa()
    {
        return $this->Plano_Cob_Faixa;
		
    }//getPlano_Cob_Faixa
	
	/*
	Add os 'Planos_cob's no array @$this->Plano_Cob 
	*/
	public function addPlano_Cob_Faixa(plano_cob_faixa $object)
    {
        $this->Plano_Cob_Faixa[] = $object;
		
    }//addPlano_Cob_Faixa
	
	
	/*
	public function store()
    {
        parent::store();
       
		//composição entre contrato e cobcontrato
                        //tabela relacional ,  FK  		  , id do obj          , vetor   
		parent::saveComposite('cm_af',  'AGENTE', $this->CODIGO, $this->cm_af);
		//parent::saveComposite('cm_pec', 'AGENTE', $this->CODIGO, $this->cm_pec);
		
        //parent::saveAggregate('CobContrato', 'contrato_id', 'cobertura_id', $this->id, $this->coberturas);
        
    }//function store
	*/
	
	
}//TRecord

?>