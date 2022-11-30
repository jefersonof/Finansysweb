<?php
/**
 * PropostaForm Form
 * @author  <jeferson oliveira forte>
 */
class PropostaForm extends TPage
{
    protected $form;
    private   $datagrid;
	
	// trait with onSave, onClear, onEdit, ...
    use Adianti\Base\AdiantiStandardFormTrait;
	
	// trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
		//pega o grupo do usuário
		$grupo =  TSession::getValue('TS_grupo_id');
		var_dump($grupo);
		
        // creates the form
        $this->form = new BootstrapFormBuilder('formProposta');
		$this->form->setFieldSizes('100%');
		$this->form->class = 'tform'; 
		
		//captura a aba ativa  
		$this->form->setTabAction( new TAction(array($this, 'onTabClick')));
		
		// create the form fields
        $ID_PROPOSTA   = new TEntry('ID_PROPOSTA');
        $FUNCIONARIO   = new TEntry('FUNCIONARIO');
        $DESCRICAO     = new TEntry('DESCRICAO');
		$ID_CORRETOR   = new TDBSeekButton('CODIGO', 'DB2', 'formProposta', 'fornecedor', 'NOME', 'CODIGO', 'NOME_CORRETOR');
		$NOME_CORRETOR = new TEntry('NOME_CORRETOR');
		$NOME          = new TEntry('NOME');
		$CPF           = new TEntry('CPF');
		$STATUS        = new TCombo('STATUS');
		$USER_ID       = new THidden('USER_ID');//THidden
		$USER_NAME     = new TEntry('USER_NAME');
		$FOTO          = new TMultiFile('FOTO');
		$FOTO3         = new TMultiFile('FOTO3');
		$FOTO2         = new TCombo('FOTO2');
		
		//FORMATAÇÕES
		$NOME_CORRETOR->setEditable(FALSE);
		$FUNCIONARIO->setEditable(FALSE);
		$USER_NAME->setEditable(FALSE);
		$FUNCIONARIO->setValue( TSession::getValue('username'));
		$FOTO->setAllowedExtensions( ['png', 'jpg', 'csv'] );
		$FOTO->setSize('50%');
		//$FOTO->style = 'margin:0 160px 0 160px';
		$STATUS->style = 'height:28px';
		
		$FOTO3->setAllowedExtensions( ['png', 'jpg' ]);
		$FOTO3->setSize('100%');
		
		
		//add items
		$STATUS->addItems( array('APROVADO' => 'APROVADO', 'REPROVADO' => 'REPROVADO', 'ANALISANDO' => 'ANALISANDO'));
		
		//ativa ações de barra de progresso, visualização e remoção de arquivos
        $FOTO->enableFileHandling();
		
		if (!empty($ID_PROPOSTA))
        {
            $ID_PROPOSTA->setEditable(FALSE);
        }
		
		//COMEÇA A PAGINA
		
		
		//ABA DADOS
		$this->form->AppendPage('Dados da Proposta');
		
		//pega os dados do LoginForm
		$grupo_id = TSession::getValue('TS_grupo_id');
		if($grupo_id == 1  )//ADMIN
		{	
			$row = $this->form->addFields(['Id Proposta', $ID_PROPOSTA],
							              ['id Corretor', $ID_CORRETOR, ],
							              ['Nome Corretor', $NOME_CORRETOR ],
									      ['Cadastrado por', $USER_NAME ]);
		    $row->layout = ['col-sm-2','col-sm-2','col-sm-6','col-sm-2'];
			
			$row = $this->form->addFields([$USER_ID ]);
			$row->layout = ['col-sm-12'];
		}
		
		if($grupo_id == 4  )//PRODUÇÃO
		{	
			$row = $this->form->addFields(['Id Proposta', $ID_PROPOSTA],
							          ['id Corretor', $ID_CORRETOR, ],
							          ['Nome Corretor', $NOME_CORRETOR ],
									  ['Cadastrado por', $USER_NAME ]);
		    $row->layout = ['col-sm-2','col-sm-2','col-sm-6','col-sm-2'];
			
			$row = $this->form->addFields([$USER_ID ]);
			$row->layout = ['col-sm-12'];
		}
		
       if($grupo_id == 5  )//CORRETOR
		{
			
			$codigo = TSession::getValue('TS_cod_corretor');
            $nome   = TSession::getValue('TS_nome_corretor');
			
			$ID_CORRETOR->setEditable(FALSE);	
			$STATUS->setEditable(FALSE);	
			$USER_NAME->setEditable(FALSE);	
			
			$obj = new StdClass;
			$obj->ID_CORRETOR = $codigo; 
			
			//atualiza o form
			TForm::sendData('formProposta', $obj);	
			
			$row = $this->form->addFields(['Id Proposta', $ID_PROPOSTA],
							          
									  ['Cadastrado por', $USER_NAME ]	);
		    $row->layout = ['col-sm-2','col-sm-2','col-sm-6','col-sm-2'];
			
			$row = $this->form->addFields([$USER_ID ]);
			$row->layout = ['col-sm-12'];
		
		}//if($grupo_id == 5	
		
		$row = $this->form->addFields(['Nome', $NOME],
		                              ['CPF', $CPF],
		                              ['Status', $STATUS]);
		$row->layout = ['col-sm-8', 'col-sm-2', 'col-sm-2'];
		
		//ABA ANEXOS
		$this->form->AppendPage('Outras Dados');
		
		$row = $this->form->addFields([$FOTO]);
		$row->layout = [ 'col-sm-12'];
		// $row->style = 'margin:0 0 0 0';
		
		   
        // Ações do form
        $btn = $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
		
        $this->form->addAction('Novo',  new TAction(array($this, 'onClear2')), 'bs:plus-sign green');//onClear
		
		$this->form->addAction('Listar',  new TAction(array('PropostaListe', 'onReload')), 'fa: fas fa-bars  blue');// fas fa-list
		
		$this->form->addAction('View',  new TAction(array($this, 'onSend')), 'fa: fas fa-eye blue ');
		
		//empacotamento
		$painel = new TPanelGroup('Cadastro de Proposta');
		$painel->add($this->form);
		
		//ativar a rolagem horizontal dentro do corpo do painel
		$painel->getBody()->style = 'overflow-x:auto';
				
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'PropostaListe'));
        $container->add( $painel );
        
        parent::add($container);
		
    }//__construct
	
	public function onReload()
	{
		$ts_current_page = TSession::getValue('TS_current_page');
		$this->form->setCurrentPage($ts_current_page);
		
	}//onReload
	
	
    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('db2'); // open a transaction
            
            $this->form->validate(); // validate form data
            
			$data   = $this->form->getData();
			
			$proposta = new proposta;
			$proposta->CORRETOR = $data->CODIGO;
            $proposta->fromArray( (array) $data);
			
			
            /*se o 'STATUS' estiver em branco, salva como 'ANALISANDO'*/
			if($data->STATUS == '' )
			{
				$proposta->STATUS = 'ANALISANDO';
			}
			
			/*caso o corretor corrija o erro da proposta ela volta para 'ANALISANDO'*/
			$grupo_id = TSession::getValue('TS_grupo_id');
			if( ($grupo_id == 5 ) and ($param['STATUS'] == 'REPROVADO') )
			{
				$proposta->STATUS = 'ANALISANDO';
				
			}

			//pega o nome do usário quando o obj é criado 
			if(empty($data->USER_NAME))   
			{
				$proposta->USER_ID   = TSession::getValue('userid');	
			    $proposta->USER_NAME = TSession::getValue('username');
			}
			else
			{
				$proposta->USER_ID2   = $data->USER_ID;
			    $proposta->USER_NAME  = $data->USER_NAME;
			}	
				
			$proposta->store();
			
			//Salva os agregados 'proposta_foto'
			$this->saveFiles($proposta, $data, 'FOTO', 'files/images', 'proposta_foto', 'FOTO', 'PROPOSTA_ID');
			
            // Manda os dados para o form
            $data->ID_PROPOSTA = $proposta->ID_PROPOSTA;
            $data->USER_NAME   = $proposta->USER_NAME;//USER_NAME
            $data->STATUS      = $proposta->STATUS;
            $data->USER_ID     = $proposta->USER_ID;
			
			//obj padrão
			$obj = new StdClass;
			$obj->USER_NAME = $proposta->USER_NAME;
			
			//Manda os dados para o form
			TForm::sendData('formProposta', $obj);
			
			
            $this->form->setData($data);
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
			
			$num_page = TSession::getValue('TS_current_page');
			$this->form->setCurrentPage($num_page);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
		
    }//onSave
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
		
		$data = $this->form->getData();
		
		$data->CODIGO        = '';
		$data->ID_PROPOSTA   = '';
		$data->NOME_CORRETOR = '';
		$data->ID_FOTO       = '';
		$data->FOTO          = '';
		$data->DESCRICAO     = '';
		
		$this->form->setData($data);
		
		TSession::setValue('TS_foto', NULL);
		TSession::setValue('TS_data', NULL);
		TSession::setValue('TS_key', NULL);
		
		
		$this->onReload($param);
		
		/*$data->ID_FOTO   = '';
		$data->FOTO      = '';
		$data->DESCRICAO = '';
		TSession::setValue('TS_data', $data);*/
		
    }//onClear
	
	/**
     * Clear2 form data
     * @param $param Request
     */
    public function onClear2( $param )
    {
        //APAGA AS SESSÕES
		TSession::setValue('TS_key', NULL);
		TSession::setValue('TS_foto', NULL);
		TSession::setValue('TS_data', NULL);
		
		//LIMPA O FORM
		$this->form->clear();
		
		//grava 
		$data = $this->form->getData();
		
		// $data->ID_CORRETOR   = '';
		// $data->ID_PROPOSTA   = '';
		// $data->CPF           = '';
		// $data->USER_ID       = '';
		// $data->USER_NAME     = '';
		// $data->NOME_CORRETOR = '';
		// $data->NOME          = '';
		// $data->STATUS        = '';
		// $data->CODIGO        = '';
		// //$data->FOTO          = '';
		// $data->DESCRICAO     = '';
		
		//se for corretor trás o id e o nome
		//pega o id do grupo do usuário
		$grupo_id = TSession::getValue('TS_grupo_id');
		
		if($grupo_id == 5)
		{
			$codigo = TSession::getValue('TS_cod_corretor');
			$nome   = TSession::getValue('TS_nome_corretor');
			
			$data->CODIGO        = $codigo;
			$data->NOME_CORRETOR = $nome;
		}	
		/*TSession::setValue('TS_cod_corretor', $codigo);
					TSession::setValue('TS_nome_corretor', $nome);*/
		
		
		TSession::setValue('TS_data', $data);
		
		$this->form->setData($data);
		$this->onReload($param);
		
    }//onClear2
	
	public function onTeste($param)
	{
		$this->form->setCurrentPage(1);
		
	}//onTeste
	
	
	public function onEdit($param)
	{
		try
		{
		
			if(isset($param['key']))
			{	
			
				TTransaction::open('db2');  
				
				$this->form->clear();
				$data = $this->form->getData();
				
				$key = $param['key']; 
				$proposta = new proposta($key); 
				
				$foto = array();	
				foreach($proposta->getproposta_foto() as $propostas)
				{
					$foto[$propostas->ID_PROPOSTA_FOTO] = $propostas->FOTO;
					
				}
				//var_dump ($foto); 
				//TCombo::reload('formProposta', 'FOTO2', $foto); DEBUG
				/*$STDClass = new STDClass;
				$STDClass->FOTO = $foto;
				TForm::sendData('formProposta', $STDClass);*/
				
				//TForm::sendData('formAssociado', $obj);
				
				$data->FOTO  = $foto;
				$data->ID_PROPOSTA = $proposta->ID_PROPOSTA;
				
				$data->NOME_CORRETOR = $proposta->nome_corretor->NOME;//_FANTASIA 
				$data->CODIGO        = $proposta->nome_corretor->CODIGO; 
				$data->NOME          = $proposta->NOME; 
				$data->CPF           = $proposta->CPF; 
				$data->STATUS        = $proposta->STATUS; 
				$data->USER_NAME     = $proposta->USER_NAME; 
				$data->USER_ID       = $proposta->USER_ID; 
				$data->ID_PROPOSTA   = $proposta->ID_PROPOSTA;
				  
				$this->form->setData($data);  
				 
				TTransaction::close(); // close the transaction
				
				//define a aba ativa
				//$ts_current_page = 0;
			    //$this->form->setCurrentPage($ts_current_page);
				//TSession::setValue('TS_current_page', $ts_current_page);
				
				$this->form->setCurrentPage(0);
				
				//$this->onTeste($param);
				
			}
			else
			{
				TTransaction::open('db2');
				
				$login_id =  (TSession::getValue('userid')); 
				echo 'User ' .  $login_id . '<br>';	
				
				//se o grupo for = 5 'corretor'
				//pega o nome do fornecedor pelo id do usuário
				
				$grupo_id = TSession::getValue('TS_grupo_id');
				if($grupo_id = 5 )
				{	
					$conn = TTransaction::get();
					$result = $conn->query('SELECT CODIGO, NOME, USER_ID FROM FORNECEDORES2 WHERE USER_ID = '. $login_id );
					  
					foreach( $result as $row)
					{
						$codigo   =  $row['CODIGO'];
						$nome     =  $row['NOME'];
						//$userid   =  $row['USER_ID'];
						  
					}//foreach

				}//grupo 5 'corretor'	

				TTransaction::close();
				
				if(empty($codigo))
				{
					$data->CODIGO = '';
				}
				else
				{
					$data->CODIGO        = $codigo;
				}

				if(empty($nome))
				{
					$data->NOME_CORRETOR = '';
				}
				else
				{
					$data->NOME_CORRETOR = $nome;
				}
				
				//define a aba ativa
				$ts_current_page = 0;
			    $this->form->setCurrentPage($ts_current_page);
				TSession::setValue('TS_current_page', $ts_current_page);
				
			}//else $param['key']
			
		}//try
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}	
		
	}//onEdit
	
	/**
     * grava na sessão a aba atual do form 
     */
    public static function onTabClick($param)
    {
        //new TMessage('info', '<b>You have clicked at the tab</b>:  <br><br>' . $param['current_page'] . str_replace(',', '<br>', json_encode($param)));
		
		//grava a aba na sessão a cada troca 
		TSession::setValue('TS_current_page', $param['current_page'] );
		
    }//onTabClick
	
	
	 /**
     * Post data
     */
    public function onSend($param)
    {
        $data = $this->form->getData();
        $this->form->setData($data);
        
        $win = TWindow::create('Result', 0.8, 0.8);
        $win->add( '<pre>' . print_r($data, true) . '</pre>' );
        $win->show();
    }
	
	public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
		
    }//show
	
	
	
	//* public function onReload($param)
	// {
		// //$data = $this->form->getData();
		// $data    = TSession::getValue('TS_data');
		
		// //paga a variavel de sessão 
	    // $ts_foto = TSession::getValue('TS_foto');
	    // $ts_key  = TSession::getValue('TS_key');
	    
		// // LIMPA AS GRIDS 
		// $this->datagrid->clear();
		
		// //CARREGA OS DADOS DAS FOTOS
		// if ($ts_foto)
        // {
            // $cont = 1;
            // foreach ($ts_foto as $list_product_id => $list_product)
            // {
                // $item_name = 'prod_' . $cont++;
                // //OBJ PADRÃO DAS CLASSES
				// $item = new StdClass;

				// //CRIA AS AÇÕES DOS BTN
                // $action_del = new TAction(array($this, 'onDeleteItem'));
                // $action_del->setParameter('list_product_id', $list_product_id);
				// $action_del->setParameter('cont',$cont);

				// $action_edi = new TAction(array($this, 'onEditItem'));
                // $action_edi->setParameter('list_product_id', $list_product_id);
				// $action_edi->setParameter('cont',$cont);
				
				// //CRIA OS BTN E ADD AS AÇÕES
                // $button_del = new TButton('delete_product'.$cont);
                // $button_del->class = 'btn btn-default btn-sm';
                // $button_del->setAction( $action_del, '' );
                // $button_del->setImage('fa:trash-o red fa-lg');

                // $button_edi = new TButton('edit_product'.$cont);
                // $button_edi->class = 'btn btn-default btn-sm';
                // $button_edi->setAction( $action_edi, '' );
                // $button_edi->setImage('fa:edit blue fa-lg');

                // //ASSOCIA O OBJ PADRÃO AOS BNT  
				// $item->edit    = $button_edi;
                // $item->delete  = $button_del;

                // $this->formFields[ $item_name.'_edit' ]   = $item->edit;
                // $this->formFields[ $item_name.'_delete' ] = $item->delete;
				
				// //ADD OS ITEMS NA GRID datagrid_af 
				// $item->ID_FOTO   = $list_product['ID_FOTO'];
				// //$item->FOTO      = $list_product['FOTO'];
				// $item->DESCRICAO = $list_product['DESCRICAO'];
				
				// $this->datagrid->addItem( $item );
                
				// //pega o id do controle + 1 para nova regra
				// $data->ID_FOTO =  (1 + $list_product['ID_FOTO']);
				
				// if($data->ID_FOTO < 1 )
				// {
					// $data->ID_FOTO = 1 ;
				// }
				
				// //$data->FOTO = $list_product['FOTO'];
				
				// $array_teste = array( '1' => 'Teste1', '2' => 'Teste2', '3' => 'Teste3' );
				
				// //$FOTO->addItems($array_teste);
				// //$FOTO->setValue($array_teste);
				// //$this->FOTO = $array_teste;
				
				// $data->ID_PROPOSTA = $ts_key;
				// $this->form->setData($data);
				
            // }//foreach ($ts_foto)
			// $this->form->setFields( $this->formFields );
			
            
        // }//if ($ts_foto)
			
        // //$this->form->setFields( $this->formFields );
		// //$data->ID_PROPOSTA = $ts_key;
		// $this->form->setData($data);
			
		// $this->loaded = TRUE;
		
	// }//onReload*/

	
    /**
     * Load object to form data
     * @param $param Request
     */
    // public function onEdit( $param )
    // {
        // try
        // {
            // if (isset($param['key']))
            // {
                // $key = $param['key'];  // get the parameter $key
				// TSession::setValue('TS_key', $key);
                
                // TTransaction::open('db2'); 
                
				// //$data = $this->form->getData();
				// $this->form->clear();
                // $proposta = new proposta($key); 
				
				// //Mostra o nome e codigo do corretor 'UF_RES'
				// $obj = new STDClass;
				// $obj->NOME_CORRETOR = $proposta->nome_corretor->NOME;//_FANTASIA 
				// $obj->CODIGO        = $proposta->nome_corretor->CODIGO; 
				// $obj->NOME          = $proposta->NOME; 
				// $obj->CPF           = $proposta->CPF; 
				// $obj->STATUS        = $proposta->STATUS; 
				// $obj->USER_NAME     = $proposta->USER_NAME; 
				// //$obj->USER_ID       = $proposta->USER_ID; 
				// $obj->ID_PROPOSTA   = $proposta->ID_PROPOSTA; 
				
				
					
				// TForm::sendData('formProposta', $obj);
				
				// $data = $this->form->getData();
				// TSession::setValue('TS_data', $data);
				
				// //** PLANOS_COB **//
				// $ts_foto = array();
				// foreach($proposta->getproposta_foto() as $proposta_foto )
				// {
					// $ts_foto[$proposta_foto->ID_PROPOSTA_FOTO]                   = $proposta_foto->toArray();
					
					// $ts_foto[$proposta_foto->ID_PROPOSTA_FOTO]['ID_FOTO']         = $proposta_foto->ID_PROPOSTA_FOTO;
					
					// $ts_foto[$proposta_foto->ID_PROPOSTA_FOTO]['FOTO'] = $proposta_foto->FOTO;
					
					// $ts_foto[$proposta_foto->ID_PROPOSTA_FOTO]['DESCRICAO']      = $proposta_foto->DESCRICAO;
				// }//getproposta_foto
				
				// //carrega item e detalha na sessão
				// /*public function loadItems($model, $foreign_key, $master_object, $detail_id, Callable $transformer = null*/
				
				// //$this->loadItems();
				
				// ///fecha detalha items
				
				// //GRAVA AS 'proposta_foto' NA SESSÃO
				// TSession::setValue('TS_foto', $ts_foto);
				// $proposta->ID_PROPOSTA = $proposta->ID_PROPOSTA;
                // $this->form->setData($data); // fill the form
                // //$this->form->setData($proposta); // fill the form
				
				// $this->onReload( $param );
                
                // TTransaction::close(); // close the transaction
            // }
            // else
            // {
                // //LIMPA O FORM
				// $this->form->clear();
				// //$this->form->clear(TRUE);
		
				// //CAPTURA OS DADOS
				// $data = $this->form->getData();
					
				//TTransaction::open('db2');//permission
				
				// $login_id =  (TSession::getValue('userid')); 
		        // echo 'User ' .  $login_id . '<br>';	
				
				// //se o grupo for = 5 'corretor'
				// //pega o nome do fornecedor pelo id do usuário
				
				// $grupo_id = TSession::getValue('TS_grupo_id');
				// if($grupo_id = 5 )
				// {	
					// $conn = TTransaction::get();
					// $result = $conn->query('SELECT CODIGO, NOME, USER_ID FROM FORNECEDORES2 WHERE USER_ID = '. $login_id );
					  
					// foreach( $result as $row)
					// {
						// $codigo   =  $row['CODIGO'];
						// $nome     =  $row['NOME'];
						// $userid   =  $row['USER_ID'];
						  
					// }//foreach

			    // }//grupo 5 'corretor'	
  
				// TTransaction::close();
				
				// if(empty($codigo))
				// {
					// $data->CODIGO = '';
				// }
				// else
				// {
					// $data->CODIGO        = $codigo;
				// }

				// if(empty($nome))
				// {
					// $data->NOME_CORRETOR = '';
				// }
				// else
				// {
					// $data->NOME_CORRETOR = $nome;
				// }

				/*if(empty($userid))
				{
					$data->USER_ID = '';
				}
				else
				{
					$data->USER_ID = $userid;
				}*/
				
				
				
				// //$data->CODIGO        = $codigo;
				// $data->ID_PROPOSTA   = '';
				// //$data->NOME_CORRETOR = $nome;
				// $data->ID_FOTO       = '';
				// $data->FOTO          = '';
				// $data->DESCRICAO     = '';
				// $data->STATUS        = '';
				// $data->CPF           = '';
				// $data->USER_NAME     = '';
				// //$data->USER_ID       = '';
				// $data->NOME          = '';
				
				// $this->form->setData($data);
				
                // TSession::setValue('TS_data', $data);
                // //TSession::setValue('TS_data', null);
                // TSession::setValue('TS_foto', null);
                // TSession::setValue('TS_key', null);
				
                // $this->onReload($param);
            // }
        // }
        // catch (Exception $e) // in case of exception
        // {
            // new TMessage('error', $e->getMessage()); // shows the exception error message
            // TTransaction::rollback(); // undo all pending operations
        // }
		
    // }//onEdit
	
	
	
	
	
}//TPage

