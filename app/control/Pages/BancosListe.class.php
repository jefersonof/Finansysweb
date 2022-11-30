<?php

class BancosListe extends TPage
{
    private $form;
	private $datagrid;
	
	public function __construct()
	{
		parent:: __construct();
		
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
		//CRIA O FORM
		$this->form = new TForm('planos_susep');
		
		//CRIA OS BOTÕES
		$btn_incluir = TButton::create('btn_incluir', array('BancosForm', 'onEdit'),  'Incluir',  'fa: fa-plus blue');
		
		$btn_fechar  = TButton::create('btn_fechar', array('PageInicial', 'onReload'),  'Fechar',  'fa: fa-power-off red');//ico_add.png ** fa:save green
		
		//CRIA A DATAGRID
		$this->datagrid = new TQuickGrid;;
		
		$this->datagrid->DisableDefaultClick();
		$this->datagrid->style = "width:100%;margin-bottom: 10px";
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center', 60);
		$banco = $this->datagrid->addQuickColumn('Banco', 'BANCO', 'center', 600);
		
		//CRIA A AÇÃO DA GRID
		$this->datagrid->addQuickAction('Editar', new TDataGridAction(array('BancosForm', 'onEdit')), 'CODIGO', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'CODIGO', 'far:trash-alt red');
		}
		
		
		
		//acões Inline
		$editaction = new TDataGridAction(array($this, 'onEditInline'));
        $editaction->setField('CODIGO');
        $banco->setEditAction($editaction);
		
		/*$editaction2 = new TDataGridAction(array($this, 'onEditInline'));
        $editaction2->setField('CODIGO');
        $teste->setEditAction($editaction2);*/
		
		//ADD A GRID EM TELA
		$this->datagrid->createModel();
		
		//EMPACOTAMENTO
		$panelGroup = new TPanelGroup('Cadastro de Bancos (T016)');
		$panelGroup->add($this->datagrid);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$panelGroup->addFooter( THBox::pack($btn_incluir, $btn_fechar) );
		}
		else
		{
			$panelGroup->addFooter( THBox::pack($btn_fechar));
		}
		
		
		//ATIVAR A ROLAGEM HORIZONTAL DENTRO DO CORPO DO PAINEL
        $panelGroup->getBody()->style = "overflow-x:auto;";
		
		//DEFINE OS CAMPOS DO FORMULÁRIO
        $this->form->setFields(array($btn_fechar, $btn_incluir));
		
		//add o painel em tela
		$menuBread = new TXMLBreadCrumb('menu.xml', __CLASS__);
		$menuBread->setHomeController('PageInicial');
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($panelGroup);

        parent::add($vbox);
		
		
		
	}//__construct
	
	/*
	  Edita o nome d banco direto na Grid
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
            $banco = new banco($key);
            $banco->{$field} = $value;
			$banco->store();
			
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
	Recarrega a página com seus parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{	
		    TTransaction::open('db2');//
			
			$repository = new TRepository('banco');
			
			$criteria   = new TCriteria;
			
            $criteria->setProperty('order', 'CODIGO');//ordena a grid em DESC 
            $criteria->setProperty('direction','DESC');
			
		   
		    $objects2 = $repository->load( $criteria );
			
			$this->datagrid->clear();
		    if ($objects2)
		    {
				foreach ($objects2 as $object2)
				{ 
				   //$item = STDClass;
				   //$object2->TESTE = 'TESTE';
				   
				   $this->datagrid->addItem( $object2 );//ADD NA GRID
				   
				   $data = $this->form->getData();//mantem os dados no formulario
				   $this->form->setData($data);
					
				   //$this->form->clear();//Reseta a Busca
				}
		    }
			else
			{
				new TMessage('info', 'Sem bancos cadastrados');
			}	
			
			//echo 'teste' . $parametro;
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
		
	}//onReload
	
	/*
	Questiona a exclusão de um plano susep
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			
			$banco = new banco($key);
			
			$nome_banco   = $banco->BANCO;
			
			$ac_onSim = new TAction( array($this, 'onSimDelete'));
			$ac_onSim->setParameter('CODIGO', $key);
			
			new TQuestion('Deseja apagar o item '. '"' . $nome_banco .'"' , $ac_onSim);
			
			TTransaction::close();
		}//try
		catch(Exception $e)
		{
			new TMessage('info', $e->getMessage() );
			TTransaction::rollback();
		}	
		
	}//onDelete
	
	/*
	Exclui um Plano susep após confirmação 
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
	
			$rp_banco = new TRepository('banco');	
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_banco->delete($criteria);	
			
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
		if(!$this->loaded)
		{
			$this->onReload( func_get_arg(0) );
		}	
        parent::show(); 
		
	}//show	
   
}//TPage