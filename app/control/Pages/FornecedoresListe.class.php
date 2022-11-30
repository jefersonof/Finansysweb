<?php
class FornecedoresListe Extends TPage
{
	private $form;
	private $datagrid;
	private $pageNavigation;
	
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
		$this->form  = new BootstrapFormBuilder('formListeFornc');
		$this->form->class='tform';
		
		//cria os atributos
		$busca      = new TEntry('BUSCA');
		$listcampos = new TCombo('LISTCAMPOS');
		
		//cria as sessões
		$busca->setValue(TSession::getValue('TS_busca'));
		$listcampos->setValue(TSession::getValue('TS_listcampos'));
		
		//ação de saida da 'TCombo', após trocar o seu valor
		$listcampos->setChangeAction(new TAction(array($this, 'onLimpaBusca')));
		
		//cria os botões
		$btn_fechar = TButton::create('btn_fechar' ,array('PageInicial', 'onReload'), 'Fechar', 'fa: fa-power-off red' );
		
		//formatações
		$listcampos->setSize('100%');
		$busca->setSize('100%');
		$listcampos->addItems( array('CODIGO' => 'Codigo', 'NOME' => 'Nome', 'CPF_CNPJ' => 'CPF CNPJ', 'TODOS' => 'Todos', 'TODOS_ORDEM_COD' => 'Todos ordem codigo', 'ATIVOS_CODIGO' => 'Ativos ordem codigo', 'ATIVOS_NOME' => 'Ativos ordem nome', 'INATIVOS_CODIGO' => 'Inativos ordem codigo', 'INATIVOS_NOME' => 'Inativos ordem nome', ) );
		
		
		//add compos do form
		$this->form->addFields([ $listcampos ],
							   [ $busca ]);
							   
		//ações do form
		$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'ico_find.png');					   
		
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');//fa:eraser red					   
		
		//cria a grid
		$this->datagrid = new TQuickGrid;
		$this->datagrid->style = 'width:100%';
		//$this->datagrid->makeScrollable();
		
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center', '10%');
		$this->datagrid->addQuickColumn('CPF / CNPJ', 'CPF_CNPJ', 'left', '20%');
		$this->datagrid->addQuickColumn('Nome', 'NOME', 'center', '40%');
		$this->datagrid->addQuickColumn('Tipo', 'TIPO', 'center', '10%');
		$this->datagrid->addQuickColumn('Telefone', 'TELEFONE', 'center', '10%');
		$this->datagrid->addQuickColumn('Usuário', 'NOME_USER', 'center', '10%');
		//$this->datagrid->addQuickColumn('User id', 'USER_ID', 'center', '10%');
		
		//cria as ações da grid
		$this->datagrid->addQuickAction('Editar' ,new TDataGridAction(array('fornecedoresForm', 'onEdit')), 'CODIGO' , 'fa:edit blue' );
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'CODIGO', 'far:trash-alt red' );
		}
		
		
		$this->datagrid->createModel();
		
		//add a grid no scroll 
		$scroll = new TScroll;
		$scroll->setSize('100%', '200');
		$scroll->add($this->datagrid);
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));		
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//cria o painel
		$painel = new TPanelGroup('Cadastro de Agente (T006)');
		
		$painel->add($this->form);
		$painel->add($scroll);
		//$painel->add($this->datagrid);
		$painel->add($this->pageNavigation);
		
		//barra footer
		$painel->addFooter(THBox::pack($btn_fechar));
		
		//ativar a rolagem horizontal dentro do corpo do painel
		$painel->getBody()->style = "overflow-x:auto" ;
		
		//add os compos no form 
		$this->formFields = array($busca, $listcampos, $btn_fechar);
		$this->form->setFields($this->formFields);
		
		//add o painel em tela
		$menuBread = new TXMLBreadCrumb('menu.xml', __CLASS__);
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($painel);
		
        parent::add($vbox);
		
	}//__construct
	
	
	
	/*
	Exclui uma 'fornecedor' após confirmação
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
	
			$rp_fornecedor = new TRepository('fornecedor');	
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_fornecedor->delete($criteria);	
			
			TTransaction::close();
			
			$this->onReload($param);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onSimDelete
	
	
	/*
	public static function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$rp_fornecedor = new TRepository('fornecedor');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $param['CODIGO'] ));
			
			$rp_fornecedor->delete($criteria);
			
			TTransaction::close();
			
			//$action = new TAction(array('FornecedoresListe', 'onReload'));
			//new TMessage('info', 'Registro apagado', $action);
			
			$this->onReload($param);
			
		}
		catch(Exception $e  )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
		
	}//onSimDelete
	*/
	/*
	  Questiona a exclusão de um 'fornecedor'
	*/
	public static function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			$fornecedor = new fornecedor($key);
			
			//Pega o nome do fornecedor
			$nome = $fornecedor->NOME;
			
			$onsim = new TAction( array('FornecedoresListe', 'onSimDelete'));
			$onsim->setParameter('CODIGO', $key);
			
			new TQuestion('Deseja apagar o item' . ' " ' . $nome . ' " ', $onsim);
			
			
			TTransaction::close();
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onDelete
	
	/*
	  Atualiza a página com os parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$rp_fornec = new TRepository('fornecedor');
			$criteria = new TCriteria;
			
			//set as propriedades
			//$criteria->setProperty('order','NOME');//NOME
			$criteria->setProperty('order','CODIGO');
			$criteria->setProperty('direction','DESC');//ASC
			$criteria->setProperty('limit',5);
			
			$criteria->setProperties($param);
			
			if(TSession::getValue('TS_filter_codigo') )
			{
				$criteria->add(TSession::getValue('TS_filter_codigo'));
			}
			
			if(TSession::getValue('TS_filter_nome') )
			{
				$criteria->add(TSession::getValue('TS_filter_nome'));
			}
			
			if(TSession::getValue('TS_filter_cpf_cnpj') )
			{
				$criteria->add(TSession::getValue('TS_filter_cpf_cnpj'));
			}

			if(TSession::getValue('TS_filter_todos') )
			{
				$criteria->add(TSession::getValue('TS_filter_todos'));
				$criteria->setProperty('order','CODIGO');//NOME
				$criteria->setProperty('direction','DESC');
			}
			
			if(TSession::getValue('TS_filter_todos_cod') )
			{
				$criteria->add(TSession::getValue('TS_filter_todos_cod'));
				$criteria->setProperty('order','CODIGO');
				$criteria->setProperty('direction','DESC');
			}
			
			if(TSession::getValue('TS_filter_ativos_nome') )
			{
				$criteria->add(TSession::getValue('TS_filter_ativos_nome'));
				$criteria->setProperty('order','NOME');
				$criteria->setProperty('direction','DESC');
			}
			
			if(TSession::getValue('TS_filter_ativos_cod') )
			{
				$criteria->add(TSession::getValue('TS_filter_ativos_cod'));
				$criteria->setProperty('order','CODIGO');
				$criteria->setProperty('direction','DESC');
			}
			
			if(TSession::getValue('TS_filter_inativos_cod') )
			{
				$criteria->add(TSession::getValue('TS_filter_inativos_cod'));
				$criteria->setProperty('order','CODIGO');
				$criteria->setProperty('direction','DESC');
			}
			
			if(TSession::getValue('TS_filter_inativos_nome') )
			{
				$criteria->add(TSession::getValue('TS_filter_inativos_nome'));
				$criteria->setProperty('order','NOME');
				$criteria->setProperty('direction','DESC');
			}
						
			//TRepository load
			$fornecedor =  $rp_fornec->load($criteria);
			
			$this->datagrid->clear();
			if($fornecedor)
			{
				foreach($fornecedor as $fornecedores)
				{
					$this->datagrid->addItem($fornecedores);
				}
			}	
			
			$criteria->resetProperties();
			$count = $rp_fornec->count( $criteria ); 

            $this->pageNavigation->setCount ( $count );
            $this->pageNavigation->setProperties ( $param );
            $this->pageNavigation->setlimit(5);
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onReload
	
	/*
	  Grava os filtros de busca na sessão e chama o onReload()
	*/
	public function onSearch($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			
			if($data->LISTCAMPOS == 'CODIGO' )
			{	
				$filter = new TFilter('CODIGO', '=', $data->BUSCA);
				TSession::setValue('TS_filter_codigo', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_codigo', NULL );
			}

			if($data->LISTCAMPOS == 'NOME' )
			{	
				$nome = '%'. $data->BUSCA .'%' ;
				$filter = new TFilter('NOME', 'LIKE', $nome);
				TSession::setValue('TS_filter_nome', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_nome', NULL );
			}
			
			if($data->LISTCAMPOS == 'CPF_CNPJ' )
			{	
				$filter = new TFilter('CPF_CNPJ', '=', $data->BUSCA);
				TSession::setValue('TS_filter_cpf_cnpj', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_cpf_cnpj', NULL );
			}

			if($data->LISTCAMPOS == 'TODOS' )
			{	
				$filter = new TFilter('CODIGO', '>=', 0);
				TSession::setValue('TS_filter_todos', $filter );
				TSession::setValue('TS_busca', NULL);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_todos', NULL );
			}
			
			if($data->LISTCAMPOS == 'TODOS_ORDEM_COD' )
			{	
				$filter = new TFilter('CODIGO', '>=', 0);
				TSession::setValue('TS_filter_todos_cod', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_todos_cod', NULL );
			}
			
			if($data->LISTCAMPOS == 'ATIVOS_NOME' )
			{	
				$filter = new TFilter('CODIGO', '>=', 0);
				$filter = new TFilter('STATUS', '=', 'A');
				TSession::setValue('TS_filter_ativos_nome', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_ativos_nome', NULL );
			}
			
			if($data->LISTCAMPOS == 'ATIVOS_CODIGO' )
			{	
				$filter = new TFilter('CODIGO', '>=', 0);
				$filter = new TFilter('STATUS', '=', 'A');
				TSession::setValue('TS_filter_ativos_cod', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_ativos_cod', NULL );
			}
			
			if($data->LISTCAMPOS == 'INATIVOS_CODIGO' )
			{	
				$filter = new TFilter('CODIGO', '>=', 0);
				$filter = new TFilter('STATUS', '=', 'I');
				TSession::setValue('TS_filter_inativos_cod', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_inativos_cod', NULL );
			}
			
			if($data->LISTCAMPOS == 'INATIVOS_NOME' )
			{	
				$filter = new TFilter('CODIGO', '>=', 0);
				$filter = new TFilter('STATUS', '=', 'I');
				TSession::setValue('TS_filter_inativos_nome', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_inativos_nome', NULL );
			}
				
			
			$this->form->setData($data);
			
			$this->onReload( $param );
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
		
	}//onSearch
	
	/*
	  Limpa o form e as variaveis de sessão
	*/
	public function onClear($param)
	{
		//$this->datagrid->clear();
		
		$data = $this->form->getData(); 
		$data->LISTCAMPOS = '';
		$data->BUSCA = '';
		$this->form->setData($data);
		
		TSession::setValue('TS_filter_codigo', NULL);
		TSession::setValue('TS_filter_nome', NULL);
		TSession::setValue('TS_filter_todos', NULL);
		TSession::setValue('TS_cm_af', NULL);
	    TSession::setValue('TS_cm_pec', NULL);
		
		TSession::setValue('TS_listcampos', NULL );
		//TSession::setValue('TS_listcampos', array() );
		
		
		//desatica o 'btn_pesquisar '
		TButton::disableField('formListeFornc', 'btn_pesquisar');

		$this->onReload($param);
		
	}//onClear
	
	/*
	Limpa a Tentry, após trocar
	o filtro de busca
	*/
	public static function onLimpaBusca($param)
	{
		$obj_std = new STDClass;
		$obj_std->BUSCA = '';
		$param['BUSCA'] = $obj_std->BUSCA;
		
		TForm::sendData('formListeFornc', $obj_std);
		
		//ativa o 'btn_pesquisar'
		TButton::enableField('formListeFornc', 'btn_pesquisar');
		
		//LIMPA A SESSÃO BUSCA
		TSession::setValue('TS_busca', NULL);
		
	}//onLimpaBusca
	
	/*
	  captura as parametros da URL e atualiza o onReload
	*/
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
	
	
	/*public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
	*/
		
	/*
	public function show()
	{
		if(!$this->loaded)
		{
			$this->onReload( func_get_arg(0) );
		}	
        parent::show(); 
		
	}//show
	
	*/	
		
}//TWindow 

?>