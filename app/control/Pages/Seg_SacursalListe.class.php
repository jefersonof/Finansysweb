<?php
class Seg_SacursalListe Extends TPage
{	
	private $datagrid;
	private $form;
	
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
		$this->form = new TForm('formListeSacursal'); 
		
		//cria os botões
		$btn_incluir = TButton::create('btn_incluir' ,array('Seg_SacursalForm' ,'onEdit'), 'Incluir', 'fa: fa-plus blue' );
		
		$btn_fechar = TButton::create('btn_fechar' ,array('PageInicial' ,'onReload'), ('Fechar'), 'fa: fa-power-off red' );
		
		//cria a grid
		$this->datagrid = new TQuickGrid;
		$this->datagrid->disableDefaultClick();
		$this->datagrid->style = 'width:100%';
		
		//add as colunas da grid
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center', '20%');
		$this->datagrid->addQuickColumn('Sacural', 'SACURSAL', 'center');
		
		//cria a ação da grid
		$this->datagrid->addQuickaction('Editar' ,new TDataGridAction(array('Seg_SacursalForm','onEdit',)), 'CODIGO', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickaction('Excluir' ,new TDataGridAction(array($this, 'onDelete')), 'CODIGO', 'far:trash-alt red');
		}
		
		//mostra a grid em tela
		$this->datagrid->createModel();
		
		//add os compos do form
		$this->formFields = array($btn_incluir, $btn_fechar);
		$this->form->setFields($this->formFields);
		
		//Empacotamento
		//add o form dentro do painel
		$painel = new TPanelGroup('Cadastro de Sacursais');
		$painel->add($this->datagrid);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_incluir, $btn_fechar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_fechar));
		}
		
		//Ativa o scroll horizontal
		$painel->getBody()->style = 'overflow-x:auto';
		
		$menuBread = new TXMLBreadCrumb('menu.xml', __CLASS__);
		
		//mostra o painel em tela
		$vbox = new TVBox; 
		$vbox->style = 'width:90%';
		$vbox->add($menuBread);
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
			
			$rp_sacursal = new TRepository('seg_sacursal');
			
			$criteria = new TCriteria;
			$criteria->setProperty('order', 'CODIGO');
			$criteria->setProperty('direction','DESC');
			
			$obj_sacursal = $rp_sacursal->load($criteria);
			
			$this->datagrid->clear();
			
			if($obj_sacursal)
			{	
				foreach($obj_sacursal as $obj_sacursals  )
				{
					$this->datagrid->addItem($obj_sacursals); 
				}
			}
 			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
		
	}//onReload
	
	
	/*
	  Exclui uma 'seg_sacursal' após confirmação 
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
			
			$rp_sacursal = new TRepository('seg_sacursal');
			
			$criteria = new TCriteria;
			$criteria->add( new TFilter('CODIGO', '=', $key));
			
			$rp_sacursal->delete($criteria);
			
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
	  Questiona a exclusão de um 'seg_sacursal'
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			$sacursal = new seg_sacursal($key);
			$nome_sacursal =  $sacursal->SACURSAL;
			
			$onSim = new TAction(array($this, 'onSimDelete',));
			$onSim->setParameter('CODIGO', $key);
			new TQuestion('Deseja apagar o registro '.'"'. $nome_sacursal.'"', $onSim);
			
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

}//TWindow


?>