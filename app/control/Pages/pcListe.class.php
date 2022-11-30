<?php
class pcListe extends TPage
{
	private $form;
	private $datagrid;
	
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
		
		//atributos
		$nome = new TEntry('nome');
		$ip   = new TEntry('ip');	
		$mac  = new TEntry('mac');
		
		//mantem o form preenchido
		$nome->setValue(TSession::getValue('busca_nome'));
		$ip->setValue(TSession::getValue('busca_ip'));
		$mac->setValue(TSession::getValue('busca_mac'));
		
		//form
		$this->form = new BootstrapFormBuilder('formListaDePc');
		$this->form->setFormTitle('Lista de computadores');
		$this->form->setFieldSizes('100%');
		
		$row = 	$this->form->addFields(['nome', $nome],
								       ['Ip', $ip],
								       ['Mac', $mac]
							          );	
		$row->layout = ['col-sm-8', 'col-sm-2','col-sm-2'];
		
		//acões do form
		$btn = $this->form->addAction('Buscar', new TAction(array($this, 'onSearch')), 'fa:search');
		$btn->class = 'btn btn-sm btn-primary';
		
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');
		
		$this->form->addAction('Incluir', new TAction(array('pcForm', 'onEdit')), 'bs:plus-sign green');
		
		//cria a datagrid
		$this->datagrid = new TQuickGrid;
		$this->datagrid->style = 'width: 100%';
		$this->datagrid->DisableDefaultClick();
		
		$this->datagrid->addQuickColumn('Id', 'id', 'center', '5%');
		$this->datagrid->addQuickcolumn('Nome', 'nome', 'center', '40%');
		$this->datagrid->addQuickcolumn('Setor', 'setor', 'center', '10%');
		$this->datagrid->addQuickcolumn('Computador', 'computador', 'center', '20%');
		$this->datagrid->addQuickcolumn('Ip', 'ip', 'center', '5%');
		$this->datagrid->addQuickcolumn('Cadastro', 'data_cad', 'center', '10%');
		$this->datagrid->addQuickcolumn('Mac', 'mac_address', 'center', '10%');
		//$this->datagrid->addQuickcolumn('Office', 'office', 'center', '10%');
		//$this->datagrid->addQuickcolumn('Observação', 'obs', 'center', '20%');
		
		//cria as ações da grid
		 // add the actions
        $this->datagrid->addQuickAction('Editar', new TDataGridAction(array('pcForm', 'onEdit')), 'id', 'fa:edit blue');
		
		$this->datagrid->addQuickaction('Deletar', new TDataGridAction(array($this, 'onDelete')), 'id', 'fa:trash-o red fa-lg');
		
		//CRIA A GRID EM TELA
		$this->datagrid->createModel();
		
		//add a grid no scroll 
		$this->scroll = new TScroll;
		$this->scroll->setSize('100%', '100%');
		$this->scroll->add($this->datagrid);
		
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));		
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//container
		$panel = new TPanelGroup;
        $panel->add($this->scroll);
        $panel->addFooter($this->pageNavigation);
		
		//add scroll horizontal 
		$panel->getBody()->style = 'overflow-x:auto';

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
		
		
	}//__construct
	
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('chamado');
			
			$key = $param['key'];
			
			$pc = new pc($key);
			$pc_nome = $pc->nome; 	
			
			$act_sim = new TAction(array($this, 'onSimDelete'));
			$act_sim->setParameter('id', $key);
			
			new TQuestion('Apagar os dados do pc do(a) ' . $pc_nome, $act_sim);
			
		}
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );	
		}
		
	}//onDelete
	
	public function onSimDelete($param)
	{
			try
			{
				TTransaction::open('chamado');
				
				$id = $param['id'];
				
				$pc = new pc($id);
				$pc->delete();
				
				TTransaction::close();
				
				$this->onReload($param);
			}
			catch(Exception $e)
			{
				TTransaction::rollback();
				new TMessage('error', $e->getMessage());
			}
			
	}//onSimDelete
	
	public function onReload($param)
	{
		try
		{
			TTransaction::open('chamado');
			
			$rp_pc    = new TRepository('pc');
			$criteria = new TCriteria;
			
			$criteria->setProperty('order', 'id');
		    $criteria->setProperty('direction','desc');
		   
		    $criteria->setProperties( $param );
		    $criteria->setProperty('limit',8);
			
			if(TSession::getValue('filter_nome'))
			{	
				$criteria->add(TSession::getValue('filter_nome'));
			}
			
			if(TSession::getValue('filter_ip'))
			{	
				$criteria->add(TSession::getValue('filter_ip'));
			}
			
			if(TSession::getValue('filter_mac'))
			{	
				$criteria->add(TSession::getValue('filter_mac'));
			}

			$lista_pcs = $rp_pc->load($criteria);
	
			$this->datagrid->clear();
			if($lista_pcs)
			{
				foreach($lista_pcs as $lista_pc)
				{
					$lista_pc->data_cad = TDate::date2br($lista_pc->data_cad);//FORMATA A DATA 
					$this->datagrid->addItem($lista_pc);
				}
			}

			$criteria->resetProperties();
			$count = $rp_pc->count($criteria);          
			  
			$this->pageNavigation->setCount ($count);
			$this->pageNavigation->setProperties ($param);
			$this->pageNavigation->setlimit(8);//numero de registros  	
			
			TTransaction::close();
			  $this->loaded = TRUE; 
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::close();
		}
		
	}//onReload
	
	public function onSearch($param)
	{
		$data = $this->form->getData();
		
		if($data->nome)
		{
			$filter = new TFilter('nome', 'LIKE', "%$data->nome%");
			TSession::setValue('filter_nome', $filter);
			TSession::setValue('busca_nome', $data->nome);
		}
		else
		{
			TSession::setValue('filter_nome', NULL);
			TSession::setValue('busca_nome', NULL);
		}

		if($data->ip)
		{
			$filter = new TFilter('ip', 'LIKE', "%$data->ip%");
			TSession::setValue('filter_ip' ,$filter);
			TSession::setValue('busca_ip' ,$data->ip);
		}
		else
		{
			TSession::setValue('filter_ip', NULL);
			TSession::setValue('busca_ip', NULL);	
		}

		if($data->mac)
		{
			$filter = new TFilter('mac_address', 'LIKE', "%$data->mac%");
			TSession::setValue('filter_mac', $filter);
			TSession::setValue('busca_mac', $data->mac);
		}
		else
		{
			TSession::setValue('filter_mac', NULL);
			TSession::setValue('busca_mac', NULL);
		}
		
		$this->form->setdata($data);
		
		$this->onReload($param);
		
	}//onSearch
	
	public function onClear($param)
	{
		$this->form->clear();
		TSession::setValue('filter_nome', NULL); 
		TSession::setValue('busca_nome', NULL); 
		TSession::setValue('filter_ip', NULL); 
		TSession::setValue('busca_ip', NULL); 
		TSession::setValue('busca_mac', NULL); 
		TSession::setValue('filter_mac', NULL); 
		
		$this->onReload($param);
		
	}//onClear
	
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