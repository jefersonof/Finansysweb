<?php

class FormasPagListe extends TPage
{
    private $form;
	private $datagrid;
	private $pageNavigation;
	
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
		//CRIA O FORM
		$this->form = new TForm('formFormasPag');
		
		//CRIA OS BOTÕES
		$btn_incluir = TButton::create('btn_incluir', array('FormasPagForm', 'onEdit'),  'Incluir',  'fa: fa-plus blue');
		
		$btn_fechar = TButton::create('btn_fechar', array('PageInicial', 'onReload'),  'Fechar',  'fa: fa-power-off red');//ico_add.png ** fa:save green
		
		//CRIA A DATAGRID
		$this->datagrid = new TQuickGrid;;
		
		$this->datagrid->DisableDefaultClick();
		$this->datagrid->style = "width:100%;margin-bottom: 10px";
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center');
		$forma = $this->datagrid->addQuickColumn('Forma', 'FORMA', 'center');
		$this->datagrid->addQuickColumn('Liberação', 'LIB', 'center');
		$this->datagrid->addQuickColumn('Inclusão de contratos', 'CAD', 'center');
		
		//CRIA A AÇÃO DA GRID
		$this->datagrid->addQuickAction('Editar', new TDataGridAction(array('FormasPagForm', 'onEdit')), 'CODIGO', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'CODIGO', 'far:trash-alt red');
		}
		
		
		
		//EDIÇÃO INLINE
		$editaction = new TDataGridAction(array($this, 'onEditInline'));
        $editaction->setField('CODIGO');
        $forma->setEditAction($editaction);
		
		//ADD A GRID EM TELA
		$this->datagrid->createModel();
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//EMPACOTAMENTO
		$panelGroup = new TPanelGroup('Formas de Pagamento (T023)');
		$panelGroup->add($this->datagrid);
		$panelGroup->add($this->pageNavigation);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$panelGroup->addFooter( THBox::pack($btn_incluir, $btn_fechar) );
		}
		else
		{
			$panelGroup->addFooter( THBox::pack($btn_fechar) );
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
	Recarrega a página com seus parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{	
		    TTransaction::open('db2');//
			
			$rp_formaspag = new TRepository('formaspag');
			
			$criteria   = new TCriteria;
			
			//set as propriedades
			$criteria->setProperty('order','CODIGO');//NOME
			$criteria->setProperty('direction','ASC');
			$criteria->setProperty('limit',8);
			
			$criteria->setProperties($param);
		   
		    $obj_formaspag = $rp_formaspag->load( $criteria );
			
			$this->datagrid->clear();
		    if ($obj_formaspag)
		    {
				foreach ($obj_formaspag as $obj_formaspags)
				{ 
				   //$item = STDClass;
				   //$obj_motivo_cancs->TESTE = 'TESTE';
				   
				   $this->datagrid->addItem( $obj_formaspags );//ADD NA GRID
				   
				   $data = $this->form->getData();//mantem os dados no formulario
				   $this->form->setData($data);
					
				   //$this->form->clear();//Reseta a Busca
				}
		    }
			
			$criteria->resetProperties();
			$count = $rp_formaspag->count( $criteria ); 

            $this->pageNavigation->setCount ( $count );
            $this->pageNavigation->setProperties ( $param );
            $this->pageNavigation->setlimit(8);
			/*else
			{
				new TMessage('info', 'Sem registros cadastrados');
			}*/	
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
		
	}//onReload
	
	/*
	  Edita um 'formaspag' direto na Grid
	*/
	function onEditInline($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('db2');
            
            // instantiates object banco
            $banco = new formaspag($key);
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
	Questiona a exclusão de um plano susep
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			
			$formaspag = new formaspag($key);
			
			$nome   = $formaspag->FORMA;
			
			$ac_onSim = new TAction( array($this, 'onSimDelete'));
			$ac_onSim->setParameter('CODIGO', $key);
			
			new TQuestion('Deseja apagar o item '. '"' . $nome .'"' , $ac_onSim);
			
			TTransaction::close();
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}	
		
	}//onDelete
	
	/*
	Exclui um 'formaspag' após confirmação 
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
	
			$rp_formaspag = new TRepository('formaspag');	
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_formaspag->delete($criteria);	
			
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
