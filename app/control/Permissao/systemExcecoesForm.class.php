<?php
class systemExcecoesForm Extends TPage
{
	 protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $saveButton;
	
	/*private $form;
	private $datagrid;
	private $pageNavigation;*/
	
	// trait with onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;
	
	public function __construct()
	{
		parent::__construct();
		
		//PERMISSÃO DE USUÁRIO
		
		//pega as permissões
		$id_grupo   = TSession::getValue('TS_id_grupo');
		$id_usuario = TSession::getValue('TS_user_id');
		$nome_user  = TSession::getValue('TS_user_name');	
		
		//var_dump ($id_grupo);
		
		//pega o nome da classe
		$nome_classe =  get_class($this);
		//var_dump($nome_classe);
		
		
		//cria o form
		$this->form  = new BootstrapFormBuilder('formExcecoes');
		$this->form->class='tform';
		$this->form->setFieldSizes('100%');
		
		//cria os atributos
		$busca      = new TEntry('BUSCA');
		$id_usuario = new TEntry('ID_USUARIO');
		$usuario    = new TEntry('USUARIO');
		$id_grupo   = new TEntry('ID_GRUPO');
		$grupo      = new TEntry('GRUPO');
		$busca      = new TEntry('BUSCA');
		
		//cria as sessões
		$busca->setValue(TSession::getValue('TS_busca'));
		
		//cria os botões
		// creates the update collection button
        $this->saveButton = new TButton('update_collection');
        $this->saveButton->setAction(new TAction(array($this, 'onSave')), 'Save');
        $this->saveButton->setImage('fa:save green');
        //$this->formgrid->addField($this->saveButton);
		
		$btn_voltar    = TButton::create('btn_voltar' ,array('systemExcecoesListe', 'onReload'), 'Voltar', 'fa: fa-arrow-left' );
		
		$btn_pesquisar = TButton::create('btn_pesquisar', array($this, 'onSearch'), 'Buscar', 'fa:search blue');
		//$btn_clear     = TButton::create('btn_incluir', array($this, 'onTeste'),  'Limpar', 'fa:eraser red');
		$btn_permissao = TButton::create('btn_permissao', array($this, 'onAddPermissao'),  'Permissão do Grupo', 'fa:plus blue');
		
		//formatações
		$busca->placeHolder = 'Pesquisar...';
		//TButton::disableField('formExcecoes', 'btn_permissao' );
		
		//cria a grid
			// creates one datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
		
		//$this->datagrid = new TQuickGrid;
        $this->datagrid->style = 'width: 100%';
        
        // make scrollable and define height
        $this->datagrid->setHeight(200);
        $this->datagrid->makeScrollable();
		$this->datagrid->DisableDefaultClick;
        
        // create the datagrid columns
	 	 //$id              = new TDataGridColumn('id', 'ID', 'left', '10%');//35%
	    $id              = new TDataGridColumn('system_program_id', 'ID', 'left', '10%');
        $id_programa     = new TDataGridColumn('programa_nome', 'Programa', 'left', '60%');
        $acesso          = new TDataGridColumn('acesso', 'Acesso', 'left', '10%');
		$insercao        = new TDataGridColumn('insercao', 'Inserção', 'left', '10%');
		$delecao         = new TDataGridColumn('delecao', 'Deleção', 'left', '10%');
		
		// creates datagrid actions
        //$this->datagrid->addQuickAction('Teste', new TDataGridAction(array($this, 'onView')), 'system_program_id', 'fa:check-circle-o green');
        //$this->datagrid->addQuickAction('Teste', new TDataGridAction(array($this, 'onTeste2')), 'system_program_id', 'fa:check-circle-o green');
        
        //$this->datagrid->addQuickAction('Select', new TDataGridAction(array($this, 'onSelect')), 'id', 'fa:check-circle-o green');
        
		// add the columns to the datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($id_programa);
        $this->datagrid->addColumn($acesso);
        $this->datagrid->addColumn($insercao);
        $this->datagrid->addColumn($delecao);
		
		$this->datagrid->createModel();
		
		
		//add compos do form
		$row = $this->form->addFields(['Usuário', $usuario],
									  ['Grupo', $grupo]);
		$row->layout = ['col-sm-9', 'col-sm-3'];
		
		//Linha oculta
		$row = $this->form->addFields([ $id_usuario],
									  [$id_grupo] );
		$row->layout = ['col-sm-9', 'col-sm-3'];
							   
		//ações do form
		/*$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'ico_find.png');	
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');*/				   
		
		$this->form->addContent( [new TFormSeparator('Lista dos Programas')] );	
		$row = $this->form->addFields(['Buscar', $busca] );
		$row->layout = ['col-sm-12'];
		
		//barra menu coberturas
		$row = $this->form->addFields([$btn_pesquisar], [$btn_permissao] );
		$row->layout = ['col-sm-1', 'col-sm-1', 'col-sm-1'];
		//$row->style = ' background:whiteSmoke; border:1px solid #cccccc';
		//$row->style = ' background:whiteSmoke; border:1px solid #cccccc; padding: 3px;padding: 5px';
		//$row->style = 'background:#D5D5D5; margin:0 0 0 0';
		$row->style = 'background:whiteSmoke; border:1px solid #cccccc; margin:0 0 0 0; padding: 2px;';
		
		// $row = $this->form->addFields(['', $btn_pesquisar],
									  // ['', $btn_clear ] );
		// //$row->layout = ['col-sm-6', 'col-sm-6'];
		
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		//$this->pageNavigation->style = 'margin:0 0 0 150px';
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));		
		
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
				
		//cria o painel
		$painel = new TPanelGroup('Lista de permissões especiais');
		
		$this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);
		
		// define the datagrid transformer method
        $this->setTransformer(array($this, 'onBeforeLoad'));
		
		$painel->add($this->form);
		$painel->add($this->formgrid);//
		//$painel->add($this->datagrid);//
		$painel->add($this->pageNavigation);
		
		//barra footer
		$painel->addFooter(THBox::pack($btn_voltar, $this->saveButton));//btn_fechar
		
		//ativar a rolagem horizontal dentro do corpo do painel
		$painel->getBody()->style = "overflow-x:auto" ;
		
		//add os compos no form 
		$this->formFields = array($busca, $id_usuario, $usuario, $btn_voltar, $this->saveButton, $grupo, $id_grupo);
		$this->form->setFields($this->formFields);
		
		
		//add o painel em tela
		$menuBread = new TXMLBreadCrumb('menu.xml', 'systemExcecoesListe' );
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($painel);
		
        parent::add($vbox);
		
	}//__construct
	
	public function onSave($param)
	{	
		$data_form = $this->form->getData();
		
		$data = $this->formgrid->getData(); // get datagrid form data
        $this->formgrid->setData($data); // keep the form filled
		
		$ts_data  = TSession::getValue('TS_data');
		
		var_dump($data);
		
        try
        {
            // open transaction
            TTransaction::open('permission');
            
            // iterate datagrid form objects
            foreach ($this->formgrid->getFields() as $name => $field)
            {
                
				if ($field instanceof TCheckButton)
				{
					$parts = explode('_', $name);
					$id = end($parts);
					$nome_campo = $parts[0];
									
					if ($nome_campo == 'acesso')
					{
					   $object = SystemUserGroupProgram::find($id);
					   if ($object)
					   {
							$object->acesso   =  $field->getValue(); 
							$object->insercao =  $data->{"insercao_$id"}; 
							$object->delecao  =  $data->{"delecao_$id"};
							
							if($object->acesso == '')
							{
								$object->acesso = 0;
							}
							
							if($object->insercao == '')
							{
								$object->insercao = 0;
							}

							if($object->delecao == '')
							{
								$object->delecao = 0;
							}	
													
							$object->store();
							
							/*
							$object->acesso   =  $field->getValue(); 
							$object->insercao =  $data->{"insercao_$id"}; 
							$object->delecao  =  $data->{"delecao_$id"};*/
													
							$object->store();
							
							//var_dump($data);
						}
					}
				}
				
            }//foreach
            new TMessage('info', AdiantiCoreTranslator::translate('Record updated'));
            
            // close transaction
            TTransaction::close();
			
			//manda os dados para o form
			
			
			/*TForm::sendData('formExcecoes', $obj);*/
			$data_form->ID_USUARIO = $ts_data->ID_USUARIO;
			$data_form->USUARIO = $ts_data->USUARIO;
			$data_form->ID_GRUPO = $ts_data->ID_GRUPO;
			$data_form->GRUPO = $ts_data->GRUPO;
			$this->form->setData($data_form);
			
			
        }
        catch (Exception $e)
        {
            // show the exception message
            new TMessage('error', $e->getMessage());
        }
	}
	
	/**
     * Run before the datagrid is loaded
     */
    public function onBeforeLoad($objects, $param)
    {
        // update the action parameters to pass the current page to action
        // without this, the action will only work for the first page
        $saveAction = $this->saveButton->getAction();
        $saveAction->setParameters($param); // important!
        
        $gridfields = array( $this->saveButton );
        
        foreach ($objects as $object)
        {
            $object->acesso = new TCheckButton('acesso' . '_' . $object->id);
            $object->acesso->setValue( $object->acesso );
			$object->acesso->setIndexValue('1');
            $gridfields[] = $object->acesso;// important
			
			$object->insercao = new TCheckButton('insercao' . '_' . $object->id);
            $object->insercao->setValue( $object->insercao );
			$object->insercao->setIndexValue('1');
            $gridfields[] = $object->insercao; // important
			
			$object->delecao = new TCheckButton('delecao' . '_' . $object->id);
            $object->delecao->setValue( $object->delecao );
			$object->delecao->setIndexValue('1');
            $gridfields[] = $object->delecao;// important
			
        }
		
        $this->formgrid->setFields($gridfields);
		
    }//onBeforeLoad
	
	function onView($param)
    {
        // get the parameter and shows the message
        $key=$param['key'];
        new TMessage('info', "The name is : $key");
    }
	
	public  function onTeste2($param)
	{	
		
		$data = $this->form->getData();
		
		
		
		//if($param['acesso'] == 'sim' )
		if($data->acesso == 'sim' )
		{
			new TMessage('info', 'Sim foi selecionado');
			
		}

		if($data->acesso == 'nao' )
		{
			new TMessage('info', 'nao foi selecionado');
			
		}	
		
	}//onTeste2
	
	public function onTeste($param)
	{	
		//new TMessage('info', 'OBJ' . $param['key'] );
		
		new TMessage('info', 'Meu nome é ' . get_class($this) ); 
		
	}//onTeste
	
	/*
		add as permissões padrões do grupo do usuário
	*/
	public function onAddPermissao($param)
	{
		try
		{
			TTransaction::open('permission');
			//pega os dados do form e limpa a sessão
			$data = $this->form->getData();
			TSession::setValue('TS_permissao', NULL);
			
			//pega o grupo do user
			$id_grupo = $data->ID_GRUPO;
			
			//var_dump($id_grupo);
			
			if($id_grupo == "Não cadastrado")
			{
				throw new exception(' Usuário sem grupo cadastrado ');
			}	
			
			//instacia o grupo
			$systemGroup = new SystemGroup($id_grupo);
			
			//percorre as permissões padrões do grupo de user
			$permissao_grupo = array();
			 foreach ($systemGroup->getSystemPrograms() as $program)
			{
				$permissao_grupo[$program->id]    = $program->id;
				//$permissao_grupo[$program->id]    = $program->toArray();
			   
			}
			//var_dump ( $permissao_grupo ) . '<br>';
			
			//se ñ tiver programas cadastrados no grupo da um aviso
			if(empty($permissao_grupo))
			{
				throw new exception ('Adicione programas ao grupo e tente novamente');
			}	
			
			
			
			//percorre as permissões especiais do user
			$rp_usergroup = new TRepository('SystemUserGroupProgram');
			$criteria = new TCriteria;
			$criteria->add(new TFilter('system_user_id', '=', $data->ID_USUARIO));
			
			$usergroupprogram = $rp_usergroup->load($criteria);	
			
			$permissao_user = array();
			foreach($usergroupprogram as $usergroupprograms)
			{
				$permissao_user[$usergroupprograms->system_program_id]    = $usergroupprograms->system_program_id;
				//$permissao_user['system_program_id'] = $usergroupprograms->system_program_id ;
			}
			
			//var_dump($permissao_user);
				
           
			
			//verifica se existem alguma permissão do grupo q ainda ñ foi add as permissões especiais 
			$result = array_diff($permissao_grupo, $permissao_user);
			
			//se existir add elas
			if(isset($result)) 
			{	
				foreach($result as $results)
				{
					$permissao = new SystemUserGroupProgram;
					$permissao->system_user_id    = $data->ID_USUARIO;
					$permissao->system_group_id   = $data->ID_GRUPO;
					$permissao->system_program_id = $results;
					
					$permissao->acesso            = 1;
					$permissao->insercao          = 1;
					$permissao->delecao           = 0;
					$permissao->acesso_especial   = 0;
					
					$permissao->store();
					
					
					var_dump($results);	
				}
			}

			 TTransaction::close();
			//manda os dados para o form
            $this->form->setData($data);
           
			$this->onReload($param);
			
		}
		catch(Exception $e )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
	}
	
	/*
	Atualiza a página com os parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{
			TTransaction::open('permission');
			
			//pega os dados do form
			$data = $this->form->getData();
			
			//pega o grupo na sessão
			$grupo_id     =  TSession::getValue('TS_grupo_id');
			$user_id      =  TSession::getValue('TS_user_id');
			$ts_data      =  TSession::getValue('TS_data');
			
			//var_dump(implode($ts_data->GRUPO));
			
			$repository = new TRepository('SystemUserGroupProgram');
			$criteria   = new TCriteria;
			$limit = 10;
			
			//seta as propriedades
			$criteria->setProperty('order','id');//NOME
			$criteria->setProperty('direction','ASC');
			$criteria->setProperty('limit',$limit);
			
			$criteria->setProperties($param);
			
			//busca pelo nome
			if(TSession::getValue('TS_filter_programa') )
			{
				$criteria->add(TSession::getValue('TS_filter_programa') );
			}
			
			$criteria->add(new TFilter('system_group_id', '=', $grupo_id) );
			
			$criteria->add(new TFilter('system_user_id', '=', $user_id) );
			
			/*//teste se existe permissões especiais
			$count_obj = $repository->count($criteria);*/ 
			
			
			$objects = $repository->load( $criteria );

			$this->datagrid->clear();
			
			$gridfields = array( $this->saveButton );
			//pega os programas relacioanados ao grupo
			if( $objects)
			{
				$cont = 1;
				foreach( $objects as $object )
				{
					$item_name = 'exce_' . $cont++;
					
					$item = new  StdClass;
					$item->id                = $object->id;
					$item->system_user_id    = $object->system_user_id; 
					$item->system_program_id = $object->system_program_id;
					$item->programa_nome     = $object->programa->name;
					$item->system_group_id   = $object->system_group_id;
					
					$item->acesso = new TCheckButton('acesso' . '_' . $object->id);
					$item->acesso->setValue( $object->acesso );
					$item->acesso->setIndexValue('1');
					$gridfields[] = $item->acesso;// important
					
					$item->insercao = new TCheckButton('insercao' . '_' . $object->id);
					$item->insercao->setValue( $object->insercao );
					$item->insercao->setIndexValue('1');
					$gridfields[] = $item->insercao; // important
					
					$item->delecao = new TCheckButton('delecao' . '_' . $object->id);
					$item->delecao->setValue( $object->delecao );
					$item->delecao->setIndexValue('1');
					$gridfields[] = $item->delecao;// important
					
					
					$this->datagrid->addItem($item);
					
					//$this->datagrid->addItem($object);
				}
			}
			 $this->formgrid->setFields($gridfields);
			
							
			$criteria->resetProperties();
			$count = $repository->count($criteria); 

            $this->pageNavigation->setCount ($count);
            $this->pageNavigation->setProperties ($param);
            $this->pageNavigation->setlimit($limit); 
			
			//manda os dados para o form
			$data->ID_GRUPO   = $grupo_id;
			$data->GRUPO      = $data->GRUPO;
			$data->USUARIO    = $data->USUARIO;
			$data->ID_USUARIO = $data->ID_USUARIO;
			
			//mantém os dados durante a navegação (da primeira vez ainda ñ criou o obj )
			if(!empty($ts_data->ID_USUARIO))
			{	
				$data->ID_USUARIO = $ts_data->ID_USUARIO;
				$data->USUARIO    = $ts_data->USUARIO;
				$data->ID_GRUPO   = $ts_data->ID_GRUPO;
				$data->GRUPO      = $ts_data->GRUPO;
			}
			
			
			$this->form->setData($data);
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
				
		}	
	}//onReload
	
	public function onSearch($param)
	{
		try
		{
			//PERQUISA
			
			$data = $this->form->getdata();
			
			$data->BUSCA = strtoupper($data->BUSCA);      
		    if ($data->BUSCA)//nome d compo a ser buscado
		    {
				//$data->BUSCA = strtoupper($data->BUSCA);
				$filter = new TFilter('(SELECT name from system_program WHERE id=system_user_group_program.system_program_id)', 'like', "%{$data->BUSCA}%");
				
				TSession::setValue('TS_filter_programa', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				
			}
			else
			{
				TSession::setValue('TS_filter_programa', NULL );
			}
			
			$this->form->setData($data);
		  
		 
		  
		    $param = array();
		    $param['offset'] = 0;
		    $param['first_page'] = 1;
		    $this->form->getdata($data);
		    $this->onReload( $param );
			
		}
		catch(Exception $e )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}	
		
	}//onSearch
		
	
	public function onEdit($param)
	{
		try
		{
			
			if($param['key'])
			{	
				//limpa as variáves de sessão
				TSession::setValue('TS_grupo_id', NULL);
				TSession::setValue('TS_user_id', NULL);
				TSession::setValue('TS_data', NULL);
				
				TTransaction::open('permission');
				
				//instancia o usuário 
				$user = new SystemUser($param['key']); 
				
				//declara os array's
				$programs    = array();
				$user_groups = array();
				
				//pega o grupo do usuário
				foreach( $user->getSystemUserGroups() as $group ) 
				{
					//pega o grupo do usuario
					$user_groups[]['id']   = $group->id;
					$user_groups[]['name'] = $group->name;
					
					//var_dump ($user_groups[1]);
					
				}
				
				var_dump ($user_groups[1]);
				
				TTransaction::close();
				
				//se for cadastrado em algum grupo cai no if abaixo
				if(isset($user_groups[0]))
				{
					$obj = new STDClass;
					$obj->ID_GRUPO   = implode($user_groups[0]) ;
					$obj->GRUPO      = implode($user_groups[1]);
					$obj->ID_USUARIO = $user->id;
					$obj->USUARIO    = $user->name;
					TForm::sendData('formExcecoes', $obj);
					
					//GRAVA O ID DO GRUPO E O ID DO USUÁRIO NA SESSÃO 
					TSession::setValue('TS_grupo_id', $user_groups[0]);
					TSession::setValue('TS_user_id', $user->id);
					TSession::setValue('TS_data', $obj);
					
					//TButton::enableField('formExcecoes', 'btn_permissao');
				
				}
				else
				{
					$obj = new STDClass;
					$obj->ID_USUARIO = $user->id;
					$obj->USUARIO    = $user->name;
					$obj->ID_GRUPO   = 'Não cadastrado' ;
					$obj->GRUPO      = 'Não cadastrado';
					TForm::sendData('formExcecoes', $obj);
					
					//GRAVA O ID DO GRUPO E O ID DO USUÁRIO NA SESSÃO 
					TSession::setValue('TS_grupo_id', NULL );
					TSession::setValue('TS_user_id', $user->id);
					TSession::setValue('TS_data', $obj);
					
					
					TButton::enableField('formExcecoes', 'btn_permissao');
				
					
				}
				
				$data = $this->form->getData();	
				$this->form->setData($data);	
				//verifica 	permissões especiais
				
				$this->onReload($param);
				
			}//param['key']
			
		}//try
		catch(Exception $e  )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
	
	}//onEdit
	
	
	/*
	  Limpa o form e as variaveis de sessão
	*/
	public function onClear($param)
	{
		//$this->datagrid->clear();
		
		TSession::setValue('TS_filter_programa', NULL);
		TSession::setValue('TS_busca', NULL);
		
		
		$data = $this->form->getData();
		
		$data->BUSCAR = '';
		
		//desatica o 'btn_pesquisar '
		//TButton::disableField('formPropostaListe', 'btn_pesquisar');

		$this->onReload($param);
		
	}//onClear
	
	/*
	Limpa a Tentry, após trocar
	o filtro de busca
	*/
	public static function onLimpaBusca($param)
	{
		$obj_std = new STDClass;
		$obj_std->BUSCA = '';
		$param['BUSCA'] = $obj_std->BUSCA;
		
		TForm::sendData('formExcecoes', $obj_std);
		
		//ativa o 'btn_pesquisar'
		TButton::enableField('formPropostaListe', 'btn_pesquisar');
		
		//LIMPA A SESSÃO BUSCA
		TSession::setValue('TS_busca', NULL);
		
	}//onLimpaBusca
	
	/*
	  Edita a proposta direto na Grid
	*/
	function onEditInline($param)
    {
        try
        {
            $data = $this->form->getData();
			
			// get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
			
				
            
            // open a transaction with database 'samples'
            TTransaction::open('permission');
			
			//transforma em maiúsculo
			$value = strtolower($value);
			
			
			//if( $value <> 'sim' OR $value <> 'nao'  )
			//if( ($value !== 'sim') OR ($value !== 'nao') )
			if( ($value !== 'sim') AND ($value !== 'nao') )
			{
				throw new Exception('Digite sim ou nao');
			}
			
            
            // instantiates object banco
            $proposta = new SystemUserGroupProgram($key);
            $proposta->{$field} = $value;
			$proposta->store();
			
            // close the transaction
            TTransaction::close();
			
			$this->form->setData($data);
            
            // reload the listing
            $this->onReload($param);
			
			//pega os dados do form da sessão
			$data = TSession::getValue('TS_data');
					
			
			
			$obj =  new STDClass;
			$obj->USUARIO    = $data->USUARIO;
			$obj->GRUPO      = $data->GRUPO;
			$obj->ID_GRUPO   = $data->ID_GRUPO;
			$obj->ID_USUARIO = $data->ID_USUARIO;
			
			TForm::sendData('formExcecoes', $obj );
			
            // shows the success message
           // new TMessage('info', "Registro atualizado");
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
		
    }//onEditInline
	
	 public function formatafont($estatus, $object, $row)
    {
        //$number = number_format($stock, 2, ',', '.');
        $var = $estatus;
        
        if ($var == 'REPROVADO'  )
        {
            return "<span style='color:red'>$estatus</span>";
        }
        
        if ($var == 'APROVADO'  )
        {
            return "<span style='color:#006400'>$estatus</span>";
        }
		
		if ($var == 'ANALISANDO'  )
        {
            return "<span style='color:#ECBE00'>$estatus</span>";
        }
        
        
       // else
        //{
            //$row->style = "background: #FFF9A7";
            //return "<span style='color:red'>$estatus</span>";
        //}
    }//formatafont
	
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
	
	
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db');
			$key = $param['ID_PROPOSTA'];
			
			$proposta = new proposta($key);
			$nome = $proposta->NOME;
			
			$ac_sim = new TAction( array($this, 'onSim') );
			$ac_sim->setParameter('ID_PROPOSTA', $key);
			
			new TQuestion('Apagar a proposta do(a) ' . $nome , $ac_sim);
			
			TTransaction::close();
			
			$this->onReload($param);
			//new TMessage('info', 'Apagar o Registro '. $key );
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onDelete
	
	public function onSim($param)
	{
		try
		{
			TTransaction::open('db');
			
			$key = $param['ID_PROPOSTA'];
			$proposta = new proposta($key);
			
			$proposta->delete();
			
			TTransaction::close();
			
			new TMessage('info', 'Registro Apagado');
			$this->onReload($param);
						
			//$this->form->setData($data);
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}	
		
	}//onSim
	
	
	/*public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
	*/
		
		
		
}//TWindow 

?>