<?php
class CodRetBancoListe Extends TPage
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
		$this->form = new TForm;
		
		$btn_incluir = TButton::create('btn_incluir', array('CodRetBancoForm', 'onEdit'), 'Incluir', 'fa:plus blue');
		
		$btn_fechar = TButton::create('btn_fechar', array('PageInicial', 'onReload'),  'Fechar',  'fa: fa-power-off red');
		
		$this->datagrid = new TQuickGrid;
		$this->datagrid->style = 'width:100%';
		$this->datagrid->DisableDefaultClick();
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center', '20%');
		$descricao =  $this->datagrid->addQuickColumn('Descrição', 'DESCRICAO', 'center', '80%');
		
		//acões Inline
		$editaction = new TDataGridAction(array($this, 'onEditInline'));
        $editaction->setField('CODIGO');
        $descricao->setEditAction($editaction);
		
		//ações da Grid
		$this->datagrid->addQuickAction('Editar', new TDataGridAction(array('CodRetBancoForm', 'onEdit')), 'CODIGO', 'fa:edit blue'); 
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'CODIGO', 'far:trash-alt red'); 
		}
		
		
		$this->datagrid->createModel();
		
		//cria a datagrid
		//cria o Navegador
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//DEFINE OS CAMPOS DO FORMULÁRIO
        $this->form->setFields( array($btn_incluir, $btn_fechar) );
		
		//cria o menu BreadCrumb
		$breadcrumb = new TXMLBreadCrumb('menu.xml', __CLASS__ );
		
		//Empacotamento
		$painel = new TPanelGroup('Código de retorno dos bancos (T013) ');
		$painel->style = '100%';
		
		//$painel->add($breadcrumb);
		$painel->add($this->datagrid);
		$painel->add($this->pageNavigation);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_incluir, $btn_fechar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_fechar));
		}
		
		
		//ativar a rolagem horizontal dentro do corpo do painel
        $painel->getBody()->style = "overflow-x:auto;";
		
		$vbox = new TVBox;
		$vbox->style = 'width:100%';
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
			
			$rp_ret_banco = new TRepository('cod_ret_banco');//TRepository
			$criteria = new TCriteria;
			
			$criteria->setProperty('order', 'CODIGO');//ordena a grid em DESC 
            $criteria->setProperty('direction','DESC');
			$criteria->setProperty('limit',11);
			$criteria->setProperties($param);
			
			$cod_ret_banco = $rp_ret_banco->load($criteria);
			
			$this->datagrid->clear();
			if($cod_ret_banco)
			{	
				foreach( $cod_ret_banco as $cod_ret_bancos  )
				{
					$this->datagrid->addItem($cod_ret_bancos);
				}
			}
			
			$criteria->resetProperties();
			$count = $rp_ret_banco->count( $criteria ); 
			
			$this->pageNavigation->setCount( $count );
            $this->pageNavigation->setProperties( $param );
            $this->pageNavigation->setlimit(11); 
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback;
		}
		
	}//onReload
	
	/*
	Exclui uma 'cat_risco' após confirmação 
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
			$rp_cod_ret_banco = new TRepository('cod_ret_banco');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_cod_ret_banco->delete($criteria);
			
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
	Questiona a exclusão de uma 'cod_ret_banco'
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//pega o id da url
			$key = $param['key'];
			
			//instância o obj pelo 'id =>key'
			$cod_ret_banco = new cod_ret_banco($key);
			
			//pega sua descrição 
			$nome = $cod_ret_banco->DESCRICAO;
			
			$onSim = new TAction( array($this, 'onSimDelete'));
			$onSim->setParameter('CODIGO', $key);
					
			new TQuestion('Deseja apagar o item '. '"'.$nome.'"' ,$onSim);
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onDelete
	
	/*
	Edita um 'cod_ret_banco' direto na Grid
	*/
	function onEditInline($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            // open a transaction with database 'samples'
            TTransaction::open('db2');
            
            // instantiates object banco
            $retbanco = new cod_ret_banco($key);
            $retbanco->{$field} = $value;
			$retbanco->store();
			
            // close the transaction
            TTransaction::close();
            
            // reload the listing
            $this->onReload($param);
            // shows the success message
            new TMessage('info', "Salvo com sucesso");
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
		
    }//onEditInline
	
	
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
	
	
}//TPage

?>