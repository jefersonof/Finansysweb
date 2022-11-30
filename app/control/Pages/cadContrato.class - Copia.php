<?php
class cadContrato Extends TPage
{
	private $form ;
	
	public function __construct()
	{
		parent::__construct();
		
		//atributos
		$contrato      = new TEntry('CONTRATO');
		$proposta      = new TEntry('PROPOSTA');
		$cadastramento = new TDate('DT_CADASTRO');
		$matricula     = new TEntry('MATR_INTERNA');
		$nome          = new TEntry('NOME');
		$matr_pens     = new TEntry('MATR_PENS');
		$nascimento    = new TEntry('NASCIMENTO');
		$teste         = new TEntry('TESTE');
		$contrato_old  = new TEntry('CONTRATO_OLD');//contrato
		$seq_fed       = new TEntry('SEQ_FED');
		$tipo_contrato = new TCombo('TIPO_CTO');//SEQ_FED22
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
		$agente         = new TEntry('NOME_AGENTE');
		
		//Cria os Btn
		$btn_avancar = TButton::create('btn_avancar', array($this, 'onAvancar'), 'Avançar', 'fa: fa-share blue' );
		
		$btn_voltar = TButton::create('btn_voltar', array($this, 'onVoltar'), 'Voltar', 'fa: fa-reply blue' );
		
		//cria o form
		$this->form = new BootstrapFormBuilder('formCadContrato');
		$this->form->setFieldSizes('100%');
		
		$row = $this->form->addFields(['N° Contrato', $contrato],
									  ['N° Proposta', $proposta],		
									  ['Cadastramento', $cadastramento],		
									  ['Matrícula', $matricula]		
									 );
		$row->layout =['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];	
		
		$row = $this->form->addFields(['Nome', $nome],
									  ['Cpf', $cpf],		
									  ['Vinc', $vinc],		
									  ['Pensionista', $matr_pens]		
									 );
		$row->layout =['col-sm-5', 'col-sm-2', 'col-sm-3', 'col-sm-2'];	
		
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
		
		//add compos no form
		$this->formFields = array($btn_voltar, $btn_avancar, $contrato, $proposta, $cadastramento, $matricula, $nome, $cpf, $vinc, $matr_pens, $cod_int, $nome_int, $cod_plano, $nome_plano, $consignado, $nascimento, $val_ret, $ti);//formFields
		$this->form->setFieldS($this->formFields);
		
		//cria o painel
		$painel = new TPanelGroup('Contratos (T011)');
		$painel->addFooter(THBox::pack($btn_voltar, $btn_avancar));	
		$painel->add($this->form);
		
		//cria TVBox
		$vbox = new TVBox;
		$vbox->add(new TXMLBreadCrumb('menu.xml', 'pesquisaAssociado'));
		$vbox->add($painel);
		
		parent::add($vbox);
	
	}//__construct
	
	public function onLoadForm($data)
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
		
	}//onLoadForm
	
	public function onAvancar($param)
	{
		$data = $this->form->getData();
		$this->form->setData($data);
		
		TSession::setValue('TS_data', $data);
		
		// Passa os dados por array para 'cadContrato'
        //AdiantiCoreApplication::loadPage('cadContrato', 'onLoadForm', (array) $data);
		
	}//onAvancar
	
	public function onVoltar()
	{
		$data = $this->form->getData();
		$this->form->setData($data);
		
		TSession::setValue('TS_data', $data);
		
		// Load another page
        AdiantiCoreApplication::loadPage('ClienteForm', 'onLoadSession');
		
	}//onVoltar

}//TPage

?>