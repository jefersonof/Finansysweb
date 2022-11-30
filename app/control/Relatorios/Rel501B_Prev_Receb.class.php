<?php
class Rel501_Prev_Receb Extends  TPage
{
	private $form;
	private $datagrid;
	private $pageNavigation;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->form = new BootstrapFormBuilder('formEnt_Gar');
		//$this->form->setFormTitle('Previsão de Rendimentos (T501) ');
		$this->form->setFieldSizes('100%');
		
		//cria os atributos
		$vence        = new TRadioGroup('VENCE');
		$dt_ini       = new TDate('DT_INI');
		$dt_fim       = new TDate('DT_FIM');
		$pagamento    = new TEntry('PAGAMENTO');
		$tipo_cto     = new TDBCombo('TIPO_CTO', 'db2', 'tipo_cto', 'CODIGO', '{CODIGO} {DESCRICAO}' );
		$moti_cance   = new TDBCombo('MOTIVOS_CANCELAMENTO', 'db2', 'motivo_cancelamento', 'CODIGO', 'MOTIVO');//
	    $entidades    = new TDBCombo('ENTIDADES', 'db2', 'entidades', 'COD_INT', '{COD_INT} - {RAZAO_SOCIAL}');
		$cobertura    = new TDBCombo('COBERTURA', 'db2', 'cobertura', 'CODIGO', 'COBERTURA');
		$ent_gar      = new TDBCombo('ENT_GAR', 'db2', 'ent_gar', 'CODIGO', 'NOME');//**código != 0
		$banco        = new TEntry('BANCO');
		$estado       = new TEntry('ESTADO');
		$apolice      = new TEntry('APOLICE');
		$ckreceber    = new TCheckGroup('CKRECEBER');//TCheckGroup
		$ckrecebidas  = new TCheckGroup('CKRECEBIDAS');
		$ckdesconto   = new TCheckGroup('CKDESCONTO');
		$ckconciliado = new TCheckGroup('CKCONCILIADO');
		$ckcpf        = new TCheckGroup('CKCPF');
		$ordenar      = new TRadioGroup('ORDENAR');
		$nome         = new TEntry('NOME');
		$contrato_old = new TEntry('CONTRATO_OLD');
		
		//cria as sessões
		$vence->setValue(TSession::getValue('TS_rel_venc'));
		$nome->setValue(TSession::getValue('TS_rel_nome'));
		
		$tipo_cto->setValue('S');
		$entidades->setValue(20165);
		$ordenar->setValue('nome');
		//$nome->setValue('JEFERSON');
		//$vence->setValue('venc');
		
		$vence->setValue('venc');
		$dt_ini->setValue(date('16/07/2016'));
		$dt_fim->setValue(date('16/07/2016'));//d/m/Y
		
		/*$tipo_cto->setValue(TSession::getValue('TS_data_tipo_cto'));
		$dt_ini->setValue(TSession::getValue('TS_data_dt_ini'));
		$dt_fim->setValue(TSession::getValue('TS_data_dt_fim'));*/
		
		
		//formatação
		$vence->addItems(['venc' => 'Vencimento', 'pag' => 'Pagamento']);
		$vence->setLayout('horizontal');
		$ckreceber->addItems(['sim' => 'Somente parcelas a receber']);
		$ckrecebidas->addItems(['sim' => 'Somente parcelas recebidas']);
		$ckdesconto->addItems(['sim' => 'Mostra descontos concedidos']);
		$ckconciliado->addItems(['sim' => 'Não mostra conciliados']);
		$ckcpf->addItems(['sim' => 'Mostra cpf']);
		$ordenar->addItems(['nome' => 'Nome', 'matr_orgao' => 'Matrícula orgão']);
		$ordenar->setLayout('horizontal');
		
		//Máscara
		$dt_ini->setMask('dd/mm/yyyy'); 
		//$dt_ini->setDataBaseMask('dd/mm/yyyy'); 
		$dt_fim->setMask('dd/mm/yyyy'); 
		//$dt_fim->setDataBaseMask('dd/mm/yyyy'); 
		
		//PAGINA
		$row = $this->form->addFields([new TLabel('Vencimento'), $vence]);
		//$row->layout = ['col-sm-12'];
		
		$row = $this->form->addFields([new TLabel('de'), $dt_ini ],
							          [new TLabel('até'), $dt_fim ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];
		
		$row = $this->form->addFields([new TLabel('Tipo'), $tipo_cto ],
									  [new TLabel('Código saída'), $moti_cance ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];
		
		$row = $this->form->addFields([new TLabel('Entidade Coletiva'),  $entidades ],
									  [new TLabel('Cobertura'), $cobertura ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];
		
		$row = $this->form->addFields([new TLabel('Ent. Garantidora'), $ent_gar ],
									  [new TLabel('Banco'), $banco ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];
		
		$row = $this->form->addFields([new TLabel('Estado'), $estado ],
									  [new TLabel('Apólice'), $apolice ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];
		
		$row = $this->form->addFields([new TLabel('Nome'), $nome ],
									  [new TLabel('Contrato'), $contrato_old ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];
		
		$row = $this->form->addFields([new TLabel('.'), $ckreceber ],
									  [new TLabel('.'), $ckrecebidas ],
									  [new TLabel('.'), $ckcpf ]);
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];
		
		$row = $this->form->addFields([new TLabel('.'), $ckdesconto ],
									  [new TLabel('.'), $ckconciliado ]);
		$row->layout = ['col-sm-4', 'col-sm-4'];
		
		$this->form->addContent([new TFormSeparator('')]);
		$row = $this->form->addFields([new TLabel('Ordenado por'), $ordenar]);
		$row->layout = ['col-sm-12'];
		
		
		
		//TESTE//
		
		$this->datagrid = new TQuickGrid;
		$this->datagrid->width = '100%';
		$this->datagrid->addQuickColumn('Nome', 'NOME', 'left');
		$this->datagrid->addQuickColumn('Inicio', 'DT_INICIO', 'left');
		
		//$this->datagrid->addQuickColumn('Valor', 'VALOR', 'left');
		
		$this->datagrid->addQuickColumn('Contrato', 'CONTRATO_OLD', 'left');
		$this->datagrid->addQuickColumn('DT Ini', 'DT_INICIO', 'left');
		$this->datagrid->addQuickColumn('Vence', 'DATA_VENCIMENTO', 'left');
		$this->datagrid->addQuickColumn('Pag', 'DATA_PAGAMENTO', 'left');
		$this->datagrid->addQuickColumn('TP de contrato', 'TIPO_CTO', 'left');
		$this->datagrid->addQuickColumn('Entidades', 'ENTIDADE_COLETIVA', 'left');
		$this->datagrid->addQuickColumn('Banco', 'BANCO', 'left');
		$this->datagrid->addQuickColumn('Proposta', 'PROPOSTA', 'left');
		//$this->datagrid->addQuickColumn('Data fim', 'DT_FIM', 'left');
		
		$this->datagrid->CreateModel();
		
		$row = $this->form->addFields([ $this->datagrid ]);
		$row->layout = ['col-sm-12'];
		
		
		//cria as ações do form
		$btn = $this->form->addAction('Gerar CSV' , new TAction(array($this, 'onSel501')), 'fa:file fas blue ');
		
		$this->form->addAction('Gerar Planilha' , new TAction(array($this, 'onTeste2')), 'fa:print fas blue');
		
		$this->form->addAction('Gerar Relatório' , new TAction(array($this, 'onSelTeste')), 'fa:print fas blue');//ico_pirnt.png **onDatagrid
		
		$this->form->addAction('Gerar Relatório2' , new TAction(array($this, 'onPDF')), 'fa:print fas blue');//ico_pirnt.png -- onSelTeste2 -- **onDatagrid
		
		$this->form->addAction('Fechar' , new TAction(array('PageInicial', 'onReload')), 'fa: fa-power-off red');
		
		//
		$img = new TImage('lib\adianti\images\bg-rel501.png');
		
		$thbox = new THBox;
		$thbox->style = '90%';
		$thbox->add($img);
		$thbox->add($this->form);
		
		//empacotamento
		$painel = new TPanelGroup('Previsão de Rendimentos (T501)');
		//$painel->style = '90%';
		$painel->add($thbox);
		//$painel->add();
		
		$vbox = new TVBox;
		$vbox->style = '90%';
		$vbox->add(new TXMLBreadCrumb('menu.xml', 'Ent_GarListe' ));
		
		$vbox->add($painel);
		
		parent::add($vbox);
		
	
	}//__construct
	
	public function onReload1($param)
	{
	    TTransaction::open('db'); // abre uma transação 
            $conn = TTransaction::get(); // obtém a conexão 
			
			$data = $this->form->getData();
			
			if(!empty($data->CKCPF))
			{
				$sql_matr_orgao = 'MIN(CL.CPF) AS MATR_ORGAO';
			}	
			else
			{
				$sql_matr_orgao = 'MIN(C.MATR_ORGAO) AS MATR_ORGAO'; 
			}	
			
			/* C.DT_PAGAMENTO AS DT_INICIO, F.CONTRATO_OLD, F.CLI_FOR, F.DATA_VENCIMENTO, F.RETORNO_CAB, ');
			Add(' F.PARCELA_CTO, F.DATA_PAGAMENTO, F.VALOR_PAGAR, F.DESCONTO, F.MULTA, ');
			Add(' F.VALOR_PAGO, C.PARCELAS , C.ENTIDADE_COLETIVA
			 MIN(CL.CPF) AS MATR_ORGAO,')
			else
			  Add(' MIN(C.MATR_ORGAO) AS MATR_ORGAO,');

			Add(' CL.NOME, E.RAZAO_SOCIAL AS ENT_NOME , C.TIPO_CTO, ');

			Add(' MIN(CC.SITUACAO) AS SITUACAO, 
			*/
			
			$sql = " SELECT C.DT_PAGAMENTO AS DT_INICIO, F.CONTRATO_OLD, F.CLI_FOR,  F.DATA_VENCIMENTO, F.RETORNO_CAB, F.PARCELA_CTO, F.DATA_PAGAMENTO, F.VALOR_PAGAR, F.DESCONTO, F.MULTA, F.VALOR_PAGO, CL.NOME, MIN(CC.SITUACAO) AS SITUACAO, E.RAZAO_SOCIAL AS ENT_NOME, C.BANCO, C.TIPO_CTO, C.ENTIDADE_COLETIVA 
			
			FROM FINANR2 F
			
			INNER JOIN CONTRATOS2 C ON (C.CONTRATO_OLD = F.CONTRATO_OLD) AND (C.MATR_INTERNA = F.CLI_FOR)
			
			INNER JOIN CLIENTES CL ON CL.MATR_INTERNA = F.CLI_FOR 
			
			INNER JOIN COB_CONTRATOS CC ON (CC.CONTRATO = C.CONTRATO_OLD) AND (CC.MATR_INTERNA = C.MATR_INTERNA)
			
			INNER JOIN ENTIDADES E ON E.COD_INT = C.ENTIDADE_COLETIVA
			
			INNER JOIN COBERTURAS CB ON CB.CODIGO = CC.COBERTURA
			
			WHERE 1 = 1
			";
			
			$parametros = array();
			//$nome      = $data->NOME ;
			
			/*$nome      = '%'. $data->NOME .'%' ;
			$matricula = $data->MATR_INTERNA;*/
			$nome         = '%'. $data->NOME .'%' ;
			$contrato_old = '%'. $data->CONTRATO_OLD .'%';
			//$contrato_old = $data->CONTRATO_OLD;
			$tipo_cto     = $data->TIPO_CTO;
			$entidades    = $data->ENTIDADES;
			
			$vence     = $data->VENCE;
			
			//$dt_ini1   = $data->DT_INI;
			//$dt_ini    = str_replace(" ' " , "",$dt_ini1);
			//$data_ini    = TDate::date2br('2016/06/18');
			
			//$data_ini    = $data->DT_INI;
			//$data_fim    = $data->DT_FIM;
			
			$data_ini    = TDate::date2us($data->DT_INI);
			$data_fim    = TDate::date2us($data->DT_FIM);
			
			
			//$dt_fim    = 28/12/2017;
			//$dt_fim    = $data->DT_FIM;
			$receber   = $data->CKRECEBER;
			$recebidas = $data->CKRECEBIDAS;
			
			//APAGAR
			
			/*if(TSession::getValue('TS_localiza_cpf') ) 
			{	
				$criteria->add(TSession::getValue('TS_localiza_cpf'));
				
			}//TS_localiza_cpf*/
			
			if(TSession::getValue('TS_data_nome'))
			{
				$parametros[] =  $nome; 
				$sql .= " AND CL.NOME LIKE ? ";
			}
			
			if(TSession::getValue('TS_data_contrato_old'))
			{
				$parametros[] =  $contrato_old; 
				$sql .= " AND F.CONTRATO_OLD LIKE  ?"; 
				//$sql .= " AND F.CONTRATO_OLD =  ?"; 
			}
			
			if(TSession::getValue('TS_data_tipo_cto'))
			{
				$parametros[] =  $tipo_cto; 
				$sql .= " AND C.TIPO_CTO =  ?";
			}
			
			if(TSession::getValue('TS_data_vencV'))
			{
				$parametros[] =  $data_ini; 
				$parametros[] =  $data_fim; 
				//$sql .= "AND F.DATA_VENCIMENTO LIKE ?";
				
				$sql .= "AND F.DATA_VENCIMENTO BETWEEN ? AND ?  ";
				
				//new TMessage('info', 'Data inicial ' . $data_ini . 'até ' . $data_fim);
			}	
			
			
			if(!empty($entidades))
			{
				$parametros[] =  $entidades; 
				$sql .= " AND C.ENTIDADE_COLETIVA =  ?";
			}
			//

			
			/*if(isset($param['VENCE']) and ($param['VENCE'] == 'venc'))
			{
				$parametros[] =  $data_ini; 
				$sql .= "AND F.DATA_VENCIMENTO LIKE ?";
				
				new TMessage('info', 'Data = ' . $data_ini);
			}
			
			if(isset($param['VENCE']) and ($param['VENCE'] == 'venc'))
			{
				$parametros[] =  $data_fim; 
				$sql .= "AND F.DATA_VENCIMENTO LIKE ?";
				
				new TMessage('info', 'Data = ' . $data_ini);
			}*/
			
			
			
			
			

			
			
			/*if(isset($param['VENCE']) and ($param['VENCE'] == 'venc'))
			{
				$parametros[] =  $dt_ini; 
				$parametros[] =  $dt_fim; 
				$sql .= "AND F.DATA_VENCIMENTO BETWEEN ? AND ?  ";
			}
			
			
			if(isset($param['VENCE']) and ($param['VENCE'] == 'pag'))
			{
				$parametros[] =  $dt_ini; 
				$parametros[] =  $dt_fim; 
				$sql .= "AND F.DATA_PAGAMENTO BETWEEN ? AND ?  ";  
			}*/
			
			
			/*if(isset($param['CKRECEBER']))
			{
				$parametros[] =  $receber; 
				$sql .= " AND F.VALOR_PAGO <= 0 ?";   
			}
			
			if(isset($param['CKRECEBIDAS']))
			{
				$parametros[] =  $recebidas; 
				$sql .= " AND F.VALOR_PAGO > 0 ? ";
			}*/
			
			
			
			
			$sql .= ' GROUP BY F.CONTRATO_OLD, F.DATA_VENCIMENTO, F.CLI_FOR,  F.RETORNO_CAB, F.PARCELA_CTO, F.DATA_PAGAMENTO, F.VALOR_PAGAR, F.DESCONTO, F.MULTA, F.VALOR_PAGO, CL.NOME, DT_INICIO, ENT_NOME,        C.BANCO, C.TIPO_CTO, C.ENTIDADE_COLETIVA';
			
            $sth = $conn->prepare($sql); 
            
            //$sth->execute(array(1,1200));
            $sth->execute($parametros);
            $result = $sth->fetchAll();
			
			TSession::setValue('TS_cliente', $result);
			 
            // exibe os resultados
            foreach ($result as $row) 
            { 
                $obj_std = new STDClass;
				$obj_std->NOME            = $row['NOME'];
				$obj_std->CONTRATO_OLD    = $row['CONTRATO_OLD'];
				$obj_std->DT_INICIO       = TDate::date2br($row['DT_INICIO']);
				$obj_std->DATA_PAGAMENTO  = TDate::date2br($row['DATA_PAGAMENTO']);
				$obj_std->DATA_VENCIMENTO = TDate::date2br($row['DATA_VENCIMENTO']);
				$obj_std->TIPO_CTO          = $row['TIPO_CTO'];
				$obj_std->ENTIDADE_COLETIVA = $row['ENTIDADE_COLETIVA'];
				$obj_std->BANCO             = $row['BANCO'];
				
				
				//$this->datagrid->addItem($row);
				$this->datagrid->addItem($obj_std);
            } 
           
		   //mantém o form
		   $this->form->setData($data);
		   
		   TTransaction::close(); // fecha a transação.	
		   
	}//onReload1
	
	
	public function onReload()
	{
		
	}
	
	public function onPDF()
    {
		try
		{
			
			$data = $this->form->getData();
			
			TTransaction::open('db2');
			
			$rp_cliente = new TRepository('cliente');
			$criteria   = new TCriteria;
			
			if($data->NOME)
			{
				$criteria->add(new TFilter('NOME', 'LIKE', "%$data->NOME%"));
			}
			
			if($data->CPF)
			{
				$criteria->add(new TFilter('CPF', '=', "$data->CPF"));
			}
			
			if($data->MATR_INTERNA)
			{
				$criteria->add(new TFilter('MATR_INTERNA', '=', "$data->MATR_INTERNA"));
			}
			$cliente = $rp_cliente->load($criteria);
			
			TTransaction::close();
			
			
			$widths = array(70, 70, 230, 60, 60);
			$table = new TTableWriterPDF ( $widths );
			$table->style = 'border:0';
		
			$table->addStyle('title', 'Arial', '10', 'BI', '#ffffff', '#407B49');
			$table->addStyle('datap', 'Arial', '10', '', '#000000', '#869FBB');
			$table->addStyle('datai', 'Arial', '10', '', '#000000', '#ffffff');
			$table->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
			$table->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
		
			$table->addRow();
			$table->addCell(('Relatório de Clientes'), 'center', 'header', 5);
			
			
			$table->addRow();
			$table->addCell('Matr. Interna',  'left', 'title');
			$table->addCell('Cpf',  'left', 'title');
			$table->addCell('Nome',  'left', 'title');
			$table->addCell('Telefone',  'left', 'title');
			$table->addCell('Celular',  'left', 'title');
			
		
			$color = FALSE;
		
			foreach($cliente as $clientes )
			{
				$style = $color ? 'datap' : 'datai';
				
				$table->addRow();
				$table->addCell( $clientes->MATR_INTERNA, 'left', $style);
				$table->addCell( $clientes->CPF, 'left', $style);
				$table->addCell( $clientes->NOME, 'left', $style);
				$table->addCell( $clientes->FONE_RES, 'left', $style);
				$table->addCell( $clientes->FONE_CEL, 'left', $style);
				
				$color = !$color;
			
			}
		
			$table->addRow();
			$table->addCell(date('d-m-Y  H:i'), 'center', 'footer', 5);//'d-m-Y h:i:s'
		
			$table->save('app/output/relatorio.pdf');
		
			parent::openFile('app/output/relatorio.pdf');
			
			$this->form->setData($data);
			
		}//try
		catch(Exception $e)	
		{
			new TMessage('error', $e->getMessage() );
			$this->datagrid->clear();
		}
			

    }//onPDF
	
	public function onSel501($param)
	{
		try
        {
			TTransaction::open('db'); // abre uma transação 
            $conn = TTransaction::get(); // obtém a conexão 
			
			$data = $this->form->getData();
			
			$nome         = '%'. $data->NOME .'%' ;
			$contrato_old = $data->CONTRATO_OLD;
			$tipo_cto     = $data->TIPO_CTO;
			$entidades    = $data->ENTIDADES;
			$vence        = $data->VENCE;
			$data_ini     = TDate::date2us($data->DT_INI);
			$data_fim     = TDate::date2us($data->DT_FIM);
			$receber      = $data->CKRECEBER;
			$recebidas    = $data->CKRECEBIDAS;
			$ordenar      = $data->ORDENAR;
			$banco        = $data->BANCO;  
			$moti_cance   = $data->MOTIVOS_CANCELAMENTO;  
			$cobertura    = $data->COBERTURA;  
			$estado       = $data->ESTADO;  
			$ent_gar      = $data->ENT_GAR;  
			$apolice      = $data->APOLICE;  
			
			if($data->CKCPF)
			{
				$sql_matr_orgao = 'MIN(CL.CPF) AS MATR_ORGAO';
			}	
			else
			{
				$sql_matr_orgao = 'MIN(C.MATR_ORGAO) AS MATR_ORGAO'; 
				//$sql_matr_orgao = 'MIN(C.MATR_ORGAO) AS MATR_ORGAO'; 
			}	
			
			/*  SELECT ');
			
			' MIN(CL.CPF) AS MATR_ORGAO,')
			else
			  Add(' MIN(C.MATR_ORGAO) AS MATR_ORGAO,');
		
			*/
			
			$sql = " SELECT C.DT_PAGAMENTO AS DT_INICIO, F.CONTRATO_OLD, F.CLI_FOR,  F.DATA_VENCIMENTO, F.RETORNO_CAB, F.PARCELA_CTO, F.DATA_PAGAMENTO, F.VALOR_PAGAR, F.DESCONTO, F.MULTA, F.VALOR_PAGO, CL.NOME, MIN(CC.SITUACAO) AS SITUACAO, E.RAZAO_SOCIAL AS ENT_NOME, C.BANCO, C.TIPO_CTO, C.ENTIDADE_COLETIVA , C.PARCELAS,   C.PROPOSTA
			
			FROM FINANR2 F
			
			INNER JOIN CONTRATOS2 C ON (C.CONTRATO_OLD = F.CONTRATO_OLD) AND (C.MATR_INTERNA = F.CLI_FOR)
			
			INNER JOIN CLIENTES CL ON CL.MATR_INTERNA = F.CLI_FOR 
			
			INNER JOIN COB_CONTRATOS CC ON (CC.CONTRATO = C.CONTRATO_OLD) AND (CC.MATR_INTERNA = C.MATR_INTERNA)
			
			INNER JOIN ENTIDADES E ON E.COD_INT = C.ENTIDADE_COLETIVA
			
			INNER JOIN COBERTURAS CB ON CB.CODIGO = CC.COBERTURA
			
			WHERE 1 = 1
			";
			
			$parametros = array();
			
			
			
			//TESTA OS COMPOS QUE FORAM PREENCHIDOS
			if(!empty($vence) and ($vence == 'venc'))
			{
				$parametros[] =  $data_ini; 
				$parametros[] =  $data_fim; 
				
				$sql .= "AND F.DATA_VENCIMENTO BETWEEN ? AND ?  ";
				
				//new TMessage('info', 'Data inicial ' . $data_ini . 'até ' . $data_fim);
			}//venc
			
			if(!empty($vence) and ($vence == 'pag'))
			{
				$parametros[] =  $data_ini; 
				$parametros[] =  $data_fim; 
				
				$sql .= "AND F.DATA_PAGAMENTO BETWEEN ? AND ?  ";
				
				//new TMessage('info', 'Data inicial ' . $data_ini . 'até ' . $data_fim);
			}//pag
			
			if(!empty($receber))
			{
				$parametros[] =  0 ; 
				$sql .= " AND F.VALOR_PAGO <=  ?";
			}//receber
			
			if(!empty($recebidas))
			{
				$parametros[] =  0 ; 
				$sql .= " AND F.VALOR_PAGO >  ?";
			}//recebidas
			
			if(!empty($tipo_cto))
			{
				$parametros[] =  $tipo_cto; 
				$sql .= " AND C.TIPO_CTO =  ?";
			}//tipo_cto
			
			if(!empty($entidades))
			{
				$parametros[] =  $entidades; 
				$sql .= " AND C.ENTIDADE_COLETIVA =  ?";
			}//entidades
			
			if(!empty($banco))
			{
				$parametros[] =  $banco; 
				$sql .= " AND C.BANCO = ? ";
			}//banco
			
			if(!empty($moti_cance))
			{
				$parametros[] =  $moti_cance; 
				$sql .= " AND CC.SITUACAO = ? ";
			}//moti_cance
			
			if(!empty($cobertura))
			{
				$parametros[] =  $cobertura; 
				$sql .= " AND CC.COBERTURA = ? ";
			}//cobertura
			
			if(!empty($estado))
			{
				$parametros[] =  $estado; 
				$sql .= " AND CL.UF_RES = ?";
			}//estado
			
			if(!empty($ent_gar))
			{
				$parametros[] =  $ent_gar; 
				$sql .= " AND CB.ENT_GAR = ?";
			}//ent_gar
			
			if(!empty($apolice))
			{
				$parametros[] =  $apolice; 
				$sql .= " AND C.PROPOSTA = ?";
			}//apolice
			
			
			//
			if(!empty($nome))
			{
				$parametros[] =  $nome; 
				$sql .= " AND CL.NOME LIKE ?";
			}//nome
			
			
			
			if(!empty($contrato_old))
			{
				$parametros[] =  $contrato_old; 
				$sql .= " AND F.CONTRATO_OLD =  ?"; 
				//$sql .= " AND F.CONTRATO_OLD =  ?"; 
			}//contrato_old
			
			
			
			$sql .= ' GROUP BY F.CONTRATO_OLD, F.DATA_VENCIMENTO, F.CLI_FOR,  F.RETORNO_CAB, F.PARCELA_CTO, F.DATA_PAGAMENTO, F.VALOR_PAGAR, F.DESCONTO, F.MULTA, F.VALOR_PAGO, CL.NOME, DT_INICIO, ENT_NOME, C.PARCELAS , C.BANCO, C.TIPO_CTO, C.ENTIDADE_COLETIVA,   C.PROPOSTA ';
			
			
			if(($ordenar) and ($ordenar == 'nome'))
			{	
			    $sql .='ORDER BY CL.NOME';
			}
			else
			{	
				$sql .='ORDER BY min(C.MATR_ORGAO) ';
			}
			
            $sth = $conn->prepare($sql); 
            
            //$sth->execute(array(1,1200));
            $sth->execute($parametros);
            $result = $sth->fetchAll();
			/** FIM SQL **/
			
			//Salva SQL na sessão
			TSession::setValue('TS_cliente', $result);
			 
			
			//$designer = new TPDFDesigner;
			$designer = new TReportHeaderFooter; 
			$designer->fromXml('app/reports/rel501_3.pdf.xml');
			
			$data = $this->form->getData();
			
			
			$rel_501 =  TSession::getValue('TS_cliente');
			$pagina = 1;
			foreach( $rel_501 as $rels_501 )
			{
				$designer->replace('{DT_INI}', $data->DT_INI );
				$designer->replace('{DT_FIM}', $data->DT_FIM );
			    $designer->replace('{NOME}', $rels_501['NOME']);
			    $designer->replace('{CONTRATO_OLD}', $rels_501['CONTRATO_OLD']);
			    $designer->replace('{n}', $pagina);
				
			    //$designer->replace('{ENTIDADES}', $rels_501['ENTIDADES']);
				
				$designer->generate();//tras todos
				
				
				
				/*$designer->gotoAnchorXY('DT_INI');
				$designer->setFont('Arial', '' ,8);
				$designer->Write(20, $data->DT_INI);
				$designer->generate();*/
				
				//$designer->save('app/output/rel501.pdf');
				
				//$designer->save('app/output/rel501.pdf');
			
				// parent::openFile('app/output/rel501.pdf');
				
				$pagina++;
			}
			
			//$designer->generate();//trás o ultimo
			$designer->save('app/output/rel501_3.pdf');
			
			parent::openFile('app/output/rel501_3.pdf');
			
		   //mantém o form
		   $this->form->setData($data);
		   
		   TTransaction::close(); //fecha a transação.
			
        }//try    
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
			TTransaction::rollback();
			$this->form->setData($data);
        }
		
	}//onSel501
	
	public function onSel502($param)
	{
		try
        {
			TTransaction::open('db'); // abre uma transação 
            $conn = TTransaction::get(); // obtém a conexão 
			
			$data = $this->form->getData();
			
			$nome         = '%'. $data->NOME .'%' ;
			$contrato_old = $data->CONTRATO_OLD;
			$tipo_cto     = $data->TIPO_CTO;
			$entidades    = $data->ENTIDADES;
			$vence        = $data->VENCE;
			$data_ini     = TDate::date2us($data->DT_INI);
			$data_fim     = TDate::date2us($data->DT_FIM);
			$receber      = $data->CKRECEBER;
			$recebidas    = $data->CKRECEBIDAS;
			$ordenar      = $data->ORDENAR;
			$banco        = $data->BANCO;  
			$moti_cance   = $data->MOTIVOS_CANCELAMENTO;  
			$cobertura    = $data->COBERTURA;  
			$estado       = $data->ESTADO;  
			$ent_gar      = $data->ENT_GAR;  
			$apolice      = $data->APOLICE;  
			
			if($data->CKCPF)
			{
				$sql_matr_orgao = 'MIN(CL.CPF) AS MATR_ORGAO';
			}	
			else
			{
				$sql_matr_orgao = 'MIN(C.MATR_ORGAO) AS MATR_ORGAO'; 
				//$sql_matr_orgao = 'MIN(C.MATR_ORGAO) AS MATR_ORGAO'; 
			}	
			
			/*  SELECT ');
			
			' MIN(CL.CPF) AS MATR_ORGAO,')
			else
			  Add(' MIN(C.MATR_ORGAO) AS MATR_ORGAO,');
		
			*/
			
			$sql = " SELECT C.DT_PAGAMENTO AS DT_INICIO, F.CONTRATO_OLD, F.CLI_FOR,  F.DATA_VENCIMENTO, F.RETORNO_CAB, F.PARCELA_CTO, F.DATA_PAGAMENTO, F.VALOR_PAGAR, F.DESCONTO, F.MULTA, F.VALOR_PAGO, CL.NOME, MIN(CC.SITUACAO) AS SITUACAO, E.RAZAO_SOCIAL AS ENT_NOME, C.BANCO, C.TIPO_CTO, C.ENTIDADE_COLETIVA , C.PARCELAS,   C.PROPOSTA
			
			FROM FINANR2 F
			
			INNER JOIN CONTRATOS2 C ON (C.CONTRATO_OLD = F.CONTRATO_OLD) AND (C.MATR_INTERNA = F.CLI_FOR)
			
			INNER JOIN CLIENTES CL ON CL.MATR_INTERNA = F.CLI_FOR 
			
			INNER JOIN COB_CONTRATOS CC ON (CC.CONTRATO = C.CONTRATO_OLD) AND (CC.MATR_INTERNA = C.MATR_INTERNA)
			
			INNER JOIN ENTIDADES E ON E.COD_INT = C.ENTIDADE_COLETIVA
			
			INNER JOIN COBERTURAS CB ON CB.CODIGO = CC.COBERTURA
			
			WHERE 1 = 1
			";
			
			$parametros = array();
			
			
			
			//TESTA OS COMPOS QUE FORAM PREENCHIDOS
			if(!empty($vence) and ($vence == 'venc'))
			{
				$parametros[] =  $data_ini; 
				$parametros[] =  $data_fim; 
				
				$sql .= "AND F.DATA_VENCIMENTO BETWEEN ? AND ?  ";
				
				//new TMessage('info', 'Data inicial ' . $data_ini . 'até ' . $data_fim);
			}//venc
			
			if(!empty($vence) and ($vence == 'pag'))
			{
				$parametros[] =  $data_ini; 
				$parametros[] =  $data_fim; 
				
				$sql .= "AND F.DATA_PAGAMENTO BETWEEN ? AND ?  ";
				
				//new TMessage('info', 'Data inicial ' . $data_ini . 'até ' . $data_fim);
			}//pag
			
			if(!empty($receber))
			{
				$parametros[] =  0 ; 
				$sql .= " AND F.VALOR_PAGO <=  ?";
			}//receber
			
			if(!empty($recebidas))
			{
				$parametros[] =  0 ; 
				$sql .= " AND F.VALOR_PAGO >  ?";
			}//recebidas
			
			if(!empty($tipo_cto))
			{
				$parametros[] =  $tipo_cto; 
				$sql .= " AND C.TIPO_CTO =  ?";
			}//tipo_cto
			
			if(!empty($entidades))
			{
				$parametros[] =  $entidades; 
				$sql .= " AND C.ENTIDADE_COLETIVA =  ?";
			}//entidades
			
			if(!empty($banco))
			{
				$parametros[] =  $banco; 
				$sql .= " AND C.BANCO = ? ";
			}//banco
			
			if(!empty($moti_cance))
			{
				$parametros[] =  $moti_cance; 
				$sql .= " AND CC.SITUACAO = ? ";
			}//moti_cance
			
			if(!empty($cobertura))
			{
				$parametros[] =  $cobertura; 
				$sql .= " AND CC.COBERTURA = ? ";
			}//cobertura
			
			if(!empty($estado))
			{
				$parametros[] =  $estado; 
				$sql .= " AND CL.UF_RES = ?";
			}//estado
			
			if(!empty($ent_gar))
			{
				$parametros[] =  $ent_gar; 
				$sql .= " AND CB.ENT_GAR = ?";
			}//ent_gar
			
			if(!empty($apolice))
			{
				$parametros[] =  $apolice; 
				$sql .= " AND C.PROPOSTA = ?";
			}//apolice
			
			
			//
			if(!empty($nome))
			{
				$parametros[] =  $nome; 
				$sql .= " AND CL.NOME LIKE ?";
			}//nome
			
			
			
			if(!empty($contrato_old))
			{
				$parametros[] =  $contrato_old; 
				$sql .= " AND F.CONTRATO_OLD =  ?"; 
				//$sql .= " AND F.CONTRATO_OLD =  ?"; 
			}//contrato_old
			
			
			
			$sql .= ' GROUP BY F.CONTRATO_OLD, F.DATA_VENCIMENTO, F.CLI_FOR,  F.RETORNO_CAB, F.PARCELA_CTO, F.DATA_PAGAMENTO, F.VALOR_PAGAR, F.DESCONTO, F.MULTA, F.VALOR_PAGO, CL.NOME, DT_INICIO, ENT_NOME, C.PARCELAS , C.BANCO, C.TIPO_CTO, C.ENTIDADE_COLETIVA,   C.PROPOSTA ';
			
			
			if(($ordenar) and ($ordenar == 'nome'))
			{	
			    $sql .='ORDER BY CL.NOME';
			}
			else
			{	
				$sql .='ORDER BY min(C.MATR_ORGAO) ';
			}
			
            $sth = $conn->prepare($sql); 
            
            //$sth->execute(array(1,1200));
            $sth->execute($parametros);
            $result = $sth->fetchAll();
			/** FIM SQL **/
			
			//Salva SQL na sessão
			TSession::setValue('TS_cliente', $result);
			 
			$data = $this->form->getData();
			
			//$designer = new TPDFDesigner;
			$designer = new TReportHeaderFooter; 
			$designer->fromXml('app/reports/rel501_3.pdf.xml');
			$designer->generate();//tras todos
			
			
			
			
			$rel_501 =  TSession::getValue('TS_cliente');
			$pagina = 1;
			foreach( $rel_501 as $rels_501 )
			{
				$designer->replace('{DT_INI}', $data->DT_INI );
				$designer->replace('{DT_FIM}', $data->DT_FIM );
			    $designer->replace('{NOME}', $rels_501['NOME']);
			    $designer->replace('{CONTRATO_OLD}', $rels_501['CONTRATO_OLD']);
			    $designer->replace('{n}', $pagina);
				
			    //$designer->replace('{ENTIDADES}', $rels_501['ENTIDADES']);
				
				//$designer->generate();//tras todos
				
				
				
				/*$designer->gotoAnchorXY('DT_INI');
				$designer->setFont('Arial', '' ,8);
				$designer->Write(20, $data->DT_INI);
				$designer->generate();*/
				
				//$designer->save('app/output/rel501.pdf');
				
				//$designer->save('app/output/rel501.pdf');
			
				// parent::openFile('app/output/rel501.pdf');
				
				$pagina++;
			}
			
			//$designer->generate();//trás o ultimo
			$designer->save('app/output/rel501_3.pdf');
			
			parent::openFile('app/output/rel501_3.pdf');
			
		   //mantém o form
		   $this->form->setData($data);
		   
		   TTransaction::close(); //fecha a transação.
			
        }//try    
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
			TTransaction::rollback();
			$this->form->setData($data);
        }
		
	}//onSel502
	
	
	
	public function onSave()
	{
		try
		{
			TTransaction::open('db');
			
			$tmp376 = new tmp376;

			//$tmp376-> = ;	
			
			/*
			  "CTMP"	VARCHAR(30) NOT NULL,
			  "ENTCODIGO"	VARCHAR(5),
			  "MRFMESANO"	DATE,
			  "QUAID"	VARCHAR(3),
			  "TPMOID"	VARCHAR(4),
			  "CMPID"	VARCHAR(4),
			  "RAMCODIGO"	VARCHAR(4),
			  "ESRDATAINICIO"	DATE,
			  "ESRDATAFIM"	DATE,
			  "ESRDATAOCORR"	DATE,
			  "ESRDATAREG"	DATE,
			  "ESRVALORMOV"	NUMERIC(9, 2),
			  "ESRDATACOMUNICA"	DATE,
			  "ESRCODCESS"	VARCHAR(5),
			  "ESRNUMSIN"	VARCHAR(20),
			  "ESRVALORMON"	NUMERIC(9, 2),
			  "ORDEM"	INTEGER,
			  "NOME"	VARCHAR(60),
			  "COBERTURA"	VARCHAR(10),
			  "CONTRATO_OLD"	VARCHAR(11),
			  "MATR_INTERNA"	INTEGER,
			  "TIPO"	VARCHAR(20),
			  "TOT_PG"	NUMERIC(9, 2),
			  "CONTROLE"	INTEGER,
			  "VL_SIN"	NUMERIC(9, 2)
			*/
			
			TTransaction::close();
			
		}
		catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
			TTransaction::rollback();
        }
		
	}//onSave
	
	public function onDatagrid($param)
	{
		try
		{
			$data = $this->form->getData();
			
			$obj = new STDClass;	
			
			$obj->DT_INI = $param['DT_INI'];
			$obj->DT_FIM = $param['DT_FIM'];
			
			//$obj->DT_INI = '01/01/2011';
			//$obj->DT_FIM = '01/01/2011';
			
			$this->datagrid->addItem($obj);
			
			$this->form->setData($data);
		}
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
		}
		
	}//onDatagrid
	
	//testes
	public function onSelTeste($param)
	{
		try
        {
			TTransaction::open('db3'); // abre uma transação 
            $conn = TTransaction::get(); // obtém a conexão 
			
			$data = $this->form->getData();
			
			$sql = " SELECT C.ID_CONTRATO, C.MATR_INTERNA, C.DT_INICIO, C.FORMA_PGTO, CL.MATR_INTERNA, CL.NOME, CL.CPF
			FROM contratos2 C
			
			INNER JOIN clientes CL ON CL.MATR_INTERNA = C.MATR_INTERNA
			
			WHERE 1 = 1
			";
			
			$parametros = array();
			
			$nome     = '%'. $data->NOME .'%' ;
			$data_ini = $data->DT_INI;
			$data_fim = $data->DT_FIM;
			$vence    = $data->VENCE;
			
			//2
			$data_ini2 = TDate::date2br($data->DT_INI);
			$data_fim2 = TDate::date2br($data->DT_FIM);
			
			$data_ini3 = TDate::date2us($data->DT_INI);
			$data_fim3 = TDate::date2us($data->DT_FIM);
			
			
			
			
			//CONVERSÃO DE DADAS
			
           $data_ini1 =  (preg_match('/\//',$data_ini)) ? implode('-', array_reverse(explode('/', $data_ini))) : implode('/', array_reverse(explode('-', $data_ini)));
		   
		   $data_fim1 =  (preg_match('/\//',$data_fim)) ? implode('-', array_reverse(explode('/', $data_fim))) : implode('/', array_reverse(explode('-', $data_fim)));
		    
			
			//APAGAR
			if(!empty($nome))
			{
				$parametros[] =  $nome; 
				$sql .= " AND CL.NOME LIKE ? ";
			}
			
			//

			if(!empty($vence) and ($vence == 'venc'))
			{
				//$parametros[] =  $data_ini1; 
				//$parametros[] =  $data_fim1; 
				
				$parametros[] =  $data_ini3; 
				$parametros[] =  $data_fim3; 
				//$sql .= "AND F.DATA_VENCIMENTO LIKE ?";
				
				$sql .= "AND C.DT_INICIO BETWEEN ? AND ?  ";
				
				//new TMessage('info', 'Data inicial ' . $data_ini3 . 'até ' . $data_fim3);
			}
			
			
			
			$sql .= ' GROUP BY C.ID_CONTRATO, C.MATR_INTERNA, C.DT_INICIO, C.FORMA_PGTO, CL.MATR_INTERNA, CL.NOME, CL.CPF';
			
            $sth = $conn->prepare($sql); 
            
            //$sth->execute(array(1,1200));
            $sth->execute($parametros);
            $result = $sth->fetchAll();
			
			TSession::setValue('TS_cliente', $result);
			 
            // exibe os resultados
            foreach ($result as $row) 
            { 
                $obj_std = new STDClass;
				$obj_std->NOME         = $row['NOME'];
				$obj_std->DT_INICIO    = $row['DT_INICIO'];
				
				//$this->datagrid->addItem($row);
				$this->datagrid->addItem($obj_std);
            } 
           
		   //mantém o form
		   $this->form->setData($data);
		   
		   TTransaction::close(); // fecha a transação.
			
        }//try    
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
			TTransaction::rollback();
			$this->form->setData($data);
        }
		
	}//onSelTeste
	
	public function onSelTeste2($param)
	{
		try
        {
			TTransaction::open('db'); // abre uma transação 
            $conn = TTransaction::get(); // obtém a conexão 
			
			$data = $this->form->getData();
			
			$sql = " SELECT C.ID_CONTRATO, C.MATR_INTERNA, C.DT_INICIO, C.DT_PAGAMENTO, C.VALOR, CL.MATR_INTERNA, CL.NOME, CL.CPF
			FROM CONTRATOS3 C
			
			INNER JOIN CLIENTES2 CL ON CL.MATR_INTERNA = C.MATR_INTERNA
			
			WHERE 1 = 1
			";
			
			$parametros = array();
			
			$nome     = '%'. $data->NOME .'%' ;
			$data_ini = $data->DT_INI;
			$data_fim = $data->DT_FIM;
			$vence    = $data->VENCE;
			
			//3
			$data_ini3 = TDate::date2us($data->DT_INI);
			$data_fim3 = TDate::date2us($data->DT_FIM);
			
			
			
			
			//CONVERSÃO DE DADAS
			
           $data_ini1 =  (preg_match('/\//',$data_ini)) ? implode('-', array_reverse(explode('/', $data_ini))) : implode('/', array_reverse(explode('-', $data_ini)));
		   
		   $data_fim1 =  (preg_match('/\//',$data_fim)) ? implode('-', array_reverse(explode('/', $data_fim))) : implode('/', array_reverse(explode('-', $data_fim)));
		    
			
			//APAGAR
			if(!empty($nome))
			{
				$parametros[] =  $nome; 
				$sql .= " AND CL.NOME LIKE ? ";
			}
			
			//

			if(!empty($vence) and ($vence == 'venc'))
			{
				//$parametros[] =  $data_ini1; 
				//$parametros[] =  $data_fim1; 
				
				$parametros[] =  $data_ini3; 
				$parametros[] =  $data_fim3; 
				//$sql .= "AND F.DATA_VENCIMENTO LIKE ?";
				
				$sql .= "AND C.DT_INICIO BETWEEN ? AND ?  ";
				
				//new TMessage('info', 'Data inicial ' . $data_ini3 . 'até ' . $data_fim3);
			}
			
			
			
			$sql .= ' GROUP BY C.ID_CONTRATO, C.MATR_INTERNA, C.DT_INICIO, C.DT_PAGAMENTO, C.VALOR, CL.MATR_INTERNA, CL.NOME, CL.CPF';
			
            $sth = $conn->prepare($sql); 
            
            //$sth->execute(array(1,1200));
            $sth->execute($parametros);
            $result = $sth->fetchAll();
			
			//TSession::setValue('TS_cliente', $result);
			 
            // exibe os resultados
            foreach ($result as $row) 
            { 
                $obj_std = new STDClass;
				$obj_std->NOME         = $row['NOME'];
				$obj_std->DT_INICIO    = $row['DT_INICIO'];
				$obj_std->VALOR        = $row['VALOR'];
				
				//$this->datagrid->addItem($row);
				$this->datagrid->addItem($obj_std);
            } 
           
		   //mantém o form
		   $this->form->setData($data);
		   
		   TTransaction::close(); // fecha a transação.
			
        }//try    
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
			TTransaction::rollback();
			$this->form->setData($data);
        }
		
	}//onSelTeste2
	
	
	public function onTeste($param)
	{
		try
		{
			$data = $this->form->getData();
			//$this->form->setData($data);
			
			$win = TWindow::create('Resultado', 0.8, 0.8);
			$win->add( '<pre>' . print_r($data, true) . '</pre>' );
			$win->show();
			
			$this->form->setData($data);
			
		}
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();//rollback
		}
		
	}//onTeste
	
	public function onTeste2($param)
	{
		try
		{
			$data = $this->form->getData();
			//$this->form->setData($data);
			
			$data_ini    = $data->DT_INI;
			$data_fim    = $data->DT_FIM;
			
			//converter data
			$data_ini2 = (preg_match('/\//',$data_ini)) ? implode('/', array_reverse(explode('/', $data_ini))) : implode('/', array_reverse(explode('-', $data_ini)));
			
			
			$data_ini1 = (int) $data->DT_INI;
			$data_ini1 = gettype($data_ini1);
			
			
			
			
			//$data_ini1 = gettype($data->DT_INI);
			
			if(!empty($param['VENCE']) and ($param['VENCE'] == 'venc'))
			{
				new TMessage('info', 'Param = ' . $data->VENCE . ' e data ini = ' . $data_ini2 . ' até 	' . $data_fim );
			}
			
			if(!empty($param['VENCE']) and ($param['VENCE'] == 'pag'))
			{
				new TMessage('info', 'Param = ' . $data->VENCE . ' e data ini = ' . $data_ini . ' até 	' . $data_fim ); 
			}
			
			if(!empty($data->TIPO_CTO))
			{
				new TMessage('info', 'Tipo de contrato = ' . $data->TIPO_CTO  ); 
			}
			
			if(!empty($data->ENTIDADES))
			{
				new TMessage('info', 'Entidade = ' . $data->ENTIDADES  ); 
			}
			
			if($data->CKCPF)
			{
				new TMessage('info', 'CPF marcado');
			}	
			else
			{
				new TMessage('info', 'CPF desmarcado');
			}
			
			/*if(isset($param['CKCPF']))
			{
				new TMessage('info', 'Cpf selecionado');
			}	
			else
			{
				new TMessage('info', 'Cpf desmarcado' );
			}
			
			if(isset($param['CKRECEBER']))
			{
				new TMessage('info', 'Valor a receber selecionado');
			}*/
			
			
			
			
			
			/*
			if($param['VENCE'] == 'venc'  )
			{
				new TMessage('info', 'Data '. $data->VENCE );
			}

			if($param['VENCE'] == 'pag'  )
			{
				new TMessage('info', 'Data '. $data->VENCE );
			}	
			*/
			
			//new TMessage('info', '<pre>' . print_r($data, true) . '</pre>' );
			
			$this->form->setData($data);
			
		}
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();//rollback
		}
		
	}//onTeste2
	
	//Teste
	function onGenerate() 
	{ 
		try 
		{ 
		// open a transaction with database 'samples' 
		TTransaction::open('db'); 

		// load all customers 
		$repository = new TRepository('OrdemServico'); 
		$criteria = new TCriteria; 
		$OrdemServico = $repository->load($criteria); 

		$data = $this->form->getData(); 
		$this->form->validate(); 

		$designer = new TPDFDesigner; 
		$designer->fromXml('app/reports/Orcamento.pdf.xml'); 
		$designer->replace('{Id_os}', $data->Id_os ); 
		$designer->replace('{DataAbertura}', $data->DataAbertura); 
		$designer->replace('{Hora_OS}', $data->Hora_OS); 
		$designer->replace('{Atendente}', utf8_decode($data->Atendente)); 
		$designer->replace('{status_id}', utf8_decode($data->status_id)); // trazer o texto ao invés do id 
		$designer->replace('{Cliente_id}', utf8_decode($data->Cliente_id)); // trazer o texto ao invés do id 
		$designer->replace('{DataVencimento}', $data->DataVencimento); 
		$designer->replace('{LocalServico}', utf8_decode($data->LocalServico)); 
		$designer->replace('{Setor}', utf8_decode($data->Setor)); // trazer o texto ao invés do id 
		$designer->replace('{TempoExecucao}', $data->TempoExecucao); 
		$designer->replace('{TituloServico}', utf8_decode($data->TituloServico)); 
		$designer->replace('{Servico}', utf8_decode($data->Servico)); 
		$designer->replace('{Valor_Inicial}', $data->Valor_Inicial); // este campo a direita 
		$designer->replace('{Desconto}', $data->Desconto); // este campo a direita 
		$designer->replace('{Acrescimos}', $data->Acrescimos); // este campo a direita 
		$designer->replace('{ValorTotal}', $data->ValorTotal); // este campo a direita 


		$designer->generate(); 



		if ($OrdemServico) 
		{ 
			foreach ($OrdemServico as $OrdemServico) 
			{   
				$designer->gotoAnchorX(''); 
				
				// cabeçalho 
				$designer->Image('zeromeia.com/apps/zeromeia/app/images/ZeroMeia_640x286.jpg',20,18,-300); 
				$designer->SetY(75); 
				$designer->SetFont('Arial', '', 18); 
				$designer->setFontColorRGB( '#000000' ); 
				$designer->SetxY(50,86); 
				$designer->SetFont('Arial', '', 10); 
				$designer->setFontColorRGB( '#000000' ); 
				$designer->Cell(0, 10, utf8_decode('www.zeromeia.com'),0,0,'L'); 


				// rodapé 
				$designer->SetY(-12); 
				$designer->SetFont('Arial', '', 8); 
				$designer->setFontColorRGB( '#000000' ); 
				$designer->Cell(590, 10, utf8_decode('Pág. ').$designer->PageNo().' de {nb}',0,0,'R'); 
				$designer->SetY(-12); 
				$designer->Cell(122, 10, utf8_decode('Zero Meia Tecnologia © 2018 '),0,0,'R'); 
				$designer->SetY(-12); 
				$designer->Cell(340,10,utf8_decode("Impresso em ".date("d/m/Y H:i:s"."")),0,1,'R'); 
				$designer->SetY(-12); 
				$designer->Cell(410,10,utf8_decode(" - ".TSession::getValue('username')),0,1,'R'); //nome do usuário 

			// grid background 

			}//foreach 
			
		}//if ($OrdemServico) 

		$file = 'app/output/OrdemServico.pdf'; 

		if (!file_exists($file) OR is_writable($file)) 
		{ 
			$designer->save($file); 
			parent::openFile($file); 
		} 
		else 
		{ 
		    throw new Exception(_t('Permission denied') . ': ' . $file); 
		} 

		// new TMessage('info', 'Relatório gerado. Por favor, habilite os popups no seu navegador.'); 

		// close the transaction 
		TTransaction::close();
		
		}//try 
		catch (Exception $e) // in case of exception 
		{ 
			new TMessage('error', 'Error ' . $e->getMessage()); 
			TTransaction::rollback(); 
		} 
	} 
	
	
	//Teste2 
	function onGenerate2()
    {
        try
        {
            // open a transaction with database 'samples'
            TTransaction::open('db');
            
            // load all customers
            $repository = new TRepository('OrdemServico');
            $criteria   = new TCriteria;
            $OrdemServico = $repository->load($criteria);
            
            $data = $this->form->getData('OrdemServico'); 
            $this->form->validate();
			
            $designer = new TPDFDesigner;
            $designer->fromXml('app/reports/Orcamento.pdf.xml');
            $designer->generate();
            $fill = TRUE;                                    
            
            $designer->gotoAnchorX('');
            $designer->SetXY(530,72);
            $designer->SetFont('Arial', 'B', 18);
            $designer->Cell(30,10, $data->Id_os, 0, 1, 'R');
            $designer->SetFont('Arial', '', 10);
            
            $designer->SetXY(16,136);
            $designer->Cell(20,10, DateTime::createFromFormat('Y-m-d', $data->DataAbertura)->format( 'd/m/Y' )); 
            
            $designer->SetXY(148,136);
            $designer->Cell(20,10, $data->Hora_OS, 0, 1, 'L');
            
            $designer->SetXY(256,136);
            $designer->Cell(30,10, utf8_decode($data->Atendente), 0, 1, 'L');
            
            $designer->SetXY(432,136);
            $designer->Cell(30,10, utf8_decode($data->Status->descricao), 0, 0, 'L'); 
            
            $designer->SetXY(16,190);
            $designer->Cell(300, 10, utf8_decode($data->Cliente->descricao), 0, 0, 'L');
            
            $designer->SetXY(432,190);
            $designer->Cell(20,10, DateTime::createFromFormat('Y-m-d', $data->DataVencimento)->format( 'd/m/Y' ));
            
            $designer->SetXY(16,245);
            $designer->Cell(200, 10, utf8_decode($data->LocalServico), 0, 1, 'L');
            $designer->SetXY(246,245);
            $designer->Cell(180,10, utf8_decode($data->Setor->descricao), 0, 1, 'L');
            
            $designer->SetXY(432,245);
            $designer->Cell(30,10, $data->TempoExecucao, 0, 1, 'L');
            
            $designer->SetXY(16,300);
            $designer->Cell(400, 10, utf8_decode($data->TituloServico), 0, 1, 'L');
            
            $designer->SetXY(16,360);
            $designer->MultiCell(550, 10, utf8_decode($data->Servico), 0, 1, 'L');
                        
            $designer->SetXY(60,655);
            $designer->Cell(80, 10, number_format((double)$data->Valor_Inicial, 2, ',', '.'), 0, 0, 'R');
            $designer->Cell(132, 10, number_format((double)$data->Desconto, 2, ',', '.'), 0, 0, 'R');
            $designer->Cell(140, 10, number_format((double)$data->Acrescimos, 2, ',', '.'), 0, 0, 'R');
            
            $designer->SetXY(90,655);
            $designer->SetFont('Arial', 'B', 13);
            $designer->Cell(475, 10, number_format((double)$data->ValorTotal,2, ',', '.'), 0, 0, 'R');
            $designer->SetXY(100,760);
            $designer->SetFont('Arial', '', 10);
            $designer->Cell(90,10, $data->Setor->descricao, 0, 1, 'C');
               

			//fim foreach
	
			
            // cabeçalho
            $designer->Image('http://zeromeia.com/apps/zeromeia/app/images/ZeroMeia_640x286.jpg',20,18,-300);
            $designer->SetY(75);
            $designer->SetFont('Arial', '', 18);
            $designer->setFontColorRGB( '#000000' );
            $designer->SetxY(50,86);
            $designer->SetFont('Arial', '', 10);
            $designer->setFontColorRGB( '#000000' );
            $designer->Cell(0, 10, utf8_decode('www.zeromeia.com'),0,0,'L');
                                                             
                                                             
            // rodapé
            $designer->SetY(-12);
            $designer->SetFont('Arial', '', 8);
            $designer->setFontColorRGB( '#000000' ); 
            $designer->Cell(590, 10, utf8_decode('Pág. ').$designer->PageNo().' de {nb}',0,0,'R');                                         
            $designer->SetY(-12);
            $designer->Cell(122, 10, utf8_decode('Zero Meia Tecnologia © 2018 '),0,0,'R'); 
            $designer->SetY(-12);
            $designer->Cell(340,10,utf8_decode("Impresso em ".date("d/m/Y H:i:s"."")),0,1,'R');
            $designer->SetY(-12);
            $designer->Cell(410,10,utf8_decode(" - ".TSession::getValue('username')),0,1,'R'); //nome do usuário
                                
            // grid background
            $fill = !$fill;
            
            
            $file = 'app/output/OrdemServico.pdf';
            
            if (!file_exists($file) OR is_writable($file))
            {
                $designer->save($file);
                parent::openFile($file);
            }
            else
            {
                throw new Exception(_t('Permission denied') . ': ' . $file);
            }
            
           // new TMessage('info', 'Relatório gerado. Por favor, habilite os popups no seu navegador.');
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            TTransaction::rollback();
        }
		
    }//onGenerate2
	
	//fim teste
	
	function converteData($data)
	{
        return (preg_match('/\//',$data)) ? implode('-', array_reverse(explode('/', $data))) : implode('/', array_reverse(explode('-', $data)));
   }
	
	/*
	captura as parametros da URL e atualiza o onReload
	*/
	
	public function show()
	{
		if(!$this->loaded)
		{
			$this->onReload( func_get_arg(0) );
		}	
        parent::show(); 
		
	}//show
	
	

}//TPage


?>