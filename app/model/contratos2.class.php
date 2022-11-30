<?php
class Contratos2 Extends TRecord
{	
	const TABLENAME  = 'CONTRATOS4';//CONTRATOS2
	const PRIMARYKEY = 'ID_CONTRATO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	private $cobcontrato;
	private $finanr2;
	private $cliente;
	private $plano;
	private $fornecedor;
	private $entidade;
	
	public function __construct($ID_CONTRATO = NULL)
	{
		parent::__construct($ID_CONTRATO);
		
		parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
		parent::addAttribute('MATR_ORGAO');
        parent::addAttribute('DT_INICIO');
        parent::addAttribute('TIPO_CTO');
        parent::addAttribute('FORMA_PGTO');
        parent::addAttribute('VALOR');
        parent::addAttribute('PARCELAS');
        parent::addAttribute('VL_PARCELA');
        parent::addAttribute('TAXA_JUROS');
        parent::addAttribute('DT_PAGAMENTO');
        parent::addAttribute('AGENTE');//FK FORNECEDOR
        parent::addAttribute('MATR_INTERNA');//FK CLIENTE
        parent::addAttribute('ENTIDADE_COLETIVA');//FK ENTIDADE
        parent::addAttribute('TP_PLANO');//FK PLANO
        
		parent::addAttribute('FORMA_LIBE');
		parent::addAttribute('DATA_LIBE');
		parent::addAttribute('DIA_CARNE');
		parent::addAttribute('DIA_DEBITO');
		parent::addAttribute('COD_DEBITO');
		parent::addAttribute('CONTA_CORRENTE');
		parent::addAttribute('AGENCIA');
		parent::addAttribute('BANCO');
		parent::addAttribute('TI');
		parent::addAttribute('VAL_RET');
		parent::addAttribute('CONSIGNADO');
		parent::addAttribute('DT_CADASTRO');
		parent::addAttribute('VINC');
		parent::addAttribute('VL_CTO');
		parent::addAttribute('PROPOSTA_FIS');
		// parent::addAttribute('NASCIMENTO');TP_IOF
		parent::addAttribute('MATR_PENS');
		//parent::addAttribute('NOME');
		parent::addAttribute('PROPOSTA');
		
	}//function __construct
	
	public function load($ID_CONTRATO)
    {   
        $this->cobcontrato = parent::loadComposite('CobContrato', 'CONTRATO_ID', $ID_CONTRATO);
        
		$this->finanr2     = parent::loadComposite('Finanr2', 'CONTRATO_ID', $ID_CONTRATO);
	    
        return parent::load($ID_CONTRATO);
    }//function load
	
	public function store()
    {
        parent::store();
        
		//composição entre contrato e cobcontrato
                        //tabela relacional ,  FK  		  , id do obj          , vetor   
		parent::saveComposite('CobContrato', 'CONTRATO_ID', $this->ID_CONTRATO, $this->cobcontrato);
		
		
		//composição entre contrato e Finanr2
		parent::saveComposite('Finanr2', 'CONTRATO_ID', $this->ID_CONTRATO, $this->finanr2);
		
        
    }//function store
	
	public function delete($ID_CONTRATO = NULL)
    {
		
        $ID_CONTRATO = isset($ID_CONTRATO) ? $ID_CONTRATO: $this->ID_CONTRATO;
       
	    //parent::deleteComposite('proposta_foto', 'PROPOSTA_ID', $ID_PROPOSTA);
        parent::deleteComposite('finanr2', 'CONTRATO_ID', $ID_CONTRATO);
        parent::deleteComposite('Cobcontrato', 'CONTRATO_ID', $ID_CONTRATO);
        parent::delete( $ID_CONTRATO );
    }//function delete
	
	public function addCobcontrato(CobContrato $object)
    {
        $this->cobcontrato[] = $object;
		
    }//addCobcontrato
	
	public function addFinanr2(finanr2 $object)
    {
        $this->finanr2[] = $object;
		
    }//addCobcontrato
	
	public function getCobcontrato()
    {
        return $this->cobcontrato;
		
    }//getCobcontrato
	
	public function getFinanr2()
    {
        return $this->finanr2;
		
    }//getFinanr2
	
	public function get_cliente()
	{
		if(empty($this->cliente))
		{	
			$this->cliente = new Cliente($this->MATR_INTERNA);
		}
		return $this->cliente;
	}
	
	public function get_plano()
	{
		if(empty($this->plano))
		{	
			$this->plano = new Plano($this->TP_PLANO);
		}
		return $this->plano;
	}
	
	public function get_entidade()
	{
		if(empty($this->entidade))
		{	
			$this->entidade = new entidades($this->ENTIDADE_COLETIVA);
		}
		return $this->entidade;
	}
	
	public function get_fornecedor()
	{
		if(empty($this->fornecedor))
		{	
			$this->fornecedor = new fornecedor($this->AGENTE);
		}
		return $this->fornecedor;
	}
	
	/*public function get_cliente()
    {
	    if(empty($this->cliente))
		{
			$this->cliente = new Cliente($this->MATR_INTERNA);
		}
		
		return $this->cliente; 
		
    }//get_clientes*/
	
}//TRecord

?>