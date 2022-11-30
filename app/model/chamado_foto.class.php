<?php
 class chamado_foto extends TRecord
 {
   const TABLENAME  = 'CHAMADOS_FOTO';
   const PRIMARYKEY = 'id_chamado_foto';
   const ODPOLICY   = 'max';
   
   const CREATEDAT  = 'created_at';
   const UPDATEDAT  = 'updated_at';
   
   use SystemChangeLogTrait;
   
   public function __construct ($id_chamado_foto = NULL) 
   {
     parent :: __construct( $id_chamado_foto );     
     
     parent::addattribute('fk_chamado');
     parent::addattribute('foto');
     parent::addattribute('created_at');
     parent::addattribute('updated_at');
   }//__construct
   
 }//TRecord

?>
