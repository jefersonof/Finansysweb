<?php
class Reg_FinListe Extends TPage 
{	
	private $form;
	private $datagrid;
	private $notbook;
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
		$this->form = new TForm('formRegimeFinan');
		
		//cria a datagrid
		$this->datagrid = new TQuickGrid;
		$this->datagrid->style = 'width:100%';
		
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center');
		$this->datagrid->addQuickcolumn('Regime', 'REGIME', 'center' );
		$this->datagrid->addQuickcolumn('Tabela', 'TABELA', 'center' );
		$this->datagrid->addQuickcolumn('Prazo Pgto', 'PRAZO_PGTO', 'center');
		
		//CRIA AS AÇÕES DA GRID
        $this->datagrid->addQuickAction('Editar', new TDataGridAction(array('Reg_FinForm', 'onEdit')), 'CODIGO', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickaction('Deletar', new TDataGridAction(array($this, 'onDelete')), 'CODIGO', 'far:trash-alt red');
		}
		
		//CRIA A GRID EM TELA
		$this->datagrid->createModel();
		
		//CRIA OS BOTÕES
		$btn_incluir = TButton::create('btn_incluir', array('Reg_FinForm', 'onEdit'), 'Incluir', 'fa: fa-plus blue' );
		
		$btn_fechar = TButton::create('btn_fechar', array('PageInicial', 'onReload'), 'Fechar', 'fa: fa-power-off red' );
		
		
		//DEFINE OS CAMPOS DO FORMULÁRIO
        $this->formFields = array($btn_incluir, $btn_fechar); 
		
        $this->form->setFields( $this->formFields );
		
		//cria o Navegador
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//empacotamento
		$painel = new TPanelGroup('Regime Financeiro (T012)');
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
		
		//add scroll horizontal
		$painel->getBody()->style = 'overflow-x:auto';
		
		
		//mostra em tela
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($painel);

        parent::add($vbox);
		
		//parent::add($painel);
		
		
	}//__construct'
	
	
	/*
	Exclui um Plano susep após confirmação 
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
			
			$rp_regFin = new TRepository('reg_fin');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_regFin->delete($criteria);
			
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
			$reg_fin = new reg_fin($key);
			//pega sua descrição 
			$nome = $reg_fin->REGIME;
			
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
	Recarrega a página com seus parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{
			
			TTransaction::open('db2');
			$data = $this->form->getData();
			
			$rp_regFin = new TRepository('reg_fin');
			
			$criteria = new TCriteria;
			$criteria->setProperty('order', 'CODIGO');//ordena a grid em DESC 
            $criteria->setProperty('direction','DESC');
			$criteria->setProperty('limit',8);
			$criteria->setProperties($param);
			
			$obj_regFin = $rp_regFin->load($criteria);
			
			$this->datagrid->clear();
			if($obj_regFin)
			{
				foreach($obj_regFin as $obj_regFins)
				{
					$this->datagrid->addItem($obj_regFins);
					// $data = $this->form->getData();
				    // $this->form->setData($data);
				}
			}	
			
			$this->form->setData($data);
			
			$criteria->resetProperties();
			$count = $rp_regFin->count( $criteria ); 
			
			$this->pageNavigation->setCount( $count );
            $this->pageNavigation->setProperties( $param );
            $this->pageNavigation->setlimit(8);
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}	
	}//onReload
	
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