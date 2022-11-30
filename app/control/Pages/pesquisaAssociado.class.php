<?php

class pesquisaAssociado Extends TPage
{
	private $form;
	private $datagrid;
	
	public function __construct ()
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
		
		//cria as TEntry
		$nome      = new TEntry('NOME');
		$cpf       = new TEntry('CPF');
		$matricula = new TEntry('MATRICULA');
		$teste     = new TEntry('teste');
		
		//sessão
		$cpf->setValue(TSession::getValue('TS_busca_cpf'));
		$nome->setValue(TSession::getValue('TS_busca_nome'));
		$matricula->setValue(TSession::getValue('TS_busca_matricula'));
		
		//botões
		$btn_cadastrar = TButton::create('btn_cadastrar', array($this, 'onTeste'), 'Verificar Dados ou cadastrar associado', 'fa: fa-user blue' );
		
		//cria a data grid
        $this->datagrid = new TDataGrid;
		$this->datagrid->style = 'width:100%';
        
        // create the datagrid columns
        $cl_nome      = new TDataGridColumn('NOME', 'Nome', 'center', '80%');
        $cl_cpf       = new TDataGridColumn('CPF', 'Cpf', 'center', '10%');
        $cl_matricula = new TDataGridColumn('MATR_INTERNA', 'Matricula', 'left', '10%');
        
        // add the columns to the datagrid
        $this->datagrid->addColumn($cl_nome);
        $this->datagrid->addColumn($cl_cpf);
        $this->datagrid->addColumn($cl_matricula);
		
		//ações da grid
         // creates two datagrid actions
        
		$action2 = new TDataGridAction(array('ClienteForm', 'onEdit'));
        $action2->setLabel('Add Contrato');
        $action2->setImage('fa:  fa-plus-square blue');
        $action2->setField('MATR_INTERNA');
		
		 // add the actions to the datagrid
        $this->datagrid->addAction($action2);

		 // creates the datagrid model
        $this->datagrid->createModel();	
		
		//cria o form
		$this->form = new BootstrapFormBuilder('formChamado');
		$this->form->setFieldSizes('100%');
		//$this->form->setFormTitle('Formulário de chamado');	
		
		$row = $this->form->addFields(['Nome', $nome],
							          ['Cpf', $cpf]
									  );
		$row->layout = ['col-sm-6', 'col-sm-4', 'col-sm-4'];
		
		$row = $this->form->addFields(['Matricula', $matricula]
									  );
		$row->layout = ['col-sm-12'];
		
		
		//acão do form
		$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'fa: fa-search blue' );
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa: fa-eraser red' );
		
		
		//add os campos no form
		$this->formFields = array($nome, $matricula, $cpf, $btn_cadastrar);
		$this->form->setFields($this->formFields);
		
		// $this->formFields = array($NOME, $CODIGO);
		// $this->form->setFields($this->formFields);
		
		//
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		$painel = new TPanelGroup('Pesquisa Associado'); 
		$painel->addFooter(THBox::pack($btn_cadastrar));
		$painel->add($this->form);
		$painel->add($this->datagrid);
		$painel->add($this->pageNavigation);
		
		// ativar a rolagem horizontal dentro do corpo do painel
        $painel->getBody()->style = "overflow-x:auto;";
		
		
		//menu TXMLBreadCrumb
		$menu = new TXMLBreadCrumb('menu.xml', __CLASS__);
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
		$vbox->add($menu);
		$vbox->add($painel);
		//$vbox->add($this->pageNavigation);
		
		parent::add($vbox);
		
		
	}//__construct
	
	public function onReload($param)
	{
		try
		{
			TTransaction::open('db2');//db2 - finansys
			
			$data = $this->form->getData();
			
			$rp_cliente = new TRepository('cliente');
			
			$criteria = new TCriteria;
			$criteria->setProperty('direction','ASC');
                     
            $criteria->setProperties( $param );
            $criteria->setProperty('limit',8);//ATIVA A PAGENAVIGATION
       
			if(TSession::getValue('TS_filtro_cpf'))
			{
				$criteria->add(TSession::getValue('TS_filtro_cpf'));
			}
			
			if(TSession::getValue('TS_filtro_nome'))
			{
				$criteria->add(TSession::getValue('TS_filtro_nome'));
			}
			
			if(TSession::getValue('TS_filtro_matricula'))
			{
				$criteria->add(TSession::getValue('TS_filtro_matricula'));
			}
	

			$obj_cliente = $rp_cliente->load($criteria);
			
			//TSession::setValue('TS_cliente', $obj_cliente);
			
			$this->datagrid->Clear();
			if($obj_cliente)
			{
				// TSession::setValue('TS_cliente', $obj_cliente);	
		
				foreach($obj_cliente as $obj_clientes)
				{
					
					$this->datagrid->addItem($obj_clientes);
					
				}//foreach
				
			}//obj_cliente	

			$criteria->resetProperties();
			$count = $rp_cliente->count( $criteria ); 

            $this->pageNavigation->setCount ( $count );
            $this->pageNavigation->setProperties ( $param );
            $this->pageNavigation->setlimit(8);	
				
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onReload
	
	public function onSearch($param)
	{
			$data = $this->form->getData();
		
		try
		{	
			if($data->NOME)
			{
				$filtro = new TFilter('NOME', 'like', "%$data->NOME%");
				TSession::setValue('TS_filtro_nome', $filtro);
				TSession::setValue('TS_busca_nome', $data->NOME);
			}
			else
			{
				TSession::setValue('TS_filtro_nome', NULL);
			}		
			
			if($data->CPF)
			{
				$filtro	= new TFilter('CPF', '=', $data->CPF);
				TSession::setValue('TS_filtro_cpf', $filtro);
				TSession::setValue('TS_busca_cpf', $data->CPF);
			}
			else
			{
				TSession::setValue('TS_filtro_cpf', NULL);
			}
			
			if($data->MATRICULA)
			{
				$filtro = new TFilter('MATR_INTERNA', '=', $data->MATRICULA);
				TSession::setValue('TS_filtro_matricula', $filtro);
				TSession::setValue('TS_busca_matricula', $data->MATRICULA);
			}
			else
			{
				TSession::setValue('TS_filtro_matricula', NULL);
			}
			
			$this->onReload($param);
		}	
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}	
		
	}//onSearch
	
	public function onTeste($param)
	{
		
	}//onSearch
	
	public function onClear($param)
	{
		TSession::setValue('TS_busca_cpf', NULL);
		TSession::setValue('TS_filtro_cpf', NULL);
		TSession::setValue('TS_busca_nome', NULL);
		TSession::setValue('TS_filtro_nome', NULL);
		TSession::setValue('TS_busca_matricula', NULL);
		TSession::setValue('TS_filtro_matricula', NULL);
		$this->form->Clear();
		
		$this->onReload($param);
		
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
    }
	
}//TPage


?>