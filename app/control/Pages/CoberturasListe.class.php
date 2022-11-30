<?php
class CoberturasListe Extends TPage
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
		
		//cria o form
		$this->form = new BootstrapFormBuilder('formCoberturas');//BootstrapFormBuilder
		$this->form->setFieldSizes('100%');
		$this->form->class = 'tform';
		
		//cria os atributos
		$tipocto = new TCombo('TIPOCTO');
		$tipocto->setValue(TSession::getValue('TS_tipocto'));
		
		//items combo
		$tipocto->additems(array('A' => 'Assistência Financeira' , 'P' => 'Pecúlio', 'S' => 'Seguro',));
		
		//cria os Btn
		
		$btn_fechar = TButton::create('btn_fechar', array('PageInicial', 'onReload'), 'Fechar', 'ico_close.png');
		
		$row = $this->form->addFields( [ new TLabel('Tipo de Contrato'), $tipocto ]);
		$row->layout = ['col-sm-12'];
		
		//cria as ações do form
		$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'fa:search' );
		
		$this->form->addAction ('Fechar', new TAction(array('PageInicial', 'onReload')), 'fa: fa-power-off red');
		
		//cria o datagrid
		$this->datagrid = new TQuickGrid;
		$this->datagrid->style = 'width:100%';
		//$this->datagrid->makeScrollable(); 
		//$this->datagrid->DisableDefaultClick(); //DisableDefaultClick
		//$this->datagrid->SetHeight(300);
		$this->datagrid->addQuickColumn('Código', 'CODIGO', 'center');
		$this->datagrid->addQuickColumn('Cobertura', 'COBERTURA', 'center', '20%');
		$this->datagrid->addQuickColumn('Proc. SUSEP', 'PROCESSO_SUSEP', 'center');
		$this->datagrid->addQuickColumn('Ent. Garantia', 'ENT_GAR', 'center');
		$this->datagrid->addQuickColumn('Tipo', 'TIPO', 'center');
		$this->datagrid->addQuickColumn('Grupo', 'GRUPO', 'center');
		$this->datagrid->addQuickColumn('Ramo', 'RAMO', 'center');
		
		//ações da grid 'Excluir' / 'Editar'
		$this->datagrid->addQuickAction('Editar' ,new TDataGridAction(array('CoberturasForm', 'onEdit')), 'CODIGO', 'fa:edit blue');
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir' ,new TDataGridAction(array($this, 'onDelete')), 'CODIGO', 'far:trash-alt red' );	
		}
		
		
		$this->datagrid->CreateModel();
		
		//cria o paginador
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters(); 
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//empacotamento
		$panelGroup = new TPanelGroup('Cadastro de Coberturas (T003) 22');
		
		$panelGroup->add($this->form);
		$panelGroup->add($this->datagrid);
		$panelGroup->add($this->pageNavigation);
		
		//rodape da pagina
		$panelGroup->addFooter(THBox::pack());
		
		//ativar a rolagem horizontal dentro do corpo do painel
        $panelGroup->getBody()->style = "overflow-x:auto;";
		
		
		//add o painel em tela
		$menuBread = new TXMLBreadCrumb('menu.xml', __CLASS__);
		//$menuBread->style = 'margin:0 0 0 30px';	
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($panelGroup);
		
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
			
			//$data = $this->form->getData();
			
			$rp_cobertura = new TRepository('cobertura');
			$criteria = new TCriteria;
			$criteria->setProperty('direction','ASC');
			$criteria->setProperty('limit',5);
			
			if(TSession::getValue('TS_filter_a'))
			{
				$criteria->add(TSession::getValue('TS_filter_a'));
			}
			
			if(TSession::getValue('TS_filter_p'))
			{
				$criteria->add(TSession::getValue('TS_filter_p'));
			}
			
			if(TSession::getValue('TS_filter_s'))
			{
				$criteria->add(TSession::getValue('TS_filter_s'));
			}

			$cobertura =  $rp_cobertura->load($criteria);	
			
			$this->datagrid->clear();
			foreach($cobertura as $coberturas)
			{
				$coberturas->ENT_GAR = $coberturas->entgarantidora->NOME;
				
				$this->datagrid->additem($coberturas);
			}
			
			$criteria->resetProperties();
			$count = $rp_cobertura->count( $criteria ); 

            $this->pageNavigation->setCount ( $count );
            $this->pageNavigation->setProperties ( $param );
            $this->pageNavigation->setlimit(5);
			
			//$this->form->setData($data);
			
			TTransaction::close();
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onReload
	
	/*
	  Questiona a exclusão de uma 'cobertura'
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			$cobertura =  new cobertura($key);
			$cobNome =  $cobertura->COBERTURA;
			
			$onSimDelete = new TAction( array($this ,'onSimDelete'));
			$onSimDelete->setParameter('CODIGO', $key);
			
			//$ac_onSim->setParameter('ID_PLANOS_SUSEP', $key);
			
			new TQuestion('Deseja apagar '. '"' . $cobNome . '"' , $onSimDelete);
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onDelete
	
	/*
	Exclui uma 'cobertura'
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['CODIGO'];
			$rp_cobertura = new TRepository('cobertura');
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $key));
			
			$rp_cobertura->delete($criteria);
			
			//new TMessage('indo', 'Registro apagado');
			
			TTransaction::close();
			
			$this->onReload($param);
			
			
		}
		catch(Excepition $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSimDelete
	
	/*
	Grava os filtros de busca na sessão e chama o onReload()
	*/
	public function onSearch($param)
	{
		try
		{
			$data = $this->form->getData();
			
			if($data->TIPOCTO == 'A')
			{
				$filter = new TFilter('TIPO', '=', $data->TIPOCTO);
				TSession::setValue('TS_filter_a', $filter);
				TSession::setValue('TS_tipocto', $data->TIPOCTO);
			}
			else
			{
				TSession::setValue('TS_filter_a', Null);
			}

			if($data->TIPOCTO == 'P')
			{
				$filter = new TFilter('TIPO', '=', $data->TIPOCTO);
				TSession::setValue('TS_filter_p', $filter);
				TSession::setValue('TS_tipocto', $data->TIPOCTO);
			}
			else
			{
				TSession::setValue('TS_filter_p', Null);
			}

			if($data->TIPOCTO == 'S')
			{
				$filter = new TFilter('TIPO', '=', $data->TIPOCTO);
				TSession::setValue('TS_filter_s', $filter);
				TSession::setValue('TS_tipocto', $data->TIPOCTO);
			}
			else
			{
				TSession::setValue('TS_filter_s', Null);
			}
			
			if($data->TIPOCTO == '')
			{	
				TSession::setValue('TS_tipocto', '');
			}
			
			$this->onReload($param);
			$this->form->setData($data);
			
		}//try
		catch(Excepition $e)
		{
			
		}
		
	}//onSearch
	
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
	Limpa o form e as variaveis de sessão
	*/
	public function onClear()
	{
		$data = $this->form->getData();
		$this->datagrid->clear();
		//$this->form->clear();
		
		//TSession::setValue('TS_tipocto', NUll);
		//TSession::setValue('TS_tipocto', array() );
		
		TSession::setValue('TS_filter_a', NUll);
		TSession::setValue('TS_filter_s', NUll);
		TSession::setValue('TS_filter_p', NUll);
		
		$this->form->setData($data);
		
	}//onClear	
	
	
	
		
	
	/*
	FILTRO DELPHI
	Coberturas List
	  begin
	  if ckTipoCto.Checked = true then
	  begin
		dmdbx.sdsCob.Filter := 'TIPO ='+ QuotedStr(dmdbx.sdsParametrosMOD_ARQ.AsString);
		dmdbx.sdsCob.Filtered := true;
	  end
	  else
	  begin
		dmdbx.sdsCob.Filtered := false;
	*/
	
	//https://www.youtube.com/watch?v=hwulmocF1GQ
	
}//TPage


?>
