<?php
class fornecedoresForm Extends TPage
{
	private $form;
	private $datagrid_af;
	private $datagrid_pec;
	
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
		//cria o form 
		$this->form = new BootstrapFormBuilder('formFornecedores');
		$this->form->setFieldSizes('100%');
		$this->form->class = 'tform';
		
		//captura a aba ativa  
		$this->form->setTabAction( new TAction(array($this, 'onTabClick')));
		
		//cria os atributos
		//PAGE DADOS
		$controle     = new TEntry('CONTROLE');//THidden
		$lb_id        = new TEntry('LB_ID');
		$lb_id2       = new TEntry('LB_ID2');
		$lb_nome      = new TEntry('LB_NOME');
		$lb_nome2     = new TEntry('LB_NOME2');
		$controle_pec = new TEntry('CONTROLE_PEC');//THidden
		$user         = new THidden('USER');//THidden
		$user_id      = new TDBSeekButton('id', 'permission', 'formFornecedores', 'SystemUser', 'name', 'id', 'NOME_USER');
		
		$nome_user   = new TEntry('NOME_USER');//THidden
		//$user_id      = new TEntry('USER_ID');//THidden
		
		$user_pec     = new THidden('USER_PEC');//THidden
		$codigo       = new TEntry('CODIGO');
		$nome         = new TEntry('NOME');
		$nome_fant    = new TEntry('NOME_FANTASIA');
		$responsavel  = new TEntry('RESPONSAVEL');
		$e_mail       = new TEntry('E_MAIL');
		$dt_cadastro  = new TDate('DATA_CADASTRAMENTO');
		$cpf_cnpj     = new TEntry('CPF_CNPJ');
		$id_ie        = new TEntry('ID_IE');
		$emp          = new TDBCombo('EMP', 'db2', 'fornec_resp', 'CONTROLE', 'NOME', 'CONTROLE');
		$telefone     = new TEntry('TELEFONE');
		$telefone2    = new TEntry('TELEFONE2');
		$fax          = new TEntry('FAX');
		$contato      = new TEntry('CONTATO');
		$endereco     = new TEntry('ENDERECO');
		$cep          = new TEntry('CEP');
		$bairro       = new TEntry('BAIRRO');
		$cidade       = new TEntry('CIDADE');
		$uf           = new TEntry('UF');
		$retpj        = new TEntry('RETPJ');
		$irrf         = new TEntry('IRRF');
		$issqn        = new TEntry('ISSQN');
		$banco        = new TEntry('BANCO');
		$usuario      = new TEntry('USUARIO');
		$agencia      = new TEntry('AGENCIA');
		$conta_corre  = new TEntry('CONTA_CORRENTE');
		$tipo         = new TRadioGroup('TIPO');
		$status       = new TRadioGroup('STATUS');
		$fis_jur      = new TRadioGroup('FIS_JUR');
		$obs          = new TText('OBS');
		$teste        = new TEntry('TESTE');
		
		//PG AF
		$descri   = new TEntry('DESCRI');//DESCRI_AF
		$ent_col     = new TDBCombo('ENT_COL', 'db2', 'entidades', 'COD_INT', '{COD_INT} | {RAZAO_SOCIAL}', 'COD_INT');
		$ent_col->setValue('TODOS');
		$di          = new TDate('DI');
		$df          = new TDate('DF');
		$pi          = new TEntry('PI2');
		$pf          = new TEntry('PF');
		$cm          = new TEntry('CM');
		$mx          = new TEntry('MX');
		
		//PAGE PEC		
		$descri_pec  = new TEntry('DESCRI_PEC');//DESCRI_AF
		$cb_pec      = new TDBCombo('CB', 'db2', 'cobertura', 'CODIGO', 'COBERTURA', 'CODIGO');
		
		//$ent_col_pec = new TEntry('ENT_COL_PEC'); 
		
		$ent_col_pec = new TDBCombo('ENT_COL_PEC', 'db2', 'entidades', 'COD_INT', '{COD_INT} | {RAZAO_SOCIAL}', 'COD_INT');
		
		$di_pec      = new TDate('DI_PEC');
		$df_pec      = new TDate('DF_PEC');
		$pi_pec      = new TEntry('PI_PEC');
		$pf_pec      = new TEntry('PF_PEC');
		$cm_pec      = new TEntry('CM_PEC');
		$mx_pec      = new TEntry('MX_PEC');
		
		//cria os Botões
		$btn_gravar_af    = TButton::create('btn_gravar_af', array($this, 'onGravarRegraAf'), 'Gravar', 'fa: fa-check blue' );//onAddRegraPec
		
		$btn_novo_af     = TButton::create('btn_novo_af', array($this, 'onAddRegraAf'), 'Incluir', 'fa: fa-plus blue' );
		
		$btn_gravar_pec = TButton::create('btn_gravar_pec', array($this, 'onGravarRegraPec'), 'Gravar', 'fa: fa-check blue' );
		
		$btn_novo_pec    = TButton::create('btn_novo_pec', array($this, 'onAddRegraPec'), 'Incluir', 'fa: fa-plus blue' );
		
		$btn_cancelar    = TButton::create('btn_cancelar', array($this, 'onCancelar'), 'Cancelar', 'ico_close.png' );
		
		$btn_voltar    = TButton::create('btn_voltar', array('FornecedoresListe', 'onReload'), 'Voltar', 'fa:arrow-left blue' );
		
		$btn_novo     = TButton::create('btn_novo', array($this, 'onIncluir'), 'Incluir', 'fa: fa-plus blue' );
		
		$btn_salvar     = TButton::create('btn_salvar', array($this, 'onSave'), 'Salvar', 'far:save' );
		$btn_salvar->class = 'btn btn-sm  btn-primary';
		
		$btn_cancelar   = TButton::create('btn_cancelar', array($this, 'onCancelar'), 'Cancelar', 'far: fa-window-close red' );
		
        
        //** Formatações
		//validação
		$nome->addValidation(' "Nome/ Razão social" ', new TRequiredValidator );
		
		$nome_user->setEditable(FALSE);
		
		//setMask
		$di->setMask('dd/mm/yyyy');
		$di->setDatabaseMask('dd/mm/yyyy');		
		$df->setMask('dd/mm/yyyy');
		$df->setDatabaseMask('dd/mm/yyyy');
		$di_pec->setMask('dd/mm/yyyy');
		$di_pec->setDatabaseMask('dd/mm/yyyy');
		$df_pec->setMask('dd/mm/yyyy');
		$df_pec->setDatabaseMask('dd/mm/yyyy');
	
		//setTip
		$id_ie->setTip('Identidade / Insc. Est/ Aut. Susep');
		$banco->setTip('Código do banco');
		$usuario->setTip('Nome do banco');
		$agencia->setTip('Agência');
		$conta_corre->setTip('Conta');
		$ent_col->setTip('Quando entidade coletiva for =');
		
		//placeholder
		$banco->placeholder = 'Código do Banco';
		$usuario->placeholder = 'Nome do Banco';
		$agencia->placeholder = 'Agência';
		$conta_corre->placeholder = 'Conta';
		
		//addItems
		$tipo->addItems(['A' =>'Agente', 'C' => 'Corretor' ]);
     	$tipo->setLayout('Horizontal');
		
		$status->addItems(['A' =>'Ativo', 'I' => 'Inativo' ]);
		$status->setLayout('Horizontal');
		
		$fis_jur->addItems(['F' =>'Física', 'J' => 'Juridica' ]);
		$fis_jur->setLayout('Horizontal');
		
		//style
		$lb_id->style = 'border:0px; background:#6287B9; color:#FFF ';
		$lb_nome->style = 'border:0px; background:#6287B9; color:#FFF ';
		$lb_id2->style = 'border:0px; background:#6287B9; color:#FFF ';
		$lb_nome2->style = 'border:0px; background:#6287B9; color:#FFF ';
		
		
        //*** PAGE 'DADOS' ***//
		$this->form->appendPage('Dados');//
		
		$row = $this->form->addFields([ new TLabel('Código'), $codigo ],
								      [ new TLabel('Nome Fantasia'), $nome_fant ],
									  [ new TLabel('Nome/ Razão social'), $nome ]);
		$row->layout = ['col-sm-2', 'col-sm-5', 'col-sm-5'];
		
		$row = $this->form->addFields([new TLabel('Id do usuário'),$user_id],
								      [new TLabel('Nome do usuário'),$nome_user]);
		$row->layout = ['col-sm-2', 'col-sm-10'];

		/*[new TLabel('Systema Id'), $user_id ]*/
							   
					   
		$row = $this->form->addFields([new TLabel('Responsável'), $responsavel ],
							          [new TLabel('E-mail'), $e_mail ],
							          [new TLabel('Cordenador'), $emp ]);
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];							  
								
		

		$row = $this->form->addFields([new TLabel('Cadastramento'), $dt_cadastro ],
								      [new TLabel('CPF/ CNPJ'), $cpf_cnpj ],
								      [new TLabel('Identidade'), $id_ie ],
								      [new TLabel('Telefone'), $telefone ]);
		$row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-4', 'col-sm-2'];							  							  

		$row = $this->form->addFields([new TLabel('Celular'), $telefone2 ],
								      [new TLabel('Fax'), $fax ],
								      [new TLabel('Contato'), $contato ]);
		$row->layout = ['col-sm-2', 'col-sm-3', 'col-sm-7'];							  


		$row = $this->form->addFields([new TLabel('Endereço'), $endereco ],
								      [new TLabel('Cep'), $cep ],
								      [new TLabel('Bairro'), $bairro ]);
		$row->layout = ['col-sm-6', 'col-sm-2', 'col-sm-4'];						

		$row = $this->form->addFields([new TLabel('Cidade'), $cidade ],
								      [new TLabel('Estado'), $uf ],
								      [new TLabel('% Retenção'), $retpj ],
									  [new TLabel('% IRRF(1708'), $irrf ],
								      [new TLabel('% ISSQN'), $issqn ]);	
		$row->layout = ['col-sm-5', 'col-sm-1', 'col-sm-2', 'col-sm-2', 'col-sm-2'];
		
		$row = $this->form->addFields([new TLabel('Banco'), $banco ],
								      [new TLabel('Nome Banco'), $usuario ],
								      [new TLabel('Agência'), $agencia ],
								      [new TLabel('Conta'), $conta_corre ]);
		$row->layout = ['col-sm-2', 'col-sm-5', 'col-sm-2', 'col-sm-3'];						
								
		$row = $this->form->addFields([ new TLabel('Obs'), $obs ]);
		$row->layout = ['col-sm-12'];						

		$frame_tipo = new TFrame;
		$frame_tipo->setLegend('Tipo');
		$frame_tipo->add( $tipo );
		$this->form->addFields( [ $frame_tipo ] );

		$frame_status = new TFrame;
		$frame_status->setLegend('Status');
		$frame_status->add( $status );
		$this->form->addFields( [ $frame_status ] );

		
		$frame_pessoa = new TFrame;
		$frame_pessoa->setLegend('Pessoal');
		$frame_pessoa->add( $fis_jur );
		$this->form->addFields( [ $frame_pessoa ] );
								
			
		 //*** PAGE 'Comissionamento AF'  ***//
		$this->form->appendPage('Comissionamento AF');//
		
		//topo da page
		$row = $this->form->addFields( [$lb_id], [$lb_nome]  );
		$row->layout = ['col-sm-2','col-sm-8'];
		$row->style = 'background:#6287B9; margin:0 0 5px 1px; ';
		
		//linha oculta
		$this->form->addFields( [ $controle ], [ $user ] );
		
		//Corpo
		$row = $this->form->addFields([new TLabel('Nome da Regra'), $descri ]);
		$row->layout = ['col-sm-12'];
								
		$row = $this->form->addFields([new TLabel('Quando ent. coletiva for='),   $ent_col ]);
		$row->layout = ['col-sm-12'];

		$row = $this->form->addFields( [new TLabel('e data cad entre '), $di ],
								       [new TLabel('e'), $df ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];							   

		$row = $this->form->addFields( [ new TLabel('e prazo entre '), $pi ],
								[ new TLabel('e'), $pf ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];						
								
		$row = $this->form->addFields( [ new TLabel('comissão de'), $cm ],
								[ new TLabel('no valor máximo de'), $mx ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];

		//barra dos btn
		$row = $this->form->addFields( [$btn_gravar_af]  );
		$row->layout = ['col-sm-1','col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';	
								
		//cria a datagrid_af
		$this->datagrid_af = new TQuickGrid;
		$this->datagrid_af->style = '100%';
		
		//add as colunas
		$this->datagrid_af->addQuickColumn('', 'edit', 'left');
		$this->datagrid_af->addQuickColumn('', 'delete', 'left' );
		$this->datagrid_af->addQuickColumn('Código', 'CONTROLE', 'left');
		$this->datagrid_af->addQuickColumn('Regra', 'DESCRI', 'center', '20%');
        $this->datagrid_af->addQuickColumn('Ent.Col', 'ENT_COL', 'center');
        $this->datagrid_af->addQuickColumn('Data inicío', 'DI', 'center');
        $this->datagrid_af->addQuickColumn('Data fim', 'DF', 'center');
        $this->datagrid_af->addQuickColumn('Pc.Inicial', 'PI', 'center');
        $this->datagrid_af->addQuickColumn('Pc.Final', 'PF', 'center');
        $this->datagrid_af->addQuickColumn('Comissão', 'CM', 'center');
        $this->datagrid_af->addQuickColumn('Vl.Máximo', 'MX', 'center');
        $this->datagrid_af->addQuickColumn('Usuário', 'USER', 'center');
		
		$this->datagrid_af->createModel();
		
		
		
		//add o scroll no form
		//add o scroll no form
		$scroll_af = new TScroll;
		$scroll_af->setSize('100%', 200);
		$scroll_af->add($this->datagrid_af);
		
		$this->form->addFields( [ $scroll_af ] );
		
		//*** PAGE 'Comissionamento Pecúlio'  ***//
		$this->form->appendPage('Comissionamento Pecúlio');//					   
		
		//topo da page
		$row = $this->form->addFields([ $lb_id2], [$lb_nome2] );//label1
		$row->layout = ['col-sm-2', 'col-sm-8'];
		$row->style='background:#6287B9; color:#000; margin:0 0 5px 1px';
		
		$row = $this->form->addFields( [ $controle_pec ], [ $user_pec ] );
		
		$row = $this->form->addFields( [ new TLabel('Nome da regra'), $descri_pec ]);
		$row->layout = ['col-sm-12'];
								
		$row = $this->form->addFields( [ new TLabel('Quando a cobertura for ='), $cb_pec ]);
		$row->layout = ['col-sm-12'];						
								
		$row = $this->form->addFields( [ new TLabel('e entidade coletiva for='), $ent_col_pec ]);
		$row->layout = ['col-sm-12'];						

		$row = $this->form->addFields( [ new TLabel('e inicío da vigência entre'), $di_pec ],
								[ new TLabel('e'), $df_pec ]);	
		$row->layout = ['col-sm-6', 'col-sm-6'];						

		$row = $this->form->addFields( [ new TLabel('e parcela entre'), $pi_pec ],
								[ new TLabel('e'), $pf_pec ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];						
								
		$row = $this->form->addFields( [ new TLabel('comissão'), $cm_pec ]);
		$row->layout = ['col-sm-12'];						

		//cria a datagrid
		$this->datagrid_pec = new TQuickGrid;
		$this->datagrid_pec->style = '100%';
		//$this->datagrid_pec->makeScrollable();
		$this->datagrid_pec->setHeight(200);
		$this->datagrid_pec->addQuickColumn('', 'edit', 'center');
        $this->datagrid_pec->addQuickColumn('', 'delete', 'center');
        $this->datagrid_pec->addQuickColumn('Código', 'CONTROLE', 'center');
        $this->datagrid_pec->addQuickColumn('Regra', 'DESCRI', 'center', '20%');
        $this->datagrid_pec->addQuickColumn('Ent.Col', 'ENT_COL', 'center');
        $this->datagrid_pec->addQuickColumn('Data inicío', 'DI', 'center');
        $this->datagrid_pec->addQuickColumn('Data fim', 'DF', 'center');
        $this->datagrid_pec->addQuickColumn('Pc.Inicial', 'PI', 'center');
        $this->datagrid_pec->addQuickColumn('Pc.Final', 'PF', 'center');
        $this->datagrid_pec->addQuickColumn('Comissão', 'CM', 'center');
        //$this->datagrid_pec->addQuickColumn('Vl.Máximo', 'MX', 'center', 60);
        $this->datagrid_pec->addQuickColumn('Usuário', 'USER', 'center');
		
		$this->datagrid_pec->createModel();
		
		$row = $this->form->addFields( [$btn_gravar_pec]  );
		$row->layout = ['col-sm-1','col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';
		
		
		//add o scroll no form
		$scroll_pec = new TScroll;
		$scroll_pec->setSize('100%', 200);
		$scroll_pec->add($this->datagrid_pec);
		
		$this->form->addFields( [ $scroll_pec ] );

		$this->formFields = array($codigo, $nome_fant, $nome, $tipo, $status, $fis_jur, $responsavel, $e_mail, $dt_cadastro, $cpf_cnpj, $id_ie, $emp, $telefone,$telefone2, $fax, $contato, $endereco, $cep, $bairro, $cidade, $uf, $retpj, $irrf,$issqn, $banco, $usuario, $agencia, $conta_corre, $obs, $controle, $lb_id, $lb_id2, $lb_nome, $lb_nome2, $descri_pec, $cb_pec, $di_pec, $df_pec, $pi_pec, $pf_pec, $cm_pec, $mx_pec, $descri, $ent_col, $ent_col_pec, $di, $df, $pi, $pf, $cm, $mx, $btn_cancelar, $btn_gravar_af, $btn_gravar_pec, $btn_voltar, $btn_novo, $btn_salvar, $controle_pec, $user_pec, $btn_novo_af, $user_id, $nome_user, $teste);//formFields
		
		$this->form->setFields( $this->formFields );
		
		$painel = new TPanelGroup('cadastro de Agentes');
	   
		//empacotamento
		$painel->add($this->form);
		
		//ativar a rolagem horizontal dentro do corpo do painell
		$painel->getBody()->style = 'overflow-x:auto';
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_voltar, $btn_novo, $btn_salvar, $btn_cancelar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_voltar ));
		}
		
		
        // wrap the page content using vertical box
        $vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'FornecedoresListe'));
        $vbox->add($painel);
		
        parent::add($vbox);
		
	}//__construct
	
	/*
	  Atualiza a página com os parâmetros atuais
	*/
	public function onReload($param)
	{
		//paga a variavel de sessão 
	    $ts_cm_af 		 = TSession::getValue('TS_cm_af');
	    $ts_cm_pec 		 = TSession::getValue('TS_cm_pec');
		$ts_current_page = TSession::getValue('TS_current_page_fornec');
        $data 			 = TSession::getValue('TS_data');
		
		// LIMPA AS GRIDS 
		$this->datagrid_af->clear();
		$this->datagrid_pec->clear(); 
        
		//CARREGA OS DADOS DAS AF's, GRID 'datagrid_af'
		if ($ts_cm_af)
        {
            $cont = 1;
            foreach ($ts_cm_af as $list_product_id => $list_product)
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
				
				//ADD OS ITEMS NA GRID datagrid_af 
				$item->CONTROLE  = $list_product['CONTROLE'];
				$item->DESCRI    = $list_product['DESCRI'];
				$item->ENT_COL   = $list_product['ENT_COL'];
				$item->DI        = $list_product['DI'];
				$item->DF        = $list_product['DF'];
				$item->CM        = $list_product['CM'];
				$item->MX        = $list_product['MX'];
				$item->PI        = $list_product['PI'];
				$item->PF        = $list_product['PF'];
				$item->ENT_COL   = $list_product['ENT_COL'];
				$item->USER      = $list_product['USER'];
				
				$this->datagrid_af->addItem( $item );
                
				//pega o id do controle + 1 para nova regra
				$data->CONTROLE =  (1 + $list_product['CONTROLE']);
				
				
				
				/*
				if($list_product['CONTROLE'] <= 0 )
				{
					$data->CONTROLE = 1 ;
				}
				*/
				
			
				
				$this->form->setData($data);
				
            }//foreach ($ts_cm_af)
			
            $this->form->setFields( $this->formFields );
		    $this->form->setData($data);
			
        }//if ($ts_cm_af)
			
			
		//CARREGA OS DADOS DAS PEC's, GRID 'datagrid_pec'
		if ($ts_cm_pec)//ts_cm_pec
        {
            $cont = 1;
            foreach ($ts_cm_pec as $list_product_id => $list_product)
            {
                $item_name = 'prod_' . $cont++;
                //OBJ PADRÃO DAS CLASSES
				$item = new StdClass;

				//CRIA AS AÇÕES DOS BTN
                $action_del = new TAction(array($this, 'onDeleteItemPec'));
                $action_del->setParameter('list_product_id', $list_product_id);
				$action_del->setParameter('cont',$cont);

				$action_edi = new TAction(array($this, 'onEditItemProdutoPec'));
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
				$item->CONTROLE  = $list_product['CONTROLE'];
				$item->DESCRI    = $list_product['DESCRI'];
				$item->ENT_COL   = $list_product['ENT_COL'];
				$item->DI        = $list_product['DI'];
				$item->DF        = $list_product['DF'];
				$item->CM        = $list_product['CM'];
				//$item->MX        = $list_product['MX'];
				$item->PI        = $list_product['PI'];
				$item->PF        = $list_product['PF'];
				$item->CB        = $list_product['CB'];
				$item->ENT_COL   = $list_product['ENT_COL'];
				$item->USER      = $list_product['USER'];
				
				$this->datagrid_pec->addItem($item);
				
				/*if($list_product['CONTROLE'] <= 0 )
				{
					$data->CONTROLE_PEC = 1 ;
				}*/
				
				
				//pega o id do controle + 1 para nova regra
				$data->CONTROLE_PEC =  (1 + $list_product['CONTROLE']);
				
				
				if($data->CONTROLE_PEC < 1 )
				{
					$data->CONTROLE_PEC = 1 ;
				}
				
				
				/*
				if(empty($data->CONTROLE_PEC))
				{
					$data->CONTROLE_PEC = 1 ;
				}
				*/
					
				
				/*
				if( !empty($data->CONTROLE_PEC) )
				{	
					$data->CONTROLE_PEC = ( 1 + $list_product['CONTROLE'] );
					
				}
				else
				{
					$data->CONTROLE_PEC =  2 ;
				}
				*/
				
				
				
            }//foreach ($ts_cm_pec)
			
			
			//$data->CONTROLE = 1;
			
            $this->form->setFields( $this->formFields );
		    $this->form->setData($data);
			
        }//if ($ts_cm_pec)
		
		
		$this->form->setCurrentPage($ts_current_page);//$ts_current_page
			
		$this->loaded = TRUE;
		
	}//onReload
	
	/*
	  Deleta um item PEC da sessão, 
	  mas nao deleta da base
	*/
	public function onDeleteItemPec($param)
	{
		$data = $this->form->getData();

        //$this->form->setData( $data );

		//LE ITENS DA SESSÃO
		$ts_cm_pec = TSession::getValue('TS_cm_pec');
		
        //'unset' APAGA OS ITEMS DA SESSÃO DE ACORDOCOM SEU ID
        unset($ts_cm_pec[ (int) $param['list_product_id'] ] );
        
		//GRAVA NA SESSÃO SEM O OBJ DO 'unset'
        TSession::setValue('TS_cm_pec', $ts_cm_pec);
		
        // RECARREGAR OS ITENS DA VENDA
        $this->onReload( $param );
		
	}//onDeleteItemPec
	
	/*
	  Instância um 'fornecedor' usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->clear();
			//$data = $this->form->getData();
			
			if(isset($param['key']))
			{
				$key = $param['key'];
				$fornecedor = new fornecedor($key);
				
			
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formFornecedores', 'btn_salvar');
				}	
				
				//carrega os dados das AF's
				$ts_cm_af = array();
				foreach($fornecedor->getcm_af() as $ac_mf )
				{	
					$ts_cm_af[$ac_mf->CONTROLE]   = $ac_mf->toArray();
					$ts_cm_af[$ac_mf->CONTROLE]['CONTROLE'] = $ac_mf->CONTROLE;
					$ts_cm_af[$ac_mf->CONTROLE]['DESCRI']   = $ac_mf->DESCRI;
					$ts_cm_af[$ac_mf->CONTROLE]['ENT_COL '] = $ac_mf->ENT_COL;
					$ts_cm_af[$ac_mf->CONTROLE]['DI']       = $ac_mf->DI;
					$ts_cm_af[$ac_mf->CONTROLE]['DF']       = $ac_mf->DF;
					$ts_cm_af[$ac_mf->CONTROLE]['PI']       = $ac_mf->PI;
					$ts_cm_af[$ac_mf->CONTROLE]['PF']       = $ac_mf->PF;
					$ts_cm_af[$ac_mf->CONTROLE]['MX']       = $ac_mf->MX;
					$ts_cm_af[$ac_mf->CONTROLE]['DI']       = $ac_mf->DI;
					$ts_cm_af[$ac_mf->CONTROLE]['DF']       = $ac_mf->DF;
					$ts_cm_af[$ac_mf->CONTROLE]['USER']     = $ac_mf->USER;
					
				}
				TSession::setValue('TS_cm_af', $ts_cm_af);
				
				//CARREGA OS DADOS DAS PEC'S
				$ts_cm_pec = array();
				foreach($fornecedor->getcm_pec() as $cm_pec )//addcm_pec
				{
					$ts_cm_pec[$cm_pec->CONTROLE]   = $cm_pec->toArray();
					$ts_cm_pec[$cm_pec->CONTROLE]['CONTROLE'] = $cm_pec->CONTROLE;
					$ts_cm_pec[$cm_pec->CONTROLE]['DESCRI']   = $cm_pec->DESCRI;
					$ts_cm_pec[$cm_pec->CONTROLE]['ENT_COL']  = $cm_pec->ENT_COL;
					$ts_cm_pec[$cm_pec->CONTROLE]['CB']       = $cm_pec->CB;
					$ts_cm_pec[$cm_pec->CONTROLE]['DI']       = $cm_pec->DI;
					$ts_cm_pec[$cm_pec->CONTROLE]['DF']       = $cm_pec->DF;
					$ts_cm_pec[$cm_pec->CONTROLE]['PI']       = $cm_pec->PI;
					$ts_cm_pec[$cm_pec->CONTROLE]['PF']       = $cm_pec->PF;
					//$ts_cm_pec[$cm_pec->CONTROLE]['MX']     = $cm_pec->MX;
					$ts_cm_pec[$cm_pec->CONTROLE]['DI']       = $cm_pec->DI;
					$ts_cm_pec[$cm_pec->CONTROLE]['DF']       = $cm_pec->DF;
					$ts_cm_pec[$cm_pec->CONTROLE]['USER']     = $cm_pec->USER;
					
						 	
				}
				TSession::setValue('TS_cm_pec', $ts_cm_pec);
				
				//atualiza os dados no form
				$obj = new STDClass;
				$obj->LB_ID     = $fornecedor->CODIGO;; 
				$obj->LB_ID2    = $fornecedor->CODIGO;;
				$obj->LB_NOME   = $fornecedor->NOME;
				$obj->LB_NOME2  = $fornecedor->NOME;
				$obj->id        = $fornecedor->USER_ID;
				$obj->NOME_USER = $fornecedor->NOME_USER;
				//$obj->USER_ID  = $fornecedor->USER_ID;
				TForm::sendData('formFornecedores', $obj);
				
				$this->form->setData($fornecedor);
				
				//grava os dados do fornecedor na sessão
				TSession::setValue('TS_data', $fornecedor);
				
				//grava o id do fornecedor no sessão
				TSession::setValue('TS_key', $key);
				
				//define a aba ativa e grava na sessão
			    $ts_current_page = 0;
			    $this->form->setCurrentPage($ts_current_page);
			
				//grava a aba na sessão
				TSession::setValue('TS_current_page_fornec', $ts_current_page);
				
				//desabilita os campos
				//TButton::disableField('formFornecedores', 'btn_gravar_af');
				//TButton::disableField('formFornecedores', 'btn_gravar_pec');
				TEntry::disableField('formFornecedores', 'CODIGO');
				
				//new TMessage('info', 'Teste');
				
				$this->onReload( $param );
				
			}//if $param['key']
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onEdit
	
	/*
	  salva o fornecedor e seus agregados   
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate(); // form validation
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formFornecedores', 'btn_salvar');
			}
			
			//INSTÂNCIA O OBJ PELO GETDATA
			$fornecedor = $this->form->getData('fornecedor');
            
			
			//PEGA AS REGRAS PARA OS AGREGAOS AF
			$ts_cm_afs = TSession::getValue('TS_cm_af');
			if ($ts_cm_afs)
			{	
				foreach($ts_cm_afs as $lista_regra)
				{
					//$item2 = new StdClass;
					$cm_af = new cm_af;
					
					$cm_af->DESCRI  = $lista_regra['DESCRI'];
					$cm_af->ENT_COL = $lista_regra['ENT_COL'];
					$cm_af->DI      = $lista_regra['DI'];
					$cm_af->DF      = $lista_regra['DF'];
					$cm_af->PI      = $lista_regra['PI'];
					$cm_af->PF      = $lista_regra['PF'];
					$cm_af->CM      = $lista_regra['CM'];
					$cm_af->MX      = $lista_regra['MX'];
					$cm_af->AGENTE  = $param['CODIGO'];
					$cm_af->USER    = $lista_regra['USER'];
					
					$fornecedor->addcm_af($cm_af);
					
				}//foreach
				
			}//$ts_cm_afs
			
			//
			//PEGA AS REGRAS PARA OS AGREGAOS PEC
			$ts_cm_pecs = TSession::getValue('TS_cm_pec');
			if ($ts_cm_pecs)
			{	
				foreach($ts_cm_pecs as $lista_regra_pec)//lista_regra
				{
					//$item2 = new StdClass;
					$cm_pec = new cm_pec;
					
					$cm_pec->DESCRI  = $lista_regra_pec['DESCRI'];
					$cm_pec->ENT_COL = $lista_regra_pec['ENT_COL'];
					$cm_pec->DI      = $lista_regra_pec['DI'];
					$cm_pec->DF      = $lista_regra_pec['DF'];
					$cm_pec->PI      = $lista_regra_pec['PI'];
					$cm_pec->PF      = $lista_regra_pec['PF'];
					$cm_pec->CM      = $lista_regra_pec['CM'];
					$cm_pec->CB      = $lista_regra_pec['CB'];
					//$cm_pec->MX      = $lista_regra_pec['MX'];
					$cm_pec->AGENTE  = $param['CODIGO'];
					$cm_pec->USER    = $lista_regra_pec['USER'];
					
					$fornecedor->addcm_pec($cm_pec);
					
				}//foreach
				
			}//$ts_cm_afs
			
			$fornecedor->USER_ID = $param['id'];
			$fornecedor->store(); // salva o objeto
			
			new TMessage('info', 'Salvo com sucesso');
			
			TTransaction::close();
			
			// $data->LB_NOME  = $fornecedor->NOME;
			// $data->LB_NOME2 = $fornecedor->NOME;
			
			//atualiza os dados no form
			$obj = new STDClass;
			$obj->LB_ID    = $fornecedor->CODIGO;; 
			$obj->LB_ID2   = $fornecedor->CODIGO;;
			$obj->LB_NOME  = $fornecedor->NOME;
			$obj->LB_NOME2 = $fornecedor->NOME;
			TForm::sendData('formFornecedores', $obj);
			
			//LIMPA ALGUNS CAMPOS SE PREENCHIDOS
			$fornecedor->DESCRI  = '';
			$fornecedor->ENT_COL = '';
			$fornecedor->DI      = '';
			$fornecedor->DF      = '';
			$fornecedor->PI2     = '';
			$fornecedor->PF      = '';
			$fornecedor->CM      = '';
			$fornecedor->MX      = '';
			
			$fornecedor->DESCRI_PEC  = '';
			$fornecedor->ENT_COL_PEC = '';
			$fornecedor->CB          = '';
			$fornecedor->DI_PEC      = '';
			$fornecedor->DF_PEC      = '';
			$fornecedor->PI_PEC      = '';
			$fornecedor->PF_PEC      = '';
			$fornecedor->CM_PEC      = '';
			
			//Mostra os dados no form
			$this->form->setData($fornecedor);
			
			TSession::setValue('TS_data', $fornecedor);
			
			//Desabilita campos
			//TButton::disableField('formFornecedores', 'btn_gravar_pec');
			//TButton::disableField('formFornecedores', 'btn_gravar_af');
			TEntry::disableField('formFornecedores' , 'CODIGO');
			
			
			$this->onReload($param);
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	
	/*
	  Grava novo regra de pecúlio pegando o
	  id do campo oculto '$data->CONTROLE_PEC'
	*/
	public function onGravarRegraPec($param)
	{
		try
		{
			$data = $this->form->getData();
			
			if(empty($data->CONTROLE_PEC))
			{
				$data->CONTROLE_PEC = 1;
			}
			
			//pega as regra atuais
			$ts_cm_pec = TSession::getValue('TS_cm_pec');
			
			//pega o login do User
			$login = (TSession::getValue('login'));
			
			//Da uma ID para nova regra
			$key = (int) $data->CONTROLE_PEC;//campo oculto
			
			//add novo regra
			$ts_cm_pec[ $key ] = array( 'DESCRI'   => $param['DESCRI_PEC'],
										'CONTROLE' => $data->CONTROLE_PEC,
										'ENT_COL'  => $param['ENT_COL_PEC'],
										'CB'       => $param['CB'],
										'DI'       => $param['DI_PEC'],
										'DF'       => $param['DF_PEC'],
										'PI'       => $param['PI_PEC'],
										'PF'       => $param['PF_PEC'],
										'CM'       => $param['CM_PEC'],
										'USER'     => $login
										//'MX'       => $param['MX_PEC']
									   );  

			//grava a nova regra na sessão
			TSession::setValue('TS_cm_pec', $ts_cm_pec);
			
			//Desabilita campos
			//TButton::disableField('formFornecedores', 'btn_gravar_pec');
			
			//Limpa os campos
			$data->DESCRI_PEC  = '';
			$data->ENT_COL_PEC = '';
			$data->CB          = '';
			$data->DI_PEC      = '';
			$data->DF_PEC      = '';
			$data->PI_PEC      = '';
			$data->PF_PEC      = '';
			$data->CM_PEC      = '';
			
			
			
			$this->form->setData($data);
			
			//recarrega a página 
			$this->onReload( $param ); // reload is items sale items
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onGravarRegraPec
	
	
	/*
	  Grava novo regra de AF pegando o
	  id do campo oculto '$data->CONTROLE'
	*/
	public function onGravarRegraAf($param)
	{
		try
		{
			$data = $this->form->getData();
			
			if(empty($data->CONTROLE))
			{
				$data->CONTROLE = 1;
			}
			
			//pega as regra atuais
			$ts_cm_af = TSession::getValue('TS_cm_af');
			$login = (TSession::getValue('login'));
			
			//isset($post->post_title) ? $post->post_title : $post->title;
			
			//Da uma ID para nova regra
			$key = (int) $data->CONTROLE;//campo oculto
			
			//add novo regra
			$ts_cm_af[ $key ] = array(  'DESCRI'   => $param['DESCRI'],
										'CONTROLE' => $data->CONTROLE,
										'ENT_COL'  => $param['ENT_COL'],
										'DI'       => $param['DI'],
										'DF'       => $param['DF'],
										'PI'       => $param['PI2'],
										'PF'       => $param['PF'],
										'CM'       => $param['CM'],
										'USER'     => $login,
										'MX'       => $param['MX']
									   );  

			//grava a nova regra na sessão
			TSession::setValue('TS_cm_af', $ts_cm_af);
			
			$ts_cm_af = TSession::getValue('TS_cm_af');
			
			$data->DESCRI  = '';
			$data->ENT_COL = '';
			$data->DI      = '';
			$data->DF      = '';
			$data->PI2     = '';
			$data->PF      = '';
			$data->CM      = '';
			$data->MX      = '';
			
			//Desabilita campos
			//TButton::disableField('formFornecedores', 'btn_gravar_pec');
			
			$this->form->setData($data);
			
			//recarrega a página 
			$this->onReload( $param ); // reload is items sale items
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onGravarRegraAf
		
	
	/*
	  Prepara o form para add e salvar uma nova regra de 'AF'.
	*/
	public function onAddRegraAf()
	{
		try
		{
			//pega os dados do form
			$data = $this->form->getData();
			
			//pega o usuário
			//PEGA LOGIN DO USUARIO
			$login = (TSession::getValue('login'));
			
            //Id da AfS
			if(empty($data->CONTROLE))
			{
				$data->CONTROLE = 1;
			}		
					
			
			//$data->CONTROLE = $new_controle;
			/*
			$data->DESCRI   = '';
			$data->ENT_COL  = '';
			$data->DI  		= '';
			$data->DF  		= '';
			$data->PI2 		= '';
			$data->PF  		= '';
			$data->CM  		= '';
			$data->MX  		= '';
			$data->USER     = $login;
			*/
			
			//$this->form->setCurrentPage(1);
			$ts_current_page = TSession::getValue('TS_current_page_fornec');
			$this->form->setCurrentPage($ts_current_page);
			
			//setFocus
			TScript::create('setTimeout(function() { $("input[name=\'DESCRI\']").focus() }, 1);');
			
			//Desabilita campos
			TButton::disableField('formFornecedores', 'btn_novo_af');
			
			$this->form->setData($data);
			
			//$this->onReload($param);
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
		}
		
	}//onAddRegraAf
	
	/*
	  Prepara o form para add e salvar uma nova regra 'PEC'.
	*/
	public function onAddRegraPec()
	{
		try
		{
			//pega os dados do form
			$data = $this->form->getData();
			
			//pega o usuário
			//PEGA LOGIN DO USUARIO
			$login = (TSession::getValue('login'));
			
            if(empty($data->CONTROLE_PEC))
			{
				$data->CONTROLE_PEC = 1;
			}	
			
			//$data->CONTROLE_PEC = $new_controle;
			$data->DESCRI_PEC   = '';
			$data->ENT_COL_PEC  = '';
			$data->DI_PEC  		= '';
			$data->DF_PEC  		= '';
			$data->PI_PEC 		= '';
			$data->PF_PEC  		= '';
			$data->CM_PEC  		= '';
			//$data->MX_PEC  		= '';
			$data->CB    		= '';
			$data->USER         = $login;
			
			//$ts_current_page = TSession::getValue('TS_current_page');
			$this->form->setCurrentPage(TSession::getValue('TS_current_page_fornec'));
			
			TScript::create('setTimeout(function() { $("input[name=\'DESCRI_PEC\']").focus() }, 1);');
			
			//Desabilita campos
			TButton::disableField('formFornecedores', 'btn_novo_pec');
			
			$this->form->setData($data);
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
		}
	}//onAddRegraPec
	
	/*
	  Prepara o form para cadastrar
	  um 'fornecedor' e seus agregados
	*/
	public function onIncluir()
	{
		$this->form->clear();
		$this->datagrid_af->clear();
		$this->datagrid_pec->clear();
		
		TSession::setValue('TS_cm_af', NULL);
	    TSession::setValue('TS_cm_pec', NULL);
	    //TSession::setValue('TS_data', NULL);
		
		$this->form->setCurrentPage(0);
		
		$ts_current_page = 0;
		TSession::setValue('TS_current_page_fornec', $ts_current_page );
		
		TScript::create('setTimeout(function() { $("input[name=\'NOME_FANTASIA\']").focus() }, 200);');
		
		
	}//onIncluir
	
	/*
	  Pega o ID do fornecedor e trás os seus dados atuais.
	*/	
	public function onCancelar()
	{
		$data = TSession::getValue('TS_data');
		//$data = $this->form->getData();
		
		$permisao = TSession::getValue('TS_alteracao');
		if($permisao == 0 )
		{	
			TButton::disableField('formFornecedores', 'btn_salvar');
		}
		
		TTransaction::open('db2');
		
		//pega o id do fornecedor na sessão
		$param['key'] = TSession::getValue('TS_key');
		if(isset($param['key']))
			{
				$key = $param['key'];
				$fornecedor = new fornecedor($key);
				
				$ts_cm_af = array();
				foreach($fornecedor->getcm_af() as $ac_mf )
				{
					$ts_cm_af[$ac_mf->CONTROLE]   = $ac_mf->toArray();
					$ts_cm_af[$ac_mf->CONTROLE]['CONTROLE'] = $ac_mf->CONTROLE;
					$ts_cm_af[$ac_mf->CONTROLE]['DESCRI']   = $ac_mf->DESCRI;
					$ts_cm_af[$ac_mf->CONTROLE]['ENT_COL '] = $ac_mf->ENT_COL;
					$ts_cm_af[$ac_mf->CONTROLE]['DI']       = $ac_mf->DI;
					$ts_cm_af[$ac_mf->CONTROLE]['DF']       = $ac_mf->DF;
					$ts_cm_af[$ac_mf->CONTROLE]['PI']       = $ac_mf->PI;
					$ts_cm_af[$ac_mf->CONTROLE]['PF']       = $ac_mf->PF;
					$ts_cm_af[$ac_mf->CONTROLE]['MX']       = $ac_mf->MX;
					$ts_cm_af[$ac_mf->CONTROLE]['DI']       = $ac_mf->DI;
					$ts_cm_af[$ac_mf->CONTROLE]['DF']       = $ac_mf->DF;
					$ts_cm_af[$ac_mf->CONTROLE]['USER']     = $ac_mf->USER;
					
				}
				TSession::setValue('TS_cm_af', $ts_cm_af);
				
				//TRÁS OS AGREGADOS PEC
				$ts_cm_pec = array();
				foreach($fornecedor->getcm_pec() as $ac_pec )
				{
					$ts_cm_pec[$ac_pec->CONTROLE]   = $ac_pec->toArray();
					$ts_cm_pec[$ac_pec->CONTROLE]['CONTROLE'] = $ac_pec->CONTROLE;
					$ts_cm_pec[$ac_pec->CONTROLE]['DESCRI']   = $ac_pec->DESCRI;
					$ts_cm_pec[$ac_pec->CONTROLE]['ENT_COL '] = $ac_pec->ENT_COL;
					$ts_cm_pec[$ac_pec->CONTROLE]['DI']       = $ac_pec->DI;
					$ts_cm_pec[$ac_pec->CONTROLE]['DF']       = $ac_pec->DF;
					$ts_cm_pec[$ac_pec->CONTROLE]['PI']       = $ac_pec->PI;
					$ts_cm_pec[$ac_pec->CONTROLE]['PF']       = $ac_pec->PF;
					//$ts_cm_pec[$ac_pec->CONTROLE]['MX']       = $ac_pec->MX;
					$ts_cm_pec[$ac_pec->CONTROLE]['DI']       = $ac_pec->DI;
					$ts_cm_pec[$ac_pec->CONTROLE]['DF']       = $ac_pec->DF;
					$ts_cm_pec[$ac_pec->CONTROLE]['USER']     = $ac_pec->USER;
					
				}
				TSession::setValue('TS_cm_pec', $ts_cm_pec);
				
			}//if(isset
			
		TTransaction::close();	
		
		$this->form->setData($data);
		
		$this->onReload($param);
		
	}//onCancelar
	
	/*
	  Deleta um item da sessão, 
	  mas não deleta da base
	*/
	public function onDeleteItem($param)
	{
		$data = $this->form->getData();

        // LIMPA O FORMULÁRIO
        $this->form->setData( $data );

		//LE ITENS DA SESSÃO
		$ts_cm_af = TSession::getValue('TS_cm_af');
		
        //'unset' APAGA OS ITEMS DA SESSÃO DE ACORDOCOM SEU ID
        unset($ts_cm_af[ (int) $param['list_product_id'] ] );
        
		//GRAVA NA SESSÃO SEM O OBJ DO 'unset'
        TSession::setValue('TS_cm_af', $ts_cm_af);
		
		//grana a aba no sessão
		$ts_current_page = 1;
		TSession::setValue('TS_current_page_fornec', $ts_current_page );
        
        // RECARREGAR OS ITENS DA VENDA
        $this->onReload( $param );
		
	}//onDeleteItem
	
	/*
	  Edita um item 'af' da sessão
	*/
	public function onEditItemProduto($param)
	{
		$data = $this->form->getData();
		
		//LE ITENS DA SESSÃO
        $ts_cm_afs = TSession::getValue('TS_cm_af');
		
        //OBTEM OS ITEM DA SESSÃO
        $ts_cm_af  = $ts_cm_afs[ (int) $param['list_product_id'] ];
		
		$login = (TSession::getValue('login'));
		
		$data->CONTROLE = $ts_cm_af['CONTROLE'];
        $data->DESCRI   = $ts_cm_af['DESCRI'];
        $data->ENT_COL  = $ts_cm_af['ENT_COL'];
        $data->DI       = $ts_cm_af['DI'];
        $data->DF       = $ts_cm_af['DF'];
        $data->PI2      = $ts_cm_af['PI'];
		$data->PF       = $ts_cm_af['PF'];
        $data->CM       = $ts_cm_af['CM'];
        $data->MX       = $ts_cm_af['MX'];
        $data->USER     = $ts_cm_af['USER'];
		
		$this->form->setData($data);
		
		//DESABILITA OS BTN
		TButton::disableField('formFornecedores', 'btn_novo_af');
		
		//PEGA O TOTAL DE REGISTROS E DESABILITA OS BOTÕES
		/*
		$cm_afs_tot = count($ts_cm_afs);
		$cm_afs_tot = $cm_afs_tot + 1;//pega todos os registros
		
		//DESABILITA OS BOTÕES DE ACORDO COM O NUMERO DE REGISTRO  
		for($i=1; $i <= $cm_afs_tot; $i++)
		{
			 TButton::disableField('formFornecedores','delete_product'.$i);
			 TButton::disableField('formFornecedores','edit_product'.$i);

		}
		*/
		
		$this->form->setCurrentPage(1);
		
	}//onEditItemProduto
	
	/*
	  Edita um item 'pec' da sessão
	*/
	public function onEditItemProdutoPec($param)
	{
		$data = $this->form->getData();
		
		//LE ITENS DA SESSÃO
        $ts_cm_pecs = TSession::getValue('TS_cm_pec');
        
        //OBTEM OS ITEM DA SESSÃO
        $ts_cm_pec  = $ts_cm_pecs[(int) $param['list_product_id'] ];
        
		$login = (TSession::getValue('login'));
		
		$data->CONTROLE_PEC = $ts_cm_pec['CONTROLE'];
        $data->DESCRI_PEC   = $ts_cm_pec['DESCRI'];
        $data->ENT_COL_PEC  = $ts_cm_pec['ENT_COL'];
        $data->CB           = $ts_cm_pec['CB'];
        $data->DI_PEC       = $ts_cm_pec['DI'];
        $data->DF_PEC       = $ts_cm_pec['DF'];
        $data->PI_PEC       = $ts_cm_pec['PI'];
		$data->PF_PEC       = $ts_cm_pec['PF'];
        $data->CM_PEC       = $ts_cm_pec['CM'];
        //$data->MX_PEC       = $ts_cm_pec['MX'];
        $data->USER         = $ts_cm_pec['USER'];
        //$data->MATR_ORGAO        = $sale_item['MATR_ORGAO'];
		
		$this->form->setData($data);
		
		//DESABILITA OS BTN
		TButton::disableField('formFornecedores', 'btn_novo_pec');
		
		//PEGA O TOTAL DE REGISTROS E DESABILITA OS BOTÕES
		/*
		$cm_pec_tot = count($ts_cm_pecs);
		$cm_pec_tot = $cm_pec_tot + 1;//pega todos os registros
		
		//DESABILITA OS BOTÕES DE ACORDO COM O NUMERO DE REGISTRO  
		for($i=1; $i <= $cm_pec_tot; $i++)
		{
			 TButton::disableField('formFornecedores','delete_product'.$i);
			 TButton::disableField('formFornecedores','edit_product'.$i);

		}
		*/
		
		$this->form->setCurrentPage(2);
		
	}//onEditItemProdutoPec
	
	/**
     * grava na sessão a aba atual do form 
     */
    public static function onTabClick($param)
    {
        //new TMessage('info', '<b>You have clicked at the tab</b>:  <br><br>' . $param['current_page'] . str_replace(',', '<br>', json_encode($param)));
		
		//grava a aba na sessão a cada troca 
		TSession::setValue('TS_current_page_fornec', $param['current_page'] );
		
    }//onTabClick
	
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