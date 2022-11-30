<?php
class cliente Extends TRecord
{	
	const TABLENAME  = 'CLIENTES4';//CLIENTES
	const PRIMARYKEY = 'MATR_INTERNA';
	const IDPOLICY   = 'max';
	
	//const CREATEDAT  = 'created_at';
	//const UPDATEDAT  = 'updated_at';
	
	private $nome_uf;
	
	public function __construct($MATR_INTERNA = NULL)
	{
		parent::__construct($MATR_INTERNA);
		
		parent::addAttribute('TIPO');
		parent::addAttribute('CPF');
		parent::addAttribute('NOME');
		parent::addAttribute('DT_CADASTRO');
		parent::addAttribute('NASCIMENTO');
		parent::addAttribute('IDADE');
		parent::addAttribute('IDENTIDADE');
		parent::addAttribute('ORGAO_EMISSOR');
		parent::addAttribute('DT_EMISSAO');
		parent::addAttribute('INSTRUCAO');
		parent::addAttribute('SEXO');
		parent::addAttribute('ESTADO_CIVIL');
		parent::addAttribute('CPF_CONJ');
		parent::addAttribute('CONJ');
		parent::addAttribute('STATUS');
		parent::addAttribute('NACIONALIDADE');
		parent::addAttribute('FAT_MENS');
		parent::addAttribute('NOME_PAI');
		parent::addAttribute('NOME_MAE');
		parent::addAttribute('CEP_RES');
		parent::addAttribute('END_RES');
		parent::addAttribute('END_NRO');
		parent::addAttribute('END_COMPL');
		parent::addAttribute('BAIRRO_RES');
		parent::addAttribute('CIDADE_RES');
		parent::addAttribute('UF_RES');//fk 'UF'
		//parent::addAttribute('UF_RES2');
		parent::addAttribute('DDD_RES');
		parent::addAttribute('FONE_RES');
		parent::addAttribute('DDD_CEL');
		parent::addAttribute('FONE_CEL');
		parent::addAttribute('E_MAIL');
		parent::addAttribute('OBS_BANCARIA');
		
		parent::addAttribute('NOME_EMP');
		parent::addAttribute('CNPJ_EMP');
		parent::addAttribute('TIPO_EMP');
		parent::addAttribute('CEP_EMP');
		parent::addAttribute('END_EMP');
		parent::addAttribute('EMP_NRO');
		parent::addAttribute('EMP_COMPL');
		parent::addAttribute('BAIRRO_EMP');
		parent::addAttribute('CIDADE_EMP');
		parent::addAttribute('UF_EMP');
		parent::addAttribute('CARGO');
		parent::addAttribute('DDD_EMP');
		parent::addAttribute('FONE_EMP');
		parent::addAttribute('RAMAL_EMP');
		parent::addAttribute('FAX');
		parent::addAttribute('DT_ADMISSAO');
		parent::addAttribute('PROFISSAO');
		parent::addAttribute('MATR_ORGAO');
		parent::addAttribute('VL_SALARIO');
		parent::addAttribute('VL_OUTROS_REND');
		parent::addAttribute('DESC_OUTROS_REND');
		parent::addAttribute('TP_AT_CLI');
		parent::addAttribute('NOME_REF1');
		parent::addAttribute('DDD_REF1');
		parent::addAttribute('FONE_REF1');
		parent::addAttribute('GRAU_REF1');
		parent::addAttribute('NOME_REF2');
		parent::addAttribute('DDD_REF2');
		parent::addAttribute('FONE_REF2');
		parent::addAttribute('GRAU_REF2');
		
		parent::addAttribute('CPF1');
		parent::addAttribute('RZ1');
		parent::addAttribute('CPF2');
		parent::addAttribute('RZ2');
		parent::addAttribute('CPF3');
		parent::addAttribute('RZ3');
		parent::addAttribute('DT_FUND');
		parent::addAttribute('PATR');
		parent::addAttribute('COD_ATIV');
		parent::addAttribute('RAMO_ATIV');
		
		parent::addAttribute('LIM_CRED');
		parent::addAttribute('CEP_CORR');
		parent::addAttribute('END_CORR');
		parent::addAttribute('END_NRO_CORR');
		parent::addAttribute('END_COMPL_CORR');
		parent::addAttribute('BAIRRO_CORR');
		parent::addAttribute('CIDADE_CORR');
		parent::addAttribute('UF_CORR');
		//parent::addAttribute('CREATED_AT');
		//parent::addAttribute('updated_at');
		
	}//function __construct
	
	public function get_nome_uf()
	{
		if(empty($this->nome_uf))
		{
			$this->nome_uf = new uf($this->UF_RES);//fk 'UF'
		}
		return $this->nome_uf;	
		
	}//get_nome_uf
	
	function get_nome_cliente()
	{
		return $this->NOME.'-'.$this->MATR_INTERNA;
	}
	
	
}//TRecord

?>