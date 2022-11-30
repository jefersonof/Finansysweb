<?php
 class chamado extends TRecord
 {
   const TABLENAME  = 'CHAMADOS';
   const PRIMARYKEY = 'id_chamado';
   const ODPOLICY   = 'max';
   
   const CREATEDAT  = 'created_at';
   const UPDATEDAT  = 'updated_at';
   
   //use SystemChangeLogTrait;
   
   private $chamado_foto;
   
   public function __construct ($id_chamado = NULL) 
   {
     parent :: __construct( $id_chamado );     
     
     parent::addattribute('problema');
     parent::addattribute('data_chamado');
     parent::addattribute('observacao');
     parent::addattribute('setor');
     parent::addattribute('modelo_pc');
     parent::addattribute('ip');
     parent::addattribute('nome');
     parent::addattribute('solucao');
     parent::addattribute('hora');
     parent::addattribute('status');
     parent::addattribute('data_solucao');
     parent::addattribute('usuario');
     parent::addattribute('created_at');
     parent::addattribute('updated_at');
     
   }//__construct
   
   public function store()
    {
        parent::store();
    }//store
   
   public function load($id_chamado)
	{
		$this->chamado_foto   = parent::loadComposite('chamado_foto',  'fk_chamado', $id_chamado);
		
		return parent::load($id_chamado);
		
	}//load
	
	public function delete($id_chamado = NULL)
    {
        
	    $id_chamado = isset($id_chamado) ? $id_chamado: $this->id_chamado;
        parent::deleteComposite('chamado_foto', 'fk_chamado', $id_chamado);
        
	   parent::delete( $id_chamado );
	   
		
    }//function delete
	
	/*
	retorna os 'chamado_foto' em forma de array
	*/
	public function getchamado_foto()
    {
        return $this->chamado_foto;
		
    }//getchamado_foto
  
   
 }//TRecord

?>
