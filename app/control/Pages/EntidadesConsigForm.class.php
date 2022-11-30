<?php
class EntidadesConsigForm Extends TPage
{
	private $form;
	private $datagrid;
	private $datagrid_org;
	private $notebook;
	
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
		//CRIA O FORM
		$this->form  = new BootstrapFormBuilder('formEntidadesConsig');
		//captura a aba ativa  
		$this->form->setTabAction(new TAction(array($this, 'onTabClickForm')));
		
		$this->form->setFieldSizes('100%');
		
		//CRIA OS ATRIBUTOS
		$lb_dados_cad  = new TLabel('Dados Cadastrais');
		$razao_social  = new TEntry('RAZAO_SOCIAL');
		$codigo        = new TEntry('COD_INT');
		$cnpj          = new TEntry('CNPJ');
		$endereco      = new TEntry('ENDERECO');
		$bairro        = new TEntry('BAIRRO');
		$cidade        = new TEntry('CIDADE');
		$cep           = new TEntry('CEP');
		$estado        = new TEntry('ESTADO');
		$telefone      = new TEntry('TELEFONE');
		$fax           = new TEntry('FAX');
		$perc_desc     = new TEntry('PERC_DESC');
		$cod_ext       = new TEntry('COD_EXT');
		$cod_ipe       = new TEntry('COD_IPE');
		$insc_estadu   = new TEntry('INSC_ESTADUAL');
		$obs           = new TText('OBS');
		$responsavel   = new TEntry('RESPONSAVEL');
		$produto       = new TEntry('PRODUTO');
		$reaj          = new TEntry('REAJ');
		$carencia      = new TEntry('CARENCIA');
		$inst          = new TEntry('INST');
		$ativo         = new TCombo('ATIVO');
		$id_item       = new THidden('ID_ITEM');//OCULTO
		$cod_item      = new TDBCombo('COD_ITEM', 'db2', 'tipo_cto', 'ID', 'DESCRICAO');
		$cod_desc_item = new TEntry('COD_DESC_ITEM');
		$status        = new TCombo('STATUS');
		$tipo_cobranca = new TCombo('TIPO_COBRANCA');
		$cod_federal   = new TEntry('COD_FEDERAL');
		
		$item_orgao    = new TEntry('ITEM_ORGAO');
		$item_codigo   = new TEntry('ITEM_CODIGO');
		$id_item_org   = new THidden('ID_ITEM_ORG');
		
		$teste1        = new TEntry('TESTE1');
		$teste2        = new TEntry('TESTE2');
		
		
		//CRIA OS BOTÕES
		$btn_cancelar = TButton::create('btn_cancelar' ,array('EntidadesConsigListe', 'onReload'), ('Cancelar'), 'far: fa-window-close red' );//onClear
		
		$btn_salvar = TButton::create('btn_salvar' ,array($this, 'onSave'), ('Salvar'), 'far:save' );
		$btn_salvar->class = 'btn btn-sm  btn-primary';
		
		$btn_gravar   = TButton::create('btn_gravar' ,[$this, 'onGravarItem'], 'Gravar', 'fa: fa-check with blue');
		
		$btn_gravar_org    = TButton::create('btn_gravar_org' ,[$this, 'onGravarItemOrg'], 'Gravar', 'fa: fa-check with blue');
		
		//FORMATAÇÕES
		$responsavel->setSize('100%');
		$produto->setSize('100%');
		$ativo->setSize(100);
		$cod_item->setSize(200);
		$obs->setSize(700, 200);
		$status->setSize(100);
		$tipo_cobranca->setSize(100);
		
		$razao_social->addValidation('"RAZÃO SOCIAL "', new TRequiredValidator);
		//$cnpj->addValidation('" CNPJ "', new TRequiredValidator);
		
		//addItem
		$items = ['T' => 'Sim', 'F' => 'Não' ];
		$ativo->addItems($items);
		$status->addItems($items);
		$tipo_cobranca->addItems($items);
		
		
		//** PAGE DADOS CADASTRAIS  **//
		$this->form->appendPage('Dados Cadastrais');
		
        //topo da page
		$label1 = new TLabel('Dados do cadastrais');//, '#7D78B6', 8, 'bi'
        $label1->style='text-align:left; width:100%; color:#FFF';
		
		$row = $this->form->addFields( [$label1]  );
		$row->layout = ['col-sm-12'];
		$row->style = 'background:#6287B9; margin:0 0 5px 1px; ';
		
		$row = $this->form->addFields([new TLabel('Código'), $codigo ],
								      [new TLabel('Nome / Razão Social'), $razao_social ],
									  [new TLabel('CNPJ'), $cnpj ],
									  [new TLabel('Telefone'), $telefone ]);
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-2', 'col-sm-2' ];					  

		$row = $this->form->addFields([new TLabel('Fax'), $fax ],
									  [new TLabel('Endereço'), $endereco ],
									  [new TLabel('Bairro'), $bairro ]);
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-4'];

		$row = $this->form->addFields([new TLabel('Cidade'), $cidade ],		
								      [new TLabel('Cep'), $cep ],
								      [new TLabel('Estado'), $estado ]);
		$row->layout = ['col-sm-8', 'col-sm-3', 'col-sm-1' ];

		$row = $this->form->addFields([new TLabel('Código Orgão'), $cod_ext ],
									  [new TLabel('Inscrição Estadual'), $insc_estadu ],
									  [new TLabel('Código Externo'), $cod_ipe ]);
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];
		
		$row = $this->form->addFields([new TLabel('')]);
		$row->layout = ['col-sm-12'];
		$row->style = 'border-bottom:#C1CDCD 1px solid; margin:5px 0 5px 1px';
		
		$this->notebook = new BootstrapNotebookWrapper( new TNotebook('100%',230) );
		$this->notebook->style = 'width:100%';
		
		//captura a aba ativa  
		$this->notebook->setTabAction(new TAction(array($this, 'onTabClickSub')));
        
        // creates the containers for each notebook page
        $pg_outras_info = new TTable;
		$pg_outras_info->style = 'width:100%';
		
        $pg_sub_cod = new TTable;
		$pg_sub_cod->style = 'width:100%';
		
        $pg_obs = new TTable;
		$pg_obs->style = 'width:100%';
		
		$pg_parametro = new TTable;
		$pg_parametro->style = 'width:100%';
		
		
		//SUB PAGE 'pg_outras_info'
		$label1 = new TLabel('Outras informações');//, '#7D78B6', 8, 'bi'
        $label1->style='text-align:left; width:100%; color:#000; border-bottom:1px solid #d5d5d5';
		
		$pg_outras_info->addRowSet(new TLabel(''), array($label1) );
		
		$pg_outras_info->addRowSet(new TLabel(''), array(new TLabel('descrição no boleto'), $responsavel, new TLabel('Porduto'), $produto, new TLabel('Forma Reajuste'), $reaj));
		
		$pg_outras_info->addRowSet(new TLabel(''), array(new TLabel('Carência'), $carencia, new TLabel('Instituidora'), $inst, new TLabel('Ativo  '), $ativo ));
		
		//** SUB PAGE 'pg_sub_cod' **//
		$this->datagrid = new TQuickGrid;
		$this->datagrid->setHeight(150);
		$this->datagrid->style = 'width:100%';
        $this->datagrid->makeScrollable();
		$this->datagrid->addQuickColumn('', 'edit', 'center');
		$this->datagrid->addQuickColumn('', 'delete', 'center');
		$this->datagrid->addQuickColumn('Código', 'TIPO_CTO', 'center');
		$this->datagrid->addQuickColumn('Tipo de contrato', 'NOME_CTO', 'center');
		$this->datagrid->addQuickColumn('Código de desconto', 'COD_DESC', 'center', '30%');
		//$this->datagrid->addQuickColumn('ID', 'CODIGO', 'center');
		
		$this->datagrid->CreateModel();
		
		
		$pg_sub_cod->addRowSet(new TLabel('') ,array( $id_item) );//OCULTO
		$pg_sub_cod->addRowSet(new TLabel('') ,array( new TLabel('Tipo de contrato'), $cod_item, new TLabel('Cod de desconto'), $cod_desc_item ));
		
		$row2 = $pg_sub_cod->addRowSet(new TLabel('') ,array( $btn_gravar ) );
		$row2->style = 'background:#D5D5D5; width:80%';
		
		
		/*$row = $this->form->addFields( [$btn_gravar_org]  );
		$row->layout = ['col-sm-1','col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';*/
		
		
		$pg_sub_cod->addRowSet(new TLabel('') ,array( $this->datagrid ) );
		
		//SUB PAGE 'pg_obs'
		$pg_obs->addRowSet(new TLabel('') ,array($obs ) );
		
		//SUB PAGE 'pg_parametro'
		$pg_parametro->addRowSet(new TLabel('') ,array(new TLabel('Mostrar no rel. de comissão'), $status, new TLabel('Obrigatório cod. orgão'), $tipo_cobranca, new TLabel('Contrato averbação'), $cod_federal ) );
		
        
        // adds as pages dentro do notebook 
        $this->notebook->appendPage('Outras informações', $pg_outras_info);
        $this->notebook->appendPage('Sub código', $pg_sub_cod);
        $this->notebook->appendPage('Observações', $pg_obs);
        $this->notebook->appendPage('Parâmetros', $pg_parametro);
		
		$row = $this->form->addFields([$this->notebook ]);
		$row->layout = ['col-sm-12'];
		
		// ** PAGE ORGÃOS VINCULADOS **//
		$this->form->appendPage('Orgãos Vinculados');
		
		$row = $this->form->addFields([new TLabel('Código'), $item_codigo ],
							   [new TLabel('Orgão'), $item_orgao ]);
		$row->layout = ['col-sm-4', 'col-sm-8' ] ;
		
		$row = $this->form->addFields([$id_item_org]);
		$row->layout = ['col-sm-12'] ;

		$row = $this->form->addFields( [$btn_gravar_org]  );
		$row->layout = ['col-sm-1','col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';	
							   
		/*
		$row = $this->form->addFields( [$btn_gravar_pec], [$btn_novo_pec]  );
		$row->layout = ['col-sm-1','col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';
		*/					   
		
		//CRIA A DATAGRID_ORG
		$this->datagrid_org = new TQuickGrid;
		$this->datagrid_org->setHeight(400);
		$this->datagrid_org->style = 'width:100%';
        $this->datagrid_org->makeScrollable();
		$this->datagrid_org->addQuickColumn('', 'edit', 'center', '10%');
		$this->datagrid_org->addQuickColumn('', 'delete', 'center', '10%');
		$this->datagrid_org->addQuickColumn('Código', 'CODIGO', 'center', '20%');
		$this->datagrid_org->addQuickColumn('Orgão', 'ORGAO', 'center', '60%');
		//$this->datagrid_org->addQuickColumn('ID', 'ID', 'center', '5%');
		//$this->datagrid_org->addQuickColumn('Ent Col', 'ENT_COL', 'center');
		
		$this->datagrid_org->CreateModel();
		
		$row = $this->form->addFields([ $this->datagrid_org] );
		$row->layout = ['col-sm-12'];
				
		
		//EMPACOTAMENTO
		$painel = new TPanelGroup('Entidades Coletivas / Consignatária (T004)');
		$painel->add($this->form);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_salvar, $btn_cancelar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_cancelar));
		}
		
		
		$painel->getBody()->style = 'overflow-x:auto';
	
		
		$this->formFields = array($razao_social, $codigo, $cnpj, $endereco, $bairro, $cidade, $cep, $estado, $telefone, $fax, $cod_federal, $perc_desc, $obs, $cod_ext, $cod_ipe, $insc_estadu, $responsavel, $produto, $reaj, $carencia, $inst, $ativo, $id_item, $cod_item, $cod_desc_item, $tipo_cobranca, $status, $id_item_org, $item_codigo, $item_orgao, $btn_cancelar, $btn_salvar, $btn_gravar, $btn_gravar_org, $teste1, $teste2);
		$this->form->setFields($this->formFields);
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
		$vbox->add(new TXMLBreadCrumb('menu.xml','EntidadesConsigListe'));
		$vbox->add($painel);
		
		parent::add($vbox);
		
	}//__construct
	
	/*
	Recarrega a página com seus parâmetros atuais
	*/
	public function onReload()
	{
		//paga a variavel de sessão 
	    //$ts_plano_cob       = TSession::getValue('TS_plano_cob');
	    $ts_ent_cod_desc = TSession::getValue('TS_ent_cod_desc');
	    $ts_org          = TSession::getValue('TS_org');
        $data 			 = TSession::getValue('TS_data');
		
		// LIMPA AS GRIDS 
		$this->datagrid->clear();
		$this->datagrid_org->clear();
		
		//CARREGA OS DADOS DOS ts_ent_cod_desc' para a GRID 'datagrid'
		if ($ts_ent_cod_desc)
        {
            $cont = 1;
            foreach ($ts_ent_cod_desc as $list_product_id => $list_product)
            {
                $item_name = 'prod_' . $cont++;
                //OBJ PADRÃO DAS CLASSES
				$item = new StdClass;

				//CRIA AS AÇÕES DOS BTN
                $action_del = new TAction(array($this, 'onDeleteItem'));
                $action_del->setParameter('list_product_id', $list_product_id);
				$action_del->setParameter('cont',$cont);

				$action_edi = new TAction(array($this, 'onEditItem'));
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
				
				//PEGA OS ITEMS DA SESSAÕ 
				/*
				$item->NOME_CTO      = new TDBCombo('NOME_CTO'.$cont, 'db2', 'tipo_cto', 'CODIGO', 'DESCRICAO');//*CODIGO campo chave ñ ID*
				$item->NOME_CTO->setSize('100%') ;
				$item->NOME_CTO->setValue($list_product['TIPO_CTO']);
				//$item->NOME_CTO->setEditable(FALSE);
				$this->form->addField($item->NOME_CTO); // important!
				*/
				
				$item->NOME_CTO = $list_product['NOME_CTO'];
				
				$item->TIPO_CTO = $list_product['TIPO_CTO'];
				
				$item->COD_DESC = $list_product['COD_DESC'];
				
				$item->CODIGO   = $list_product['CODIGO'];
					
				//ADD OS ITEMS NA GRID
				$this->datagrid->addItem( $item );
				
				//pega o código + 1 para a nova 'ent_cod_desc'
				$data->ID_ITEM = ( 1 + $list_product['CODIGO'] );
				
				//MANTÉM OS DADOS NO FORM
				$this->form->setData($data);
				
            }//foreach ($ts_ent_cod_desc)
			
            $this->form->setFields( $this->formFields );
		    //$this->form->setData($data);
			
        }//if ($ts_ent_cod_desc)
			
		////
		//CARREGA OS DADOS DOS ts_ent_cod_desc' para a GRID 'datagrid'
		if ($ts_org)
        {
            $cont = 1;
            foreach ($ts_org as $list_product_id => $list_product)
            {
                $item_name = 'prod_' . $cont++;
                //OBJ PADRÃO DAS CLASSES
				$item = new StdClass;

				//CRIA AS AÇÕES DOS BTN
                $action_del = new TAction(array($this, 'onDeleteItemOrg'));
                $action_del->setParameter('list_product_id', $list_product_id);
				$action_del->setParameter('cont',$cont);

				$action_edi = new TAction(array($this, 'onEditItemOrg'));
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
				
				//PEGA OS ITEMS DA SESSAÕ 
				$item->CODIGO  = $list_product['CODIGO'];
				
				$item->ID      = $list_product['ID'];
				
				$item->ORGAO   = $list_product['ORGAO'];
				
				$item->ENT_COL = $list_product['ENT_COL'];
				
				//ADD OS ITEMS NA GRID
				$this->datagrid_org->addItem($item);
				
				//pega o id + 1 para a nova 'org'
				$data->ID_ITEM_ORG = ( 1 + $list_product['ID'] );
				
				//MANTÉM OS DADOS NO FORM
				$this->form->setData($data);
				
            }//foreach ($ts_ent_cod_desc)
			
            $this->form->setFields( $this->formFields );
		    //$this->form->setData($data);
			
        }//if ($ts_org)
			
		$this->loaded = TRUE;
		
		//mantém na mesma aba do form
		$this->form->setCurrentPage(TSession::getValue('TS_current_page_form'));
		
		//mantém na mesma aba do sub notebook
		$id_aba = (TSession::getValue('TS_current_page_sub') - 1);
		$this->notebook->setCurrentPage( $id_aba );

	}//onReload
	
	/*
	  Salva uma 'entidade' e as suas
	 'entidades_cod_desc's E 'org's relacionadas
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formEntidadesConsig', 'btn_salvar');
			}
			
			$entidades = $this->form->getData('entidades');
			
			//PEGA AS 'ENTIDADES_COD_DESC' PARA OS AGREGAGOS DA 'ENTIDADE'
			$ts_ent_cod_desc= TSession::getValue('TS_ent_cod_desc');
			if ($ts_ent_cod_desc)
			{	
				foreach($ts_ent_cod_desc as $lista_ent_cod_desc)
				{
					$ent_cod_desc = new entidades_cod_desc;
					
					$ent_cod_desc->ENT_COL  = $lista_ent_cod_desc['ENT_COL'];
					$ent_cod_desc->TIPO_CTO = $lista_ent_cod_desc['COD_CTO'];
					$ent_cod_desc->COD_DESC = $lista_ent_cod_desc['COD_DESC'];
					
					$entidades->addEnt_Cod_Desc($ent_cod_desc);
					
				}//foreach
				
			}//$ts_ent_cod_desc
			
			//PEGA AS 'ORG' PARA OS AGREGADOS DA 'ENTIDADE'
			$ts_org = TSession::getValue('TS_org');
			if ($ts_org)
			{	
				foreach($ts_org as $lista_org)//lista_ent_cod_desc
				{
					$org = new org;
					
					$org->CODIGO  = $lista_org['CODIGO'];
					$org->ORGAO   = $lista_org['ORGAO'];
					$org->ENT_COL = $lista_org['ENT_COL'];
					
					$entidades->addOrg($org);
				}
				
			}//$ts_org
			
			$entidades->store();
			
			//$action = new TAction(array('PesquisaEntidade', 'onReload'));
			new TMessage('info', 'Registro Salvo');
			
			TTransaction::close();
			
			$this->form->setData($entidades);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	/*
	 Grava um Item 'ent_cod_desc' na sessão
	*/
	public function onGravarItem($param)
	{
		
		try
		{
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			if(empty($data->ID_ITEM)) 
			{
				$data->ID_ITEM = 1;
			}		
			
			//pega as 'ent_cod_desc's atuais
			$ts_ent_cod_desc = TSession::getValue('TS_ent_cod_desc');
			
			//instância om obj 'tipo_cto'
			$tipo_cto = new tipo_cto($data->COD_ITEM);
			
			//Da uma ID para nova regra
			$key = (int) $data->ID_ITEM;//campo oculto
			
			//add novo regra
			$ts_ent_cod_desc[ $key ] = array('CODIGO'         => $data->ID_ITEM,
			                                 'ENT_COL'        => $data->COD_INT,
										     'TIPO_CTO'       => $tipo_cto->CODIGO,
										     'COD_DESC'       => $data->COD_DESC_ITEM,
										     'NOME_CTO'       => $tipo_cto->DESCRICAO,
										     'COD_CTO'        => $tipo_cto->ID
									        ); 
			TTransaction::close();		
									   
			//grava o novo item na sessão
			TSession::setValue('TS_ent_cod_desc', $ts_ent_cod_desc);
			
			//limpa os dados no form
			$data->COD_ITEM = '';
			$data->COD_DESC_ITEM = '';
			$this->form->setData($data);
			
			//mantém na mesma aba
			$this->notebook->setCurrentPage(1);
			
			//recarrega a página 
			$this->onReload( $param ); // reload is items sale items
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
		
	}//onGravarItem
	
	/*
	 Edita um Item 'ent_cod_desc' da sessão
	*/
	public function onEditItem($param)
	{
		$data = $this->form->getData();
		
		//pega os items da sessão
		//$ts_plano_cobs = TSession::getValue('TS_plano_cob');
		$ts_ent_cod_descs = TSession::getValue('TS_ent_cod_desc');
		
        //OBTEM A COBERTURA 
		//$ts_plano_cob  = $ts_plano_cobs[ (int) $param['list_product_id'] ];
		$ts_ent_cod_desc  = $ts_ent_cod_descs[ (int) $param['list_product_id'] ];
		
		$data->ID_ITEM        = $ts_ent_cod_desc['CODIGO'];//TIPO_CTO
		$data->COD_ITEM       = $ts_ent_cod_desc['COD_CTO'];//TIPO_CTO
		$data->COD_DESC_ITEM  = $ts_ent_cod_desc['COD_DESC'];
		
		$this->form->setData($data);
		
		$this->notebook->setCurrentPage(1);
		
		//$this->onReload($param);
		
	}//onEditItem
	
	/*
	  Deleta um Item 'ent_cod_desc' da sessão
	*/
	public function onDeleteItem($param)
	{
		$data = $this->form->getData();

        $this->form->setData( $data );

		//LE ITENS DA SESSÃO
		$ts_ent_cod_desc = TSession::getValue('TS_ent_cod_desc');
		
        //'unset' APAGA OS ITEMS DA SESSÃO DE ACORDOCOM SEU ID
        unset($ts_ent_cod_desc[ (int) $param['list_product_id'] ] );
        
		//GRAVA NA SESSÃO SEM O OBJ DO 'unset'
        TSession::setValue('TS_ent_cod_desc', $ts_ent_cod_desc);
		
        // RECARREGAR OS ITENS DA VENDA
        $this->onReload( $param );
		
	}//onDeleteItem
	
	/*
	 Grava um Item 'org' na sessão
	*/
	public function onGravarItemOrg($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			if(empty($data->ID_ITEM_ORG)) 
			{
				$data->ID_ITEM_ORG = 1;
			}		
			
			//pega as regra atuais
			$ts_org = TSession::getValue('TS_org');
			
			//Da uma ID para nova regra
			$key = (int) $data->ID_ITEM_ORG;//campo oculto
			
			//add novo regra
			$ts_org[ $key ] = array('CODIGO'  => $data->ITEM_CODIGO,
			                        'ID'      => $data->ID_ITEM_ORG,
								    'ORGAO'   => $data->ITEM_ORGAO,
								    'ENT_COL' => $data->COD_INT
									); 
										
					
			TTransaction::close();		
									   
			//grava o novo item na sessão
			TSession::setValue('TS_org', $ts_org);
			
			//mantém os dados no form
			$data->ITEM_ORGAO  = '';
			$data->ITEM_CODIGO = '';
			$data->ID_ITEM_ORG = '';
			
			$this->form->setData($data);
			
			//mantém na mesma aba
			$this->form->setCurrentPage(1);
			
			//recarrega a página 
			$this->onReload( $param ); // reload is items sale items
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	
	}//onGravarItemOrg
	
	/*
	 Deleta um Item 'org' da sessão
	*/
	public function onDeleteItemOrg($param)
	{
		$data = $this->form->getData();

        $this->form->setData( $data );

		//LE ITENS DA SESSÃO
		$ts_org = TSession::getValue('TS_org');
		
        //'unset' APAGA OS ITEMS DA SESSÃO DE ACORDOCOM SEU ID
        unset($ts_org[ (int) $param['list_product_id'] ] );
        
		//GRAVA NA SESSÃO SEM O OBJ DO 'unset'
        TSession::setValue('TS_org', $ts_org);
		
        // RECARREGAR OS ITENS DA VENDA
        $this->onReload( $param );
	
	}//onDeleteItemOrg
	
	/*
	 Edita um Item 'org' da sessão
	*/
	public function onEditItemOrg($param)
	{
		$data = $this->form->getData();
		
		//pega os items da sessão
		$ts_orgs = TSession::getValue('TS_org');
		
        //OBTEM A COBERTURA 
		$ts_org  = $ts_orgs[ (int) $param['list_product_id'] ];
		
		$data->ITEM_CODIGO = $ts_org['CODIGO'];
		$data->ITEM_ORGAO  = $ts_org['ORGAO'];
		$data->ID_ITEM_ORG = $ts_org['ID'];
		
		$this->form->setData($data);
		
		$this->form->setCurrentPage(1);
		
		//$this->onReload($param);
	
	}//onEditItemOrg
	
	
	/*
	 Instância uma 'entidades' usando o @param['key'] como id do Objeto  
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
				$key = $param['key'];
				
				TEntry::disableField('formEntidadesConsig', 'COD_INT');
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formEntidadesConsig', 'btn_salvar');
				}
				
				$entidades = new entidades($key);
				
				//** ENTIDADES_COD_DESC **//*
				$ts_ent_cod_desc = array();
				foreach($entidades->getEnt_Cod_Desc() as $ent_desc )
				{
					$ts_ent_cod_desc[$ent_desc->CODIGO]     = $ent_desc->toArray();
					
					$ts_ent_cod_desc[$ent_desc->CODIGO]['CODIGO']   = $ent_desc->CODIGO;
					
					$ts_ent_cod_desc[$ent_desc->CODIGO]['ENT_COL']  = $ent_desc->ENT_COL;
					
					$ts_ent_cod_desc[$ent_desc->CODIGO]['TIPO_CTO'] = $ent_desc->nome_tipo_cto->CODIGO;//LETRA
					
					$ts_ent_cod_desc[$ent_desc->CODIGO]['COD_CTO']  = $ent_desc->TIPO_CTO;
					
					$ts_ent_cod_desc[$ent_desc->CODIGO]['NOME_CTO'] = $ent_desc->nome_tipo_cto->DESCRICAO;
					
					$ts_ent_cod_desc[$ent_desc->CODIGO]['COD_DESC'] = $ent_desc->COD_DESC;
										
					
					/*$ts_ent_cod_desc[$ent_desc->CODIGO]['NOME_CTO'] = $ent_desc->nome_entidade->RAZAO_SOCIAL;*/
					
					
				}//foreach() 
				//GRAVA NA SESSÃO as 'ts_ent_cod_desc'
				TSession::setValue('TS_ent_cod_desc', $ts_ent_cod_desc );
				
				//** ORG **//*
				$ts_org = array();
				foreach($entidades->getOrg() as $org )//ent_desc
				{
					$ts_org[$org->ID]     =$org->toArray();
					
					$ts_org[$org->ID]['ID']      = $org->ID;
					
					$ts_org[$org->ID]['CODIGO']  = $org->CODIGO;
					
					$ts_org[$org->ID]['ORGAO']   = $org->ORGAO;
					
					$ts_org[$org->ID]['ENT_COL'] = $org->ENT_COL;
					
				}//foreach() 
				//GRAVA NA SESSÃO as 'ts_ent_cod_desc'
				TSession::setValue('TS_org', $ts_org );
				
				//GRAVA NA SESSAÕ '$this->form->getData'
				TSession::setValue('TS_data', $entidades );
				
				$this->form->setData($entidades);
				
			}//if( isset($param['key'] )
				
			//DEFINE A ABA ATIVA
			TSession::setValue('TS_current_page_form', 0 );
			
			TSession::setValue('TS_current_page_sub', 1 );
			

			$this->onReload( $param );
			
			TTransaction::close();
			
			// $this->form->setData($data);
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onEdit
	
	
	/**
     * Executado quando traca a aba do sub notebook 
     */
    public static function onTabClickSub($param)
    {
		
        //new TMessage('info', '<b>You have clicked at the tab</b>:  <br><br>' . $param['current_page'] . str_replace(',', '<br>', json_encode($param)));
		
		
		//grava a aba na sessão a cada troca 
		TSession::setValue('TS_current_page_sub', $param['current_page'] );
		
    }//onTabClickSub
	
	/**
     * Executado quando traca a aba do form 
     */
    public static function onTabClickForm($param)
    {
		
        //new TMessage('info', '<b>You have clicked at the tab</b>:  <br><br>' . $param['current_page'] . str_replace(',', '<br>', json_encode($param)));
		
		
		//grava a aba na sessão a cada troca 
		TSession::setValue('TS_current_page_form', $param['current_page'] );
		
    }//onTabClickForm
	
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