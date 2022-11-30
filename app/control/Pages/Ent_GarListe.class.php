<?php
class Ent_GarListe Extends  TPage
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
		
		$this->form = new BootstrapFormBuilder('formEnt_Gar');
		//$this->form->setFormTitle('Entidades Garantidoras (T001) ');
		$this->form->setFieldSizes('100%');
		
		//cria os Btn
		$btn =  $btn_salvar = TButton::create('btn_salvar', ['Ent_GarForm' ,'onEdit'], 'Incluir', 'fa: fa-plus blue');// fa:save
		
		//cria a grid
		$this->datagrid = new TQuickGrid;
		$this->datagrid->style = 'width:100%';
		$this->datagrid->addQuickColumn('Código', 'CODIGO','center', '20%');
		$this->datagrid->addQuickColumn('Nome', 'NOME','center', '80%');
		
		//cria as ações da grid
		$this->datagrid->addQuickAction('Editar', new TDataGridAction(array('Ent_GarForm', 'onEdit')), 'CODIGO', 'fa:edit blue' );
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Apagar', new TDataGridAction(array($this, 'onDelete')), 'CODIGO', 'far:trash-alt red' );
		}
		
		
		$this->datagrid->createModel();
		
		//add grid no form
		$row = $this->form->addFields( [ $this->datagrid ] );
	    $row->layout = ['col-sm-12'];
		
		//cria o paginador
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//empacotamento
		$painel = new TPanelGroup('Entidades Garantidoras (T001)');
		$painel->add($this->form);
		$painel->add($this->pageNavigation);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_salvar));
		}
		
		
		
		// ativar a rolagem horizontal dentro do corpo do painel
        $painel->getBody()->style = "overflow-x:auto;";
		
		//add os campos no form
		$this->form->setFields([ $btn_salvar ]);
		
		
		$vbox = new TVBox;
		$vbox->style = '90%';
		$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__ ));
		$vbox->add($painel);
		
		parent::add($vbox);
		
	
	}//__construct
	
	public function onEdit()
	{
		
	}
	
	public function onReload($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$rp_ent_gar = new TRepository('ent_gar');
			$criteria   = new TCriteria;

			//set as propriedades
			$criteria->setProperty('order','CODIGO');
			$criteria->setProperty('direction','ASC');
			$criteria->setProperty('limit',2);
			
			$criteria->setProperties($param);	
			
			$ent_gar = $rp_ent_gar->load($criteria);
			
			$this->datagrid->clear();
			foreach($ent_gar as $ent_gars )
			{
				$this->datagrid->addItem($ent_gars);
			}
			
			$criteria->resetProperties();
			$count = $rp_ent_gar->count( $criteria ); 

            $this->pageNavigation->setCount ( $count );
            $this->pageNavigation->setProperties ( $param );
            $this->pageNavigation->setlimit(2);
			
			TTransaction::close();
			
		}//try
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onReload
	
	/*
	Exclui um 'ent_gar' após confirmação 
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
			
			$rp_ent_gar = new TRepository('ent_gar');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_ent_gar->delete($criteria);
			
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
	  Questiona a exclusão de um regime financeiro
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//pega o id da url
			$key = $param['key'];
			
			//instância o obj pelo 'id =>key'
			$ent_gar = new ent_gar($key);
			
			//pega sua descrição 
			$nome = $ent_gar->NOME;
			
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