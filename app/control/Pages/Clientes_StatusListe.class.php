<?php
class Clientes_StatusListe Extends TPage
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
		
		$this->form = new TForm;
		
		//cria os btn
		$btn_fechar = TButton::create('btn_fechar',array('PageInicial', 'onReload'),'Fechar', 'fa: fa-power-off red' );
		
		$btn_incluir = TButton::create('btn_incluir',array('Clientes_StatusForm', 'onEdit'),'Fechar', 'fa: fa-plus blue' );
		
		$this->datagrid = new TQuickGrid;
		$this->datagrid->style = 'width:100%';
		
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center', '10%');
		$this->datagrid->addQuickColumn('Status', 'STATUS', 'center');
		$this->datagrid->addQuickColumn('Novos', 'NOVOS', 'center');
		
		//cria as ações da grid
		$this->datagrid->addQuickAction('Editar',new TDataGridAction(array('Clientes_StatusForm', 'onEdit')), 'CODIGO', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir',new TDataGridAction(array($this, 'onDelete')), 'CODIGO', 'far:trash-alt red');
		}
		
		
		$this->datagrid->CreateModel();
		
		//add os campos do form
		$this->form->setFields(array($btn_fechar, $btn_incluir));
		
		//Empacotamento
		$painel = new TPanelGroup('Situação dos Associados(T014)');
		$painel->add($this->datagrid);
		
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_incluir, $btn_fechar) );
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_fechar) );
		}
		
		//Ativo Scroll Horizontal
		$painel->getBody()->style = 'overflow-x:auto';
		
		//menu breadCrumb
		$breadcrumb = new TXMLBreadCrumb('menu.xml', __CLASS__ );
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
		$vbox->add($breadcrumb);
		$vbox->add($painel);
		
		
		parent::add($vbox);
		
	}//__construct
	
	/*
	Atualiza a página com os parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$rp_clientes_status = new TRepository('clientes_status');
			$criteria = new TCriteria;
			
			$obj_clientes_status =  $rp_clientes_status->load($criteria);
			
			$this->datagrid->clear();
			if($obj_clientes_status)
			{	
				foreach($obj_clientes_status as $obj_clientes_statuss)
				{
					$this->datagrid->addItem($obj_clientes_statuss);
				}
			}
			
			TTransaction::close();
			
		}//try
		catch(Exception $e )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
	}//onReload
	
	/*
	Questiona a exclusão de um 'clientes_status'
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			$clientes_status = new clientes_status($key);
			$nome =  $clientes_status->STATUS;
			
			$onSim = new TAction(array($this, 'onSimDelete',));
			$onSim->setParameter('CODIGO', $key);
			new TQuestion('Deseja apagar o registro '.'"'. $nome.'"', $onSim);
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onDelete
	
	/*
	Exclui um 'clientes_status'
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
			
			$rp_clientes_status = new TRepository('clientes_status');
			
			$criteria = new TCriteria;
			$criteria->add( new TFilter('CODIGO', '=', $key));
			
			$rp_clientes_status->delete($criteria);
			
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
}

?>