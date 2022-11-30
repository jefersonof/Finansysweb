<?php
class systemExcecoesListe Extends TPage
{
	private $form;
	private $datagrid;
	private $pageNavigation;
	
	public function __construct()
	{
		parent::__construct();
		
		//echo get_class($this);

		//cria o form
		$this->form  = new BootstrapFormBuilder('formExcecoesListe');
		$this->form->class='tform';
		$this->form->setFieldSizes('100%');
		
		//cria os atributos
		$NOME   = new TEntry('NOME');
		$CODIGO = new TEntry('CODIGO');
		
		//cria as sessões
		$NOME->setValue(TSession::getValue('TS_nome'));
		$CODIGO->setValue(TSession::getValue('TS_codigo'));
		
		//cria os botões
		//$btn_fechar = TButton::create('btn_fechar' ,array($this, 'onClear'), 'Limpar', 'fa:plus blue' );//user-plus blue
		
		//add compos do form
		$row = $this->form->addFields(['Nome', $NOME ],
							          ['Código', $CODIGO ]);
		$row->layout = ['col-sm-10', 'col-sm-2'];
							   
		//ações do form
		$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'ico_find.png');	
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');//fa:eraser red	
		
		//cria a grid
		$this->datagrid = new TQuickGrid;
		$this->datagrid->setHeight(150);
		$this->datagrid->style = 'width:100%';
		$this->datagrid->makeScrollable();
		
		$this->datagrid->addQuickColumn('Código', 'id', 'center', '10%');
		$this->datagrid->addQuickColumn('Nome', 'name', 'center', '70%');
		$this->datagrid->addQuickColumn('Grupo', 'nome_grupo', 'center', '20%');
		
		//cria as ações da grid
		//$this->datagrid->addQuickAction('Add Exceção' ,new TDataGridAction(array('systemExcecoesForm', 'onSearch')), 'id' , 'fa:edit blue' );
		$this->datagrid->addQuickAction('Add Exceção' ,new TDataGridAction(array('systemExcecoesForm', 'onEdit')), 'id' , 'fa:edit blue' );
		
		$this->datagrid->createModel();
		
		//add a grid no scroll 
		/*$scroll = new TScroll;
		$scroll->setSize('100%', '200');
		$scroll->add($this->datagrid);*/	
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));		
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//cria o painel
		$painel = new TPanelGroup('Lista dos Usuários');
		
		$painel->add($this->form);
		//$painel->add($scroll);
		$painel->add($this->datagrid);
		$painel->add($this->pageNavigation);
		
		//barra footer
		//$painel->addFooter(THBox::pack($btn_fechar));
		
		//ativar a rolagem horizontal dentro do corpo do painel
		$painel->getBody()->style = "overflow-x:auto" ;
		
		//add os compos no form 
		$this->formFields = array($NOME, $CODIGO);
		$this->form->setFields($this->formFields);
		
		//add o painel em tela
		$menuBread = new TXMLBreadCrumb('menu.xml', __CLASS__);
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($painel);
		
        parent::add($vbox);
		
		
		
	}//__construct
	
	public function onTeste()
	{
		
	}//onTeste
	
	public function onReload($param)
	{
		try
		{
			TTransaction::open('permission');
						
			$rp_system_user = new TRepository('SystemUser');
			$criteria       = new TCriteria;
			
			//set as propriedades
			//$criteria->setProperty('order','NOME');//NOME
			$criteria->setProperty('order','id');
			$criteria->setProperty('direction','DESC');//ASC
			$criteria->setProperty('limit',10);
			
			$criteria->setProperties($param);
			
			if(TSession::getValue('TS_filter_codigo') )
			{
				$criteria->add(TSession::getValue('TS_filter_codigo'));
			}
			
			if(TSession::getValue('TS_filter_nome') )
			{
				$criteria->add(TSession::getValue('TS_filter_nome'));
			}
			
			
						
			//TRepository load
			$system_user =  $rp_system_user->load($criteria);
			
			$this->datagrid->clear();
			
			$id_grupo = array();
			if($system_user)
			{
				foreach($system_user as $system_users)
				{
					//pega o id grupo do usuário
					$funcao = new funcao;
					$id_grupo = $funcao->grupoUsuario($system_users->id);
					
					//pega o nome do grupo do usuário
					$grupo = new SystemGroup($id_grupo);
					$nome_grupo = $grupo->name;
					
					
					//manda os dados para grid
					$system_users->nome_grupo = $nome_grupo; 
					$this->datagrid->addItem($system_users);
				}
			}	
			
			$criteria->resetProperties();
			$count = $rp_system_user->count( $criteria );

            $this->pageNavigation->setCount ( $count );
            $this->pageNavigation->setProperties ( $param );
            $this->pageNavigation->setlimit(10);
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
			
			$this->form->setData( $this->form->getData() );
		}
		
		
		
	}//onReload
	
	public function onSearch($param)
	{
		
		try
		{
		
		    //TTransaction::open('db');
		
			$data = $this->form->getData();
			
			
			if($data->CODIGO)
			{	
				$filter = new TFilter('id', '=', $data->CODIGO);
				TSession::setValue('TS_filter_codigo', $filter );
				TSession::setValue('TS_codigo', $data->CODIGO);
			}
			else
			{
				TSession::setValue('TS_filter_codigo', NULL );
			}

			if($data->NOME)
			{	
				//$nome = '%'. $data->NOME .'%' ;
				$nome = $data->NOME;
				$filter = new TFilter('name', 'LIKE', "%$data->NOME%");
				TSession::setValue('TS_filter_nome', $filter );
				TSession::setValue('TS_nome', $data->NOME);
			}
			else
			{
				TSession::setValue('TS_filter_nome', NULL );
			}
			
			
	
			
			$this->form->setData($data);
			
			$this->onReload( $param );
			//TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
		}	
		
	}//onSearch
	
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
	
	public static function onLimpaBusca()
	{
		
	}//onLimpaBusca
	
	
	public function onClear($param)
	{
		//limpa o form 
		$this->form->clear();
		
		//seta as variavés como nulas
		TSession::setValue('TS_codigo', NULL);
		TSession::setValue('TS_nome', NULL);
		TSession::setValue('TS_filter_nome', NULL);
		TSession::setValue('TS_filter_codigo', NULL);
		
		//recarega a pagina
		$this->onReload($param);
		
	}//onClear
	
	public function onDelete()
	{
		
	}//onDelete
		
}//TWindow 

?>