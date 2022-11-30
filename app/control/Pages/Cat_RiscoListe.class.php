<?php
class Cat_RiscoListe Extends TPage
{	
	private $form;
	private $datagrid;
	private $notbook;
	private $pageNavigation;
	
	public function __construct()
	{
		parent::__construct();
		//parent::setSize(0.99, 0.99);//X|Y
		
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
		
		//cria o form
		$this->form = new TForm;
		
		//cria os Btn
		$btn_fechar = new TButton('btn_fechar');
		$btn_fechar->setImage('fa: fa-power-off red');
		$btn_fechar->setAction(new TAction(array('PageInicial', 'onReload')), 'Fechar' );
		
		$btn_incluir = TButton::create('btn_incluir' ,array('Cat_RiscoForm', 'onEdit'), 'Incluir', 'fa: fa-plus blue' );
		
		// creates one datagrid
        $this->datagrid = new TQuickGrid;
		//$this->datagrid->style = "width:100%; margin-bottom: 10px";
        $this->datagrid->DisableDefaultClick(); 
		
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center', '10%');
		$this->datagrid->addQuickcolumn('Tipo', 'TIPO', 'center');
		$this->datagrid->addQuickcolumn('Descrição', 'DESC', 'center', '30%');
		$this->datagrid->addQuickcolumn('Dias Ini', 'INI', 'center');
		$this->datagrid->addQuickcolumn('Dias Fim', 'FIM', 'center');
		$this->datagrid->addQuickcolumn('%', 'PERC', 'center');
		$this->datagrid->addQuickcolumn('Conta', 'CONTA', 'center');
        
        //$this->datagrid->enablePopover('Descrição', '<b> {DESC} </b>');
		
        // add the actions
        $this->datagrid->addQuickAction('Editar',  new TDataGridAction(array('Cat_RiscoForm', 'onEdit') ), 'CODIGO', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{	
			$this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete') ), 'CODIGO', 'far:trash-alt red');
		}	
        
        
        // creates the datagrid model
        $this->datagrid->createModel();
       
		//cria o Navegador
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
        
		//cria o container geral e faz o empacotamento
        $panel = new TPanelGroup('Categoria de risco');
		$panel->add($this->datagrid);
        $panel->add($this->pageNavigation);
		
		
		if($permissao_geral['insercao'] == 1)
		{	
			$panel->addFooter(THBox::pack($btn_incluir,$btn_fechar));
		}
		else
		{
			$panel->addFooter(THBox::pack($btn_fechar));
		}		
		
		// ativar a rolagem horizontal dentro do corpo do painel
        $panel->getBody()->style = "overflow-x:auto;";
		
        
        //add os campos do formulario
        $this->form->setFields( array($btn_fechar, $btn_incluir) );
		
        //cria o TVBox
        $vbox = new TVBox;
        $vbox->style = 'width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);

        parent::add($vbox);
		//parent::add($painel);
		
	}//__construct'
	
	/*
	Recarrega a página com seus parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{	
			TTransaction::open('db2');
			//$data = $this->form->getData();
			
			$rp_catRisco = new TRepository('cat_risco');
			
			$criteria = new TCriteria;
			$criteria->setProperty('order', 'CODIGO');//ordena a grid em DESC 
            $criteria->setProperty('direction','DESC');
			$criteria->setProperty('limit',8);
			$criteria->setProperties($param);
			
			$obj_catRisco = $rp_catRisco->load($criteria);
			
			$this->datagrid->clear();
			if($obj_catRisco)
			{
				foreach($obj_catRisco as $obj_catRiscos)
				{
					$this->datagrid->addItem($obj_catRiscos);
				}
			}	
			
			//$this->form->setData($data);
			$criteria->resetProperties();
			$count = $rp_catRisco->count( $criteria ); 
			
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
	Questiona a exclusão de uma 'vaga'
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//pega o id da url
			$key = $param['key'];
			//instância o obj pelo 'id =>key'
			$catRisco = new cat_risco($key);
			//pega sua descrição 
			$nome = $catRisco->DESC;
			
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
	Exclui uma 'vaga' após confirmação 
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
			$rp_catRisco = new TRepository('cat_risco');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_catRisco->delete($criteria);
			
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