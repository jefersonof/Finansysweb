<?php
class cadContrato2 Extends TPage
{
	private $form;
	private $cobertura_list;
	
	public function __construct()
	{
		parent::__construct();
		
		//PERMISSÃO DA PAGINA
		try
		{
			TTransaction::open('permission');
			//permissão padrão do grupo
			
			//pega o nome do classe da pagina 'get_class', verifica se tem permissão especial se não tiver pega as permissões padrão do grupo
			$nome_classe =  get_class($this);
			$funcao      = new funcao;
			$id_classe   = $funcao->buscaIdProgram($nome_classe);
			$classe_id   = (int) implode($id_classe);
			
			/*Percorre as permissões especiais dessa pagina na tabela 'system_user_group_program' e 'system_group', grava as permissões n array 'permissao_geral'; se tiver pega as permissões especiais do usuário 'system_user_group_program' assume esse valor se nao assume o padrão do grupo 'system_group'*/
			$permissao_users = TSession::getValue('usergroupids');
			
			$permissao_geral['acesso']     = 1;
			$permissao_geral['insercao']   = 0;
			$permissao_geral['alteracao']  = 0;
			$permissao_geral['delecao']    = 0;
			foreach($permissao_users as $permissao_user)
			{
				$grupo = new SystemGroup($permissao_user);
				
				if($grupo->insercao == 1 )
				{
					$permissao_geral['insercao']  = $grupo->insercao; 
				}
				
				if($grupo->alteracao == 1 )
				{
					$permissao_geral['alteracao']  = $grupo->alteracao; 
				}
				
				if($grupo->delecao == 1 )
				{
					$permissao_geral['delecao']  = $grupo->delecao; 
				}
			}
			//grava na sessão
			TSession::setValue('TS_alteracao', $permissao_geral['alteracao']);
			
			
			//Percorre as permissões do Usuário ; se tiver permissões especiais pra essa página pega se nao usa as permissões padrão do grupo. 
			//permissão especial do usuário
			$programas_user = TSession::getValue('TS_permissaouser');
			
			foreach($programas_user as $programa_user)
			{
				//var_dump($programa_user['system_program_id']);
				
				if( ((int) $programa_user['system_program_id'] == $classe_id) )
				{	
					$permissao_geral['acesso']    = $programa_user['acesso'];
					$permissao_geral['insercao']  = $programa_user['insercao'];
					$permissao_geral['alteracao'] = $programa_user['alteracao'];
					$permissao_geral['delecao']   = $programa_user['delecao'];
					
				}	
			}
			//grava na sessão
			TSession::setValue('TS_alteracao', $permissao_geral['alteracao']);
			
			//BLOQUEIA O ACESSO DA PAGINA CONFORME AS CONFIG DE PERMISSÃO ESPECIAL
			if($permissao_geral['acesso'] == 0 )
			{
				throw new Exception("Acesso bloqueado! Favor entrar em contato com o adminstrador");
			}
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
			exit;	
		}	
		TTransaction::close();
		
		//FECHA PERMISSÃO DA PAGINA
		
		//botões
		$btn_calctaxa = TButton::create('btn_calctaxa', array($this, 'onSomaCobertura'), 'Calcular Taxa', 'fa: fa-save' ); //onCargaCombo
		
		$btn_salvar   = TButton::create('btn_salvar', array($this,'onSave'), 'Salvar','far:save');
		$btn_salvar->class = 'btn btn-sm  btn-primary';//fa:floppy-o *onSave * onTeste
		//'fa:floppy-o ');
		//$btn->class = 'btn btn-sm  btn-primary';//fa:floppy-o
		
		$btn_cancelar = TButton::create('btn_cancelar', array($this,'onCancel'), 'Cancelar','far: fa-window-close red');
		
		$btn_voltar   = TButton::create('btn_voltar', array($this,'onVoltar'), 'Voltar','fa: fa-share fa-flip-horizontal blue');
		
		$btn_incluir   = TButton::create('btn_incluir', array($this,'onCargaCombo'), 'Incluir','fa: fa-plus blue');
		
		$btn_gravar   = TButton::create('btn_gravar', array($this,'addCobertura'), 'Gravar','fa: fa-check blue ');
		
		$btn_gera_venci = TButton::create('btn_gera_venci', array($this,'onGeraVenci'), 'Gerar Vencimentos','fa: fa-calendar blue');
		
		$btn_del_venci = TButton::create('btn_del_venci', array($this,'onDelVenci'), 'Excluir Vencimentos','fa: fa-calendar red');
		
		//Atributos
		$teste   = new TEntry('TESTE');
		$teste2  = new TEntry('TESTE2');
		
		$cod_cobe       = new TEntry('COD_COBE');
		$val_contrato   = new TEntry('VAL_CONTRATO');
		$val_parcela    = new TEntry('VAL_PARCELA');
		$parcelas       = new TEntry('PARCELAS');
		$contrato       = new TEntry('ID_CONTRATO');
		$matr_interna   = new TEntry('MATR_INTERNA');
		$nome           = new TEntry('NOME');
		//$nome->style = 'background:#6287B9; color:#FFF; border:none';
		//$nome->style = 'border:none';
		$tipo_cobertura = new TEntry('TIPO_COBERTURA');
		$cobertura      = new TCombo('COBERTURA');
		$vl_cobertura   = new TEntry('VL_COBERTURA');
		$vl_repasse     = new TEntry('VL_REPASSE');
		$vl_financiado  = new TEntry('VL_FINANCIADO');
		$carencia       = new TEntry('CARENCIA');
		$taxa           = new TEntry('TAXA');
		$parcela        = new TEntry('PARCELA');
		$vl_parcela     = new TEntry('VL_PARCELA');
		$tp_iof         = new TCombo('TP_IOF');//TP_IOF
		$iof            = new TEntry('IOF');
		$situacao       = new TDBCombo('SITUACAO', 'db2', 'motivo_cancelamento', 'CODIGO','MOTIVO');
		//EX => TDBCombo('nome_objeto','banco_de_dados','classe_model','campo_id','campo_descricao');
		$dt_situacao    = new TDate('DT_SITUACAO');
		$dt_inicio      = new TDate('DT_INICIO');
		$cod_plano      = new TEntry('COD_PLANO');
		
		/*teste],
									  ['Valor do contrato', $val_contrato],
									  ['Valor da parcela', $val_parcela],
									  ['Parcelas', $parcelas]*/
		//**formatações
		$teste->style = 'background:#6287B9; border:0; color:#FFF';
		//$val_contrato->style = 'background:#6287B9; color:#FFF;';
		$val_parcela->style = 'background:#6287B9; ; color:#FFF;';
		$parcelas->style = 'background:#6287B9; ; color:#FFF; border:1';
		$val_contrato->style = 'background:#6287B9; color:#FFF; border:1';
		$dt_inicio->setMask('dd/mm/yyyy');
		$dt_inicio->setDataBaseMask('dd/mm/yyyy');
		$dt_situacao->setMask('dd/mm/yyyy');
		$dt_situacao->setDataBaseMask('dd/mm/yyyy');
		$vl_cobertura->setNumericMask(2, '.', ',', TRUE);
		$vl_repasse->setNumericMask(2, '.', ',', TRUE);
		$vl_financiado->setNumericMask(2, '.', ',', TRUE);
		//$val_parcela->setNumericMask(2, '.', ',', TRUE);
		//$val_contrato->setNumericMask(2, '.', ',', TRUE);
		$iof->setNumericMask(2, '.', ',', TRUE);
		$vl_financiado->setEditable(FALSE);
		$vl_parcela->setEditable(FALSE);
		//$contrato->setEditable(FALSE);
		$nome->setEditable(FALSE);
		$matr_interna->setEditable(FALSE);
		
		$tp_iof->addItems(array('IOF' => '0 | Sem IOF', 'D' => '1 | Deduzido', 'F' => '2 | Financiado' ));
		
		//DEFINE A AÇÃO DE SAÍDA DO CAMPO "$vl_cobertura"
		$exit_vl_cobertura = new TAction(array($this, 'onCalcFinan2'));//onExitAction
		$vl_cobertura->setExitAction($exit_vl_cobertura);
		
		
		// //DEFINE A AÇÃO DE SAÍDA DO CAMPO "$parcela"
		$exit_parcela = new TAction(array($this, 'onCalcParcela'));//onExitAction
		$parcela->setExitAction($exit_parcela);
		
		// //DEFINE A AÇÃO DE SAÍDA DO CAMPO "$vl_repasse"
		$exit_vl_repasse = new TAction(array($this, 'onCalcFinan'));//onExitAction
		$vl_repasse->setExitAction($exit_vl_repasse);
		
		
		//cria a data grid 'cobertura_list'
        $this->cobertura_list = new TQuickGrid;
		$this->cobertura_list->style = "width:100%";
		//$this->cobertura_list->style = "width:100%; margin-bottom: 10px";
        $this->cobertura_list->DisableDefaultClick(); 
		
		$this->cobertura_list->addQuickColumn('', 'edit', 'center');
		$this->cobertura_list->addQuickColumn('', 'delete', 'center');
		$this->cobertura_list->addQuickColumn('Cód Cobertura', 'CODIGO', 'center');
		$this->cobertura_list->addQuickColumn('Cobertura', 'COBERTURA', 'center');
		$tot =  $this->cobertura_list->addQuickColumn('R$ Cobertura', 'VL_COBERTURA', 'center');
		
        $this->cobertura_list->createModel();
		
		////
		
		//cria a data grid 'vencimento_list'
        $this->vencimento_list = new TQuickGrid;
		$this->vencimento_list->style = "width:100%";
		$this->vencimento_list->setHeight(300);
        $this->vencimento_list->makeScrollable();
        $this->vencimento_list->DisableDefaultClick(); 
		
		$this->vencimento_list->addQuickColumn('Parcela', 'PARCELA', 'center');
		$this->vencimento_list->addQuickColumn('Vencimento', 'VENCIMENTO', 'center');
		$this->vencimento_list->addQuickColumn('Valor', 'VALOR', 'center');
		$vl_pg = $this->vencimento_list->addQuickColumn('Vl Pago', 'VALOR_PAGO', 'center');
		$this->vencimento_list->addQuickColumn('Cheque', 'CHEQUE', 'center');
		$this->vencimento_list->addQuickColumn('Desconto', 'DESCONTO', 'center');
		
        $this->vencimento_list->createModel();
		
		////
		//FAZ A SOMA DO TOTAL DO EMPRÉSTIMO E MOSTRA NA GRID1 
		$format_value = function($value)
		{
            if (is_numeric($value)) {
               // return 'R$ '.number_format($value, 2, ',', '.');
				//return number_format($value, 2, '.', ',');
				
				//$value =  str_replace(',', '', $value);
				
				//return 'R$ '. number_format($value, 2, '.', ',');
				
				return 'R$ '. number_format($value, 3, '.', ','); //(2, '.', ',', FALSE);
				
                
            }
			return $value;
        };

		$tot->setTransformer( $format_value );

		$tot->setTotalFunction( function($values) {
            
			return array_sum((array) $values);
			
        });
		
		//FORMATA AS COLUNAS
		
		
		///
		
		//form
		$this->form = new BootstrapFormBuilder('formCadContrato2') ;	
		$this->form->setFieldSizes('100%');
		
		$row = $this->form->addFields(
							          ['Contrato', $contrato],
							          ['Nome', $nome],
							          ['Matrícula', $matr_interna],
							          ['.', $btn_calctaxa]
							         );
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-2', 'col-sm-2'];
		$row->style = 'background:#6287B9; margin:-2px 0 0 0';

		$row = $this->form->addFields(['Cobertura', $cobertura],
							          ['Cobertura R$', $vl_cobertura],
							          ['Repasse', $vl_repasse],
							          ['Financiado R$', $vl_financiado]
							         );
		$row->layout = ['col-sm-6', 'col-sm-2', 'col-sm-2', 'col-sm-2'];
		
		$row = $this->form->addFields(['Carência', $carencia],
							          ['N° Parcelas', $parcela],
							          ['Taxa', $taxa],
							          ['Parcela R$', $vl_parcela],
							          ['Calc IOF', $tp_iof],
							          ['IOF R$', $iof]
							         );
		$row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2'];
		
		$row = $this->form->addFields(['Situação', $situacao],
							          ['DT Situação', $dt_situacao],
							          ['DT Inicio', $dt_inicio]
							         );
		$row->layout = ['col-sm-8', 'col-sm-2', 'col-sm-2'];
		
		$row = $this->form->addFields([$cod_cobe]);
		$row->layout = ['col-sm-12'];
		
		//barra menu coberturas
		$row = $this->form->addFields([$btn_voltar],[$btn_gravar], [$btn_incluir] );
		$row->layout = ['col-sm-1', 'col-sm-1', 'col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';
		
		$row = $this->form->addFields([$this->cobertura_list]);
		$row->layout = ['col-sm-12'];
		
		//barra vencimentos
		$row = $this->form->addFields([$btn_gera_venci],[$btn_del_venci] );
		$row->layout = ['col-sm-2', 'col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';
		
		//barra menu coberturas
		$row = $this->form->addFields(['Vencimentos', $teste],
									  ['Valor do contrato', $val_contrato],
									  ['Valor da parcela', $val_parcela],
									  ['Parcelas', $parcelas]
									  );
		$row->layout = ['col-sm-1', 'col-sm-4', 'col-sm-4', 'col-sm-3'];
		$row->style = 'background:#6287B9; margin:0 0 0 1px; color:#FFF';
		
		//add grid
		$row = $this->form->addFields([$this->vencimento_list]);
		$row->layout = ['col-sm-12'];	
		
		
		 //DEFINE OS CAMPOS DO FORMULÁRIO
        $this->formFields = array($contrato, $nome, $matr_interna, $btn_calctaxa, $cobertura, $vl_cobertura, $vl_repasse, $vl_financiado, $carencia, $parcela, $taxa, $vl_parcela, $tp_iof, $iof, $situacao, $dt_situacao, $dt_inicio, $btn_salvar, $btn_cancelar, $val_contrato, $val_parcela, $parcelas, $cod_cobe); //, $numero_parcela, $valor_parc

        $this->form->setFields( $this->formFields );
		
		//painel
		$painel = new TPanelGroup('Cadastro de contrato');
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_salvar, $btn_cancelar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_cancelar));
		}
		
		$painel->add($this->form);
		
		//menu Bread
		/*$menu = new TBreadCrumb();
		$menu->addHome();
		$menu->addItem('Cadastro de Contratos');*/
		
		$vbox = new TVBox;
		$vbox->add(new TXMLBreadCrumb('menu.xml', 'ClienteListe'));
		$vbox->add($painel);
		
		parent::add($painel);
		
	}//__construct
	
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			TSession::setValue('TS_cobertura', NULL);
			TSession::setValue('TS_vencimentos', NULL);
			
			//onEdit
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formCadContrato2', 'btn_salvar');
			}
			
			$key = $param['key'];
			$contrato = new contratos2($key);
			
			//cobcontratos
			$cob_contrato = $contrato->getCobcontrato();
			$items_sessao = array();
			foreach($cob_contrato as $item)//VALOR
			{
				$items_sessao[$item->COBERTURA_ID] = $item->toArray();
				$items_sessao[$item->COBERTURA_ID]['CODIGO']        = $item->COBERTURA_ID;
				$items_sessao[$item->COBERTURA_ID]['VL_FINANCIADO'] = $item->VL_FINANCIADO;
				$items_sessao[$item->COBERTURA_ID]['TAXA']          = $item->TAXA;
				$items_sessao[$item->COBERTURA_ID]['VL_COBERTURA']  = $item->VL_COBERTURA;
				$items_sessao[$item->COBERTURA_ID]['VL_REPASSE']    = $item->VL_REPASSE;
				$items_sessao[$item->COBERTURA_ID]['SITUACAO']      = $item->SITUACAO;
				$items_sessao[$item->COBERTURA_ID]['DT_SITUACAO']   = $item->DT_SITUACAO;
				$items_sessao[$item->COBERTURA_ID]['COBERTURA']     = $item->cobertura->COBERTURA;
				$items_sessao[$item->COBERTURA_ID]['PARCELAS']      = $item->PARCELAS;
				$items_sessao[$item->COBERTURA_ID]['PARCELA']       = $item->PARCELAS_COB;
				$items_sessao[$item->COBERTURA_ID]['DT_INICIO']     = $item->DT_INICIO;
				$items_sessao[$item->COBERTURA_ID]['CARENCIA']      = $item->CARENCIA ;
				$items_sessao[$item->COBERTURA_ID]['VL_PARCELA ']   = $item->VL_PARCELA ;
				$items_sessao[$item->COBERTURA_ID]['TP_IOF ']       = $item->TP_IOF ;
				$items_sessao[$item->COBERTURA_ID]['IOF ']          = $item->IOF ;
				//$items_sessao[$item->COBERTURA_ID]['DT_SITUACAO ']  = $item->DT_SITUACAO ;
				
			}
			TSession::setValue('TS_cobertura', $items_sessao);
			
			//Finanr2
			$finanr2 = $contrato->getFinanr2();
			$items_sessao_finanr2 = array();
			foreach($finanr2 as $item2)//VALOR
			{
			   $items_sessao_finanr2[$item2->ID_FINANR] = $item2->toArray();
			   $items_sessao_finanr2[$item2->ID_FINANR]['DATA_LANCAMENTO'] = $item2->DATA_LANCAMENTO;
			   $items_sessao_finanr2[$item2->ID_FINANR]['VALOR_PAGAR']     = $item2->VALOR_PAGAR;
			   $items_sessao_finanr2[$item2->ID_FINANR]['VALOR_PAGO']      = $item2->VALOR_PAGO;
			   $items_sessao_finanr2[$item2->ID_FINANR]['PARCELA_CTO']     = $item2->PARCELA_CTO;
		       $items_sessao_finanr2[$item2->ID_FINANR]['PARCELAS']        = $item2->PARCELAS;
			   $items_sessao_finanr2[$item2->ID_FINANR]['DATA_VENCIMENTO'] = $item2->DATA_VENCIMENTO;
				
			}
			TSession::setValue('TS_vencimentos', $items_sessao_finanr2);
			
			//dados do contrato
			//$stdclass = new STDClass;
			$stdclass = array() ;
			$stdclass['ID_CONTRATO']       = $contrato->ID_CONTRATO;
			$stdclass['NOME']              = $contrato->cliente->NOME;
			$stdclass['CPF']               = $contrato->cliente->CPF;
			$stdclass['MATR_INTERNA']      = $contrato->MATR_INTERNA;
			$stdclass['PROPOSTA_FIS']      = $contrato->PROPOSTA_FIS;
			$stdclass['DT_CADASTRO']       = $contrato->DT_CADASTRO ;
			$stdclass['DT_INICIO']         = $contrato->DT_INICIO ;
			$stdclass['ENTIDADE_COLETIVA'] = $contrato->ENTIDADE_COLETIVA;
			$stdclass['NOME_INT']          = $contrato->entidade->RAZAO_SOCIAL;
			$stdclass['CODIGO']            = $contrato->TP_PLANO;
			$stdclass['NOME_PLANO']        = $contrato->plano->PLANO;
			$stdclass['MATR_PENS']         = $contrato->MATR_PENS;
			$stdclass['NASCIMENTO']        = $contrato->cliente->NASCIMENTO;
			$stdclass['VINC']              = $contrato->VINC;
			$stdclass['CONSIGNADO']        = $contrato->CONSIGNADO;
			$stdclass['VAL_RET']           = $contrato->VAL_RET;
			$stdclass['TI']                = $contrato->TI;
			$stdclass['BANCO']             = $contrato->BANCO;
			$stdclass['AGENCIA']           = $contrato->AGENCIA;
			$stdclass['CONTA_CORRENTE']    = $contrato->CONTA_CORRENTE;
			$stdclass['FORMA_PGTO']        = $contrato->FORMA_PGTO;
			$stdclass['COD_DEBITO']        = $contrato->COD_DEBITO;
			$stdclass['DIA_DEBITO']        = $contrato->DIA_DEBITO;
			$stdclass['DIA_CARNE']         = $contrato->DIA_CARNE;
			$stdclass['DATA_LIBE']         = $contrato->DATA_LIBE;
			$stdclass['FORMA_LIBE']        = $contrato->FORMA_LIBE;
			$stdclass['NOME_AGENTE']       = $contrato->fornecedor->NOME;
			$stdclass['CODIGO2']           = $contrato->AGENTE;
			$stdclass['TIPO_CTO']          = $contrato->TIPO_CTO;
			$stdclass['MATR_ORGAO']        = $contrato->MATR_ORGAO;
			$stdclass['DT_CADASTRO']       = $contrato->DT_CADASTRO;
			$stdclass['PROPOSTA']          = $contrato->PROPOSTA_FIS;
			$stdclass['IOF']               = $contrato->IOF;
			$stdclass['VL_PARCELA']        = $contrato->VL_PARCELA;
			$stdclass['TP_IOF']            = $contrato->TP_IOF;
			TSession::setValue('TS_dados_contrato', $stdclass);//   DT_CADASTRO
			
			//manda os dados para o form
			$stdclass2 = new StdClass;
			$stdclass2->NOME         = $contrato->cliente->NOME;
			$stdclass2->MATR_INTERNA = $contrato->MATR_INTERNA; ;
			$stdclass2->ID_CONTRATO  = $key;
			$stdclass2->VAL_PARCELA  = $contrato->VL_PARCELA;
			$stdclass2->PARCELAS     = $contrato->PARCELAS;
			$stdclass2->VAL_CONTRATO = $contrato->VALOR;
			//$stdclass2->VAL_CONTRATO = number_format($contrato->VALOR, 2, ',', '.');
			
			$this->form->sendData('formCadContrato2', $stdclass2);
			TSession::setValue('TS_barraVence', $stdclass2);
			
			//pega os planos
			$plano = new Plano($contrato->TP_PLANO);
			
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
			//GRAVA NA SESSÃO
			TSession::setValue('TS_planos_cob', $options);
			
			TTransaction::close();
			
			$this->onReload($param);
			
			
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onEdit()
	
	public function onGeraVenci($param)
	{
		$data = $this->form->getData();
		
		$ts_coberturas = TSession::getValue('TS_cobertura');
		
		$ts_cadContrato = TSession::getValue('TS_dados_contrato');
		
		/*$items_sessao[$item->COBERTURA_ID] = $item->toArray();
				$items_sessao[$item->COBERTURA_ID]['CODIGO']        = $item->COBERTURA_ID;
				$items_sessao[$item->COBERTURA_ID]['VL_FINANCIADO'] = $item->VL_FINANCIADO;
				$items_sessao[$item->COBERTURA_ID]['TAXA']          = $item->TAXA;
				$items_sessao[$item->COBERTURA_ID]['VL_COBERTURA']  = $item->VL_COBERTURA;
				$items_sessao[$item->COBERTURA_ID]['VL_REPASSE']    = $item->VL_REPASSE;
				$items_sessao[$item->COBERTURA_ID]['SITUACAO']      = $item->SITUACAO;
				$items_sessao[$item->COBERTURA_ID]['DT_SITUACAO']   = $item->DATA_LANCAMENTO;
				$items_sessao[$item->COBERTURA_ID]['COBERTURA']     = $item->cobertura->COBERTURA;
				$items_sessao[$item->COBERTURA_ID]['PARCELA']       = $item->PARCELAS;
				$items_sessao[$item->COBERTURA_ID]['PARCELAS']      = $item->PARCELAS;
				$items_sessao[$item->COBERTURA_ID]['CARENCIA']      = $item->CARENCIA ;
				$items_sessao[$item->COBERTURA_ID]['VL_PARCELA ']   = $item->VL_PARCELA ;
				$items_sessao[$item->COBERTURA_ID]['TP_IOF ']       = $item->TP_IOF ;
				$items_sessao[$item->COBERTURA_ID]['IOF ']          = $item->IOF ;*/
		
		//**faz a soma das parcelas
		$tot_soma = 0;
		foreach($ts_coberturas as $ts_cobertura)
		{
			$tot_soma     = ($tot_soma +  $ts_cobertura['VL_COBERTURA']);
			$num_parcelas = (int) $ts_cobertura['PARCELA'];
			$dt_inicio    = $ts_cobertura['DT_INICIO'];
				
			
		}
		
		
		
		
		// var_dump($ts_cobertura['PARCELA']);
		
		// exit;
		//manda os dados para barra de valores 
		$stdclass = new StdClass;
		$stdclass->VAL_CONTRATO  = number_format($tot_soma, 2, ',', '.');
		$stdclass->PARCELAS      = $num_parcelas;
		$stdclass->VAL_PARCELA   = number_format($tot_soma / $num_parcelas, 2, '.', ',');
		$stdclass->MATR_INTERNA  = $data->MATR_INTERNA;
		$stdclass->NOME          = $data->NOME;
		$stdclass->ID_CONTRATO   = $data->ID_CONTRATO;
		
		//manda dos dados pro form
		TForm::sendData('formCadContrato2', $stdclass);
		
		//grava o total  na sessão
		TSession::setValue('TS_total', $stdclass->VAL_PARCELA);
		
		//gera os vencimentos
		$funcao = new Funcao;
		$vencimentos = $funcao->vencimento($dt_inicio, $num_parcelas);
		
		
		//grava os vencimentos na sessão
		TSession::setValue('TS_vencimentos', $vencimentos);
		
		//recarrega a pagina
		$this->onReload($param);
		
	}//onGeraVenci
	
	public function onDelVenci($param)
	{
		$data = $this->form->getData();
		
		TSession::setValue('TS_vencimentos', NULL);
		
		//manda os dados para barra de valores 
		$stdclass = new StdClass;
		$stdclass->VAL_CONTRATO  = '';
		$stdclass->PARCELAS      = '';
		$stdclass->VAL_PARCELA   = '';
		$stdclass->MATR_INTERNA  = $param['MATR_INTERNA'];
		$stdclass->NOME          = $param['NOME'];
		$stdclass->ID_CONTRATO   = $param['ID_CONTRATO'];
		
		//manda dos dados pro form
		//$this->form->setData($data);
		TForm::sendData('formCadContrato2', $stdclass);
		// $data = $this->form->getData();
		//$this->form->setData($data);
		
		$this->onReload($param);
	}//onDelVenci
	
	public function onEditItemProduto($param)
	{
		//pega os dados do form
		$data = $this->form->getData();
		
		//pega os dados da sessão
		$ts_coberturas = TSession::getValue('TS_cobertura');
		
		$ts_cobertura = $ts_coberturas[(int) $param['list_product_id']];
		
		$data->VL_COBERTURA  = $ts_cobertura['VL_COBERTURA'];
		$data->VL_REPASSE    = $ts_cobertura['VL_REPASSE'];
		$data->VL_FINANCIADO = $ts_cobertura['VL_FINANCIADO'];
		$data->CARENCIA      = $ts_cobertura['CARENCIA'];
		$data->PARCELA       = $ts_cobertura['PARCELA'];
		$data->TAXA          = $ts_cobertura['TAXA'];
		$data->PARCELAS      = $ts_cobertura['PARCELAS'];
		$data->VAL_CONTRATO  = '';
		$data->VAL_PARCELA   = '';
		$data->VL_PARCELA    = $ts_cobertura['VL_PARCELA'];
		$data->TP_IOF        = $ts_cobertura['TP_IOF'];
		$data->IOF           = $ts_cobertura['IOF'];
		$data->SITUACAO      = $ts_cobertura['SITUACAO'];
		$data->DT_SITUACAO   = $ts_cobertura['DT_SITUACAO'];
		$data->DT_INICIO     = $ts_cobertura['DT_INICIO'];
		
		//pega os planos da sessão
		$ts_planos = TSession::getValue('TS_planos_cob');
		
		//pega o plano que foi selecionado
		$options = array();
		foreach($ts_planos as $key => $planos)
		{
			if($key == $param['list_product_id'] )
			{
				$options[$key] = $planos;
				//var_dump($planos);
			}	
		}
		
		//Recarrega combo
		TCombo::reload('formCadContrato2', 'COBERTURA', $options);//form , obj , array
		
		//manda os dados para o form
		$this->form->setData($data);
		
		//apaga a sessão do vencimentos
		TSession::setValue('TS_vencimentos', NULL);
		
		$this->vencimento_list->clear();
		
	}//onEditItemProduto
	
	public function onDeleteItem($param)
	{
		$data = $this->form->getData();

		// $data->CODIGO         = '';
        // $data->PLANO          = '';
		// $data->VL_REPASSE     = '';
		// $data->PARCELA        = '';
		// $data->VL_PARCELA     = '';
		// $data->TIPO_COBERTURA = '';
		//$data->VL_FINANCIADO  = '';
		// $data->SITUACAO       = '';
		// $data->TAXA           = '';
		// $data->DT_SITUACAO    = '';
		// $data->VL_COBERTURA   = '';
		// $data->DT_SAIDA       = '';
		// $data->TP_IOF         = '';
		// $data->IOF            = '';
		// $data->DT_INICIO      = '';
		// $data->DT_CADASTRO    = '';

        // LIMPA O FORMULÁRIO
        $this->form->setData( $data );
		/*//grava as coberturas na sessão 
			TSession::setValue('TS_cobertura', $items_cobertura);	*/

		//LE ITENS DA SESSÃO
		$ts_cobertura = TSession::getValue('TS_cobertura');
		//$var_soma   = TSession::getValue('var_soma');
		
        //'unset' APAGA OS ITEMS DA SESSÃO DE ACORDOCOM SEU ID
        unset($ts_cobertura[ (int) $param['list_product_id'] ] );
		
		//GRAVA NA SESSÃO SEM O OBJ DO 'unset'
        TSession::setValue('TS_cobertura', $ts_cobertura);

		//apaga a sessão do vencimentos
		TSession::setValue('TS_vencimentos', NULL);
		
		$this->vencimento_list->clear();
		
		$this->onReload($param);
		
	}//onDeleteItem
	
	public function addCobertura($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//mantém os dados no form 
			$data = $this->form->getData();
			
			$cobertura = new Cobertura($data->COBERTURA);//CODIGO
			
			//pega os items das coberturas
			$items_cobertura = TSession::getValue('TS_cobertura');	
			
			if(empty($data->COD_COBE))
			{
				$data->COD_COBE = 1;
			}
				
			//$key = (int) $data->COD_COBE;//campo oculto autoincrement
			$key = (int) $data->COBERTURA;//id da cobertura 
			$items_cobertura[$key] = array('CODIGO'          => $param['COBERTURA'],
										   'COBERTURA'       => $cobertura->COBERTURA,
										   'VL_COBERTURA'    => (double) str_replace(',' ,'', $param['VL_COBERTURA']),
										   'VL_REPASSE'      => (double) str_replace(',' ,'', $param['VL_REPASSE']),
										   'VL_FINANCIADO'   => (double) str_replace(',' ,'', $param['VL_FINANCIADO']),
										   'CARENCIA'        => $param['CARENCIA'],
										   'PARCELA'         => $param['PARCELA'],
										   'TAXA'            => $param['TAXA'],
										   'PARCELAS'        => $param['PARCELAS'],
										   'VL_PARCELA'      => $param['VL_PARCELA'],
										   'TP_IOF'          => $param['TP_IOF'],
										   'IOF'             => $param['IOF'],
										   'SITUACAO'        => $param['SITUACAO'],
										   'DT_SITUACAO'     => $param['DT_SITUACAO'],
										   'DT_INICIO'       => $param['DT_INICIO']
										   ); 
										   
			//grava as coberturas na sessão  'SESSÃO CADCONTRATO2'
			TSession::setValue('TS_cobertura', $items_cobertura);
			
			//LIMPA O FORM
			$data->VL_COBERTURA   = '';
			$data->VL_REPASSE     = '';
			$data->VL_PARCELA     = '';
			$data->VL_FINANCIADO  = '';
			$data->CARENCIA       = '';
			$data->PARCELA        = '';
			$data->TAXA           = '';
			$data->PARCELAS       = '';
			$data->TP_IOF         = '';
			$data->IOF            = '';
			$data->SITUACAO       = '';
			$data->DT_SITUACAO    = '';
			$data->DT_INICIO      = '';
			
			$this->form->setData($data);	
			
			//pega os planos da sessão
			// $options = TSession::getValue('TS_planos_cob');	
			
			// //Recarrega combo coberturas
			// TCombo::reload('formCadContrato2', 'COBERTURA', $options);//form , obj , array
			
			TTransaction::close();
			
			//recarrega a pagina
			$this->onReload($param);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//addCobertura
	
	public function onReload($param)
	{
		$data = $this->form->getData();
		
		
		//recarrega o grid
		$ts_coberturas = TSession::getValue('TS_cobertura');	
		
		//limpa a grid
		$this->cobertura_list->clear();
		$this->vencimento_list->clear();
		
		//CARREGA OS DADOS DA COBERTURA GRID1
		if ($ts_coberturas)
        {
            $cont = 1;
            foreach ($ts_coberturas as $list_product_id => $list_product)
            {
                $item_name = 'prod_' . $cont++;
                //OBJ PADRÃO DAS CLASSES
				$item = new StdClass;

				//CRIA AS AÇÕES DOS BTN
                $action_del = new TAction(array($this, 'onDeleteItem'));
                $action_del->setParameter('list_product_id', $list_product_id);
				$action_del->setParameter('cont',$cont);

				$action_edi = new TAction(array($this, 'onEditItemProduto'));
                $action_edi->setParameter('list_product_id', $list_product_id);
				$action_edi->setParameter('cont',$cont);
				
				//CRIA OS BTN E ADD AS AÇÕES
                $button_del = new TButton('delete_product'.$cont);
                $button_del->class = 'btn btn-default btn-sm';
                $button_del->setAction( $action_del, '' );
                $button_del->setImage('far:trash-alt red');

                $button_edi = new TButton('edit_product'.$cont);
                $button_edi->class = 'btn btn-default btn-sm';
                $button_edi->setAction( $action_edi, '' );
                $button_edi->setImage('fa:edit blue fa-lg');

                //ASSOCIA O OBJ PADRÃO AOS BNT
				$item->edit    = $button_edi;
                $item->delete  = $button_del;

                $this->formFields[ $item_name.'_edit' ]   = $item->edit;
                $this->formFields[ $item_name.'_delete' ] = $item->delete;

				//PEGA O USUARIO
				//$login = (TSession::getValue('login'));
				
				//autoincrement
				$data->COD_COBE = $list_product['CODIGO'];
				
                //ADICIONA OS ITEMS NA GRID 1
				$item->CODIGO       = $list_product['CODIGO'];
				$item->COBERTURA    = $list_product['COBERTURA'];
                $item->VL_COBERTURA = $list_product['VL_COBERTURA'];
				
                $this->cobertura_list->addItem( $item );
                // $row = $this->cobertura_list->addItem( $item );
                // $row->onmouseover = 'teste';
                // $row->onmouseout  = '';
				
				$this->form->setData($data);
				
				$this->loaded = TRUE;
								
            }//foreach ($sale_items)
			
            $this->form->setFields( $this->formFields );
			
        }//if ($ts_coberturas)
			
		
		//pega os vencimentos da sessão
		$vencimentos = TSession::getValue('TS_vencimentos');
		
		//add na grid
		$total = TSession::getValue('TS_total');
		
		$num_parcela = 1 ;
		if($vencimentos)
		{	
			foreach($vencimentos as $vencimento)
			{
				//se nao vir do banco
				if(empty($vencimento['DATA_VENCIMENTO']))
				{
					$item = new STDClass;
					$item->PARCELA    = $num_parcela . '°';
					$item->VENCIMENTO = $vencimento;
					$item->VALOR      = $total;
					$item->VALOR_PAGO = 0.0;
					$item->CHEQUE     = '-';
					$item->DESCONTO   = 0.0;
					
					$this->vencimento_list->addItem( $item );
					
					$num_parcela++;	
				}
				else
				{
					$item = new STDClass;
					$item->PARCELA    = $num_parcela . '°';
					$item->VENCIMENTO = $vencimento['DATA_VENCIMENTO'];
					$item->VALOR      = $vencimento['VALOR_PAGAR'];
					$item->VALOR_PAGO = 0.0;
					$item->CHEQUE     = '-';
					$item->DESCONTO   = 0.0;
					
					$this->vencimento_list->addItem( $item );
					
					$num_parcela++;
				}		
				
			}
		}//vencimentos
		
		//$vencimento['DATA_VENCIMENTO'];
	   // $item->VALOR      = $vencimento['VALOR_PAGAR'];
		
		/*/*
			   $items_sessao_finanr2[$item2->ID_FINANR] = $item2->toArray();

			   $items_sessao_finanr2[$item2->ID_FINANR]['DATA_LANCAMENTO'] = $item2->DATA_LANCAMENTO;
			   $items_sessao_finanr2[$item2->ID_FINANR]['VALOR_PAGAR']     = $item2->VALOR_PAGAR;
			   $items_sessao_finanr2[$item2->ID_FINANR]['VALOR_PAGO']      = $item2->VALOR_PAGO;
			   $items_sessao_finanr2[$item2->ID_FINANR]['PARCELA_CTO']     = $item2->PARCELA_CTO;
		       $items_sessao_finanr2[$item2->ID_FINANR]['PARCELAS']        = $item2->PARCELAS;
			   $items_sessao_finanr2[$item2->ID_FINANR]['DATA_VENCIMENTO'] = $item2->DATA_VENCIMENTO;*/
		
		//APAGA OS DADOS DO FORM
		//$data->CODIGO       = '';
		$data->COBERTURA    = '';
		
		$data->VL_COBERTURA   = '';
		$data->VL_REPASSE     = '';
		$data->VL_PARCELA     = '';
		$data->VL_FINANCIADO  = '';
		$data->CARENCIA       = '';
		$data->PARCELA        = '';
		$data->TAXA           = '';
		$data->PARCELAS       = '';
		$data->TP_IOF         = '';
		$data->IOF            = '';
		$data->SITUACAO       = '';// *10* se deixar em branco '' sempre trás o '0 ATIVO'
		$data->DT_SITUACAO    = '';
		$data->DT_INICIO      = '';
		
		$data->DT_INICIO      = '';
		
		$this->form->setData($data);
		
		//$this->onCargaCombo($param);
		
	}//onReload
	
	public function onTeste($param)
	{
		$data = $this->form->getData();
		
		$ts_cadContratos = TSession::getValue('TS_dados_contrato');
		//$data_cadastro = '24/11/1983';
			
			// $ts_vencimentos = TSession::getValue('TS_vencimentos');
		//$data_cadastrof = date("d/m/Y", $data_cadastro);	
		//$teste = date("d/m/Y", strtotime($data_cadastro));
		
		$teste = TDate::date2us($ts_cadContratos['DT_CADASTRO']);
		var_dump ($teste);
		
		//date("d/m/Y", strtotime($data)
		
		//var_dump(TSession::getValue('TS_vencimentos'));
		
		//var_dump(TSession::getValue('TS_cadContrato'));
		
		
		//var_dump(TSession::getValue('TS_cobertura'));
		
		/*array(2) { [1]=> array(15) { ["CODIGO"]=> string(1) "1" ["COBERTURA"]=> string(16) "PECULIO SIMPLES " ["VL_COBERTURA"]=> float(1500) ["VL_REPASSE"]=> float(320) ["VL_FINANCIADO"]=> float(1820) ["CARENCIA"]=> string(2) "10" ["PARCELA"]=> string(2) "45" ["TAXA"]=> string(1) "7" ["PARCELAS"]=> string(0) "" ["VL_PARCELA"]=> string(5) "40.44" ["TP_IOF"]=> string(3) "IOF" ["IOF"]=> string(6) "100.00" ["SITUACAO"]=> string(1) "1" ["DT_SITUACAO"]=> string(10) "05/12/2019" ["DT_INICIO"]=> string(10) "05/12/2019" } [2]=> array(15) { ["CODIGO"]=> string(1) "2" ["COBERTURA"]=> string(19) "EMPRESTIMO SOCICRED" ["VL_COBERTURA"]=> float(3600) ["VL_REPASSE"]=> float(1500) ["VL_FINANCIADO"]=> float(5100) ["CARENCIA"]=> string(2) "20" ["PARCELA"]=> string(2) "60" ["TAXA"]=> string(1) "5" ["PARCELAS"]=> string(2) "45" ["VL_PARCELA"]=> string(5) "85.00" ["TP_IOF"]=> string(3) "IOF" ["IOF"]=> string(6) "250.00" ["SITUACAO"]=> string(1) "1" ["DT_SITUACAO"]=> string(10) "05/12/2019" ["DT_INICIO"]=> string(10) "05/12/2019" } }*/
		
		// $vencimentos = TSession::getValue('TS_cobertura');
		
		// foreach($vencimentos as $vencimento)
		// {
			 // $vencimento
		// }
		
		// $item_vencimentos->DATA_LANCAMENTO = $ts_vencimento['DT_INICIO'];
		// $item_vencimentos->VALOR_PAGAR     = (soma);
		// $item_vencimentos->VALOR_PAGO      = 0.00;
		// $item_vencimentos->PARCELA_CTO     = $parcela;
		// $item_vencimentos->PARCELAS        = $ts_vencimento['PARCELAS'];
		// $item_vencimentos->DATA_VENCIMENTO = $ts_vencimento['DT_INICIO'];
		
		// $coberturas = TSession::getValue('TS_cobertura');
		
		// foreach($coberturas as $cobertura)
		// {
			 // $coberturas
		// }
		
		
		
		$this->form->setData($data);
	}//onTeste		
	
	public function onSave($param)
	{
		try
		{
			//var_dump(TSession::getValue('TS_dados_contrato'));
			//exit;
			
			TTransaction::open('db2');//db2
			//TTransaction::dump();

			//PEGA OS DADOS DO ARRAY CONTRATO 'criado na pagina CADCONTRATO'
			$ts_cadContratos = TSession::getValue('TS_dados_contrato');
			$data_cadastro = $ts_cadContratos['DT_CADASTRO'];
			
			// $ts_vencimentos = TSession::getValue('TS_vencimentos');
			//var_dump ($ts_vencimentos);
			// exit;
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formCadContrato2', 'btn_salvar');
			}
			
			
			
			//instancia o obj contrato
			$contrato = $this->form->getData('Contratos2');
			
			//PEGA AS COBERTURA DA SESSÃO
			$ts_coberturas = TSession::getValue('TS_cobertura');
			if($ts_coberturas)
			{	
				foreach($ts_coberturas as $ts_cobertura)
				{
					$item_cobertura = new CobContrato;
					$item_cobertura->COBERTURA_ID  = $ts_cobertura['CODIGO'];
					$item_cobertura->VALOR         = $ts_cobertura['VL_FINANCIADO'];
					$item_cobertura->VL_FINANCIADO = $ts_cobertura['VL_FINANCIADO'];
					$item_cobertura->TAXA          = $ts_cobertura['TAXA'];
					$item_cobertura->IOF           = $ts_cobertura['IOF'];
					$item_cobertura->TP_IOF        = $ts_cobertura['TP_IOF'];
					$item_cobertura->VL_COBERTURA  = $ts_cobertura['VL_COBERTURA'];
					$item_cobertura->VL_REPASSE    = $ts_cobertura['VL_REPASSE'];
					$item_cobertura->SITUACAO      = $ts_cobertura['SITUACAO'];
					$item_cobertura->CARENCIA      = $ts_cobertura['CARENCIA'];
					$item_cobertura->DT_SITUACAO   = $ts_cobertura['DT_SITUACAO'];
					$item_cobertura->PARCELAS_COB  = $param['PARCELAS'];
					$item_cobertura->VL_PARCELA    = $param['VAL_PARCELA'];
					$item_cobertura->DT_INICIO     = $ts_cobertura['DT_INICIO'];
					$item_cobertura->DT_CADASTRO   = $data_cadastro;
					
					$contrato->addCobcontrato($item_cobertura);
				}	
			}//IF

						
			//PEGA OS VENCIMENTOS DA SESSÃO
			$ts_vencimentos = TSession::getValue('TS_vencimentos');
			$parcela = 1;
			if($ts_vencimentos)
			{	
				foreach($ts_vencimentos as $ts_vencimento)
				{	
					$item_vencimentos = new finanr2;
					$item_vencimentos->DATA_LANCAMENTO = $data_cadastro;
					$item_vencimentos->DT_CADASTRO     = $data_cadastro ;
					$item_vencimentos->VALOR_PAGAR     = $param['VAL_PARCELA'];
					$item_vencimentos->VALOR_PAGO      = 0;
					$item_vencimentos->PARCELA_CTO     = $parcela;
					$item_vencimentos->PARCELAS        = $param['PARCELAS'];
					
					if(isset($ts_vencimento['DATA_VENCIMENTO']) )
					{	
						$item_vencimentos->DATA_VENCIMENTO = TDate::date2us($ts_vencimento['DATA_VENCIMENTO']);
					}
					else
					{
						$item_vencimentos->DATA_VENCIMENTO = TDate::date2us($ts_vencimento);
					}	
					$contrato->addFinanr2($item_vencimentos);
					
					$parcela++;
				}	
			}//IF
			/*
				TDate::date2br($object->NASCIMENTO);
				$object->NASCIMENTO = TDate::date2us($object->NASCIMENTO);*/			
			//$contrato->MATR_INTERNA      = $param['MATR_INTERNA'];
			$contrato->MATR_ORGAO        = $ts_cadContratos['MATR_ORGAO'];//$ts_cadContrato['MATR_ORGAO']
			$contrato->DT_INICIO         = $ts_cadContratos['DT_INICIO'];
			$contrato->TIPO_CTO          = $ts_cadContratos['TIPO_CTO'];
			$contrato->FORMA_PGTO        = $ts_cadContratos['FORMA_PGTO'];
			$contrato->AGENTE            = $ts_cadContratos['CODIGO2'];
			$contrato->ENTIDADE_COLETIVA = $ts_cadContratos['ENTIDADE_COLETIVA'];
			$contrato->TP_PLANO          = $ts_cadContratos['CODIGO'];
			$contrato->DT_CADASTRO       = TDate::date2br($ts_cadContratos['DT_CADASTRO']);
			//$contrato->VALOR             = (double) str_replace(',' ,'', $param['VAL_CONTRATO']);
			$contrato->VALOR             = str_replace('.' ,'',  $param['VAL_CONTRATO']);
			$contrato->VL_PARCELA        = $param['VAL_PARCELA'];
			$contrato->PARCELAS          = $param['PARCELAS'];
			$contrato->TAXA_JUROS        = $ts_cobertura['TAXA'];
			
			//PAGINA CADCONTRATO
			$contrato->FORMA_LIBE        = $ts_cadContratos['FORMA_LIBE'];
			$contrato->DATA_LIBE         = $ts_cadContratos['DATA_LIBE'];
			$contrato->DIA_CARNE         = $ts_cadContratos['DIA_CARNE'];
			$contrato->DIA_DEBITO        = $ts_cadContratos['DIA_DEBITO'];
			$contrato->COD_DEBITO        = $ts_cadContratos['COD_DEBITO'];
			$contrato->CONTA_CORRENTE    = $ts_cadContratos['CONTA_CORRENTE'];
			$contrato->AGENCIA           = $ts_cadContratos['AGENCIA'];
			$contrato->BANCO             = $ts_cadContratos['BANCO'];
			$contrato->TI                = $ts_cadContratos['TI'];
			$contrato->VAL_RET           = $ts_cadContratos['VAL_RET'];
			$contrato->CONSIGNADO        = $ts_cadContratos['CONSIGNADO'];
			$contrato->NOME_INT          = $ts_cadContratos['NOME_INT'];
			$contrato->VINC              = $ts_cadContratos['VINC'];
			$contrato->CPF               = $ts_cadContratos['CPF'];
			$contrato->PROPOSTA_FIS      = $ts_cadContratos['PROPOSTA_FIS'];
			$contrato->MATR_PENS         = $ts_cadContratos['MATR_PENS'];
			$contrato->PROPOSTA          = $ts_cadContratos['PROPOSTA'];
			$contrato->PARCELAS_COB      = $param['PARCELAS'];//DT_CADASTRO
			
			
			//SALVA O CONTRATO
			$contrato->store();
			
			new TMessage('info', 'Salvamento ok' );
			
			TTransaction::close();
			
			$contrato->ID_CONTRATO;
			$contrato->DT_INICIO = 	'';
			$contrato->VL_PARCELA = 	'';
			$this->form->setData($contrato);
			
			
		}catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
	
	}//onSave
	
	public function onVoltar()
	{
		//grava na sessão
		//TSession::setValue('TS_cadContrato', $data);
		//TSession::setValue('TS_planos_cob', $options);

		$data = $this->form->getData();
		$this->form->setData($data);
		
		//$data_cliente = TSession::getValue('TS_cadContrato');//'pagina = cadContrato'
		$data_cliente = TSession::getValue('TS_dados_contrato');//'pagina = cadContrato'
		//$data_cliente->ID_CONTRATO = $data->ID_CONTRATO;	
		//$data_cliente = TSession::getValue('TS_data');	
		
		// Passa os dados por array para 'cadContrato'
        AdiantiCoreApplication::loadPage('cadContrato', 'onLoadForm2', (array) $data_cliente);
		
	}//onVoltar
	
	public function onLoadForm($param)
	{	
		$obj = new STDClass;
		$obj->ID_CONTRATO  = $param['ID_CONTRATO'];
		$obj->NOME         = $param['NOME'];
		$obj->MATR_INTERNA = $param['MATR_INTERNA'];
		$obj->MATR_ORGAO   = $param['MATR_ORGAO'];
		$obj->ID_CONTRATO  = $param['ID_CONTRATO'];
		// $obj->FORMA_PGTO   = $param['FORMA_PGTO'];
		// $obj->CODIGO2      = $param['CODIGO2'];
		// $obj->COD_INT      = $param['COD_INT'];
		// $obj->CODIGO       = $param['CODIGO'];
		
		$this->form->setData($obj);
		//$this->form->setData($data);
		
		//recarrega barra vencimentos
		$vencimentos = TSession::getValue('TS_barraVence');
		TForm::sendData('formCadContrato2', $vencimentos);
		
		//pega as coberturas da sessão
		/*$options = TSession::getValue('TS_planos_cob');	
		
		//Carrega a TCombo 
		TCombo::reload('formCadContrato2', 'COBERTURA', $options);*///form , obj , array
		
		//Grava sessão
		
		
	}//onLoadForm
	
	public function onGravar()
	{
		
	}
	
	public function onCancel($param)
	{
		// $data = $this->form->getData();
		
		// $ts_cadContrato = TSession::getValue('TS_cadContrato');
		
		// $data->TESTE2 = $
		
	}//onCancel
	
	
	
	/*
	 estancia o obj de acordo com 
	 o codigo da cobertura, 
	 Grava o tipo de cobertura
	 da TCombo
	*/
	public function onCargaCombo($param)//onGenderChange
    {  
		$data = $this->form->getData();
		$this->form->setData($data);
		
		$options = TSession::getValue('TS_planos_cob');
					
		//carrega o TCombo com as suas coberturas
        TCombo::reload('formCadContrato2', 'COBERTURA', $options);//form , obj , array
		
		//var_dump($options);
		
		
    }//onCargaCombo
	
	public function onSomaCobertura($param)
	{
		$ts_coberturas = TSession::getValue('TS_cobertura');
		
		var_dump($ts_coberturas);
		
		$tot_soma = 0;
		foreach($ts_coberturas as $ts_cobertura)
		{
			$tot_soma = ($tot_soma +  $ts_cobertura['VL_COBERTURA']);
		}
		
		echo 'Total' . $tot_soma;
		
	}//onSomaCobertura
	
	/*
	DEFINE A AÇÃO DE SAÍDA
	DO CAMPO "$vl_repasse"
	*/
	public static function onCalcFinan($param)
	{
		//CAPTURA OS VALORES
		$num_parcelas   = $param['PARCELA'];
		$vl_cobertura   = (double) str_replace(',', '', $param['VL_COBERTURA']);
		$vl_repasse     = (double) str_replace(',', '', $param['VL_REPASSE']);

		//CALCULA O TOTAL FINANCIADO
		$tot_financiado = number_format($vl_cobertura + $vl_repasse, 2, '.', ',');

		//CRIA O OBJETO E ALTERA O VALOR FINANCIADO
		$obj = new StdClass;
		$obj->VL_FINANCIADO = $tot_financiado;

		//REMOVE A FORMATAÇÃO
		$tot_financiado  = (double) str_replace(',', '', $tot_financiado);

		//CALCULA O VALOR DAS PARCELAS SE ELA FOR MAIOR QUE ZERO
		if($num_parcelas > 0)
		{
			$val_parcela = ($tot_financiado / $num_parcelas);

			//FORMATA O VALOR
			$val_parcela = number_format($val_parcela, 2, '.', ',');

			//ALTERA O VALOR DAS PARCELAS
			$obj->VL_PARCELA = $val_parcela;
		}

		//ADICIONA EM TELA AS VALORES
		TForm::sendData('formCadContrato2', $obj);

	}//onCalcFinan2
	
	/*
	DEFINE A AÇÃO DE SAÍDA
	DO CAMPO "$vl_cobertura"
	*/
	public static function onCalcFinan2($param)
	{
		//CAPTURA OS VALORES
		$num_parcelas   = $param['PARCELA'];
		$vl_cobertura   = (double) str_replace(',', '', $param['VL_COBERTURA']);
		$vl_repasse     = (double) str_replace(',', '', $param['VL_REPASSE']);

		//CALCULA O TOTAL FINANCIADO
		$tot_financiado = number_format($vl_cobertura + $vl_repasse, 2, '.', ',');

		//CRIA O OBJETO E ALTERA O VALOR FINANCIADO
		$obj = new StdClass;
		$obj->VL_FINANCIADO = $tot_financiado;

		//REMOVE A FORMATAÇÃO
		$tot_financiado  = (double) str_replace(',', '', $tot_financiado);

		//CALCULA O VALOR DAS PARCELAS SE ELA FOR MAIOR QUE ZERO
		if($num_parcelas > 0)
		{
			$val_parcela = ($tot_financiado / $num_parcelas);

			//FORMATA O VALOR
			$val_parcela = number_format($val_parcela, 2, '.', ',');

			//ALTERA O VALOR DAS PARCELAS
			$obj->VL_PARCELA = $val_parcela;
		}

		//ADICIONA EM TELA AS VALORES
		TForm::sendData('formCadContrato2', $obj);

	}//onCalcFinan2
	
	/*
	setExitAction da parcela
	DEFINE A AÇÃO DE SAÍDA DO CAMPO "$parcela"
	*/
	public static function onCalcParcela($param)
	{
		//CALCULA O VALOR DAS PARCELAS
		$tot_financiado  = (double) str_replace(',', '', $param['VL_FINANCIADO']);
		$num_parcelas    = $param['PARCELA'];

		$obj = new StdClass;
		if( $num_parcelas > 0 )
		{  
         	$obj->VL_PARCELA = number_format($tot_financiado / $num_parcelas, 2, '.', ',');
        }
		
		//ADICIONA EM TELA AS VALORES
		TForm::sendData('formCadContrato2', $obj);

	}//onCalcParcela
	
	/*
	captura as parametros da URL e atualiza o onReload
	*/
	public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }//show
	
	// public function show()
	// {
		// if(!$this->loaded)
		// {
			// $this->onReload( func_get_arg(0) );
		// }	
        // parent::show(); 
		
	// }//show	
	
}//TPage


?>