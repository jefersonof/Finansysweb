<?php
 
 class finanr2 extends TRecord
 {
   const TABLENAME  = 'FINANR2';//FINANR2
   const PRIMARYKEY = 'ID_FINANR'; 
   const ODPOLICY   = 'max';
   
   const CREATEDAT  = 'created_at';
   const UPDATEDAT  = 'updated_at';
   
   public function __construct ($ID_FINANR = NULL) 
   {
       parent :: __construct( $ID_FINANR );     
     
       parent::addattribute('DATA_LANCAMENTO');
       parent::addattribute('VALOR_PAGAR');
       parent::addattribute('VALOR_PAGO');
       parent::addattribute('CONTRATO_ID');//fk contratos2 
       parent::addattribute('PARCELA_CTO');
       parent::addattribute('PARCELAS');
       parent::addattribute('DATA_VENCIMENTO');
       parent::addattribute('created_at');
       parent::addattribute('updated_at');
	   
	   

    
   }//__construct 

 }//TRecord

 

?>
