<?php
class Planos_SusepListe Extends TPage 
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
		
		//cria os Btn
		$btn_fechar = new TButton('btn_fechar');
		$btn_fechar->setImage('fa: fa-power-off red');
		$btn_fechar->setAction(new TAction(array('PageInicial', 'onReload')), 'Fechar' );
		
		$btn_incluir = TButton::create('btn_incluir' ,array('Planos_SusepForm', 'onEdit'), 'Incluir', 'fa: fa-plus blue' );
		
		// creates one datagrid
        $this->datagrid = new TQuickGrid;//BootstrapDatagridWrapper
        $this->datagrid->style = 'width:100%';
		//$this->datagrid->setHeight(300);
        //$this->datagrid->makeScrollable();
        
		$this->datagrid->addQuickColumn('Id', 'ID_PLANOS_SUSEP', 'center');
		$this->datagrid->addQuickColumn('Fip.Cod', 'PLNCODIGO', 'center');
		$this->datagrid->addQuickColumn('Nro. Processo', 'PROCESSO', 'center', '30%');
		$this->datagrid->addQuickColumn('Plano/Benefício', 'DESCRICAO', 'center', '20%');
		$this->datagrid->addQuickColumn('Carregamento', 'CARREG', 'center');
		$this->datagrid->addQuickColumn('Ativo', 'ATIVO', 'left');
		$this->datagrid->addQuickColumn('Tipo', 'TIPO', 'left');
		$this->datagrid->addQuickColumn('Grupo', 'GRUPO', 'left');
		$this->datagrid->addQuickColumn('Ramo', 'RAMO', 'left');
        
        //$this->datagrid->enablePopover('Descrição', '<b> {DESC} </b>');
        
        
        //CRIA A AÇÃO NA GRID
		$this->datagrid->addQuickAction('Editar', new TDataGridAction(array('Planos_SusepForm', 'onEdit')), 'ID_PLANOS_SUSEP', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'ID_PLANOS_SUSEP', 'far:trash-alt red');
		}	
        
        //$this->datagrid->addQuickAction('View',   $action1, 'DESC', 'ico_find.png');
        //$this->datagrid->addQuickAction('Delete', $action2, 'CODIGO', 'fa:trash-o red fa-lg');
        
        // creates the datagrid model
        $this->datagrid->createModel();
       
		//cria o Navegador
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup('Cadastro de Planos SUSEP(T203)');
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
		
		//add scroll horizontal 
		$panel->getBody()->style = 'overflow-x:auto';
        
        //add os campos do formulario
        $this->form->setFields( array($btn_fechar, $btn_incluir) );
		
		//menu TBreadCrumb manual (esta pg esta em dois lugares no menu)
		$breadCrumb = new TBreadCrumb;
		$breadCrumb->setHomeController('PegeInicial');
		$breadCrumb->addHome();
		$breadCrumb->addItem('Cadastro');
		$breadCrumb->addItem('Previdência');
		$breadCrumb->addItem('Processo SUSEP Previdência');
		
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width:90%';
        //$vbox->add($breadCrumb);
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'Planos_SusepListe') );
        $vbox->add($panel);

        parent::add($vbox);
		//parent::add($painel);
		
		
	}//__construct
	
	/*
	Recarrega a página com seus parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{	
		    TTransaction::open('db2');//
			
			$repository2 = new TRepository('planos_susep');
			
			$criteria   = new TCriteria;
			$parametro  = TSession::getValue('TS_parametro'); 
			$parametro  = 'P'; 
       
            $criteria->setProperty('order', 'ID_PLANOS_SUSEP');//ordena a grid em DESC 
            //$criteria->setProperty('direction','ID_PLANOS_SUSEP');
            $criteria->setProperty('direction','DESC');
			$criteria->setProperty('limit',12);
			$criteria->setProperties($param);
			
			$criteria->add(new TFilter('TIPO', '=', $parametro));
		   
		    $objects2 = $repository2->load( $criteria );
			
			$this->datagrid->clear();
		    if ($objects2)
		    {
				foreach ($objects2 as $object2)
				{ 
				   $this->datagrid->addItem( $object2 );//ADD NA GRID
				   
				   $data = $this->form->getData();//mastem os dados no formulario
				   //$this->form->setData($data);
					
				   //$this->form->clear();//Reseta a Busca
				}
		    }
			
			//echo 'teste' . $parametro;
			$criteria->resetProperties();
			$count = $repository2->count( $criteria ); 
			
			$this->pageNavigation->setCount( $count );
            $this->pageNavigation->setProperties( $param );
            $this->pageNavigation->setlimit(12); 
			
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
			
			$planos_susep = new planos_susep($key);
			
			$desc_susep   = $planos_susep->DESCRICAO;
			$id_susep     = $planos_susep->ID_PLANOS_SUSEP;
			
			$ac_onSim = new TAction( array($this, 'onSimDelete'));
			$ac_onSim->setParameter('ID_PLANOS_SUSEP', $key);
			
			new TQuestion('Deseja apagar o item '. '"' . $desc_susep .'"' , $ac_onSim);
			
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
			
			$key = $param['ID_PLANOS_SUSEP'];
	
			$rp_planos = new TRepository('planos_susep');	
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('ID_PLANOS_SUSEP', '=', $key));
			
			$rp_planos->delete($criteria);	
			
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
	Seta o parâmetro do filtro processo susepe como 'S' =  seguro
	*/
	public function onParamSeg($param)
	{
		$parametro = 'S';
		TSession::setValue('TS_parametro', $parametro);//TSession::setValue('sale_items', $sale_items);
		$this->onReload($param);
		
	}//onParamSeg
	
	/*
	Seta o parâmetro do filtro processo susepe como 'P' =  peculho
	*/
	public function onParamPec($param)
	{
		$parametro = TSession::getValue('TS_parametro');
		$parametro  = 'P';
		TSession::setValue('TS_parametro', $parametro);
		$this->onReload($param);
		
	}//onParamPec
	
		
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
	
	/*
	
	Show
/*
	captura as parametros da URL e atualiza o onReload
*/


//Close
/*
fecha a TWindow
*/

//onSimDel
/*
Exclui um Plano susep após confirmação 
*/

//onDelete
/*
Questiona a exclusão de um plano susep
*/
	

	
	
}//TWindow


?>