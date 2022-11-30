<?php
class Banco Extends TRecord
{	
	const TABLENAME  = 'BANCOS';
	const PRIMARYKEY = 'CODIGO';
	const IDPOLICY   = 'max';
	
	const CREATEDAT  = 'created_at';
	const UPDATEDAT  = 'updated_at';
	
	public function __construct($CODIGO = NULL)
	{
		parent::__construct($CODIGO);
		
		parent::addAttribute('BANCO');
		parent::addAttribute('COD_CONVENIO');
		parent::addAttribute('SEQUENCIA');
		parent::addAttribute('VALOR_DOC');
		parent::addAttribute('LINHA1');
		parent::addAttribute('LINHA2');
		parent::addAttribute('LINHA3');
		parent::addAttribute('LINHA4');
		parent::addAttribute('LINHA5');
		parent::addAttribute('LINHA6');
		parent::addAttribute('CNPJ_L');
		parent::addAttribute('CNPJ_C');
		parent::addAttribute('VENC_L');
		parent::addAttribute('VENC_C');
		parent::addAttribute('EMI_L');
		parent::addAttribute('EMI_C');
		parent::addAttribute('VALOR_L');
		parent::addAttribute('VALOR_C');
		parent::addAttribute('L1_L');
		parent::addAttribute('L1_C');
		parent::addAttribute('L2_L');
		parent::addAttribute('L2_C');
		parent::addAttribute('L3_L');
		parent::addAttribute('L3_C');
		parent::addAttribute('L4_L');
		parent::addAttribute('L4_C');
		parent::addAttribute('L5_L');
		parent::addAttribute('L5_C');
		parent::addAttribute('L6_L');
		parent::addAttribute('L6_C');
		parent::addAttribute('NOME_L');
		parent::addAttribute('NOME_C');
		parent::addAttribute('MAT_L');
		parent::addAttribute('MAT_C');
		parent::addAttribute('CTO_L');
		parent::addAttribute('CTO_C');
		parent::addAttribute('END_L');
		parent::addAttribute('END_C');
		parent::addAttribute('BAI_L');
		parent::addAttribute('BAI_C');
		parent::addAttribute('CEP_L');
		parent::addAttribute('CEP_C');
		parent::addAttribute('CID_L');
		parent::addAttribute('CID_C');
		parent::addAttribute('UF_L');
		parent::addAttribute('UF_C');
		parent::addAttribute('LINHAS');
		parent::addAttribute('TAXA_DEB');
		parent::addAttribute('PREF');
		parent::addAttribute('NRO');
		parent::addAttribute('CARTEIRA');
		parent::addAttribute('AGENCIA');
		parent::addAttribute('DIG_AG');
		parent::addAttribute('CEDENTE');
		parent::addAttribute('DIG_CED');
		parent::addAttribute('CART_CN');
		parent::addAttribute('AG_CN');
		parent::addAttribute('CC_CN');
		parent::addAttribute('CEMP_CN');
		parent::addAttribute('SEQ_CN');
		parent::addAttribute('PROT_CN');
		parent::addAttribute('PROTD_CN');
		parent::addAttribute('PRIM_CN');
		parent::addAttribute('CEDENTE2');
		parent::addAttribute('NOME_CED');
		parent::addAttribute('VL_DOC2');
		parent::addAttribute('CNPJ');
		parent::addAttribute('CNAB');
		parent::addAttribute('MULTA');
		parent::addAttribute('MORA_DIA');
		parent::addAttribute('created_at');
		parent::addAttribute('updated_at');
 				   
		
	}//function __construct
	
}//TRecord

?>