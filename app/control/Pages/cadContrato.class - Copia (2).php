<?php
class cadContrato Extends TPage
{
	private $form ;
	
	public function __construct()
	{
		parent::__construct();
		
		//atributos
		$teste3        = new TEntry('TESTE3');
		
		$contrato      = new TEntry('ID_CONTRATO');
		$proposta      = new TEntry('PROPOSTA');
		$cadastramento = new TDate('DT_CADASTRO');
		$matricula     = new TEntry('MATR_INTERNA');
		$nome          = new TEntry('NOME');
		$matr_pens     = new TEntry('MATR_PENS');
		$nascimento    = new TDate('NASCIMENTO');
		$teste         = new TEntry('TESTE');
		$tipo_cto      = new TCombo('TIPO_CTO');//contrato
		$seq_fed       = new TEntry('SEQ_FED');
		$num_proposta  = new TEntry('PROPOSTA_FIS');
		$vigencia      = new TDate('DT_INICIO');
		$ini_vigencia  = new TDate('DT_PAGAMENTO');
		$cpf           = new TEntry('CPF');
		$tipo          = new TCombo('TIPO');
		$matr_orgao    = new TEntry('MATR_ORGAO');
		$vinc          = new TEntry('VINC');
		$nome_int      = new TEntry('NOME_INT');
		$cod_int       = new TDBSeekButton('COD_INT', 'DB2', 'formCadContrato', 'entidades', 'RAZAO_SOCIAL', 'COD_INT', 'NOME_INT');
		$nome_plano     = new TEntry('NOME_PLANO');
		$cod_plano      = new TDBSeekButton('CODIGO', 'DB2', 'formCadContrato', 'plano', 'PLANO', 'CODIGO', 'NOME_PLANO');
		
		$consignado     = new TEntry('CONSIGNADO');
		//$teste          = new TEntry('');
		$consignado2    = new TEntry('CONSIGNADO2');
		$nascimento2    = new TDate('NASCIMENTO2');
		$val_ret        = new TEntry('VAL_RET');
		$ti             = new TEntry('TI');
		$banco          = new TEntry('BANCO');
		$agencia        = new TEntry('AGENCIA');
		$conta_corrent  = new TEntry('CONTA_CORRENTE');
		$forma_pgto     = new TCombo('FORMA_PGTO');
		$cod_debito     = new TEntry('COD_DEBITO');
		$dia_debito     = new TEntry('DIA_DEBITO');
		$dia_carne      = new TEntry('DIA_CARNE');
		$data_libe      = new TDate('DATA_LIBE');
		$forma_libe     = new TCombo('FORMA_LIBE');
		$agente         = new TEntry('NOME_AGENTE');
		$cod_agente     = new TDBSeekButton('CODIGO2', 'DB2', 'formCadContrato', 'fornecedor', 'NOME', 'CODIGO2', 'NOME_AGENTE');
		
		//FORMATAÇÕES
		$forma_pgto->addItems(array('1' => 'Desconto em folha', '2' => 'Débito em conta', '3' => 'Carnê', '4' => 'Cheque'));
		$tipo_cto->addItems(array('A' => 'A | Assistência Financeira','P' => 'P | Pecúlio','S' => 'S | Seguro'));
		$forma_libe->addItems(array( 'Ch' => 'Cheque', 'De' => 'Depósito', 'Din' => 'Dinheiro', 'Tran' => 'Transferência Eletrônica' ));
		
		$contrato->setEditable(FALSE);
		$nome_int->setEditable(FALSE);
		$nome_plano->setEditable(FALSE);
		$agente->setEditable(FALSE);
		$matricula->setEditable(FALSE);
		
		//MÁSCARAS
		$cadastramento->setMask('dd/mm/yyyy');
		$cadastramento->setDataBaseMask('dd/mm/yyyy');
		$data_libe->setMask('dd/mm/yyyy');
		$data_libe->setDataBaseMask('dd/mm/yyyy');
		$nascimento->setMask('dd/mm/yyyy');
		$nascimento->setDataBaseMask('dd/mm/yyyy');
		
		$val_ret->setNumericMask(2, '.', ',', TRUE);
		
		//Cria os Btn
		$btn_avancar = TButton::create('btn_avancar', array($this, 'onAvancar'), 'Avançar', 'fa: fa-share blue' );
		
		$btn_voltar = TButton::create('btn_voltar', array($this, 'onVoltar'), 'Voltar', 'fa: fa-reply blue' );
		
		$btn_teste = TButton::create('btn_teste', array($this, 'onTeste3'), 'Teste3', 'fa: fa-reply blue' );
		
		$btn_teste2 = TButton::create('btn_teste2', array($this, 'onTeste4'), 'Teste4', 'fa: fa-reply blue' );
		
		//cria o form
		$this->form = new BootstrapFormBuilder('formCadContrato');
		$this->form->setFieldSizes('100%');
		
		$row = $this->form->addFields(['N° Contrato', $contrato],
									  ['Contrato', $tipo_cto],	
									  ['N° Proposta', $num_proposta],		
									  ['Cadastramento', $cadastramento],		
									  ['Matrícula', $matricula]		
									 );
		$row->layout =['col-sm-2', 'col-sm-3', 'col-sm-3', 'col-sm-2', 'col-sm-2'];	
		
		$row = $this->form->addFields(['Nome', $nome],
									  ['Cpf', $cpf],		
									  ['Matr. Orgão', $matr_orgao],		
									  ['Vinc', $vinc],		
									  ['Pensionista', $matr_pens]		
									 );
		$row->layout =['col-sm-5', 'col-sm-2', 'col-sm-2', 'col-sm-1', 'col-sm-2'];	
		
		$row = $this->form->addFields(['Cod Entidade', $cod_int],
									  ['Entidade', $nome_int]
									 );
		$row->layout =['col-sm-2', 'col-sm-10'];
		
		$row = $this->form->addFields(['Cod Plano', $cod_plano],
									  ['Plano', $nome_plano]
									 );
		$row->layout =['col-sm-2', 'col-sm-10'];
		
		$row = $this->form->addFields(['Consignado', $consignado],
									  ['Nascimento', $nascimento],		
									  ['Valor Ret.', $val_ret],		
									  ['T.I', $ti]		
									 );
		$row->layout =['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];	
		
		$row = $this->form->addFields(['Banco', $banco],
									  ['Agencia', $agencia],		
									  ['Conta', $conta_corrent],		
									  ['Pagamento', $forma_pgto],
									  ['Débito', $cod_debito]				
									 );
		$row->layout =['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-3', 'col-sm-3'];	
		
		$row = $this->form->addFields(['Dia débito', $dia_debito],
									  ['Dia Carnê', $dia_carne],		
									  ['Liberação', $forma_libe],
									  ['Data', $data_libe]				
									 );
		$row->layout =['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];
		
		$row = $this->form->addFields(['Cod Agente', $cod_agente],		
									  ['Agente', $agente]		
									 );
		$row->layout =['col-sm-2', 'col-sm-10'];
				
		//add compos no form
		$this->formFields = array($teste3, $btn_voltar, $btn_teste, $btn_teste2, $btn_avancar, $contrato, $proposta, $num_proposta, $cadastramento, $matricula, $nome, $cpf, $vinc, $matr_pens, $cod_int, $nome_int, $cod_plano, $nome_plano, $consignado, $nascimento, $val_ret, $ti, $banco, $agencia, $conta_corrent, $forma_pgto, $cod_debito, $dia_debito, $dia_carne, $forma_libe, $data_libe, $cod_agente, $agente, $tipo_cto, $matr_orgao);//formFields
		$this->form->setFieldS($this->formFields);
		
		//cria o painel
		$painel = new TPanelGroup('Contratos (T011)');
		$painel->addFooter(THBox::pack($btn_avancar));//$btn_voltar,	
		$painel->add($this->form);
		
		//cria TVBox
		$vbox = new TVBox;
		$vbox->add(new TXMLBreadCrumb('menu.xml', 'pesquisaAssociado'));
		$vbox->add($painel);
		
		parent::add($vbox);
	
	}//__construct
	
	public function onLoadForm($data)
	{
		$data_form = $this->form->getData();
		
		$obj = new STDClass;
		$obj->NOME           = $data['NOME'];
		$obj->CPF            = $data['CPF'];
		$obj->MATR_INTERNA   = $data['MATR_INTERNA'];
			
		$this->form->setData($obj);
			
		TSession::setValue('TS_data', $data_form);
		
		
	}//onLoadForm
	
	public function onLoadForm2($data)
	{	
		$obj = new STDClass;
		$obj->NOME           = $data['NOME'];
		$obj->CPF            = $data['CPF'];
		$obj->MATR_INTERNA   = $data['MATR_INTERNA'];
		$obj->ID_CONTRATO    = $data['ID_CONTRATO'];
		$obj->PROPOSTA_FIS   = $data['PROPOSTA_FIS'];
		$obj->DT_CADASTRO    = $data['DT_CADASTRO'];
		$obj->COD_INT        = $data['COD_INT'];
		$obj->CODIGO         = $data['CODIGO'];
		$obj->MATR_PENS      = $data['MATR_PENS'];
		$obj->NASCIMENTO     = $data['NASCIMENTO'];
		$obj->CPF            = $data['CPF'];
		$obj->VINC           = $data['VINC'];
		$obj->NOME_INT       = $data['NOME_INT'];
		$obj->NOME_PLANO     = $data['NOME_PLANO'];
		$obj->CODIGO         = $data['CODIGO'];
		$obj->CONSIGNADO     = $data['CONSIGNADO'];
		$obj->VAL_RET        = $data['VAL_RET'];
		$obj->TI             = $data['TI'];
		$obj->BANCO          = $data['BANCO'];
		$obj->AGENCIA        = $data['AGENCIA'];
		$obj->CONTA_CORRENTE = $data['CONTA_CORRENTE'];
		$obj->FORMA_PGTO     = $data['FORMA_PGTO'];
		$obj->COD_DEBITO     = $data['COD_DEBITO'];
		$obj->DIA_DEBITO     = $data['DIA_DEBITO'];
		$obj->DIA_CARNE      = $data['DIA_CARNE'];
		$obj->DATA_LIBE      = $data['DATA_LIBE'];
		$obj->FORMA_LIBE     = $data['FORMA_LIBE'];
		$obj->NOME_AGENTE    = $data['NOME_AGENTE'];
		$obj->CODIGO2        = $data['CODIGO2'];
		$obj->TIPO_CTO       = $data['TIPO_CTO'];
		$obj->MATR_ORGAO     = $data['MATR_ORGAO'];
		//$obj->CONTRATO_OLD   = $data['CONTRATO_OLD'];
			
		$this->form->setData($obj);
			
		TSession::setValue('TS_data', $data);
		
	}//onLoadForm2
	
	public function onAvancar($param)
	{
		try
		{
			//pega os dados do form
			$data = $this->form->getData();
			$this->form->setData($data);
			
			TTransaction::open('db2');
			
			//pega os coberturas do plano
			//Instancia o plano 
			$plano = new Plano($data->CODIGO);
			
			if(empty($data->CODIGO) )
			{
				throw new Exception('Selecione um Plano'); 
			}	
		   
		   //array dos Itens da TCombo
		   $options = array();
		   //Percorre as coberturas do plano
			if(isset($plano))
			{	
				foreach( $plano->getPlano_Cob() as $planos )
			   {
				   $options[$planos->COD_COBERTURA] = $planos->plano_cobertura->COBERTURA;
			   }
			} 
			
			TTransaction::close();
			/*
		
		
	
		
		$val_ret        = new TEntry('VAL_RET');
		$ti             = new TEntry('TI');
		$banco          = new TEntry('BANCO');
		$agencia        = new TEntry('AGENCIA');
		$conta_corrent  = new TEntry('CONTA_CORRENTE');
		$forma_pgto     = new TCombo('FORMA_PGTO');
		$cod_debito     = new TEntry('COD_DEBITO');
		$dia_debito     = new TEntry('DIA_DEBITO');
		$dia_carne      = new TEntry('DIA_CARNE');
		$data_libe      = new TDate('DATA_LIBE');
		$forma_libe     = new TCombo('FORMA_LIBE');
		$agente         = new TEntry('NOME_AGENTE');
		$cod_agente     = new TDBSeekButton('CODIGO2', 'DB2', 'formCadContrato', 'fornecedor', 'NOME', 'CODIGO2', 'NOME_AGENTE');*/
			//CRIA OS ARRAY COM OS DADOS 
			
			$array_cad = array();
		
			$array_cad['NOME_AGENTE']       = $data->NOME_AGENTE;
			$array_cad['FORMA_LIBE']        = $data->FORMA_LIBE;
			$array_cad['DATA_LIBE']         = $data->DATA_LIBE;
			$array_cad['DIA_CARNE']         = $data->DIA_CARNE;
			$array_cad['DIA_DEBITO']        = $data->DIA_DEBITO;
			$array_cad['COD_DEBITO']        = $data->COD_DEBITO;
			$array_cad['FORMA_PGTO']        = $data->FORMA_PGTO;
			$array_cad['CONTA_CORRENTE']    = $data->CONTA_CORRENTE;
			$array_cad['AGENCIA']           = $data->AGENCIA;
			$array_cad['BANCO']             = $data->BANCO;
			$array_cad['TI']                = $data->TI;
			$array_cad['VAL_RET']           = $data->VAL_RET;
			$array_cad['CONSIGNADO']        = $data->CONSIGNADO;
			$array_cad['NOME_INT']          = $data->NOME_INT;
			$array_cad['VINC']              = $data->VINC;
			$array_cad['CPF']               = $data->CPF;
			$array_cad['PROPOSTA_FIS']      = $data->PROPOSTA_FIS;
			$array_cad['NASCIMENTO ']       = $data->NASCIMENTO;
			$array_cad['MATR_PENS']         = $data->MATR_PENS;
			$array_cad['NOME']              = $data->NOME;
			$array_cad['MATR_INTERNA']      = $data->MATR_INTERNA;
			$array_cad['PROPOSTA']          = $data->PROPOSTA;
			$array_cad['ID_CONTRATO']       = $data->ID_CONTRATO;
			$array_cad['MATR_ORGAO']        = $data->MATR_ORGAO;
			$array_cad['DT_INICIO']         = $data->DATA_LIBE;
			$array_cad['TIPO_CTO']          = $data->TIPO_CTO; 
			$array_cad['FORMA_PGTO']        = $data->FORMA_PGTO; 
			$array_cad['AGENTE']            = $data->CODIGO2; 
			$array_cad['ENTIDADE_COLETIVA'] = $data->COD_INT; 
			$array_cad['TP_PLANO']          = $data->CODIGO;
			$array_cad['NOME_PLANO']        = $data->NOME_PLANO;
			//$array_cad['DATA_LANCAMENTO']   = $data->DATA_LANCAMENTO;
			$array_cad['DT_CADASTRO']       = $data->DT_CADASTRO;
			
			//grava na sessão
			TSession::setValue('TS_cadContrato', $array_cad);
			
			// if(empty($data->ID_CONTRATO))
			// {
				// $data->ID_CONTRATO = '';
			// }		
			
			//grava na sessão
			TSession::setValue('TS_cadContrato', $data);
			TSession::setValue('TS_dados_contrato', $array_cad);
			TSession::setValue('TS_planos_cob', $options);	
			
			// Passa os dados por array para 'cadContrato2'
			AdiantiCoreApplication::loadPage('cadContrato2', 'onLoadForm', (array) $data);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error',$e->getMessage());
			TTransaction::rollback();
		}
		
	}//onAvancar
	
	public function onTeste4()
	{
		$data = $this->form->getData();
		
		$array_cad = TSession::getValue('TS_cadContrato');
		
		
		var_dump($array_cad);
		
		
		$this->form->setData($data);
		
	}//onTeste4
	
	public function onTeste3()
	{
		$data = $this->form->getData();
		
		$array_cad = array();
		
		$array_cad['MATR_ORGAO']        = $data->MATR_ORGAO;
		$array_cad['DT_INICIO']         = $data->DATA_LIBE;
		$array_cad['TIPO_CTO']          = $data->TIPO_CTO; 
		$array_cad['FORMA_PGTO']        = $data->FORMA_PGTO; 
		$array_cad['AGENTE']            = $data->CODIGO2; 
		$array_cad['ENTIDADE_COLETIVA'] = $data->COD_INT; 
		$array_cad['TP_PLANO']          = $data->CODIGO;
		
		
		TSession::setValue('TS_cadContrato', $array_cad);
		
		/*	$dados_contrato->MATR_ORGAO        = $data->MATR_ORGAO; 
			$dados_contrato->DT_INICIO         = $data->DATA_LIBE; 
			$dados_contrato->TIPO_CTO          = $data->TIPO_CTO; 
			$dados_contrato->FORMA_PGTO        = $data->FORMA_PGTO; 
			$dados_contrato->AGENTE            = $data->CODIGO2; 
			$dados_contrato->ENTIDADE_COLETIVA = $data->COD_INT; 
			$dados_contrato->TP_PLANO          = $data->CODIGO; */
		var_dump($data);
		
		
		$this->form->setData($data);
		
	}//onTeste3
	
	
	public function onTeste()
	{
		$data = $this->form->getData();
		
		TSession::setValue('TS_data_cad', $data);
		
		var_dump($data);
		
		
		$this->form->setData($data);
		
	}//onTeste
	
	public function onTeste2()
	{
		
		$data = $this->form->getData();
		
		$ts_data = TSession::getValue('TS_data_cad');
		
		
		$dados_contrato = new Contratos2;
		$dados_contrato->MATR_ORGAO = $ts_data->MATR_ORGAO;

		var_dump($dados_contrato);	

		/*// $contrato->MATR_ORGAO        = $matr_orgao;//$ts_cadContrato['MATR_ORGAO']
			// $contrato->DT_INICIO         = $dt_inicio;
			// $contrato->TIPO_CTO          = $tipo_cto;
			// $contrato->FORMA_PGTO        = $param['FORMA_PGTO'];
			// $contrato->AGENTE            = $param['CODIGO2'];
			// $contrato->ENTIDADE_COLETIVA = $param['COD_INT'];
			// $contrato->TP_PLANO          = $param['CODIGO'];*/	
		
		// $teste_consig = array();
		// foreach($ts_data as $ts_datas)
		// {
			// $teste_consig = $ts_datas['COD_INT'];
		// }
		
		// var_dump($ts_data);
		
		$this->form->setData($ts_data);
		
	}//onTeste
	
	public function onVoltar()
	{
		$data = $this->form->getData();
		$this->form->setData($data);
		
		TSession::setValue('TS_data', $data);
		
		// Load another page
        AdiantiCoreApplication::loadPage('ClienteForm', 'onLoadSession');
		
	}//onVoltar
	
	/*public function onLoadForm($data)
	{
		$ts_parametro = TSession::getValue('TS_parametro');
		
		if(isset($ts_parametro))
		{	
			$obj = new STDClass;
			$obj->NOME         = $data['NOME'];
			$obj->CPF          = $data['CPF'];
			$obj->MATR_INTERNA = $data['MATR_INTERNA'];
			
			$this->form->setData($obj);
			
			TSession::setValue('TS_data', $data);
			TSession::setValue('TS_parametro', 1);
		
		}
		else
		{
			$obj = TSession::getValue('TS_data');
			$this->form->setData($obj);
		}	
		
	}//onLoadForm*/

}//TPage

?>