<?php
class Tipo_CtoListe Extends TPage
{
	private $form;
	private $datagrid;
	private $pagenavagation;
	
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
		
		$btn_incluir = TButton::create('btn_incluir',array('Tipo_CtoForm', 'onEdit'),'Incluir', 'fa: fa-plus blue' );
		
		$this->datagrid = new TQuickGrid;
		$this->datagrid->style = 'width:100%';
		
		$this->datagrid->addQuickColumn('Id', 'ID', 'center', '10%');
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center');
		$this->datagrid->addQuickColumn('Descrição', 'DESCRICAO', 'center');
		$this->datagrid->addQuickColumn('Tipo', 'TIPO', 'center');
		
		//cria as ações da grid
		$this->datagrid->addQuickAction('Editar',new TDataGridAction(array('Tipo_CtoForm', 'onEdit')), 'ID', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir',new TDataGridAction(array($this, 'onDelete')), 'ID', 'far:trash-alt red');
		}
		
		
		
		$this->datagrid->CreateModel();
		
		//add os campos do form
		$this->form->setFields(array($btn_fechar, $btn_incluir));
		
		//Empacotamento
		$painel = new TPanelGroup('Tipo de Contrato(T017)');
		$painel->add($this->datagrid);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_incluir, $btn_fechar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_fechar));
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
			
			$rp_tipo_cto = new TRepository('tipo_cto');
			$criteria = new TCriteria;
			
			$obj_tipo_cto =  $rp_tipo_cto->load($criteria);
			
			$this->datagrid->clear();
			if($obj_tipo_cto)
			{	
				foreach($obj_tipo_cto as $obj_tipo_ctos)
				{
					$this->datagrid->addItem($obj_tipo_ctos);
				}
			}
			
			TTransaction::close();
			
		}
		catch(Exception $e )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
	}//onReload
	
	/*
	  Exclui uma 'tipo_cto' após confirmação 
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['ID'];
			
			$rp_tipo_cto = new TRepository('tipo_cto');
			
			$criteria = new TCriteria;
			$criteria->add( new TFilter('ID', '=', $key));
			
			$rp_tipo_cto->delete($criteria);
			
			TTransaction::close();
			
			$this->onReload($param);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}
	
	/*
	  Questiona a exclusão de um 'tipo_cto'
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			$tipo_cto = new tipo_cto($key);
			$nome =  $tipo_cto->DESCRICAO;
			
			$onSim = new TAction(array($this, 'onSimDelete'));
			$onSim->setParameter('ID', $key);
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
}

?>