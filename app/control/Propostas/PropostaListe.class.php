<?php
class PropostaListe Extends TPage
{
	private $form;
	private $datagrid;
	private $pageNavigation;
	
	public function __construct()
	{
		parent::__construct();
		
		//cria o form
		$this->form  = new BootstrapFormBuilder('formPropostaListe');
		$this->form->class='tform';
		
		//cria os atributos
		$busca      = new TEntry('BUSCA');
		$listcampos = new TCombo('LISTCAMPOS');
		$usuario    = new TEntry('USUARIO');
		
		$usuario->setValue = 'teste';
		
		//ação de saida da 'TCombo', após trocar o seu valor
		$listcampos->setChangeAction(new TAction(array($this, 'onLimpaBusca')));
		
		//cria as sessões
		$busca->setValue(TSession::getValue('TS_busca'));
		$listcampos->setValue(TSession::getValue('TS_listcampos'));
		
		//cria os botões
		$btn_fechar = TButton::create('btn_fechar' ,array('PageInicial', 'onReload'), 'Fechar', 'fa: fa-power-off red' );
		
		$btn_incluir = TButton::create('btn_incluir', array('PropostaForm', 'onClear2'),  'Incluir',  'fa: fa-plus blue');
		
		//formatações
		$listcampos->setSize('100%');
		$busca->setSize('100%');
		$listcampos->addItems( array('ID_PROPOSTA' => 'Id Proposta','NOME' => 'Nome', 'FUNCIONARIO' => 'Funcionário', 'CORRETOR' => 'Corretor', 'TODOS' => 'Todos', 'APROVADOS' => 'Aprovados','REPROVADO' => 'Reprovados', 'ANALISANDO' => 'Em análise' ) );
		
		//add compos do form
		$this->form->addFields([ $listcampos ],
							   [ $busca ]);
							   
		//ações do form
		$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'ico_find.png');					   
		
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');//fa:eraser red					   
		
		// creates one datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
		//$this->datagrid = new TQuickGrid;
        $this->datagrid->style = 'width: 100%';
        
        // make scrollable and define height
        $this->datagrid->setHeight(230);
        $this->datagrid->makeScrollable();
        
        // create the datagrid columns
        $col_id_proposta = new TDataGridColumn('ID_PROPOSTA', 'Id', 'right', '10%');
		
        $col_nome     = new TDataGridColumn('NOME', 'Nome', 'left', '35%');//35%
        
		$col_cpf      = new TDataGridColumn('CPF', 'CPF', 'left', '10%');
		
        $col_corretor = new TDataGridColumn('{nome_corretor->NOME}', 'Corretor', 'left', '25%');//35%
		
        $col_status   = new TDataGridColumn('STATUS', 'STATUS', 'left', '10%');
        
		$col_cad      = new TDataGridColumn('USER_NAME', 'User', 'left', '10%');
        
        $col_status->setTransformer(array($this, 'formatafont'));//add cor 
		
		// add the columns to the datagrid
        $this->datagrid->addColumn($col_id_proposta);
        $this->datagrid->addColumn($col_nome);
        $this->datagrid->addColumn($col_cpf);
        $this->datagrid->addColumn($col_corretor);
        $this->datagrid->addColumn($col_status);
        $this->datagrid->addColumn($col_cad);
		
		//Ações da datagrid
		$this->datagrid->addQuickAction('Editar' ,new TDataGridAction(array('PropostaForm', 'onEdit')), 'ID_PROPOSTA' , 'fa:edit blue' );
		
		///permite a exclussão apenas pelo usuário Adm ou quem cadastrou a proposta
		//pega o id do usuario
		
		/*$login_id =  (TSession::getValue('userid')); 
		//echo $login_id;
		
		//pega o id do grupo de usuario
		TTransaction::open('permission');//permission
          
          $conn = TTransaction::get();
          
          $result = $conn->query('SELECT system_group_id FROM system_user_group WHERE system_user_id = '. $login_id );
          
          foreach( $result as $row)
		  {
              $grupo_id =  $row['system_group_id'] . '<br>';
			  
          }//foreach 

			//echo $grupo_id;
          
        TTransaction::close();*/
		
		//Pega o id do grupo no LoginForm
		//$grupo_id = implode(TSession::getValue('TS_grupo_id') );
		
		 // var_dump($grupo_id);
		// if($grupo_id == 1  )
		// {	
			// $this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'ID_PROPOSTA', 'fa:trash-o red fa-lg' );
		// }
		
			
		$this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'ID_PROPOSTA', 'fa:trash-o red fa-lg' );
		
		//acões Inline
		$editaction = new TDataGridAction(array($this, 'onEditInline'));
        $editaction->setField('ID_PROPOSTA');
        $col_nome->setEditAction($editaction);
		
		$editaction2 = new TDataGridAction(array($this, 'onEditInline'));
        $editaction2->setField('ID_PROPOSTA');
        $col_cpf->setEditAction($editaction2);
		
		$this->datagrid->createModel();
		
		
		//pega o nome do fornecedor pelo login_id
		/*if($grupo_id == 5  )
		{
			TTransaction::open('db2');
			$conn = TTransaction::get();
				  
			$result = $conn->query('SELECT CODIGO, NOME FROM FORNECEDORES2 WHERE USER_ID = '. $login_id );
				  
			foreach( $result as $row)
			{
				$codigo  =  $row['CODIGO'];
				$nome    =  $row['NOME'];
					  
			}//foreach 

			echo 'Código Fornec ' . $codigo . '</br>' ;
			//echo 'nome '    . $nome ;
				  
			TTransaction::close();
			
			$codigo = TSession::getValue('TS_cod_corretor');
			$nome   = TSession::getValue('TS_nome_corretor');
		}*/	
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		//$this->pageNavigation->style = 'margin:0 0 0 150px';
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));		
		
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//cria o scroll
		//add a grid no scroll 
		$scroll = new TScroll;
		$scroll->setSize('100%', '300');
		$scroll->add($this->datagrid);		
		
		//cria o painel
		$painel = new TPanelGroup('Cadastro de Agente (T006)');
		
		$painel->add($this->form);
		$painel->add($scroll);//$this->datagrid
		$painel->add($this->pageNavigation);
		
		//barra footer
		$painel->addFooter(THBox::pack($btn_fechar, $btn_incluir));
		
		//ativar a rolagem horizontal dentro do corpo do painel
		$painel->getBody()->style = "overflow-x:auto" ;
		
		//add os compos no form 
		$this->formFields = array($busca, $listcampos, $usuario, $btn_fechar, $btn_incluir);
		$this->form->setFields($this->formFields);
		
		//add o painel em tela
		$menuBread = new TXMLBreadCrumb('menu.xml', __CLASS__);
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($painel);
		
        parent::add($vbox);
		
	}//__construct
	
	
	
	/*
	Exclui uma 'fornecedor' após confirmação
	*/
	/*public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['ID_PROPOSTA'];
	
			$rp_proposta = new TRepository('proposta');	
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('ID_PROPOSTA', '=', $key));
			
			$rp_proposta->delete($criteria);	
			
			TTransaction::close();
			
			$this->onReload($param);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onSimDelete*/
	
	
	/*
	public static function onSimDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$rp_fornecedor = new TRepository('fornecedor');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('CODIGO', '=', $param['CODIGO'] ));
			
			$rp_fornecedor->delete($criteria);
			
			TTransaction::close();
			
			//$action = new TAction(array('FornecedoresListe', 'onReload'));
			//new TMessage('info', 'Registro apagado', $action);
			
			$this->onReload($param);
			
		}
		catch(Exception $e  )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
		
	}//onSimDelete
	*/
	/*
	  Questiona a exclusão de um 'fornecedor'
	*/
	/*public static function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			$proposta = new proposta($key);
			
			//Pega o nome do fornecedor
			$nome = $proposta->NOME;
			
			$onsim = new TAction( array('PropostaListe', 'onSimDelete'));
			$onsim->setParameter('ID_PROPOSTA', $key);
			
			new TQuestion('Deseja apagar o item' . ' " ' . $nome . ' " ', $onsim);
			
			
			TTransaction::close();
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onDelete*/
	
	/*
	  Atualiza a página com os parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$rp_proposta = new TRepository('proposta');
			$criteria    = new TCriteria;
			
			//set as propriedades
			//$criteria->setProperty('order','NOME');//NOME
			$criteria->setProperty('order','ID_PROPOSTA');
			$criteria->setProperty('direction','ASC');//ASC
			$criteria->setProperty('limit',15);
			
			$criteria->setProperties($param);
			
			
			if(TSession::getValue('TS_filter_id_proposta') )
			{
				$criteria->add(TSession::getValue('TS_filter_id_proposta'));
			}
			
			if(TSession::getValue('TS_filter_nome') )
			{
				$criteria->add(TSession::getValue('TS_filter_nome'));
			}
			
			if(TSession::getValue('TS_filter_funcionario') )
			{
				$criteria->add(TSession::getValue('TS_filter_funcionario'));
			}
			
			if(TSession::getValue('TS_filter_corretor') )
			{
				$criteria->add(TSession::getValue('TS_filter_corretor'));
			}
			
			if(TSession::getValue('TS_filter_todos') )
			{
				$criteria->add(TSession::getValue('TS_filter_todos'));
			}
			
			if(TSession::getValue('TS_filter_aprovado') )
			{
				$criteria->add(TSession::getValue('TS_filter_aprovado'));
			}
			
			if(TSession::getValue('TS_filter_reprovado') )
			{
				$criteria->add(TSession::getValue('TS_filter_reprovado'));
			}
			
			if(TSession::getValue('TS_filter_analisando') )
			{
				$criteria->add(TSession::getValue('TS_filter_analisando'));
			}
			
			//pega o grupo do usuário, se for corretor pega apenas as suas propostas.
			$grupo_id = TSession::getValue('TS_grupo_id');
			
			if($grupo_id == 5)
			{
				
				$codigo = TSession::getValue('TS_cod_corretor');
				$criteria->add(new TFilter('CORRETOR', '=', $codigo));
				
				//$login_id =  TSession::getValue('userid');
				//$criteria->add( 'USER_ID', '=', $login_id);
	   	    }
			
						
			//TRepository load
			$proposta =  $rp_proposta->load($criteria);
			
			$this->datagrid->clear();
			if($proposta)
			{
				foreach($proposta as $propostas)
				{
					$this->datagrid->addItem($propostas);
					
					/*$propostas->STATUS = new TCombo('USER_ID333');
					$propostas->STATUS->addItems(array('APROVADO' => 'APROVADO', 'REPROVADO' => 'REPROVADO', 'ANALISANDO' => 'ANALISANDO' ));

					$propostas->STATUS->setValue($propostas->STATUS);*/
					
					
					//$this->form->addField($propostas->STATUS);
				}
			}	
			
			$criteria->resetProperties();
			$count = $rp_proposta->count( $criteria ); 

            $this->pageNavigation->setCount ( $count );
            $this->pageNavigation->setProperties ( $param );
            $this->pageNavigation->setlimit(15);
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onReload
	
	/*
	  Grava os filtros de busca na sessão e chama o onReload()
	*/
	public function onSearch($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			if($data->LISTCAMPOS == 'ID_PROPOSTA' )
			{	
				$filter = new TFilter('ID_PROPOSTA', '=', $data->BUSCA);
				TSession::setValue('TS_filter_id_proposta', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_id_proposta', NULL );
			}
			
			if($data->LISTCAMPOS == 'NOME' )
			{	
				$filter = new TFilter('NOME', 'like', "%$data->BUSCA%" );
				TSession::setValue('TS_filter_nome', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_nome', NULL );
			}
			
			if($data->LISTCAMPOS == 'FUNCIONARIO' )
			{	
				$filter = new TFilter('FUNCIONARIO', 'LIKE', "%$data->BUSCA%");
				TSession::setValue('TS_filter_funcionario', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_funcionario', NULL );
			}
			
			
			if($data->LISTCAMPOS == 'CORRETOR' )
			{
				//$data->BUSCA = strtoupper($data->BUSCA);	
				$filter = new TFilter('(SELECT NOME from FORNECEDORES2 WHERE CODIGO=PROPOSTA.CORRETOR)', 'like', "{$data->BUSCA}%");
				TSession::setValue('TS_filter_corretor', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
				
			}
			else
			{
				TSession::setValue('TS_filter_corretor', NULL );
			}
			
			if($data->LISTCAMPOS == 'TODOS' )
			{	
				$filter = new TFilter('ID_PROPOSTA', '>', 0);
				TSession::setValue('TS_filter_todos', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_todos', NULL );
			}
			
			if($data->LISTCAMPOS == 'APROVADOS' )
			{	
				$filter = new TFilter('STATUS', 'like', 'APROVADO' );
				TSession::setValue('TS_filter_aprovado', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_aprovado', NULL );
			}
			
			if($data->LISTCAMPOS == 'REPROVADO' )
			{	
				$filter = new TFilter('STATUS', 'like', 'REPROVADO' );
				TSession::setValue('TS_filter_reprovado', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_reprovado', NULL );
			}
			
			if($data->LISTCAMPOS == 'ANALISANDO' )
			{	
				$filter = new TFilter('STATUS', 'like', 'ANALISANDO' );
				TSession::setValue('TS_filter_analisando', $filter );
				TSession::setValue('TS_busca', $data->BUSCA);
				TSession::setValue('TS_listcampos', $data->LISTCAMPOS);
			}
			else
			{
				TSession::setValue('TS_filter_analisando', NULL );
			}
			
			
			$this->form->setData($data);
			
			$this->onReload( $param );
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
		
	}//onSearch
	
	/*
	  Limpa o form e as variaveis de sessão
	*/
	public function onClear($param)
	{
		//$this->datagrid->clear();
		
		$data = $this->form->getData(); 
		$data->LISTCAMPOS = '';
		$data->BUSCA = '';
		$this->form->setData($data);
		
		TSession::setValue('TS_filter_nome', NULL);
		TSession::setValue('TS_filter_id_proposta', NULL);
		TSession::setValue('TS_filter_funcionario', NULL);
		TSession::setValue('TS_filter_corretor', NULL);
		TSession::setValue('TS_filter_aprovado', NULL);
		TSession::setValue('TS_filter_reprovado', NULL);
		TSession::setValue('TS_filter_analisando', NULL);
		TSession::setValue('TS_filter_todos', NULL);
		TSession::setValue('TS_listcampos', NULL);
		TSession::setValue('TS_busca', NULL);
		
		//desatica o 'btn_pesquisar '
		TButton::disableField('formPropostaListe', 'btn_pesquisar');

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
		
		TForm::sendData('formPropostaListe', $obj_std);
		
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
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            // open a transaction with database 'samples'
            TTransaction::open('db2');
            
            // instantiates object banco
            $proposta = new proposta($key);
            $proposta->{$field} = $value;
			$proposta->store();
			
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
			$key = $param['ID_PROPOSTA'];
			
			$ac_sim = new TAction( array($this, 'onSim') );
			$ac_sim->setParameter('ID_PROPOSTA', $key);
			
			
			new TQuestion('Apagar o Registro', $ac_sim);
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
			TTransaction::open('db2');
			
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
	}
	
	
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
		
	/*
	public function show()
	{
		if(!$this->loaded)
		{
			$this->onReload( func_get_arg(0) );
		}	
        parent::show(); 
		
	}//show
	
	*/	
		
}//TWindow 

?>