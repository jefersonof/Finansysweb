<?php
class PlanosForm Extends TPage
{	
	private $form;
	private $datagridPc;
	private $datagridPf;
	private $notbook;
	
	public function __construct()
	{
		parent::__construct();
		//parent::setSize(.89, .99);
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
				exit;
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
		
        $this->form = new BootstrapFormBuilder('formPlanos');
		$this->form->setFormTitle('Cadastro de Planos (T010)');
        $this->form->setFieldSizes('100%');
        
		$codigo = new TEntry('CODIGO');
		$plano  = new TEntry('PLANO');
		$status = new TCombo('STATUS');
		$tipo   = new TCombo('TIPO');
		$tipo2  = new TDBCombo('TIPO2', 'db2', 'tipo_cto', 'CODIGO','({CODIGO})  {DESCRICAO}');
		$processo = new TDBCombo('PROCESSO_SUSEP', 'db2', 'planos_susep', 'ID_PLANOS_SUSEP', '{PROCESSO} | {DESCRICAO} ');
		
		$cobertura_id = new TDBSeekButton('COD_COBERTURA', 'DB2', 'formPlanos', 'COBERTURA', 'COBERTURA', 'COD_COBERTURA', 'COBERTURA_NOME');
		$cobertura_nome = new TEntry('COBERTURA_NOME');
		
		$faixa     = new TEntry('FAIXA');
		$cod_faixa = new TEntry('COD_FAIXA');//THidden
		
		$id_ini    = new TEntry('ID_INI');
		$id_fim    = new TEntry('ID_FIM');
		$pu_puro   = new TEntry('PU_PURO');
		//TDBCombo('nome_objeto','banco_de_dados','classe_model','campo_id','campo_descricao');
		
		
		//Formatações
		$cobertura_nome->setEditable(FALSE);
		
		
		//cria os btn
		$btn_gravar_cob = TButton::create('btn_gravar_cob',array($this, 'onGravarCob'), 'Gravar', 'fa: fa-check blue' );
		
		$btn_add_faixa = TButton::create('btn_add_faixa',array($this, 'onAddCobFaixa'), 'incluir', 'fa:plus blue' );
		
		$btn_gravar_faixa = TButton::create('btn_gravar_faixa',array($this, 'onGravarFaixa'), 'Gravar', 'fa: fa-check blue' );
		
		//formatações
		$status->addItems(array('S' => 'SIM', 'N' => 'NÃO' ));
		$tipo->addItems(array('S' => 'SIM', 'N' => 'NÃO' ));
		
		$tipo->setTip('Sempre mudar faixa etária no reajuste');
		
		//VALIDAÇÃO
		$plano->addValidation(' "Descrição" ' , new TRequiredValidator);
		
		//COMEÇA A PAGINA
		
		$row =  $this->form->addFields([ new TLabel('Código'), $codigo ],
                                       [ new TLabel('Descrição'), $plano ],
                                       [ new TLabel('Ativo'), $status ]);
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-4' ];

		$row =  $this->form->addFields([ new TLabel('Sempre mudar faixa etária'), $tipo ],
                                       [ new TLabel('Tipo de plano'), $tipo2 ],
									   [new TLabel('Processo Susep'), $processo]);
		$row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-6' ];
		
		//LINHA DIVISÓRIA 'COBERTURAS'
		$lb_coberturas = new TLabel('Coberturas');
		$lb_coberturas->style = 'color:#3c8dbc';
		$row = $this->form->addFields([$lb_coberturas]);
		$row->layout = ['col-sm-12'];
		$row->style = 'border-bottom:1px solid #D5D5D5; margin:0 0 5px 1px;';
		
		$row = $this->form->addFields([new TLabel('Código'), $cobertura_id ],
							          [new TLabel('Cobertura'), $cobertura_nome ]);
		$row->layout = ['col-sm-2','col-sm-10' ];
		
		//BARRA MENU COBERTURAS
		$row = $this->form->addFields([$btn_gravar_cob] );
		$row->layout = ['col-sm-1', 'col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';
		
		//CRIA A GRID 'PLANOS_COB'
		/*
		//$this->datagridPc = new BootstrapDatagridWrapper(new TDataGrid);
		$this->datagridPc = new TDataGrid;
		$this->datagridPc->DisableDefaultClick();
		$this->datagridPc->style = 'width:100%';
		
		$edit           = new TDataGridColumn('edit', '', 'center');
        $delete         = new TDataGridColumn('delete', '', 'left');        $cod_cobertura  = new TDataGridColumn('COD_COBERTURA', 'Código', 'left');
        $cobertura_nome = new TDataGridColumn('COBERTURA_NOME', 'Cobertura', 'left');
        
        // add the columns to the DataGrid
        $this->datagridPc->addColumn($edit);
        $this->datagridPc->addColumn($delete);
        $this->datagridPc->addColumn($cod_cobertura);
        $this->datagridPc->addColumn($cobertura_nome);

        $cod_cobertura->setTransformer([$this, 'formatRow'] );
		
		$this->datagridPc->CreateModel();
		*/
		
		///
		
		//CRIA A GRID 'PLANOS_COB'
		$this->datagridPc = new TQuickGrid;
		$this->datagridPc->DisableDefaultClick();
		$this->datagridPc->style = 'width:100%';
		
		$this->datagridPc->addQuickColumn('', 'edit', 'center', '10%');
		$this->datagridPc->addQuickColumn('', 'delete', 'center', '10%');
		$cod_cobertura = $this->datagridPc->addQuickColumn('Código', 'COD_COBERTURA', 'center', '10%');
		$this->datagridPc->addQuickColumn('Cobertura', 'COBERTURA_NOME', 'center');
		
		$cod_cobertura->setTransformer([$this, 'onFormatRow'] );
		
		// creates the datagrid actions
        $action1 = new TDataGridAction([$this, 'onSelect']);
        $action1->setUseButton(TRUE);
        $action1->setButtonClass('btn btn-default');
        $action1->setLabel(AdiantiCoreTranslator::translate('Select'));
        $action1->setImage('fa:check-circle-o blue');
        $action1->setField('COD_COBERTURA');
        //$action1->setField('CODIGO');
        
        // add the actions to the datagrid
        //$this->datagridPc->addAction($action1);
		
		//$cod_cobertura->setTransformer([$this, 'onFormatRow'] );
		//$cod_cobertura->setTransformer([$this, 'formatRow'] );
		
		$this->datagridPc->CreateModel();
		
		$row = $this->form->addFields([$this->datagridPc ] );
		$row->layout = ['col-sm-12'];
		
		//LINHA DIVISÓRIA 'FAIXAR ETÁRIAS'
		$lb_faixa = new TLabel('Faixas etárias');
		$lb_faixa->style = 'color:#3c8dbc';
		$row = $this->form->addFields([$lb_faixa]);
		$row->layout = ['col-sm-12'];
		$row->style = 'border-top:1px solid #C1CDCD;';
		$row->style = 'border-bottom:1px solid #C1CDCD; margin:0 0 5px 1px;';
		
		$row = $this->form->addFields([new TLabel('Faixa'), $faixa ],
							          [new TLabel('Idade incial'), $id_ini ],
									  [new TLabel('Idade final'), $id_fim ],
									  [new TLabel('PU puro'), $pu_puro ]);
		$row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];
		
		$row = $this->form->addFields([$cod_faixa ]);
		$row->layout = ['col-sm-12'];
		
		//barra menu coberturas
		$row = $this->form->addFields([$btn_gravar_faixa],[$btn_add_faixa] );
		$row->layout = ['col-sm-1', 'col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';
		
		//CRIA A GRID 'FAIXAS'
		$this->datagridPf = new TQuickGrid;
		$this->datagridPf->setHeight(150);
        $this->datagridPf->makeScrollable();
		$this->datagridPf->style = 'width:100%';
		
		$this->datagridPf->addQuickColumn('', 'edit', 'center', '10%');
		$this->datagridPf->addQuickColumn('', 'delete', 'center', '10%');
		$this->datagridPf->addQuickColumn('Faixa', 'FAIXA', 'center', '10%' );
		//$id_faixa = $this->datagridPf->addQuickColumn('Faixa', 'FAIXA', 'center', '10%' );
		$this->datagridPf->addQuickColumn('Idade inicial', 'ID_INI', 'center', '20%');
		$this->datagridPf->addQuickColumn('Idade final', 'ID_FIM', 'center', '20%');
		$this->datagridPf->addQuickColumn('PU puro', 'PU_PURO', 'center', '20%');
		$this->datagridPf->addQuickColumn('Cobertura', 'COD_COBERTURA', 'center', '10%');
		$this->datagridPf->addQuickColumn('ID', 'CODIGO', 'center', '10%');
		
		//CRIA A AÇÃO DA GRID
		
		//acões Inline
		/*$editaction = new TDataGridAction(array($this, 'onEditInline'));
        $editaction->setField('CODIGO');
        //$editaction->setField('FAIXA');
		$id_faixa->setEditAction($editaction);*/
		
		
		$this->datagridPf->CreateModel();
		
		$row = $this->form->addFields([$this->datagridPf ] );
		$row->layout = ['col-sm-12'];
								
		// define as ações do form
		if($permissao_geral['insercao'] == 1)
		{	
			$btn = $this->form->addAction('Salvar' ,new TAction(array($this, 'onSave')), 'far:save' );
			$btn->class = 'btn btn-sm  btn-primary';
		}
		
		$this->form->addAction('Cancelar' ,new TAction(array('PlanosListe', 'onReload')), 'far: fa-window-close red');
		
		//ADD OS CAMPOS DO FORM
		$this->formFields = array($codigo, $plano, $status, $tipo, $tipo2, $processo, $cobertura_id, $cobertura_nome, $faixa, $cod_faixa, $id_ini, $id_fim, $pu_puro, $btn_gravar_cob, $btn_gravar_faixa, $btn_add_faixa);//btn_add_cob
		
		$this->form->setFields($this->formFields );
		
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'PlanosListe'));
        $vbox->add($this->form);

        parent::add($vbox);
		
	}//__construct'
	
	/*
	  Atualiza a página com os parâmetros atuais
	*/
	public function onReload($param)
	{
		//paga a variavel de sessão 
	    $ts_plano_cob       = TSession::getValue('TS_plano_cob');
	    $ts_plano_cob_faixa = TSession::getValue('TS_plano_cob_faixa');
        $data 			    = TSession::getValue('TS_data');
		
		// LIMPA AS GRIDS 
		$this->datagridPc->clear();
		$this->datagridPf->clear();
		
		//CARREGA OS DADOS DOS plano_cob's, GRID 'datagridPc'
		if ($ts_plano_cob)
        {
            $cont = 1;
            foreach ($ts_plano_cob as $list_product_id => $list_product)
            {
                $item_name = 'prod_' . $cont++;
                //OBJ PADRÃO DAS CLASSES
				$item = new StdClass;

				//CRIA AS AÇÕES DOS BTN
                $action_del = new TAction(array($this, 'onDeleteCob'));
                $action_del->setParameter('list_product_id', $list_product_id);
				$action_del->setParameter('cont',$cont);

				$action_edi = new TAction(array($this, 'onEditItemCob'));
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
				
				//ADD OS ITEMS NA GRID datagrid_af 
				$item->CODIGO         = $list_product['CODIGO'];
				$item->COD_COBERTURA  = $list_product['COD_COBERTURA'];
				$item->COBERTURA_NOME = $list_product['COBERTURA_NOME'];
				$item->COD_PLANO      = $list_product['COD_PLANO'];
					
				$this->datagridPc->addItem( $item );
					
				//TSession::setValue('TS_codCob_faixa', $list_product['COD_COBERTURA']);
                
				$this->form->setData($data);
				
            }//foreach ($ts_plano_cob)
			
            $this->form->setFields( $this->formFields );
		    //$this->form->setData($data);
			
        }//if ($ts_plano_cob)
			
		//CARREGA OS DADOS DOS plano_cob_faxa's, para GRID 'datagridPf'
		if ($ts_plano_cob_faixa)
        {
            $cont = 1;//list_product
            foreach ($ts_plano_cob_faixa as $list_product_id => $list_product2)
            {
                $item_name = 'prod_' . $cont++;
                //OBJ PADRÃO DAS CLASSES
				$item = new StdClass;

				//CRIA AS AÇÕES DOS BTN
                $action_del = new TAction(array($this, 'onDeleteCobFaixa'));
                $action_del->setParameter('list_product_id', $list_product_id);
				$action_del->setParameter('cont',$cont);

				$action_edi = new TAction(array($this, 'onEditItemFaixa'));
                $action_edi->setParameter('list_product_id', $list_product_id);
				$action_edi->setParameter('cont',$cont);
				
				//CRIA OS BTN E ADD AS AÇÕES
                $button_del = new TButton('delete_product'.$cont);
                $button_del->class = 'btn btn-default btn-sm';
                $button_del->setAction( $action_del, '' );
                $button_del->setImage('fa:trash-o red fa-lg');

                $button_edi = new TButton('edit_product'.$cont);
                $button_edi->class = 'btn btn-default btn-sm';
                $button_edi->setAction( $action_edi, '' );
                $button_edi->setImage('fa:edit blue fa-lg');
				
                //ASSOCIA O OBJ PADRÃO AOS BNT  
				$item->edit    = $button_edi;
                $item->delete  = $button_del;
                //$item->select  = $button_sel;

                $this->formFields[ $item_name.'_edit' ]   = $item->edit;
                $this->formFields[ $item_name.'_delete' ] = $item->delete;
                //$this->formFields[ $item_name.'_select' ] = $item->select;
			
				
				//TRÁS AS FAIXAS DE ACORDO COM AS COBERTURAS 	
				$codcob_faixa =  TSession::getValue('TS_codCob_faixa');
        		
				if($list_product2['COD_COBERTURA'] == $codcob_faixa )
				{	
					//ADD OS ITEMS NA GRID datagrid_af 
					$item->FAIXA         = $list_product2['FAIXA'];
					$item->ID_INI        = $list_product2['ID_INI'];
					$item->ID_FIM        = $list_product2['ID_FIM'];
					$item->PU_PURO       = $list_product2['PU_PURO'];
					$item->COD_COBERTURA = $list_product2['COD_COBERTURA'];
					$item->CODIGO        = $list_product2['COD_FAIXA'];
					//$item->PU_PURO       = $codcob_faixa;
					
					$this->datagridPf->addItem( $item );
					
					
				}
				
				//pega o id da faixa + 1 para nova faixa
				if(!empty($data->COD_FAIXA))
				{	
					$data->COD_FAIXA =  (1 + $list_product2['COD_FAIXA']);
                }
				
				$this->form->setData($data);
				
				//TS_codCob_faixa
				
            }//foreach ($ts_plano_cob_faixa)
			
            $this->form->setFields( $this->formFields );
		    //$this->form->setData($data);
			
        }//if ($ts_plano_cob_faixa)
			
		$this->loaded = TRUE;
		
	}//onReload
	
	
	/*
	  Edita um 'cod_ret_banco' direto na Grid
	*/
	function onEditInline($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
			
			/*
			$ts_plano_cob_faixas = TSession::getValue('TS_plano_cob_faixa');
		
			//OBTEM A COBERTURA
			$ts_plano_cob_faixa = $ts_plano_cob_faixas[ (int) $param['list_product_id'] ];
			
			$data->COD_FAIXA   = $ts_plano_cob_faixa['COD_FAIXA'];
			*/
			
			TTransaction::open('db2');
			
			/*
			$ts_plano_cob_faixas = TSession::getValue('TS_plano_cob_faixa');
			
			//
			$ts_plano_cob_faixa = $ts_plano_cob_faixas[ (int) $param['list_product_id'] ];
            
			//$ts_plano_cob_faixa['FAIXA'] = $value;
			//$value = $ts_plano_cob_faixa['FAIXA']; 
			
			//$ts_plano_cob_faixa['FAIXA'] = $value;
			//$ts_plano_cob_faixa->{$field} = $value;
			//$ts_plano_cob_faixa['FAIXA'] = $field = $value;
			$ts_plano_cob_faixa['FAIXA'] = $value;
			
			TSession::setValue('TS_plano_cob_faixa', $ts_plano_cob_faixa);
			*/
			
			// instantiates object banco
            $cob_faixa = new plano($key);
            $cob_faixa->{$field} = $value;
			//$cob_faixa->store();
			
			TTransaction::close();
			
			//TSession::setValue('TS_plano_cob_faixa', $ts_plano_cob_faixa);
			
            // open a transaction with database 'samples'
			
            
            // reload the listing
            $this->onReload($param);
            // shows the success message
            new TMessage('info', "Salvo com sucesso");
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
		
    }//onEditInline
	
	/*
	Deleta um item PEC da sessão, 
	mas nao deleta da base
	*/
	public function onDeleteCobFaixa($param)
	{
		$data = $this->form->getData();

        $this->form->setData( $data );

		//LE ITENS DA SESSÃO
		//$ts_plano_cob = TSession::getValue('TS_plano_cob');
		$ts_plano_cob_faixa = TSession::getValue('TS_plano_cob_faixa');
		
        //'unset' APAGA OS ITEMS DA SESSÃO DE ACORDOCOM SEU ID
        unset($ts_plano_cob_faixa[ (int) $param['list_product_id'] ] );
        
		//GRAVA NA SESSÃO SEM O OBJ DO 'unset'
        TSession::setValue('TS_plano_cob_faixa', $ts_plano_cob_faixa);
		
		//DESABILITA O CAMPO CODIGO
		TEntry::disableField('formPlanos', 'CODIGO');
		
        // RECARREGAR OS ITENS DA VENDA
        $this->onReload( $param );
		
	}//onDeleteCobFaixa
	
	/*
	Deleta uma  cobertura da sessão 
	se nao tiver faixas relacionadas, 
	mas nao deleta da base
	*/
	public function onDeleteCob($param)
	{
		try
		{
			$data = $this->form->getData();

			//LE ITENS DA SESSÃO
			$ts_plano_cob       = TSession::getValue('TS_plano_cob');
			$ts_plano_cob_faixa = TSession::getValue('TS_plano_cob_faixa');
			
			//Pega o id do plano que vai ser excluído
			$cod_plano =  ($param['list_product_id']);
			
			/** percorre os 'plano_cob_faixa's 
			e verifica se não há faixas correspondentes ao plano **/
			foreach($ts_plano_cob_faixa as $ts_plano_cob_faixas )
			{
				if( $ts_plano_cob_faixas['COD_COBERTURA'] ==  $cod_plano)
				{
					$this->form->setData( $data );
					
					throw new Exception('Para excluir a cobertura, exclua primeiro as faixas relacionadas  ');
					
					//DESABILITA O CAMPO CODIGO
					TEntry::disableField('formPlanos', 'CODIGO');
				}	
			}
			
			//new TMessage('info', 'id ' . $cod_plano );
			
			//'unset' APAGA O ITEMS 'PLANO' DA SESSÃO DE ACORDOCOM SEU ID
			unset($ts_plano_cob[ (int) $param['list_product_id'] ] );
			
			//GRAVA NA SESSÃO SEM O OBJ DO 'unset'
			TSession::setValue('TS_plano_cob', $ts_plano_cob);
			
			//limpa o form
			$data->COD_COBERTURA  = '';
			$data->COBERTURA_NOME = '';
			
			$this->form->setData( $data );
			
			//Desabilita o campo CODIGO
			TEntry::disableField('formPlanos', 'CODIGO');
			
			// RECARREGAR OS ITENS DA VENDA
			$this->onReload( $param );
		
		}
		catch(Exception $e)
		{
			new TMessage('erro ', $e->getMessage() );
		}
	}//onDeleteCob
	
	/*
	  Prepara o form para um nova faixa, 
	  se estiver uma cobertura selecionada 
	*/
	public function onAddCobFaixa()
	{
		try
		{
			$data = $this->form->getData();
			
			if(empty($data->COD_COBERTURA)) 
			{
				//Desabilita os campos
				TButton::disableField('formPlanos', 'btn_gravar_faixa');
			    TEntry::disableField('formPlanos', 'CODIGO');
				
				throw new Exception('Selecione o plano');
			}
			
			if(empty($data->COD_FAIXA))
			{
				$data->COD_FAIXA = 1;
			}
			else
			{
				$data->COD_FAIXA = 1 + $data->COD_FAIXA;
			}	
			
			TScript::create('setTimeout(function() { $("input[name=\'FAIXA\']").focus() }, 200);');
			
			//DESABILITA O CAMPO CODIGO
			TEntry::disableField('formPlanos', 'CODIGO');
				
			// $this->form->setData($data);

		}
		catch(Exception $e)			
		{
			new TMessage('error ', $e->getMessage() );
		}
		
		$this->form->setData($data);
		
	}//onAddCobFaixa
	
	/*
	  Grava uma nova faixa de cobertura na sessão e 
	  trás todas as faixa da mesma cobertura.
	*/
	public function onGravarFaixa($param)
    {
		try
		{
			$data = $this->form->getData();
			
			 
			if(empty($data->COD_COBERTURA)) 
			{
				$this->form->setData($data);
				
				TButton::disableField('formPlanos', 'btn_gravar_faixa');
				throw new Exception('Selecione o plano');
			}
			
			if(empty($data->FAIXA)) 
			{
				$this->form->setData($data);
				throw new Exception('Informe a faixa');
			}
			
			if(empty($data->ID_INI)) 
			{
				$this->form->setData($data);
				throw new Exception('Informe a idade inicial');
			}

			if(empty($data->ID_FIM)) 
			{
				$this->form->setData($data);
				throw new Exception('Informe a idade final');
			}

			if(empty($data->PU_PURO)) 
			{
				$this->form->setData($data);
				throw new Exception('Informe a PU puro');
			}
			
			if(empty($data->COD_FAIXA)) 
			{
				$data->COD_FAIXA = 1;
			}	
			
				
			//pega as regra atuais
			$ts_plano_cob_faixa = TSession::getValue('TS_plano_cob_faixa');
			
			//Da uma ID para nova faixa
			$key = (int) $data->COD_FAIXA;//campo oculto
			
			//add novo regra
			$ts_plano_cob_faixa[$key] = array(
										'FAIXA'  => $param['FAIXA'],
										'COD_FAIXA'     => $data->COD_FAIXA,
										'PLANO'         => $param['CODIGO'],
										'ID_INI'        => $param['ID_INI'],
										'ID_FIM'        => $param['ID_FIM'],
										'PU_PURO'       => $param['PU_PURO'],
										'COD_COBERTURA' => $param['COD_COBERTURA']
									   ); 
								   

			//Grava plano_cob_faixa sessão
			TSession::setValue('TS_plano_cob_faixa', $ts_plano_cob_faixa);
			
			//atualiza o parâmetro da cobertura e alimenta a grid
			TSession::setValue('TS_codCob_faixa', $param['COD_COBERTURA']);
			
			
			
			//$this->form->clear();
			$data->FAIXA   = '';
			$data->ID_INI  = '';
			$data->ID_FIM  = '';
			$data->PU_PURO = '';
			
			//Desabilita os campos
			TEntry::disableField('formPlanos', 'CODIGO');
			//TButton::disableField('formAssociado', 'btn_gravar_pec');
			
			
			$this->form->setData($data);
			
			//recarrega a página 
			$this->onReload( $param ); 
		}
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
		}
			
	}//onGravarFaixa
	
	/*
	Grava um novo 'plano_cobertura'
	*/
	public function onGravarCob($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			if(empty($data->COD_COBERTURA)) 
			{
				$this->form->setData($data);
				
				//Desabilita os campos
			    TEntry::disableField('formPlanos', 'CODIGO');
				TButton::disableField('formPlanos', 'btn_gravar_faixa');
				
				throw new Exception('Escolha a cobertura');
			}

			if(empty($data->COD_FAIXA))
			{
				$data->COD_FAIXA = 1;
			}	
			
			//pega as regra atuais
			$ts_plano_cob = TSession::getValue('TS_plano_cob');
			
			//continua o carrinho de compras
			$cobertura = new cobertura($data->COD_COBERTURA);//CODIGO
			
			//Da uma ID para nova regra
			$key = (int) $data->COD_COBERTURA;//campo oculto
			
			//add novo regra
			$ts_plano_cob[ $key ] = array('CODIGO'         => $param['CODIGO'],
			                              'COBERTURA_NOME' => $cobertura->COBERTURA,
										  'COD_PLANO'      => $param['CODIGO'],
										  'COD_COBERTURA'  => $param['COD_COBERTURA']
									     );  
					
			TTransaction::close();		
									   
			//grava a nova regra na sessão
			TSession::setValue('TS_plano_cob', $ts_plano_cob);
			
			$ts_plano_cob = TSession::getValue('TS_plano_cob');
			
			//$data->COD_COBERTURA  = '';
			//$data->COBERTURA_NOME = '';
			
			
			//Desabilita campos
			TEntry::disableField('formPlanos', 'CODIGO');
			//TButton::disableField('formAssociado', 'btn_gravar_pec');
			
			
			$this->form->setData($data);
			
			//recarrega a página 
			$this->onReload( $param ); // reload is items sale items
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onGravarCob
	
	/*
	  Salva um 'plano'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formPlanos', 'btn_salvar');
			}
			
			$this->form->validate();
			
			$plano = $this->form->getData('plano');
			
			//PEGA AS REGRAS PARA OS AGREGAOS 'plano_cob'
			$ts_plano_cob = TSession::getValue('TS_plano_cob');
			if ($ts_plano_cob)
			{	
				foreach($ts_plano_cob as $lista_cob)
				{
					//$cm_af = new cm_af;
					$plano_cob = new plano_cob;
					
					$plano_cob->COD_PLANO     = $lista_cob['COD_PLANO'];
					$plano_cob->COD_COBERTURA = $lista_cob['COD_COBERTURA'];
					
					$plano->addPlano_Cob($plano_cob);
					
				}//foreach
				
			}//$ts_plano_cob
			
			//PEGA AS REGRAS PARA OS AGREGAOS 'plano_cob_faixa'
			$ts_plano_cob_faixa = TSession::getValue('TS_plano_cob_faixa');
			if ($ts_plano_cob_faixa)
			{	
				foreach($ts_plano_cob_faixa as $lista_cob_faixa)
				{
					//$cm_af = new cm_af;
					$plano_cob_faixa = new plano_cob_faixa;
					
					$plano_cob_faixa->PLANO         = $lista_cob_faixa['PLANO'];
					$plano_cob_faixa->FAIXA         = $lista_cob_faixa['FAIXA'];
					$plano_cob_faixa->ID_INI        = $lista_cob_faixa['ID_INI'];
					$plano_cob_faixa->ID_FIM        = $lista_cob_faixa['ID_FIM'];
					$plano_cob_faixa->PU_PURO       = $lista_cob_faixa['PU_PURO'];
					$plano_cob_faixa->COBERTURA     = $lista_cob_faixa['COD_COBERTURA'];
					$plano_cob_faixa->PU_PURO       = $lista_cob_faixa['PU_PURO'];
					
					$plano->addPlano_Cob_Faixa($plano_cob_faixa);
					
				}//foreach
				
			}//$ts_plano_cob
			
			$plano->store();
			
			new TMessage('info', 'Registro salvo');
			
			$this->form->setData($plano);
			
			//Desabilita os campos
			TEntry::disableField('formPlanos', 'CODIGO');
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	/*
	  Edita um item de cobertura e grava na sessão
	*/
	public function onEditItemCob ($param)
	{
		
		$data = $this->form->getData();
		
		//pega os items da sessão
		$ts_plano_cobs = TSession::getValue('TS_plano_cob');
		
        //OBTEM A COBERTURA 
		$ts_plano_cob  = $ts_plano_cobs[ (int) $param['list_product_id'] ];
		
		$data->COD_COBERTURA  = $ts_plano_cob['COD_COBERTURA'];
        $data->COBERTURA_NOME = $ts_plano_cob['COBERTURA_NOME'];
		
		//pega o codigo da cobertura
		$codFaixa = $param['COD_COBERTURA'];
		
		//grava na sessão, e trás as faixas q pertecem a cobertura
		TSession::SetValue('TS_codCob_faixa', $data->COD_COBERTURA);
		
		//LIMPA OS CAMPOS DA FAIXA
		$data->FAIXA         = '';
		$data->ID_INI        = '';
		$data->ID_FIM        = '';
		$data->PU_PURO       = '';
		
		$this->form->setData($data);
		
		//DESABILITA O CAMPO CODIGO
		TEntry::disableField('formPlanos', 'CODIGO');
		
		$this->onReload($param);
		
		//DESABILITA OS BTN
		//TButton::disableField('formAssociado', 'btn_novo_af');
		
	}//onEditItemCob
	
	/*
	  Edita um item de faixa e grava na sessão
	*/
	public function onEditItemFaixa($param)
	{
		$data = $this->form->getData();
		
		//pega os items da sessão
		$ts_plano_cob_faixas = TSession::getValue('TS_plano_cob_faixa');
		
        //OBTEM A COBERTURA
		$ts_plano_cob_faixa = $ts_plano_cob_faixas[ (int) $param['list_product_id'] ];
		
		$data->COD_FAIXA   = $ts_plano_cob_faixa['COD_FAIXA'];
		//$data->COD_FAIXA  = $ts_plano_cob_faixa['FAIXA'];
		$data->FAIXA       = $ts_plano_cob_faixa['FAIXA'];
        $data->PLANO       = $ts_plano_cob_faixa['PLANO'];
        $data->ID_INI      = $ts_plano_cob_faixa['ID_INI'];
        $data->ID_FIM      = $ts_plano_cob_faixa['ID_FIM'];
        $data->PU_PURO     = $ts_plano_cob_faixa['PU_PURO'];
        
		$this->form->setData($data);
		
		//DESABILITA O CAMPO CODIGO
		TEntry::disableField('formPlanos', 'CODIGO');
		
		//$this->onReload($param);
		
	}//onEditItemFaixa
	
	
	/*
	 Instância um 'plano' usando o @param['key'] como id do Objeto
     e trás os agregados 'planos_cob' e 'planos_cob_faixa' ;	 
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//$data = $this->form->getData();
			
			$this->form->clear();
			if(isset($param['key']))
			{
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formPlanos', 'btn_salvar');
				}	
				
				$key = $param['key'];
				$plano = new plano($key);
				
				//** PLANOS_COB **//
				$ts_plano_cob = array();
				foreach($plano->getPlano_Cob() as $plano_cob )//addcm_pec
				{
					$ts_plano_cob[$plano_cob->COD_COBERTURA]                   = $plano_cob->toArray();
					
					$ts_plano_cob[$plano_cob->COD_COBERTURA]['CODIGO']         = $plano_cob->CODIGO;
					
					$ts_plano_cob[$plano_cob->COD_COBERTURA]['COD_PLANO']      = $plano_cob->COD_PLANO;
					
					$ts_plano_cob[$plano_cob->COD_COBERTURA]['COBERTURA_NOME'] = $plano_cob->plano_cobertura->COBERTURA;
					
					$ts_plano_cob[$plano_cob->COD_COBERTURA]['COD_COBERTURA']  = $plano_cob->COD_COBERTURA;
					
					//TSession::setValue('TS_codCob_faixa', $plano_cob->COD_COBERTURA);
					
				}//getPlano_Cob
				
				//GRAVA OS 'plano_cob' NA SESSÃO
				TSession::setValue('TS_plano_cob', $ts_plano_cob);
				
				
				//** PLANOS_COB_FAIXA **//
				$ts_plano_cob_faixa = array();
				foreach($plano->getPlano_Cob_Faixa() as $plano_cob_faixa )//plano_cob
				{
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO] = $plano_cob_faixa->toArray();
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['PLANO'] = $plano_cob_faixa->PLANO;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['FAIXA'] = $plano_cob_faixa->FAIXA;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['ID_INI'] = $plano_cob_faixa->ID_INI;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['ID_FIM'] = $plano_cob_faixa->ID_FIM;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['PU_PURO'] = $plano_cob_faixa->PU_PURO;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['COD_COBERTURA'] = $plano_cob_faixa->COBERTURA;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['COD_FAIXA'] = $plano_cob_faixa->CODIGO;
					
					//grava o último código de cobertura para trazer as faixas
					TSession::setValue('TS_codCob_faixa', $plano_cob_faixa->COBERTURA);
					
					//$ts_plano_cob_faixa[$plano_cob->CODIGO]['PU_PURO'] = $plano_cob->PU_PURO;
					
				}//getPlano_Cob
				//GRAVA OS 'plano_cob' NA SESSÃO
				TSession::setValue('TS_plano_cob_faixa', $ts_plano_cob_faixa);
				
				//GRAVA OS DADOS DO FORM NA SESSÃO
				TSession::setValue('TS_data', $plano);
				
				$this->form->setData($plano);
				
				//Desabilita os campos
				TButton::disableField('formPlanos', 'btn_gravar_faixa');
				TEntry::disableField('formPlanos', 'CODIGO');
				
				$this->onReload( $param );
				TTransaction::close();
				
				
				//desabilita a ediçaõ do codigo
				//TEntry::disableField('formCatRisco', 'CODIGO');
				
			}//(isset($param['key']
			
			
		}//try
		catch(Exception $e)
		{
			new TMessage('info', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onEdit
	
	 /**
     * Save the object reference in session
     */
    public function onSelect($param)
    {
        // get the selected objects from session 
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        
        TTransaction::open('db2');
		
        $object = new cobertura($param['COD_COBERTURA']); // load the object
		
        if (isset($selected_objects[$object->COD_COBERTURA]))
        {
            unset($selected_objects[$object->COD_COBERTURA]);
        }
        else
        {
            $selected_objects[$object->COD_COBERTURA] = $object->toArray();
			//add the object inside the array
        }
        TSession::setValue(__CLASS__.'_selected_objects', $selected_objects);
		//put the array back to the session
        TTransaction::close();
        
        // reload datagrids
        $this->onReload( func_get_arg(0) );
		
    }//onSelect
    
    /**
     * Highlight the selected rows
     */
    public function onFormatRow($value, $object, $row)
    {
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        
        if ($selected_objects)
        {
            if (in_array( (int) $value, array_keys( $selected_objects ) ) )
            {
                $row->style = "background: #FFD965";
            }
        }
        
        return $value;
		
    }//onFormatRow
	
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
		

}//TPage


?>