<?php
class FormUserSistem Extends TPage
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
		$this->form  = new BootstrapFormBuilder('form_System_user');
		$this->form->class='tform';
		$this->form->setFieldSizes('100%');
		
		//cria os atributos
		$teste_id      = new TEntry('teste_id');//hidden
		$teste_name    = new TEntry('teste_name');//hidden
		
		$id            = new TEntry('id_user');
        $name          = new TEntry('name');
		$exit_name     = new TAction(array($this, 'onEnviaDados'));//onExitAction
		$name->setExitAction($exit_name);
		
        $login         = new TEntry('login');
        $password      = new TPassword('password');
        $repassword    = new TPassword('repassword');
        $email         = new TEntry('email');
        $unit_id       = new TDBCombo('system_unit_id','permission','SystemUnit','id','name');
        //$groups        = new TDBRadioGroup('groups','permission','SystemGroup','id','name');
        $groups        = new TDBCheckGroup('groups','permission','SystemGroup','id','name');
        $frontpage_id  = new TDBUniqueSearch('frontpage_id', 'permission', 'SystemProgram', 'id', 'name', 'name');
        $units         = new TDBCheckGroup('units','permission','SystemUnit','id','name');
		
		$programa       = new TDBSeekButton('id', 'permission', 'form_System_user', 'SystemProgram', 'name');
		$nome_programaf = new TEntry('nome_programaf');
		$programa->setAuxiliar($nome_programaf);//nome_programa
		
		/*$city_id   = new TDBSeekButton('city_id', 'samples', 'form_seek', 'City', 'name');
        $city_name = new TEntry('city_name');
        $city_id->setAuxiliar($city_name);*/
        
        $units->setLayout('horizontal');
        if ($units->getLabels())
        {
            foreach ($units->getLabels() as $label)
            {
                $label->setSize(200);
            }
        }
        
        $groups->setLayout('horizontal');
        if ($groups->getLabels())
        {
            foreach ($groups->getLabels() as $label)
            {
                $label->setSize(200);
            }
        }
		
		//atributos old
		$busca      = new TEntry('BUSCA');
				
		//cria as sessões
		$busca->setValue(TSession::getValue('TS_busca'));
		
		/* $bt5c->addFunction("if (confirm('Want to go?') == true) { __adianti_load_page('index.php?class=ContainerWindowView'); }");*/
		
		//cria os botões
		// creates the update collection button
        $this->saveButton = new TButton('update_collection');
        //$this->saveButton->setAction(new TAction(array($this, 'onTesteGrid')), 'Save');//onSave
        $this->saveButton->addFunction("{__adianti_load_page('index.php?class=FormUserSistem&method=onTesteGrid');}");
        $this->saveButton->setImage('fa:save green');
        //$this->formgrid->addField($this->saveButton);
		
		$btn_voltar    = TButton::create('btn_voltar' ,array('SystemUserList', 'onReload'), 'Voltar', 'fa: fa-arrow-left' );
		
		$btn_pesquisar = TButton::create('btn_pesquisar', array($this, 'onSearch'), 'Buscar', 'fa:search blue');
		//$btn_clear     = TButton::create('btn_incluir', array($this, 'onTeste'),  'Limpar', 'fa:eraser red');
		$btn_permissao = TButton::create('btn_permissao', array($this, 'onAddPermissao2'),  'Adicionar', 'fa:plus blue');// **  //onTeste4 //onTeste3
		
		$btn = $btn_teste = TButton::create('btn_teste', array($this, 'onSave'),  'Salvar', 'far:save');
		$btn->class = 'btn btn-sm btn-primary';
		//onTeste//onView2//onTesteGrid
		
		//formatações
		$busca->placeHolder = 'Pesquisar...';
		//TButton::disableField('formExcecoes', 'btn_permissao' );
		
		
		
		//cria a grid
			// creates one datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
		
		//$this->datagrid = new TQuickGrid;
        $this->datagrid->style = 'width: 100%';
        
        // make scrollable and define height
        $this->datagrid->setHeight(250);
        $this->datagrid->makeScrollable();
		$this->datagrid->DisableDefaultClick;
        
        // create the datagrid columns
	 	 //$id              = new TDataGridColumn('id', 'ID', 'left', '10%');//35%
	   // $edit              = new TDataGridColumn('edit', '', 'center', '5%');
	    $col_delete          = new TDataGridColumn('delete', '', 'center', '10%');
	    $col_id              = new TDataGridColumn('system_program_id', 'ID', 'left', '10%');
        $col_id_programa     = new TDataGridColumn('programa_nome', 'Programa', 'left', '40%');
        $col_acesso          = new TDataGridColumn('acesso', 'Acesso', 'left', '10%');
		$col_insercao        = new TDataGridColumn('insercao', 'Inserção', 'left', '10%');
		$col_delecao         = new TDataGridColumn('delecao', 'Deleção', 'left', '10%');
		$col_alteracao       = new TDataGridColumn('alteracao', 'Alteração', 'left', '10%');
		
		// creates datagrid actions
        //$this->datagrid->addQuickAction('Teste', new TDataGridAction(array($this, 'onView')), 'system_program_id', 'fa:check-circle-o green');
        //$this->datagrid->addQuickAction('Teste', new TDataGridAction(array($this, 'onTeste2')), 'system_program_id', 'fa:check-circle-o green');
        
        //$this->datagrid->addQuickAction('Select', new TDataGridAction(array($this, 'onSelect')), 'id', 'fa:check-circle-o green');
        
		// add the columns to the datagrid
        $this->datagrid->addColumn($col_id);
		//$this->datagrid->addColumn($edit);
        $this->datagrid->addColumn($col_delete);
        $this->datagrid->addColumn($col_id_programa);
        $this->datagrid->addColumn($col_acesso);
        $this->datagrid->addColumn($col_insercao);
        $this->datagrid->addColumn($col_alteracao);
        $this->datagrid->addColumn($col_delecao);
		
		$this->datagrid->createModel();
		
		//add os campos no form
		 // define the sizes
        $id->setSize('50%');
        $name->setSize('100%');
        $login->setSize('100%');
        $password->setSize('100%');
        $repassword->setSize('100%');
        $email->setSize('100%');
        $unit_id->setSize('100%');
        $frontpage_id->setSize('100%');
        $frontpage_id->setMinLength(1);
		$programa->setSize(100);
		$nome_programaf->setSize('calc(100% - 120px)');
        
        // outros
        $id->setEditable(false);
        
        // validations
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $login->addValidation('Login', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        
        $this->form->addFields( [new TLabel('ID')], [$id],  [new TLabel(_t('Name'))], [$name] );
        $this->form->addFields( [new TLabel(_t('Login'))], [$login],  [new TLabel(_t('Email'))], [$email] );
        $this->form->addFields( [new TLabel(_t('Main unit'))], [$unit_id],  [new TLabel(_t('Front page'))], [$frontpage_id] );
        $this->form->addFields( [new TLabel(_t('Password'))], [$password],  [new TLabel(_t('Password confirmation'))], [$repassword] );
        $this->form->addFields( [new TFormSeparator(_t('Units'))] );
        $this->form->addFields( [$units] );
        $this->form->addFields( [new TFormSeparator(_t('Groups'))] );
        $this->form->addFields( [$groups] );
		
		
		//add compos do form
		/*$row = $this->form->addFields(['Usuário', $usuario],
									  ['Grupo', $grupo]);
		$row->layout = ['col-sm-9', 'col-sm-3'];
		
		//Linha oculta
		$row = $this->form->addFields([ $id_usuario],
									  [$id_grupo] );
		$row->layout = ['col-sm-9', 'col-sm-3'];*/
							   
		//ações do form
		/*$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'ico_find.png');	
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');*/				   
		
		
		$this->form->addContent( [new TFormSeparator('Lista de programas e permissões')] );
	
		//$this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
		$row = $this->form->addFields( [new TLabel('Buscar Programa')],  [$programa]);
		$row->layout = ['col-sm-2', 'col-sm-10'];
		//$row->layout = ['col-sm-12'];
		/*$row = $this->form->addFields(['Buscar', $programas] );
		$row->layout = ['col-sm-12'];*/
		
		//barra menu coberturas
		$row = $this->form->addFields([$btn_permissao] );
		$row->layout = ['col-sm-1', 'col-sm-1', 'col-sm-1'];
		//$row->style = ' background:whiteSmoke; border:1px solid #cccccc';
		//$row->style = ' background:whiteSmoke; border:1px solid #cccccc; padding: 3px;padding: 5px';
		//$row->style = 'background:#D5D5D5; margin:0 0 0 0';
		$row->style = 'background:whiteSmoke; border:1px solid #cccccc; margin:0 0 0 0; padding: 2px;';
		
		// $row = $this->form->addFields(['', $btn_pesquisar],
									  // ['', $btn_clear ] );
		// //$row->layout = ['col-sm-6', 'col-sm-6'];
		
		$row = $this->form->addFields( [$this->datagrid]);
		$row->layout = ['col-sm-12'];
		
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		//$this->pageNavigation->style = 'margin:0 0 0 150px';
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));		
		
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
				
		//cria o painel
		$painel = new TPanelGroup('Permissões do usuário');
		
		//colunas Hidden
		$table1 = new TTable;
		$table1->addRowSet( $teste_id, array($teste_name));
		
		$this->formgrid = new TForm('formPermissao');
		//$this->formgrid->setFields([$teste_id, $teste_name]);
        $this->formgrid->add($table1);
		$this->formgrid->add($this->datagrid);
        
        //$this->formgrid->add($this->form);
		
		// define the datagrid transformer method
        $this->setTransformer(array($this, 'onBeforeLoad'));
		
		$painel->add($this->form);
		//$painel->add($this->formgrid);//
		
		//$painel->add($table1);//
		//$painel->add($this->datagrid);//
		//$painel->add($this->pageNavigation);
		
		//barra footer
		$painel->addFooter(THBox::pack($btn_voltar, $btn_teste));//$this->saveButton, btn_fechar
		
		//ativar a rolagem horizontal dentro do corpo do painel
		$painel->getBody()->style = "overflow-x:auto" ;
		
		$id->setSize('50%');
        $name->setSize('100%');
        $login->setSize('100%');
        $password->setSize('100%');
        $repassword->setSize('100%');
        $email->setSize('100%');
        $unit_id->setSize('100%');
        $frontpage_id->setSize('100%');
        $frontpage_id->setMinLength(1);
		
		//add os compos no form 
		$this->formFields = array( $btn_voltar, $id, $programa, $nome_programaf, $name, $teste_name, $teste_id, $login, $password, $repassword, $email, $unit_id, $frontpage_id, $units, $groups, $btn_teste, $this->saveButton);
		$this->form->setFields($this->formFields);
		
		//$this->formgrid->setFields($this->formFields);
		
		// keep the form filled with session data
        $this->form->setData( TSession::getValue('ProductUpdateList_filter_data') );
		
		
		//add o painel em tela
		//$menuBread = new TXMLBreadCrumb('menu.xml', 'systemExcecoesListe' );
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        //$vbox->add($menuBread);
        $vbox->add($painel);
		
        parent::add($vbox);
		
	}//__construct
	
	public function onSave($param)
	{
		
		$data = $this->form->getData();
		
		//TSession::setValue('TS_teste', $data);
		
		
		//$data = $this->form->getData(); // get datagrid form data
		
		//var_dump(TSession::getValue('ProductUpdateList_filter_data'));
		
		$data_form = $this->formgrid->getData();
        $this->formgrid->setData($data_form); // keep the form filled
		
		$ts_data  = TSession::getValue('TS_data');
		
		var_dump($data_form);
		
        try
        {
            // open transaction
            TTransaction::open('permission');
            
            // iterate datagrid form objects
            foreach ($this->formgrid->getFields() as $name => $field)
            {
                
				/**CAPTURA DADOS DAS PERMISSÕES**/
				if ($field instanceof TCheckButton)
				{
					$parts = explode('_', $name);
					$id = end($parts);
					$nome_campo = $parts[0];
									
					if ($nome_campo == 'acesso')
					{
					   $object2 = SystemUserGroupProgram::find($id);
					   if ($object2)
					   {
							$object2->acesso    =  $field->getValue(); 
							$object2->insercao  =  $data_form->{"insercao_$id"}; 
							$object2->delecao   =  $data_form->{"delecao_$id"};
							$object2->alteracao =  $data_form->{"alteracao_$id"};
							
							if($object2->acesso == '')
							{
								$object2->acesso = 0;
							}
							
							if($object2->insercao == '')
							{
								$object2->insercao = 0;
							}

							if($object2->delecao == '')
							{
								$object2->delecao = 0;
							}
							
							if($object2->alteracao == '')
							{
								$object2->alteracao = 0;
							}	
													
							//**SALVA AS PERMISSÕES
							$object2->store();//object
							
							
							/*$object->acesso   =  $field->getValue(); 
							$object->insercao =  $data->{"insercao_$id"}; 
							$object->delecao  =  $data->{"delecao_$id"};*/
							
							//**SALVA DADOS DO USUÁRIO **	
							//$object->store();
							
							//var_dump($data);
						}
					}
				}
				
            }//foreach
			
			//salva os dados do usuário
			$object = new SystemUser;
			//$object->id = $data->id_user;
            $object->fromArray( (array) $data );
            
            $senha = $object->password;
            
            if( empty($object->login) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
			/*o campo 'id' é reservado para o novo programa add ao usuário, é o campo padrão da 'TDBSeekButton => $programa' */ 
			$object->id = $data->id_user;
            if( empty($object->id) )
            {
                if (SystemUser::newFromLogin($object->login) instanceof SystemUser)
                {
                    throw new Exception(_t('An user with this login is already registered'));
                }
                
                if (SystemUser::newFromEmail($object->email) instanceof SystemUser)
                {
                    throw new Exception(_t('An user with this e-mail is already registered'));
                }
                
                if ( empty($object->password) )
                {
                    throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
                }
                
                $object->active = 'Y';
            }
            
            if( $object->password )
            {
                if( $object->password !== $param['repassword'] )
                    throw new Exception(_t('The passwords do not match'));
                
                $object->password = md5($object->password);
            }
            else
            {
                unset($object->password);
            }
            
            $object->store();
            $object->clearParts();
            
            if( !empty($data->groups) )
            {
                foreach( $data->groups as $group_id )
                {
                    $object->addSystemUserGroup( new SystemGroup($group_id) );
                }
            }
            
            if( !empty($data->units) )
            {
                foreach( $param['units'] as $unit_id )
                {
                    $object->addSystemUserUnit( new SystemUnit($unit_id) );
                }
            }
            
            /*if (!empty($data->program_list))
            {
                foreach ($data->program_list as $program_id)
                {
                    $object->addSystemUserProgram(new SystemProgram($program_id));
                }
            }*/
            
            $data = new stdClass;
            $data->id_user = $object->id;
            TForm::sendData('form_System_user', $data);
            
            // close transaction
            TTransaction::close();
			
			$this->onAtualiza($param);
			
			// shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
			
			//$this->form->setData($data);
			
			//$this->form->setData($data_form);
			
		
			//manda os dados para o form
			//TForm::sendData('form_System_user', $data_form);
			//$this->onTeste2($param);
			
			//var_dump($param['name']);
			
        }
        catch (Exception $e)
        {
            // show the exception message
            new TMessage('error', $e->getMessage());
        }
	}//onSave
	
	/**
     * Executa antes que a datagrid seja carregado
     */
    public function onBeforeLoad($objects, $param)
    {
        // atualiza os parâmetros de ação para passar a página atual para a ação
        // sem isso, a ação funcionará apenas na primeira página
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
			
			$object->alteracao = new TCheckButton('alteracao' . '_' . $object->id);
            $object->alteracao->setValue( $object->alteracao );
			$object->alteracao->setIndexValue('1');
            $gridfields[] = $object->alteracao;// important
			
        }
		
		$param['name'];
        $this->formgrid->setFields($gridfields);
		
		//var_dump($gridfields);
		TSession::setValue('TS_permissaoAtual',$gridfields);
		
		exit;
		
    }//onBeforeLoad
	
	function onView2($param)
    {
        // get the parameter and shows the message
        //$key=$param['key'];
        //new TMessage('info', "The name is : $key");
		
		$data_form = $this->formgrid->getData();
		//manda os dados para o form
		TForm::sendData('form_System_user', $data_form);
		
        //$this->formgrid->setData($data_form); // keep the form filled
		
		/*$data = $this->form->getData();
		
		var_dump($data->name);
		
		TSession::setValue('TS_dados', $data->name);
		
		$this->form->setData($data);*/
		
		
		//$this->onSave($param);
    }
	
	function onView($param)
    {
        // get the parameter and shows the message
        //$key=$param['key'];
        //new TMessage('info', "The name is : $key");
		
		try
		{
			$data_form = $this->formgrid->getData();
			$this->formgrid->setData($data_form);
			
			 // open transaction
            TTransaction::open('permission');
            
            // iterate datagrid form objects
            foreach ($this->formgrid->getFields() as $name => $field)
            {
                
				/**CAPTURA DADOS DAS PERMISSÕES**/
				if ($field instanceof TCheckButton)
				{
					$parts = explode('_', $name);
					$id = end($parts);
					$nome_campo = $parts[0];
									
					if ($nome_campo == 'acesso')
					{
					   $object2 = SystemUserGroupProgram::find($id);
					   if ($object2)
					   {
							$object2->acesso    =  $field->getValue(); 
							$object2->insercao  =  $data_form->{"insercao_$id"}; 
							$object2->alteracao =  $data_form->{"alteracao_$id"};
							$object2->delecao   =  $data_form->{"delecao_$id"};
							
							if($object2->acesso == '')
							{
								$object2->acesso = 0;
							}
							
							if($object2->insercao == '')
							{
								$object2->insercao = 0;
							}

							if($object2->delecao == '')
							{
								$object2->delecao = 0;
							}

							if($object2->alteracao == '')
							{
								$object2->alteracao = 0;
							}	
													
							//**SALVA AS PERMISSÕES
							$object2->store();//object
							
							
							/*$object->acesso   =  $field->getValue(); 
							$object->insercao =  $data->{"insercao_$id"}; 
							$object->delecao  =  $data->{"delecao_$id"};*/
							
							//**SALVA DADOS DO USUÁRIO **	
							//$object->store();
							
							//var_dump($data);
						}
					}
				}
				
            }//foreach
            new TMessage('info', AdiantiCoreTranslator::translate('Record updated'));
            
            // close transaction
            TTransaction::close();
			
			//manda os dados para o form
			TForm::sendData('form_System_user', $data_form);
			
			//$this->formgrid->setData($data_form); // keep the form filled
			
			$data = $this->form->getData();
			
			var_dump($data->name);
			
			TSession::setValue('TS_dados', $data->name);
			
			$this->form->setData($data);
			
			
			//$this->onSave($param);
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}	
    }//onView
	
	/*
		Pega as dados de um formalario e
		joga para o outro
	*/
	public static function onEnviaDados($param)
	{
		//$data_form = TForm::getData();
		//$data_form = $this->form->getData();
		
		$stdclass = new StdClass;
		$stdclass->teste_name  = $param['name'];
		
		TForm::sendData('formPermissao', $stdclass);
		//$stdclass->teste_name  = 'teste';//$param['name'];
		
		//$stdclass->teste_id    = $data_form->id;//$param['id'];
		
		/*$stdclass = new StdClass;
		$stdclass->name  = $data_form->name;//$param['name'];
		$stdclass->id    = $data_form->id;//$param['id'];*/
		
		//$data_form = TForm::getData();
		
		/*$stdclass = new StdClass;
		$stdclass->name  = 'Teste 1';//$param['name'];
		$stdclass->id    = 3;//$param['id'];*/
		
		//$data_form = $this->form->getData();
		
		
		//funciona
		//FormUserSistem::onReload($param);
		
	}//onEnviaDados
	
	public function onTesteForm($param)
	{
		$data      = $this->form->getData();
		$data_form = $this->formgrid->getData();
			
		var_dump($data);
		var_dump($data_form);
		
		//manda os dados para o form
		TForm::sendData('form_System_user', $data_form);
		
	}//onTesteForm
	
	public function onTesteGrid($param)
	{
		$data      = $this->form->getData();
		$data_form = $this->formgrid->getData();
		
		//$griddata = $this->datagrid->getOutputData();
			
		//var_dump($data->teste_name);
		
		var_dump($data);
		var_dump($data_form);
		//var_dump($griddata);
		
		//var_dump($param['id']);
		//var_dump($param['teste_name']);
		
		//manda os dados para o form
		TForm::sendData('form_System_user', $data_form);
		
	}//onTesteGrid
	
	public function onTeste4($param)
	{
		
		$data = $this->form->getData();
		
		//TSession::setValue('TS_teste', $data);
		
		
		//$data = $this->form->getData(); // get datagrid form data
		
		//var_dump(TSession::getValue('ProductUpdateList_filter_data'));
		
		$data_form = $this->formgrid->getData();
        $this->formgrid->setData($data_form); // keep the form filled
		
		$ts_data  = TSession::getValue('TS_data');
		
		var_dump($data);
		
		try
        {
            // open transaction
            TTransaction::open('permission');
            
            // iterate datagrid form objects
            foreach ($this->formgrid->getFields() as $name => $field)
            {
                
				/**CAPTURA DADOS DAS PERMISSÕES**/
				if ($field instanceof TCheckButton)
				{
					$parts = explode('_', $name);
					$id = end($parts);
					$nome_campo = $parts[0];
									
					if ($nome_campo == 'acesso')
					{
					   $object2 = SystemUserGroupProgram::find($id);
					   if ($object2)
					   {
							$object2->acesso   =  $field->getValue(); 
							$object2->insercao =  $data_form->{"insercao_$id"}; 
							$object2->delecao  =  $data_form->{"delecao_$id"};
							
							if($object2->acesso == '')
							{
								$object2->acesso = 0;
							}
							
							if($object2->insercao == '')
							{
								$object2->insercao = 0;
							}

							if($object2->delecao == '')
							{
								$object2->delecao = 0;
							}	
													
							//**SALVA AS PERMISSÕES
							$object2->store();//object
							
							
						}
					}
				}
				
            }//foreach
			
			//salva os dados do usuário
			
			
            new TMessage('info', AdiantiCoreTranslator::translate('Record updated'));
            
            // close transaction
            TTransaction::close();
			
			$this->form->setData($data);
			//$this->form->setData($data_form);
			
		
			//manda os dados para o form
			TForm::sendData('form_System_user', $data_form);
			//$this->onTeste2($param);
			
			//var_dump($param['name']);
			
        }
        catch (Exception $e)
        {
            // show the exception message
            new TMessage('error', $e->getMessage());
        }
		
			
		//$this->form->setData($data);
		$this->form->setData($data_form);
			
		
			//manda os dados para o form
		TForm::sendData('form_System_user', $data_form);
			//$this->onTeste2($param);
			
			//var_dump($param['name']);
			
        
	}//onTeste4
	
	public  function onTeste2($param)
	{	
		
		try
        {
            // open a transaction with database 'permission'
            TTransaction::open('permission');
			
			
			$grup_array = array();
			  if( !empty($param->groups) )
            {
                foreach( $param->groups as $group_id )
                {
                    $grup_array = $group_id;
					//$object->addSystemUserGroup( new SystemGroup($group_id) );
                }
            }
            
			var_dump($grup_array);
			
			exit;
			
            
            $object = new SystemUser;
            $object->fromArray( (array) $data );
            
            $senha = $object->password;
            
            if( empty($object->login) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
            if( empty($object->id) )
            {
                if (SystemUser::newFromLogin($object->login) instanceof SystemUser)
                {
                    throw new Exception(_t('An user with this login is already registered'));
                }
                
                if (SystemUser::newFromEmail($object->email) instanceof SystemUser)
                {
                    throw new Exception(_t('An user with this e-mail is already registered'));
                }
                
                if ( empty($object->password) )
                {
                    throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
                }
                
                $object->active = 'Y';
            }
            
            if( $object->password )
            {
                if( $object->password !== $param['repassword'] )
                    throw new Exception(_t('The passwords do not match'));
                
                $object->password = md5($object->password);
            }
            else
            {
                unset($object->password);
            }
            
            $object->store();
            $object->clearParts();
            
            if( !empty($data->groups) )
            {
                foreach( $data->groups as $group_id )
                {
                    $object->addSystemUserGroup( new SystemGroup($group_id) );
                }
            }
            
            if( !empty($data->units) )
            {
                foreach( $param['units'] as $unit_id )
                {
                    $object->addSystemUserUnit( new SystemUnit($unit_id) );
                }
            }
            
            if (!empty($data->program_list))
            {
                foreach ($data->program_list as $program_id)
                {
                    $object->addSystemUserProgram(new SystemProgram($program_id));
                }
            }
            
            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_System_user', $data);
            
            // close the transaction
            TTransaction::close();
            
            // shows the success message
            //new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
			
			//$this->onSave($param);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
		
	}//onTeste2
	
	public function onAddPermissao2($param)
	{
		try
		{
			
			$data = $this->form->getData();
			
			$usuario_id = $data->id_user;
			//$teste = explode('', $data->groups);
			
			//var_dump($teste);
			//var_dump($data->id_user);
			//var_dump($data->groups[0]);
			
			/*$data->programa 
			$data->nome_programa 
			var_dump($data);*/
			
			//$data_form = $this->formgrid->getData();
			//$this->formgrid->setData($data_form);
			
		    TTransaction::open('permission');
			
			
			$rp_programa = new TRepository('SystemUserGroupProgram');
			$criteria = new TCriteria;
			$criteria->add(new TFilter('system_user_id', '=', $usuario_id));
			$criteria->add(new TFilter('system_program_id', '=', $data->id));
			
			$listaprograma = $rp_programa->load($criteria);
			
			if(empty($listaprograma))
			{
				$permissao = new SystemUserGroupProgram;
				$permissao->system_user_id     = $usuario_id;
				$permissao->system_program_id  = $data->id;
				$permissao->acesso			   = 1;	
				$permissao->insercao		   = 0;
				$permissao->alteracao		   = 0;
				$permissao->delecao            = 0;
				$permissao->store();
				
				
				$permissao2 = new SystemUserProgram;				
				$permissao2->system_user_id    = $usuario_id;
				$permissao2->system_program_id = $data->id;
				$permissao2->store();
			}
			else
			{
				new TMessage('info', 'Programa já adicionado ao usuário');
			}	
			
			TTransaction::close();
			
			//grava ma sessão
			$items_permissao = TSession::getValue('TS_permissao');

			$data->programa       = '';
			$data->nome_programaf = '';
			$this->form->setData($data);
			
			$this->onAtualiza($param);
			
			//$this->onReload($param);
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onAddPermissao2
	
	public function onTeste($param)
	{	
		//new TMessage('info', 'Meu nome é ' . get_class($this) ); 
		$data = $this->formgrid->getData(); // get datagrid form data
        $this->formgrid->setData($data); // keep the form filled
		
		$data_form = $this->form->getData();
		$this->form->setData($data_form);
		
		var_dump($param['name']);
		exit;
		
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
                
				/**CAPTURA DADOS DAS PERMISSÕES**/
				if ($field instanceof TCheckButton)
				{
					$parts = explode('_', $name);
					$id = end($parts);
					$nome_campo = $parts[0];
									
					if ($nome_campo == 'acesso')
					{
					   $object2 = SystemUserGroupProgram::find($id);
					   if ($object2)
					   {
							$object2->acesso   =  $field->getValue(); 
							$object2->insercao =  $data->{"insercao_$id"}; 
							$object2->delecao  =  $data->{"delecao_$id"};
							
							if($object2->acesso == '')
							{
								$object2->acesso = 0;
							}
							
							if($object2->insercao == '')
							{
								$object2->insercao = 0;
							}

							if($object2->delecao == '')
							{
								$object2->delecao = 0;
							}	
													
							//**SALVA AS PERMISSÕES
							$object2->store();//object
							
							
							/*$object->acesso   =  $field->getValue(); 
							$object->insercao =  $data->{"insercao_$id"}; 
							$object->delecao  =  $data->{"delecao_$id"};*/
							
							//**SALVA DADOS DO USUÁRIO **	
							//$object->store();
							
							//var_dump($data);
						}
					}
				}
				
            }//foreach
            new TMessage('info', AdiantiCoreTranslator::translate('Record updated'));
            
            // close transaction
            TTransaction::close();
			
			$this->form->setData($data_form);
		
			//manda os dados para o form
			
			
			TForm::sendData('form_System_user', $data_form);
			$this->onTeste2($param);
			
			
			
        }
        catch (Exception $e)
        {
            // show the exception message
            new TMessage('error', $e->getMessage());
        }
	}//onTeste
	
	public function onDelPermissao($param)
	{
		$data = $this->form->getData();
		$this->form->setData($data);
		
		$ts_permissao = TSession::getValue('TS_permissao');
		//$var_soma   = TSession::getValue('var_soma');
		
		//$objs_teste = var_dump($ts_permissao[ (int) $param['list_product_id'] ]);
		//var_dump($obj_teste[1]);
		
		$id_prog = array();
		foreach($ts_permissao as $key  => $value  )
		{
				//$id_prog[$key]           = $obj_teste->toArray();
				//$id_prog[$key]['id']     = $value;
				//$id_prog[$key]['acesso'] = $value;
				//$items_sessao[$item->COBERTURA_ID]['VL_FINANCIADO'] = $item->VL_FINANCIADO;
				
				$id_prog[$key]           = $value;
		}
		//var_dump($id_prog);
		
		$teste2 = ($ts_permissao[ (int) $param['list_product_id'] ] );
		var_dump($teste2->system_program_id);
		//var_dump($teste2->id);
		
	}//onDelPermissao
	
	/*
	     Questiono a remoção de um  programa 
	*/
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('permission');
			
			$data = $this->form->getData();
			$this->form->setData($data);
			
			//pega as permissões
			$ts_permissao = TSession::getValue('TS_permissao');
			$id_permissao = ($ts_permissao[ (int) $param['list_product_id']]);
			
			//pega o ID do programa
			$key = $id_permissao->system_program_id;
			
			//instacia o obj
			$programa = new SystemProgram($key);
			
			//pega o nome
			$nome   = $programa->name;
			
			$ac_onSim = new TAction( array($this, 'onSimDelete'));
			$ac_onSim->setParameter('id', $key);
			
			$ac_onNao = new TAction( array($this, 'onNaoDelete'));
			//$ac_onSim->setParameter('system_user_id', $programa->id); está no form
			
			new TQuestion('Deseja remover o programa  '. '"' . $nome .'"' , $ac_onSim, $ac_onNao );
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::close();
		}
		
	}//onDelete
	
	/*
	    Remove o programa das tabelas 'SystemUserGroupProgram'
		e 'SystemUserProgram' e atualiza a pagina sem o programa removido
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('permission');
			$data = $this->form->getData();
			
			$key     = $param['id'];
			//$userkey = $data->id_user;
			$userkey = TSession::getValue('TS_key');
	
			$rp_programa = new TRepository('SystemUserGroupProgram');	
			$criteria = new TCriteria;
			$criteria->add(new TFilter('system_program_id', '=', $key));
			$criteria->add(new TFilter('system_user_id', '=', $userkey));
			
			$rp_programa->delete($criteria);

			$rp_programagrupo = new TRepository('SystemUserProgram');
			$criteria2 = new TCriteria;
			$criteria2->add(new TFilter('system_program_id', '=', $key));
			$criteria2->add(new TFilter('system_user_id', '=', $userkey));
			
			$rp_programagrupo->delete($criteria2);	
			
			//var_dump($key);
			//var_dump($userkey);
			
			TTransaction::close();
			
			//$this->onReload($param);
			
			$this->onAtualiza($param);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSimDelete	
	
	public function onNaoDelete($param)
	{
		$this->onAtualiza($param);
		
	}//onNaoDelete
	
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
					
					
					//var_dump($results);	
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
		
	}//onAddPermissao
	
	/*
	Atualiza a página com os parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{
			TTransaction::open('permission');
			
			//pega os dados do form
			//$data = $this->form->getData();
			
			
			//var_dump(TSession::getValue('TS_permissaoAtual') );
			
			//var_dump(TSession::getValue('TS_permissao') );
			//var_dump(TSession::getValue('ProductUpdateList_filter_data') );
			/*
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
				//$criteria->add(TSession::getValue('TS_filter_programa') );
			}
			
			//$criteria->add(new TFilter('system_group_id', '=', $grupo_id) );
			
			$criteria->add(new TFilter('system_user_id', '=', $user_id) );
			
			/*
			//teste se existe permissões especiais
			$count_obj = $repository->count($criteria); 
			
			
			$objects = $repository->load( $criteria );*/
			
			$data    = TSession::getValue('TS_user');
			$objects = TSession::getValue('TS_permissao');
			
			$this->datagrid->clear();
			//FormUserSistem::datagrid::clear();
			
			$gridfields = array( $this->saveButton );
			//pega os programas relacioanados ao grupo
			if( $objects)
			{
				$cont = 1;
				foreach( $objects as $list_product_id => $object )
				{
					$item_name = 'exce_' . $cont++;
					
					/*MONSTAGEM DAS TCheckButton E ATRIBUTOS DA DATAGRID*/
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
					
					$item->alteracao = new TCheckButton('alteracao' . '_' . $object->id);
					$item->alteracao->setValue( $object->alteracao );
					$item->alteracao->setIndexValue('1');
					$gridfields[] = $item->alteracao;// important
					/*FINAL DA MONSTAGEM DAS TCheckButton*/
					
					/*MONSTAGEM DO BTN REMOVE ITEM*/
					//CRIA AS AÇÕES DOS BTN
					$action_del = new TAction(array($this, 'onDelete'));//onDelPermissao
					$action_del->setParameter('list_product_id', $list_product_id);
					//$action_del->setParameter('list_product_id', $object->name);
					$action_del->setParameter('cont',$cont);

					/*$action_edi = new TAction(array($this, 'onAddPermissao'));
					$action_edi->setParameter('list_product_id', $list_product_id);
					$action_edi->setParameter('cont',$cont);*/
					
					//CRIA OS BTN E ADD AS AÇÕES
					$button_del = new TButton('delete_product'.$cont);
					$button_del->class = 'btn btn-default btn-sm';
					$button_del->setAction( $action_del, '' );
					$button_del->setImage('far:trash-alt red');
					$gridfields[] = $button_del;// important

					/*$button_edi = new TButton('edit_product'.$cont);
					$button_edi->class = 'btn btn-default btn-sm';
					$button_edi->setAction( $action_edi, '' );
					$button_edi->setImage('fa:edit blue fa-lg');
					$gridfields[] = $button_edi;*/// important
					/*FINAL DA MONSTAGEM DO BTN REMOVE ITEM*/
					
					//ASSOCIA O OBJ PADRÃO AOS BNT
					//$item->edit    = $button_edi;
					$item->delete  = $button_del;
					
					
					
					
					$this->datagrid->addItem($item);
					
					//$this->datagrid->addItem($object);
				}
			}
			 $this->formgrid->setFields($gridfields);
			
							
			/*$criteria->resetProperties();
			$count = $repository->count($criteria); 

            $this->pageNavigation->setCount ($count);
            $this->pageNavigation->setProperties ($param);
            $this->pageNavigation->setlimit($limit);*/ 
			
			//manda os dados para o form
			// $data->ID_GRUPO   = $grupo_id;
			// $data->GRUPO      = $data->GRUPO;
			// $data->USUARIO    = $data->USUARIO;
			// $data->ID_USUARIO = $data->ID_USUARIO;
			
			// //mantém os dados durante a navegação (da primeira vez ainda ñ criou o obj )
			// if(!empty($ts_data->ID_USUARIO))
			// {	
				// $data->ID_USUARIO = $ts_data->ID_USUARIO;
				// $data->USUARIO    = $ts_data->USUARIO;
				// $data->ID_GRUPO   = $ts_data->ID_GRUPO;
				// $data->GRUPO      = $ts_data->GRUPO;
			// }
			
			//$data->id            = '';
			//limpa o campo buscar programa
			/*if(!empty($data->nome_programa))  
			{		
				$data->nome_programa = '';
		    }*/
			
			//limpa o 'id' do programa do campo 'Buscar Programa'
			if(isset($data->id))
			{	
				$data->id            = '';
			}	
			
			//limpa o 'nome' do programa do campo 'Buscar Programa'
			
			//$data->nome_programaf = '';
			
			// $this->form->setData($data);
			/*$object->id_user    = $object->id;
				$object->teste_name = $object->name*/
			
			//$insercao = $object->insercao == 1 ? 'insercao' : 'A';		

			//Alimenta o form oculto.
			$stdclass = new StdClass;
			//$stdclass->teste_name = $data->teste_name !== '' ? $data->teste_name : '';//$data->teste_name;
			if(isset($data->id_user))
			{	
				$stdclass->teste_id   = $data->id_user;
		    }
			TForm::sendData('formPermissao', $stdclass);
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
			TTransaction::open('permission');
			
			if($param['key'])
			{	
				$key=$param['key'];
				TSession::setValue('TS_key', $key);
                
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object System_user
                $object = new SystemUser($key);
                
                unset($object->password);
                
                $groups = array();
                $units  = array();
                
                if( $groups_db = $object->getSystemUserGroups() )
                {
                    foreach( $groups_db as $group )
                    {
                        $groups[] = $group->id;
                    }
                }
				
				//var_dump($groups);
                
                if( $units_db = $object->getSystemUserUnits() )
                {
                    foreach( $units_db as $unit )
                    {
                        $units[] = $unit->id;
                    }
                }
                //var_dump($units);
				
                $program_ids = array();
                $acesso      = array();
                foreach ($object->getSystemUserPrograms() as $program)
                {
                    $program_ids[] = $program->id;
                    //$program_ids[]      = $program->acesso;
                }
                $object->groups       = $groups;
                $object->units        = $units;
				
				/*
				$data->id            = '';
				
				
				$stdclass->teste_name = $data->name;
				$stdclass->teste_id   = $data->user_id;*/
				//Alimenta o form Hidden
				$object->id_user        = $object->id;
				$object->teste_name     = $object->name;
				$object->nome_programaf = '';
				
				//Grava na sessão
				TSession::setValue('TS_user', $object);
				
                //$object->program_list = $acesso;
               /* $object->program_list = $program_ids;
                $object->groups       = $groups;
                $object->units        = $units;*/
                
                // fill the form with the active record data
                
				
				//TForm::sendData('form_System_user', $object);
				
				//$object->id_user       = $object->id;
				//$object->id            = '';
				$object->nome_programaf = '';
				$this->form->setData($object);
                
                
				
				
				
			//** verifica 	permissões *//*
			//pega o grupo na sessão
			$grupo_id     =  TSession::getValue('TS_grupo_id');
			$user_id      =  TSession::getValue('TS_user_id');
			$ts_data      =  TSession::getValue('TS_data');
			
			//var_dump(implode($ts_data->GRUPO));
			
			$repository = new TRepository('SystemUserGroupProgram');
			$criteria   = new TCriteria;
			$limit = 10;
			
			//seta as propriedades
			$criteria->setProperty('order','system_program_id');//NOME
			$criteria->setProperty('direction','ASC');
			$criteria->setProperty('limit',$limit);
			
			$criteria->setProperties($param);
			
			//busca pelo nome
			if(TSession::getValue('TS_filter_programa') )
			{
				//$criteria->add(TSession::getValue('TS_filter_programa') );
			}
			
			//$criteria->add(new TFilter('system_group_id', '=', $grupo_id) );
			
			$criteria->add(new TFilter('system_user_id', '=', $object->id) );
			
			/*//teste se existe permissões especiais
			$count_obj = $repository->count($criteria);*/ 
			
			
			$objects = $repository->load( $criteria );
			
			//monta a variavel de sessão
			$items_sessao = array();
			foreach($objects as $item)//VALOR
			{
				$items_sessao[$item->id]                      = $item->toArray();
				$items_sessao[$item->id]['id']                = $item->id;
				$items_sessao[$item->id]['system_unit_id']    = $item->system_unit_id;
				$items_sessao[$item->id]['system_group_id']   = $item->system_group_id;
				$items_sessao[$item->id]['system_program_id'] = $item->system_program_id;
				$items_sessao[$item->id]['delecao']           = $item->delecao;
				$items_sessao[$item->id]['insercao']          = $item->insercao;
				$items_sessao[$item->id]['acesso']            = $item->acesso;
				$items_sessao[$item->id]['alteracao']         = $item->alteracao;
				
			}
			
			
			
			
			//fecha montagem de sessão
			//$stdobj = new StdClass;
			//$stdobj->nome_programaf = '';
			//$objects->nome_programaf = $stdobj->nome_programaf;
			
			TSession::setValue('TS_permissaoTeste', $items_sessao);	
			TSession::setValue('TS_permissao', $objects);

				//var_dump($items_sessao);
			
			// close the transaction
                TTransaction::close();
				
				
			    $this->onReload($param);
				
			}//param['key']
			
			
			
		}//try
		catch(Exception $e  )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
	
	}//onEdit
	
	public function onAtualiza($param)
	{
		try
		{
			//TTransaction::open('permission');
			
			$key = TSession::getValue('TS_key');
			if($key)
			{	
				//limpa sessão
				TSession::setValue('TS_permissao', NULL );	
			
				
                
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object System_user
                $object = new SystemUser($key);
                
                unset($object->password);
                
                $groups = array();
                $units  = array();
                
                if( $groups_db = $object->getSystemUserGroups() )
                {
                    foreach( $groups_db as $group )
                    {
                        $groups[] = $group->id;
                    }
                }
				
				//var_dump($groups);
                
                if( $units_db = $object->getSystemUserUnits() )
                {
                    foreach( $units_db as $unit )
                    {
                        $units[] = $unit->id;
                    }
                }
                //var_dump($units);
				
                $program_ids = array();
                $acesso      = array();
                foreach ($object->getSystemUserPrograms() as $program)
                {
                    $program_ids[] = $program->id;
                    //$program_ids[]      = $program->acesso;
                }
                $object->groups       = $groups;
                $object->units        = $units;
				
				$object->nome_programaf = '';
				//grava na sessão
				TSession::setValue('TS_user', $object);
				
                //$object->program_list = $acesso;
               /* $object->program_list = $program_ids;
                $object->groups       = $groups;
                $object->units        = $units;*/
                
                // fill the form with the active record data
                
				
				//TForm::sendData('form_System_user', $object);
				
				$object->id_user = $object->id;
				$this->form->setData($object);
                
                
				
				
				
			//** verifica 	permissões *//*
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
				//$criteria->add(TSession::getValue('TS_filter_programa') );
			}
			
			//$criteria->add(new TFilter('system_group_id', '=', $grupo_id) );
			
			$criteria->add(new TFilter('system_user_id', '=', $object->id) );
			
			/*//teste se existe permissões especiais
			$count_obj = $repository->count($criteria);*/ 
			
			
			$objects = $repository->load( $criteria );
			
			//monta a variavel de sessão
			
			/*$items_sessao = array();
			foreach($objects as $item)//VALOR
			{
				$items_sessao[$item->id]                      = $item->toArray();
				$items_sessao[$item->id]['id']                = $item->id;
				$items_sessao[$item->id]['system_unit_id']    = $item->system_unit_id;
				$items_sessao[$item->id]['system_group_id']   = $item->system_group_id;
				$items_sessao[$item->id]['system_program_id'] = $item->system_program_id;
				$items_sessao[$item->id]['delecao']           = $item->delecao;
				$items_sessao[$item->id]['insercao']          = $item->insercao;
				$items_sessao[$item->id]['acesso']            = $item->acesso;
				
			}*/
			
			/* system_user_id    INT,
				system_group_id   INT,
				system_program_id INT,
				acesso            CHAR (3),
				insercao          CHAR (3),
				delecao           CHAR (3),
				acesso_especial   INT (1),*/
			
			
			//fecha montagem de sessão
			
			//TSession::setValue('TS_permissaoTeste', $items_sessao);	
			TSession::setValue('TS_permissao', $objects);	
			
			// close the transaction
                TTransaction::close();
				
				
			    $this->onReload($param);
				
			}//param['key']
			
			
			
		}//try
		catch(Exception $e  )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
	
	}//onAtualiza
	
	
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