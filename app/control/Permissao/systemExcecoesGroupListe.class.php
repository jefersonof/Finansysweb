<?php

class systemExcecoesGroupListe extends TPage
{
	protected $saveButton;
	
	// trait with onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;
	
	public function __construct()
	{
		parent::__construct();
		
		
		//cria o form
		$this->form  = new BootstrapFormBuilder('formExcecoesGroupListe');
		$this->form->class='tform';
		$this->form->setFieldSizes('100%');
		
		// creates the update collection button
        $this->saveButton = new TButton('update_collection');
        $this->saveButton->setAction(new TAction(array($this, 'onSave')), 'Save');
        $this->saveButton->setImage('fa:save green');
        //$this->formgrid->addField($this->saveButton);
		
		//cria os atributos
		$NOME   = new TEntry('NOME');
		$CODIGO = new TEntry('CODIGO');
		
		//cria as sessões
		$NOME->setValue(TSession::getValue('TS_nome'));
		$CODIGO->setValue(TSession::getValue('TS_codigo'));
		
		
		//add compos do form
		$row = $this->form->addFields(['Nome', $NOME ],
							          ['Código', $CODIGO ]);
		$row->layout = ['col-sm-10', 'col-sm-2'];
							   
		//ações do form
		$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'ico_find.png');	
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');
		//$this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:eraser red');//fa:eraser red	
		
		//cria a grid
		$this->datagrid = new TQuickGrid;
		$this->datagrid->setHeight(150);
		$this->datagrid->style = 'width:100%';
		$this->datagrid->makeScrollable();
		$this->datagrid->DisableDefaultClick;
		
		$this->datagrid->addQuickColumn('ID', 'id', 'center', '10%');
		$this->datagrid->addQuickColumn('Grupo', 'grupo_name', 'center', '60%');
		//$this->datagrid->addQuickColumn('Grupo', '{grupo->name}', 'center', '60%');
		$this->datagrid->addQuickColumn('Acesso', 'acesso', 'center', '10%');
		$this->datagrid->addQuickColumn('Inserção', 'insercao', 'center', '10%');
		$this->datagrid->addQuickColumn('Deleção', 'delecao', 'center', '10%');
		
		//cria as ações da grid
		
		//$this->datagrid->addQuickAction('Add Exceção' ,new TDataGridAction(array($this, 'onTeste')), 'id' , 'fa:edit blue' );
		
		$this->datagrid->createModel();
		
		//add a grid no scroll 
		/*$scroll = new TScroll;
		$scroll->setSize('100%', '200');
		$scroll->add($this->datagrid);*/	
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));		
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		
		
		//cria o painel
		$painel = new TPanelGroup('Lista dos Grupos');
		
		
		
		$this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);
		
		// define the datagrid transformer method
        $this->setTransformer(array($this, 'onBeforeLoad'));
		
		$painel->add($this->form);
		//$painel->add($scroll);
		$painel->add($this->formgrid);
		//$painel->add($this->datagrid);
		$painel->add($this->pageNavigation);
		
		//barra footer
		$painel->addFooter(THBox::pack($this->saveButton));
		
		//ativar a rolagem horizontal dentro do corpo do painel
		$painel->getBody()->style = "overflow-x:auto" ;
		
		//add os compos no form 
		$this->formFields = array($NOME, $CODIGO, $this->saveButton);
		$this->form->setFields($this->formFields);
		
		//add o painel em tela
		$menuBread = new TXMLBreadCrumb('menu.xml', __CLASS__);
		
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
					   $object = SystemUserGroupDefault::find($id);
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
			// $data_form->ID_USUARIO = $ts_data->ID_USUARIO;
			// $data_form->USUARIO = $ts_data->USUARIO;
			// $data_form->ID_GRUPO = $ts_data->ID_GRUPO;
			// $data_form->GRUPO = $ts_data->GRUPO;
			
			$this->form->setData($data_form);
			
			
        }
        catch (Exception $e)
        {
            // show the exception message
            new TMessage('error', $e->getMessage());
        }
	}//onSave
	
	/*onBeforeLoad*/
	
	/**
     * Executar antes que o datagrid seja carregado
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
	
	/**/
	
	/*onReload*/
	
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
			/*$grupo_id     =  TSession::getValue('TS_grupo_id');
			$user_id      =  TSession::getValue('TS_user_id');
			$ts_data      =  TSession::getValue('TS_data');*/
			
			//var_dump(implode($ts_data->GRUPO));
			
			$repository = new TRepository('SystemUserGroupDefault');
			$criteria   = new TCriteria;
			$limit = 10;
			
			//seta as propriedades
			$criteria->setProperty('order','id');//NOME
			$criteria->setProperty('direction','ASC');
			$criteria->setProperty('limit',$limit);
			
			$criteria->setProperties($param);
			
			//busca pelo nome
			if(TSession::getValue('TS_filter_nome') )
			{
				$criteria->add(TSession::getValue('TS_filter_nome') );
			}
			
			//$criteria->add(new TFilter('system_group_id', '=', $grupo_id) );
			
			//$criteria->add(new TFilter('system_user_id', '=', $user_id) );
			
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
					$item->grupo_name        = $object->grupo->name;
					//$item->system_group_id   = $object->system_group_id; 
					
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
			
							
			// $criteria->resetProperties();
			// $count = $repository->count($criteria); 

            // $this->pageNavigation->setCount ($count);
            // $this->pageNavigation->setProperties ($param);
            // $this->pageNavigation->setlimit($limit); 
			
			//manda os dados para o form
			/*$data->ID_GRUPO   = $grupo_id;
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
			}*/
			
			
			$this->form->setData($data);
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
				
		}	
	}//onReload
	
	/**/
	

	
	public function onSearch($param)
	{
		$data = $this->form->getData();
		
		if($data->NOME )
		{
			//$filtro = new TFilter('name', 'LIKE', "%$data->NOME%");
			$filtro = new TFilter('system_group_id', '=', "$data->NOME");
			TSession::setValue('TS_filter_nome', $filtro);
			TSession::setValue('TS_nome', $data->NOME);
		}

		$this->onReload($param);	
		
	}//onSearch
	
	// /**
     // * Run before the datagrid is loaded
     // */
    // public function onBeforeLoad($objects, $param)
    // {
        // // update the action parameters to pass the current page to action
        // // without this, the action will only work for the first page
        // $saveAction = $this->saveButton->getAction();
        // $saveAction->setParameters($param); // important!
        
        // $gridfields = array( $this->saveButton );
        
        // foreach ($objects as $object)
        // {
            // $object->acesso = new TCheckButton('acesso' . '_' . $object->id);
            // $object->acesso->setValue( $object->acesso );
			// $object->acesso->setIndexValue('1');
            // $gridfields[] = $object->acesso;// important
			
			// $object->insercao = new TCheckButton('insercao' . '_' . $object->id);
            // $object->insercao->setValue( $object->insercao );
			// $object->insercao->setIndexValue('1');
            // $gridfields[] = $object->insercao; // important
			
			// $object->delecao = new TCheckButton('delecao' . '_' . $object->id);
            // $object->delecao->setValue( $object->delecao );
			// $object->delecao->setIndexValue('1');
            // $gridfields[] = $object->delecao;// important
			
        // }
		
        // $this->formgrid->setFields($gridfields);
		
    // }//onBeforeLoad
	
	// public function onSave($param)
	// {	
		// $data_form = $this->form->getData();
		
		// $data = $this->formgrid->getData(); // get datagrid form data
        // $this->formgrid->setData($data); // keep the form filled
		
		// $ts_data  = TSession::getValue('TS_data');
		
		// var_dump($data);
		
        // try
        // {
            // // open transaction
            // TTransaction::open('permission');
            
            // // iterate datagrid form objects
            // foreach ($this->formgrid->getFields() as $name => $field)
            // {
                
				// if ($field instanceof TCheckButton)
				// {
					// $parts = explode('_', $name);
					// $id = end($parts);
					// $nome_campo = $parts[0];
									
					// if ($nome_campo == 'acesso')
					// {
					   // $object = systemUserGroupDefault::find($id);
					   // if ($object)
					   // {
							// $object->acesso   =  $field->getValue(); 
							// $object->insercao =  $data->{"insercao_$id"}; 
							// $object->delecao  =  $data->{"delecao_$id"};
							
							// if($object->acesso == '')
							// {
								// $object->acesso = 0;
							// }
							
							// if($object->insercao == '')
							// {
								// $object->insercao = 0;
							// }

							// if($object->delecao == '')
							// {
								// $object->delecao = 0;
							// }	
													
							// $object->store();
							
							// /*
							// $object->acesso   =  $field->getValue(); 
							// $object->insercao =  $data->{"insercao_$id"}; 
							// $object->delecao  =  $data->{"delecao_$id"};*/
													
							
							
							// //var_dump($data);
						// }
					// }
				// }
				
            // }//foreach
            // new TMessage('info', AdiantiCoreTranslator::translate('Record updated'));
            
            // // close transaction
            // TTransaction::close();
			
			// //manda os dados para o form
			
			
			// /*TForm::sendData('formExcecoes', $obj);*/
			// // $data_form->ID_USUARIO = $ts_data->ID_USUARIO;
			// // $data_form->USUARIO = $ts_data->USUARIO;
			// // $data_form->ID_GRUPO = $ts_data->ID_GRUPO;
			// // $data_form->GRUPO = $ts_data->GRUPO;
			// $this->form->setData($data_form);
			
			
        // }
        // catch (Exception $e)
        // {
            // // show the exception message
            // new TMessage('error', $e->getMessage());
        // }
	// }//onSave */
	
	public function onClear($param)
	{
		TSession::setValue('TS_filter_nome', NULL);
		
		$this->form->clear();
		
		$this->onReload($param);
	}
	
	public function onTeste()
	{
		
	}
	
	/*
	captura as parametros da URL e atualiza o onReload
	*/
	public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
		
    }//show
	
}//TPage

?>