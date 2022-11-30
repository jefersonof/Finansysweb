<?php

class RamosGruposListe Extends TPage
{
	private $form;
	private $grupo_list;
	
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
		
		//cria o form 
		$this->form = new BootstrapFormBuilder('formAssociado');
		$this->form->setFieldSizes('100%');
		
		//captura a aba ativa  
		$this->form->setTabAction( new TAction(array($this, 'onTabClick')));
		
		//cria os btn
		$btn_fechar = TButton::create('btn_fechar' ,array('PageInicial', 'onReload'), 'Fechar','fa: fa-power-off red' );
		$btn_incluirG = TButton::create('btn_incluirG' ,array('GruposForm', 'onEdit'), 'Incluir', 'fa: fa-plus blue' );
		$btn_incluirR = TButton::create('btn_incluirR' ,array('RamosForm', 'onEdit'), 'Incluir', 'fa: fa-plus blue' );	
		
		 //*** PAGE 'GRUPOS' ***//
		$this->form->appendPage('Grupos');//
		
		//cria a datagrid
		$this->grupo_list = new TQuickGrid ;//BootstrapDatagridWrapper 
		$this->grupo_list->style = 'width:100%' ; 
		$this->grupo_list->addQuickColumn('Código', 'CODIGO', 'center');
		$this->grupo_list->addQuickcolumn('Grupo', 'GRUPO', 'center', '50%');
		
		//ADD A AÇÕES DA GRID
		$this->grupo_list->addQuickAction('Editar'  , new TDataGridAction(array('GruposForm', 'onEdit')), 'CODIGO', 'fa:edit blue' );
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->grupo_list->addQuickAction('Exclui'  , new TDataGridAction(array($this, 'onDelGrupo')), 'CODIGO', 'far:trash-alt red' );
		}
		
		$this->grupo_list->CreateModel();
		
		$row = $this->form->addFields([ $this->grupo_list ]);
		$row->layout = ['col-sm-12'];
		
		if($permissao_geral['insercao'] == 1)
		{	
			$row = $this->form->addFields([$btn_incluirG] );
			$row->layout = ['col-sm-12'];
		}
		
		 //*** PAGE 'RAMOS' ***//
		$this->form->appendPage('Ramos');
		
		//cria a grid 
		$this->ramos_list = new TQuickGrid ;//BootstrapDatagridWrapper 
		$this->ramos_list->style = 'width:100%' ; 
		
		$this->ramos_list->addQuickColumn('Código', 'CODIGO', 'center');
		$this->ramos_list->addQuickcolumn('Ramo', 'RAMO', 'center', '50%');
		$this->ramos_list->addQuickcolumn('Grupo', 'Grupo', 'center');
		$this->ramos_list->addQuickcolumn('Grupo Ramo', 'GRUPORAMO', 'center');
		
		//ADD A AÇÕES DA GRID
		$this->ramos_list->addQuickAction('Editar'  , new TDataGridAction(array('RamosForm', 'onEdit')), 'CODIGO', 'fa:edit blue' );
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->ramos_list->addQuickAction('Exclui'  , new TDataGridAction(array($this, 'onDelRamo')), 'CODIGO', 'far:trash-alt red' );
		}
		
		
		$this->ramos_list->CreateModel();
		
		$row = $this->form->addFields([$this->ramos_list] );
		$row->layout = ['col-sm-12'];
		
		if($permissao_geral['insercao'] == 1)
		{	
			$row = $this->form->addFields([$btn_incluirR] );
			$row->layout = ['col-sm-12'];
		}
		
		
		//Empacotamento
		$painel = new TPanelGroup('Cadastro de Ramos e Grupos');
		$painel->add($this->form);
		//define rodapé
		$painel->addFooter($btn_fechar);
		
		$painel->getBody()->style = 'overflow-x:auto';//overflow-x:auto
		
		
		//DEFINE OS CAMPOS DO FORMULÁRIO
        $this->formFields = array($btn_fechar, $btn_incluirR, $btn_incluirG ); //, $numero_parcela, $valor_parc

        $this->form->setFields( $this->formFields );
		
		$menuBread = new TXMLBreadCrumb('menu.xml', __CLASS__);
		
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
		$vbox->add($menuBread);
		$vbox->add($painel);
		
		parent::add($vbox);
		
	}//function __construct
	
	/*
	  Recarrega a página com seus parâmetros atuais
	*/
	function onReload( $param )
	{
		try
		{	
		    TTransaction::open('db2');//
			
			//$data = $this->form->getData();
			
			//CARREGA DADOS DO GRUPO 
			$repository = new TRepository('seg_grupos');
			
			$criteria   = new TCriteria;
       
            $criteria->setProperty('order', 'GRUPO');//ordena a grid em DESC 
            $criteria->setProperty('direction','ASC');
		   
		    $objects = $repository->load( $criteria );
			
			$this->grupo_list->clear();
		    if ($objects)
		    {
				foreach ($objects as $object)
				{ 
				   
				   $this->grupo_list->addItem( $object );//ADD NA GRID
				   
				   $data = $this->form->getData();//mantem os dados no formulario
				   $this->form->setData($data);
					
				   //$this->form->clear();//Reseta a Busca
				}
		    }
		   
		    //** DADOS RAMOS PAGE 2 **/
			
			$repository2 = new TRepository('seg_ramos');
			
			$criteria2   = new TCriteria;
       
            $criteria2->setProperty('order', 'RAMO');//ordena a grid em DESC 
            $criteria2->setProperty('direction','ASC');
		   
		    $objects2 = $repository2->load( $criteria2 );
			
			
			$this->ramos_list->clear();
		    if ($objects)
		    {
				foreach ($objects2 as $object2)
				{ 
				  
				   // $std = new STDClass;
				   // $object2->TESTE1 = 'Teste1 Teste1 Teste1 Teste1 Teste1 Teste1 Teste1 ';
				   // $object2->TESTE2 = 'Teste2 Teste2 Teste2 Teste2 Teste2 Teste2 Teste2 ';
				   // $object2->TESTE3 = 'Teste3 Teste3 Teste3 Teste3 Teste3 Teste3 Teste3 ';
				   
				   $this->ramos_list->addItem( $object2 );//ADD NA GRID
				   
				   $data = $this->form->getData();//mastem os dados no formulario
				   $this->form->setData($data);
					
				   //$this->form->clear();//Reseta a Busca
				}
		    }
						
			$this->form->setCurrentPage(TSession::getValue('TS_current_page'));
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
		
	}//onReload
	
	/*
	Questiona a exclusão de um ramo
	*/
	public function onDelRamo($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			
			$ramos = new seg_ramos($key);
			$ramo = $ramos->RAMO;
			
			$onSim = new TAction(array($this, 'onSimRamos'));
			$onSim->setParameter('CODIGO', $key);
			
			new TQuestion('Deseja apagar o item '. '"'.$ramo.'"'  , $onSim);
			$this->form->setCurrentPage(1);
			//$this->notebook->setCurrentPage(1);
			
			//. '"'. $grupo .'"'
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
		
	}//onDelRamo
	
	/*
	Exclui um Ramo após confirmação 
	*/
	public function onSimRamos($param)
	{
		try 
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
			
			$rp_segRamos = new TRepository('seg_ramos');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_segRamos->delete($criteria);
			
			//new TMessage('info', 'Registro apagado');
			
			TTransaction::close();
			$this->onReload($param);
			$this->form->setCurrentPage(1);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onSimRamos
	
	/*
	Questiona a exclusão de um grupo
	*/
	public function onDelGrupo($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			
			$grupo = new seg_grupos($key);
			$grupo = $grupo->GRUPO;
			
			
			$onSim = new TAction(array($this, 'onSimGrupo'));
			$onSim->setParameter('CODIGO', $key);
			
			$onNao = new TAction(array($this, 'onNaoGrupo'));
			$onNao->setParameter('CODIGO', $key);
			
			new TQuestion('Deseja apagar o item '  . '"'. $grupo .'"'  , $onSim, $onNao);
			
			TTransaction::close();
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}	
	
	}//onDelGrupo
	
	/*
	Exclui um grupo após confirmação
	*/
	public function onSimGrupo($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
			
			$rp_segGrupos = new TRepository('seg_grupos');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_segGrupos->delete($criteria);
			
			//new TMessage('info', 'Registro apagado');
			
			TTransaction::close();
			$this->onReload($param);
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onSimGrupo
	
	/*
	Quando clicado no não do questionamento, não faz nada  
	*/
	public function onNaoGrupo($param)
	{
		
	}//onNaoGrupo
	
	/*
	Edição do registro Inline na datagrid 'erro'
	*/
	/*
	public function onEdit($param)
	{
		try
		{
			//obtem os parâmetros
			$field  = $param['field'];
			$key    = $param['key'];
			$value  = $param['value'];
			
			TTransaction::open('db2');
			
			$seg_grupos = new seg_grupos($key);
			$seg_grupos->{$field} = $value;
			$seg_grupos->store();
			
			TTransaction::close();
			
			$this->onReload($param);
			
			new TMessage('info', 'certo');
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onEdit
	
	*/

	
	/*
	ação do botão cancelar da pg 'RamosForm' - 
	chama o onReload e mantem na 'pg_ramos' - 
	*/
	public function onCarregar($param)
	{
		$this->onReload($param);
		//$this->notebook->setCurrentPage(1);
	}//onCarregar
	
	/*
	  captura as parametros da URL e atualiza o onReload
	*/
	function show()
	{
		if(!$this->loaded)
		{
			$this->onReload( func_get_arg(0) );
		}	
        parent::show(); 
	}//show
	
	/**
     * Executed when the user clicks over the tab
     */
    public static function onTabClick($param)
    {
        //new TMessage('info', '<b>You have clicked at the tab</b>:  <br><br>' . $param['current_page'] . str_replace(',', '<br>', json_encode($param)));
		
		//grava a aba na sessão a cada troca 
		TSession::setValue('TS_current_page', $param['current_page'] );
		
    }
	
	/*
	public function onListar( $param )
	{
		try
		{	
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			$repository = new TRepository('seg_grupos');
			
			$criteria   = new TCriteria;
       
            //$criteria->setProperty('order', 'nome');//ordena a grid em DESC 
            $criteria->setProperty('direction','ASC');
		   
		    $objects = $repository->load( $criteria );
			
			$this->grupo_list->clear();
		   if ($objects)
		   {
				foreach ($objects as $object)
				{
				   //$object->data_cad = TDate::date2br($object->data_cad);//FORMATA A DATA  
				   $this->grupo_list->addItem( $object );//ADD NA GRID
				   
				   $data = $this->form->getData();//mastem os dados no formulario
				   $this->form->setData($data);
					
				   //$this->form->clear();//Reseta a Busca
				}
		   }
		
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
		
	}//onListar
	*/
	
}//TWindow

?>