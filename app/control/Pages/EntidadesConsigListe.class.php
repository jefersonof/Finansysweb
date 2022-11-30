<?php
class EntidadesConsigListe Extends TPage
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
		$this->form  = new BootstrapFormBuilder('formEstipulante');
		$this->form->setFieldSizes('100%');
		$this->form->class='tform';
		
		//cria os atributos
		$razao_social = new TEntry('RAZAO_SOCIAL');
		$codigo       = new TEntry('CODIGO');
		
		//formatações
		$codigo->setSize('50%');
		
		//recupera valores digitados
		$razao_social->setValue( TSession::getValue('relacao_nome'));
		$codigo->setValue( TSession::getValue('relacao_codigo'));
		
		//add compos do form
		$row =  $this->form->addFields([new TLabel('Nome'), $razao_social ],
							           [new TLabel('Código'), $codigo ]);
		$row->layout = ['col-sm-4', 'col-sm-8'];							   
				
		//add as ações do form
		$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'fa:search');

		$this->form->addAction('limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');
		
		
		//cria a datagrid
		$this->datagrid = new TQuickGrid;
		
		// define the CSS class
        //$this->datagrid->class='tdatagrid_table customized-table';
        // import the CSS file
        //parent::include_css('app/resources/custom-table.css');
		
		$this->datagrid->style = 'width:100%';
		$this->datagrid->addQuickColumn('Código', 'COD_INT', 'center', '150');
		$this->datagrid->addQuickColumn('Nome', 'RAZAO_SOCIAL', 'center', '560');
		
		$this->datagrid->addQuickAction('Editar' , new TDataGridAction(array('EntidadesConsigForm', 'onEdit')), 'COD_INT', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir' ,new TDataGridAction(array($this, 'onDelete',)), 'COD_INT', 'far:trash-alt red' );
		}
		
		
		$this->datagrid->createModel();
		
		//cria os btn
		$btn_incluir = TButton::create('btn_incluir' ,array('EntidadesConsigForm', 'onEdit'), ('Incluir'), 'fa:plus blue' );
		
		$btn_fechar = TButton::create('btn_fechar' ,array('PageInicial', 'onReload',), '(Fechar)', 'fa: fa-power-off red' );
		
		//add os campos no form
		$this->formFields = array($btn_incluir, $btn_fechar, $razao_social, $codigo);
		$this->form->setFields($this->formFields);
		
		//cria o navegador
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->style = 'margin:0 0 0 120px';
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//Empacotamento
		$painel = new TPanelGroup('Estipulantes (T204)');
		$painel->add($this->form);
		$painel->add($this->datagrid);
		$painel->add($this->pageNavigation);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter( THBox::pack($btn_incluir, $btn_fechar));
		}
		else
		{
			$painel->addFooter( THBox::pack($btn_fechar) );
		}
		
		
		//ativar a rolagem horizontal dentro do corpo do painel
        $painel->getBody()->style = "overflow-x:auto;";
		
		//caixa vertical
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml',__CLASS__)); 
		$vbox->add($painel);
		
		parent::add($vbox);
		
	}//__construct
	
	/*
	Recarrega a página com seus parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//$data = $this->form->getData();
			
			$repository = new TRepository('entidades');
			
			$criteria   = new TCriteria;
			$criteria->add(new TFilter('TIPO', '=', 'C'));
			
			//set as propriedades
			$criteria->setProperty('order','RAZAO_SOCIAL');//NOME
			$criteria->setProperty('direction','ASC');
			$criteria->setProperty('limit',10);
			
			
			$criteria->setProperties($param);
			
			if(TSession::getValue('localiza_nome') )
			{
				$criteria->add(TSession::getValue('localiza_nome') );
			}
			
			if(TSession::getValue('localiza_codigo'))
			{
				$criteria->add(TSession::getValue('localiza_codigo'));
			}
				
			
			$objects = $repository->load( $criteria );

			$this->datagrid->clear();
			
			if( $objects)
			{
				foreach( $objects as $object )
				{
					$this->datagrid->addItem( $object );
					//$this
				}
			}
				
			$criteria->resetProperties();
			$count = $repository->count( $criteria ); 

            $this->pageNavigation->setCount ( $count );
            $this->pageNavigation->setProperties ( $param );
            $this->pageNavigation->setlimit(10); 
			
			//$this->form->setData($data);
			
			//DEFINE O MENUBREAD
			
			
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
				
		}
		
	}//onReload
	
	/*
	  Questiona a exclusão de uma 'entidades'
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			$entidade = new entidades($key);
			$nome_ent = $entidade->RAZAO_SOCIAL;
			
			$onSim = new TAction( array($this, 'onSimDelete'));
			$onSim->setParameter('COD_INT', $key);
			
			new TQuestion('Deseja apagar o registro '.'"'. $nome_ent .'"', $onSim);
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onDelete
	
	/*
	Exclui uma 'entidades' após confirmação 
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['COD_INT'];
			
			$rp_entidades = new TRepository('entidades');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('COD_INT', '=', $key));
			
			//deleta sem carregar pra memória
			$rp_entidades->delete($criteria);
			
			//new TMessage('info', 'Registro Apagado');
			
			TTransaction::close();
			$this->onReload($param);
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSimDelete
	
	
	/*
	Grava na sessão os filtos de busca e chama o onReload()
	*/
	public function onSearch()
	{
		try
		{
			$data = $this->form->getData();
			
			if($data->RAZAO_SOCIAL	)
			{
				$filter = new TFilter('RAZAO_SOCIAL', 'like', "%$data->RAZAO_SOCIAL%");
				TSession::setValue('localiza_nome', $filter);
				TSession::setValue('relacao_nome', $data->RAZAO_SOCIAL);
			}
			else
			{
				TSession::setValue('localiza_nome', NULL);
			}	
			
			if($data->CODIGO)
			{
				$filter = new TFilter('COD_INT', 'like', "$data->CODIGO");
				TSession::setValue('localiza_codigo', $filter);//
				TSession::setValue('relacao_codigo', $data->CODIGO);
			}
			else
			{
				TSession::setValue('localiza_codigo', NULL);
			}		
			
			$param = array();
			$param['offset'] = 0;
			$param['first_page'] = 1;
			$this->form->getdata();
			
			$this->onReload($param);	
			
			$this->form->setData($data);
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
			
	}//onSearch
	
	/*
	Limpa o form e as variaveis de sessão
	*/
	public function onClear($param)
	{	
        
		TSession::setValue('relacao_nome', NULL);
        TSession::setValue('localiza_nome', NULL);
		TSession::setValue('relacao_codigo', NULL);
        TSession::setValue('localiza_codigo', NULL);
		
        //TSession::setValue('localiza_codigo', NULL );
		
		//add TFilter
		$filter = new TFilter('TIPO', '=', "E");
	    
		
		$this->form->clear();
		$this->datagrid->clear();
		//$this->datagrid->Disable();
		
		$this->onReload( $param );
		
	}//onClear
	
	
	public function onClearFilter($param)
	{ 	
		//TRÁS A GRID VAZIA
		$filter = new TFilter('RAZAO_SOCIAL', 'like', "");
	    TSession::setValue('localiza_nome', $filter);
		TSession::setValue('relacao_nome', "");
		
		$this->onReload($param);
		
	}//onClearFilter
	
	public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
		
    }//show
	
	

	//public function onEstip($param)
	//{ 	
        
		
		/*
		$filter = new TFilter('TIPO', '=', "E");
	    TSession::setValue('TS_set_tipo', $filter);
	    TSession::setValue('TS_tipo', 'E');
		
		$ts_breadcrumb1 = 'Estipulante';
		TSession::setValue('TS_Bread1', $ts_breadcrumb1);
		*/
		
		
		/*
		//TRÁS A GRID VAZIA
		$filter = new TFilter('RAZAO_SOCIAL', 'like', "");
	    TSession::setValue('localiza_nome', $filter);
		TSession::setValue('relacao_nome', "");
		*/
		
		
		//define a legenda TBreadCrumb
		/*
		$ts_breadcrumb1 = 'Cadastro';
		$ts_breadcrumb2 = 'Seguradora';
		$ts_breadcrumb3 = 'Estipulante';
		
		TSession::setValue('TS_Bread1', $ts_breadcrumb1);
		TSession::setValue('TS_Bread2', $ts_breadcrumb2);
		TSession::setValue('TS_Bread3', $ts_breadcrumb3);
		*/
		
		//$this->onReload($param);
		
	//}//onEstip
	
	/*
	public function onConsig($param)
	{ 	
        $filter = new TFilter('RAZAO_SOCIAL', 'like', "");
	    TSession::setValue('localiza_nome', $filter);
		TSession::setValue('relacao_nome', "");

		$filter = new TFilter('TIPO', '=', "C");
	    TSession::setValue('TS_set_tipo', $filter);
		TSession::setValue('TS_tipo', 'C');
		
		//define a legenda TBreadCrumb
		TSession::setValue('TS_Bread1', 'Consignatários');
		
		$this->onReload($param);
		
	}//onConsig
	
	*/
	
	
	
	
}//TPage 

?>