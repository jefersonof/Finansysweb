<?php
 class PC extends TRecord
 {
   const TABLENAME  = 'PC';
   const PRIMARYKEY = 'id';
   const ODPOLICY   = 'max';
   
   const CREATEDAT  = 'created_at';
   const UPDATEDAT  = 'updated_at';
   
   use SystemChangeLogTrait;//liga o registro de logs
   
   public function __construct($id = NULL) 
   {
     parent ::__construct($id);     
     
     parent::addattribute('nome');
     parent::addattribute('computador');
     parent::addattribute('ip');
     parent::addattribute('setor');
     parent::addattribute('obs');
     parent::addattribute('data_cad');
     parent::addattribute('mac_address');
     parent::addattribute('office');
     parent::addattribute('created_at');
     parent::addattribute('updated_at');
     
   }//__construct
   
 }//TRecord

?>

