<?php

class ClienteListe extends TPage
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
		$this->form = new BootstrapFormBuilder('formAssossiado');
		$this->form->setFieldSizes('100%');
		//$this->form->style = 'width:100%'; 
		$this->form->class = 'tform'; 
		
		//cria os atributo
		$nome       = new TEntry('NOME');
		$cpf        = new TEntry('CPF'); 
		$matricula  = new TEntry('MATR_INTERNA'); 
		
		//recupera a sessão
		$cpf->setValue( TSession::getValue('TS_relacao_cpf'));
		$nome->setValue( TSession::getValue('TS_relacao_nome')) ;
		$matricula->setValue( TSession::getValue('TS_relacao_matricula')) ;
		
		//cria o botão
		$btn_fechar = TButton::create('btn_fechar', array('PageInicial', 'onReload'), 'Fechar', 'fa: fa-power-off red');	
		
		$btn_pdf = TButton::create('btn_pdf', array($this, 'onPDF'), 'Imprime Lista', 'ico_print.png');
		
		$btn_pdf2 = TButton::create('btn_pdf2', array($this, 'onPDFCompleto'), 'Imprime Lista Completa', 'ico_print.png'); /* pdf_cliente/onRelatorio*/	
		
		$btn_limpar = TButton::create('btn_limpar', array($this, 'onClear'), 'Limpar', 'fa:eraser red');
		
		//formatação
		$nome->setSize('100%');
		$cpf->setSize('100%');
		$matricula->setSize('100%');
		
		//add os atributos dentro do form
		$row = $this->form->addfields( [ new  TLabel('CPF'), $cpf ],
								       [ new TLabel('NOME'), $nome ]);
		$row->layout = ['col-sm-4', 'col-sm-8'];							   
		
		$row = $this->form->addfields( [ new  TLabel('Matrícula'), $matricula ]);
		$row->layout = ['col-sm-4'];
		
		//$this->form->addQuickfields('Matr. Interna' ,array($matricula));
		
		//add as ações do form
		$this->form->addAction('Pesquisar' ,new TAction(array($this, 'onSearch2')), 'fa:search');
		
		
		$this->form->addAction('limpar' ,new TAction(array($this, 'onClear')), 'fa:eraser red');
		
		$this->form->addAction('Imprime Lista',  new TAction(array($this, 'onPDF')), 'ico_print.png' );  
	 
		$this->form->addAction( 'Imprime Ficha Completa',  new TAction(array($this, 'onPDFCompleto')), 'ico_print.png' );
		
		//cria a grid
		$this->datagrid = new TQuickGrid;
		$this->datagrid->style = 'width:100%';
		$this->datagrid->DisableDefaultClick();
		
		$this->datagrid->addQuickColumn('Código', 'MATR_INTERNA', 'center', '10%');
		$this->datagrid->addQuickColumn('Nome', 'NOME', 'center', '50%');
		$this->datagrid->addQuickColumn('Cpf', 'CPF', 'center', '20%');
		//$cpf->setParameters('CPF');
		$this->datagrid->addQuickColumn('Identidade', 'IDENTIDADE', 'center', '20%');
		
		//cria as ações da grid
		$this->datagrid->addQuickAction('Editar' ,new TDataGridAction(array('ClienteForm', 'onEdit')), 'MATR_INTERNA', 'fa:edit blue' );//fa:edit blue
		
		if($permissao_geral['delecao'] == 1)
		{
			$this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'MATR_INTERNA', 'far:trash-alt red' );//far:trash-alt red
		//$action_detail->setField('nome_cliente');
		}
		

		$action_detail = $this->datagrid->addQuickAction('Teste', new TDataGridAction(array($this, 'onTeste')), 'MATR_INTERNA', 'fa: fa-plus red' );//far:trash-alt red
		$action_detail->setField('nome_cliente');	
		
		$action_detail2 = $this->datagrid->addQuickAction('Teste2', new TDataGridAction(array($this, 'onTeste2')), 'CPF', 'fa: fa-plus red' );//far:trash-alt red
		$action_detail2->setField('CPF');

		
		$this->datagrid->createModel();
		
		//informa os campos do form
		$this->formFields =  array($nome, $cpf, $matricula, $btn_fechar, $btn_pdf, $btn_limpar, $btn_pdf2);
		
		//add os campos no form
		$this->form->setFields($this->formFields);
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//Empacotamento
		$painel = new TPanelGroup('Cadastro de Associado(T029)');
		
		$painel->add($this->form);
		$painel->add($this->datagrid);
		$painel->add($this->pageNavigation);
		
		//add os btn no footer da pagina
		$painel->addFooter(THBox::pack($btn_fechar, $btn_limpar ));
		
		// ativar a rolagem horizontal dentro do corpo do painel
        $painel->getBody()->style = "overflow-x:auto;";
		
		//add o painel em tela
		$menuBread = new TXMLBreadCrumb('menu.xml', __CLASS__);
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($painel);
        //$vbox->add($this->pageNavigation);

        parent::add($vbox);		
	}//__construct

	/*
	Atualiza a página com os parâmetros atuais
	*/
	public function onReload($param)
	{
		try
		{
			TTransaction::open('finansys');//db//db2//
			
			//$data = $this->form->getData();
			
			var_dump(TSession::getValue('TS_cliente2'));
			
			$rp_cliente = new TRepository('cliente');
			
			$criteria = new TCriteria;
			
			//set as propriedades
			//$criteria->setProperty('order','NOME');//NOME
			$criteria->setProperty('order','MATR_INTERNA');//NOME
			$criteria->setProperty('direction','ASC');
			$criteria->setProperty('limit',5);
			
			$criteria->setProperties($param);
			
			if(TSession::getValue('TS_localiza_cpf') ) 
			{	
				$criteria->add(TSession::getValue('TS_localiza_cpf'));
				
			}//TS_localiza_cpf
			
			if(TSession::getValue('TS_localiza_nome') ) 
			{	
				$criteria->add(TSession::getValue('TS_localiza_nome'));
				
				/*$i = $this->onTiraAcentos(TSession::getValue('TS_localiza_nome'));
				$criteria->add($i);*/
			}//TS_localiza_nome
			
			if(TSession::getValue('TS_localiza_matricula') ) 
			{	
				$criteria->add(TSession::getValue('TS_localiza_matricula'));
				
			}//TS_localiza_matricula
			
			$obj_cliente = $rp_cliente->load($criteria);
			
			TSession::setValue('TS_cliente', $obj_cliente);
			
			$this->datagrid->Clear();
			if($obj_cliente)
			{
				// TSession::setValue('TS_cliente', $obj_cliente);	
		
				foreach($obj_cliente as $obj_clientes)
				{
					
					$obj_clientes->NOME = utf8_encode($obj_clientes->NOME);
					$this->datagrid->addItem($obj_clientes);
					
				}//foreach
				
			}//obj_cliente
			
			
			$criteria->resetProperties();
			$count = $rp_cliente->count( $criteria ); 

            $this->pageNavigation->setCount ( $count );
            $this->pageNavigation->setProperties ( $param );
            $this->pageNavigation->setlimit(5);
			
			TTransaction::close();
			//$this->form->setData($data);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onReload	
	
	public function onTeste($param)
	{
		$partes = explode("-", $param['nome_cliente']);
		$antes_barra  =  $partes[0]; 	
		$depois_barra =  $partes[1]; 
		
		echo '<pre>';
			var_dump($antes_barra) . '<br>';
			var_dump($depois_barra);
		echo '</pre>';
		
		echo '<pre>';
			var_dump($param['nome_cliente']);
		echo '</pre>';
		
	}//onTeste
	
	public function onTeste2($param)
	{	
		echo '<pre>';
			var_dump($param['CPF']);
		echo '</pre>';
		
	}//onTeste
	
	/*
	Questiona a exclusão de um 'cliente'
	*/
	public function onDelete($param)
	{
		TTransaction::open('finansys');//db2
		
		$key = $param['key'];
		$cliente = new cliente($key);
		
		$nome = $cliente->NOME;
		
		$onsim = new TAction(array($this, 'onSimDelete'));
		$onsim->setParameter('MATR_INTERNA', $key );
		
		new TQuestion('Deseja apagar o cliente ' . ' " ' . $nome . ' " ', $onsim );
		
		TTransaction::close();
		
	}//onDelete
	
	/*
	exclui um 'cliente'
	*/
	public function onSimDelete($param)
	{
		try
		{
			TTransaction::open('finansys');//db2
			
			$rp_cliente = new TRepository('cliente');
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('MATR_INTERNA', '=', $param['MATR_INTERNA'] ));
			
			$rp_cliente->delete($criteria);
			
			TTransaction::close();
			
			$this->onReload($param);
			
			new TMessage('info', 'Cliente apagado' );
			
		}
		catch(Exception $e  )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
	}//onSimDelete
	
	
	/*
	Grava os filtros de busca na sessão e chama o onReload()
	*/
	public function onSearch($param)
	{
		try
		{
			$data1 = $this->form->getData();
			//$data1f = utf8_decode($data1);
			
			$data = $this->form->getData();
			//$data = utf8_decode($data1);
			
			
			
			/*
			if(empty ($data->NOME) AND empty($data->CPF ) AND empty($data->MATR_INTERNA) )
			{
				throw new Exception(' Selecione uma das opções de pesquisa! ');
			}
			*/
			
			if($data->CPF)
			{
				$filter	= new TFilter('CPF', 'LIKE', "%$data->CPF%");
				TSession::setValue('TS_localiza_cpf', $filter);
				TSession::setValue('TS_relacao_cpf', $data->CPF);
			}
			else
			{
				TSession::setValue('TS_localiza_cpf', NULL);
				TSession::setValue('TS_relacao_cpf', NULL);
			}//CPF
			
			if($data->NOME)
			{
				//$data->NOME = utf8_decode($data->NOME);
				$i = $data->NOME;
				$data->NOME = $this->onTiraAcentos($i);
				$filter	= new TFilter($this->onTiraAcentos('NOME'), 'LIKE', "%$data->NOME%");
				TSession::setValue('TS_localiza_nome', $filter);
				$data->NOME = utf8_encode($data->NOME);
				TSession::setValue('TS_relacao_nome', $data->NOME);
			}
			else
			{
				TSession::setValue('TS_localiza_nome', NULL);
				TSession::setValue('TS_relacao_nome', NULL);
			}//NOME
			
			if($data->MATR_INTERNA)
			{
				$filter	= new TFilter('MATR_INTERNA', 'LIKE', "%$data->MATR_INTERNA%");
				TSession::setValue('TS_localiza_matricula', $filter);
				TSession::setValue('TS_relacao_matricula', $data->MATR_INTERNA);
			}
			else
			{
				TSession::setValue('TS_localiza_matricula', NULL);
				TSession::setValue('TS_relacao_matricula', NULL);
			}//MATR_INTERNA
			
			$param = array();
			$param['offset'] = 0;
			$param['first_page'] = 1;
			$this->form->getdata();
			
			$this->onReload( $param );
			
			$this->form->setData($data);
			
			//habilita o btn 'Imprime lista' 
			TButton::enableField('formAssossiado', 'btn_pdf');
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			
			//limpa a datagrid
			$this->datagrid->clear();
			
			//disabilita o btn_pdf
			TButton::disableField('formAssossiado', 'btn_pdf');	
			
			//reseta os valores da sessão
			TSession::setValue('TS_relacao_cpf', array());
			TSession::setValue('TS_relacao_nome', array());
			TSession::setValue('TS_relacao_matricula', array());
		}
		
	}//onSearch
	
	/*if($data->NOME)
			{
				//$data->NOME = utf8_decode($data->NOME);
				$i = $data->NOME;
				$data->NOME = $this->onTiraAcentos($i);
				$filter	= new TFilter($this->onTiraAcentos('NOME'), 'LIKE', "%$data->NOME%");
				TSession::setValue('TS_localiza_nome', $filter);
				$data->NOME = utf8_encode($data->NOME);
				TSession::setValue('TS_relacao_nome', $data->NOME);
			}
			else
			{
				TSession::setValue('TS_localiza_nome', NULL);
				TSession::setValue('TS_relacao_nome', NULL);
			}//NOME*/
	
	public function onSearch2($param)
	{
		try
        {
			TTransaction::open('finansys'); // abre uma transação 
			
			TSession::setValue('TS_cliente2', NULL);
			
            $conn = TTransaction::get(); // obtém a conexão 
			
			$data = $this->form->getData();
			
			$i = $data->NOME;
			$data->NOME = $this->onTiraAcentos($i);
			
			$sql = "SELECT * FROM CLIENTES F WHERE 1 = 1 ";
			//$sql = "SELECT * FROM CLIENTES WHERE ". $this->onTiraAcentos(NOME)." LIKE '%".$this->onTiraAcentos($data->NOME)."%'";
			
			$parametros = array();
			
			//TESTA OS COMPOS QUE FORAM PREENCHIDOS
			
			//if(!empty($data->NOME))
			//{
				$parametros[] = $data->NOME; 
				$sql .= "AND NOME LIKE '%".$this->onTiraAcentos($data->NOME)."%'";
				/*$sql .= " AND  NOME LIKE ?";*/
			//}//nome
			
			
			
            $sth = $conn->prepare($sql); 
            
            //$sth->execute(array(1,1200));
            $sth->execute($parametros);
            $result = $sth->fetchAll();
			/** FIM SQL **/
			
			//Salva SQL na sessão
			TSession::setValue('TS_cliente2', $result);
			 
			$data = $this->form->getData();
			
		   TTransaction::close(); //fecha a transação.
		   
		   $this->onReload($param);
			
        }//try    
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
			TTransaction::rollback();
			$this->form->setData($data);
        }
		
	}//onSearch2
	
	public function onTiraAcentos($i)
	{
		return preg_replace(array("/(á|à|ã|â|ä|Á|À|Ã|Â|Ä)/","/(é|è|ê|ë|É|È|Ê|Ë)/","/(í|ì|î|ï|Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö|Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü|Ú|Ù|Û|Ü)/","/(ñ|Ñ)/","/(ç|Ç)/","/(ý|ÿ|Ý)/"),explode(" ","a e i o u n c y"),$i);
	}//onTiraAcentos
	
	/*
	Limpa o form e as variaveis de sessão
	*/
	public function onClear($param)
	{
		//Reseta as TEntry 		
		TSession::setValue('TS_localiza_cpf', NULL);
		TSession::setValue('TS_localiza_nome', NULL);
		TSession::setValue('TS_localiza_matricula', NULL);
		
		//Reseta os TFilter
		TSession::setValue('TS_relacao_cpf', NULL);
		TSession::setValue('TS_relacao_nome', NULL);
		TSession::setValue('TS_relacao_matricula', NULL);
		
		$this->form->clear();
		//$this->datagrid->clear() ;
		
		$this->onReload($param);
		
		TButton::disableField('formAssossiado', 'btn_pdf');	
		
	}//onClear
	
	
	 /**
     * method onPDF()
     * Executed whenever the user clicks at the generate button
     */
   
    public function onPDFCompleto()
    {
		try
		{
			$data = $this->form->getData();
			TPage::include_css('app/resources/styles_cliente.css');
       
			//crie o renderizador de HTML
			$this->html = new THtmlRenderer('app/resources/rel_cliente.html');
 			
			TTransaction::open('finansys');//db2
			
			$criteria = new TCriteria;
				
			if($data->NOME)
			{
				$criteria->add(new TFilter('NOME', 'LIKE', "%$data->NOME%"));
			}
			
			if($data->CPF)
			{
				$criteria->add(new TFilter('CPF', '=', "$data->CPF"));
			}
			
			if($data->MATR_INTERNA)
			{
				$criteria->add(new TFilter('MATR_INTERNA', '=', "$data->MATR_INTERNA"));
			}
			
            $rp_cliente = new TRepository('cliente');
			
			$count  = $rp_cliente->count($criteria); 
            
			$cliente = $rp_cliente->load($criteria); 
            
			//$cliente = cliente::getObjects($criteria);
           
            $replace_detail = array();
            if ($cliente)
            {
                // iterate products ** product
				$div = (int) ($count / 7);
				
                foreach ($cliente as $cliente)
                {	
					$cliente->UF_RES = $cliente->nome_uf->UF;
					//$clientes->nome_uf->UF ;//nome_uf
					$replace_detail[] = $cliente->toArray(); // array of replacements
						
                }
				
            }
			
			//pega a data atual
			$ob_std = new STDClass;
			$ob_std->data = date('d/m/Y H:i');
			$ob_std->total = $div;
			
			$replace = array();
            $replace['object']      = $ob_std;
			
			$this->html->enableSection('topo', $replace);
			
			
			TTransaction::close();
			
			$this->form->setData($data);
           
            // enable products section as repeatable
            $this->html->enableSection('clientes', $replace_detail, TRUE);
			
			//$this->html->enableSection('manage');
			
			$contents = '<style>'.file_get_contents('app/resources/styles_cliente.css') .'</style>'. $this->html->getContents();
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            // write and open file
            file_put_contents('tmp/document.pdf', $dompdf->output());
            parent::openFile('tmp/document.pdf');
			
		}//try
		catch(Exception $e)	
		{
			new TMessage('error', $e->getMessage() );
			$this->datagrid->clear();
		}
			

    }//onPDFCompleto
	
	public function onPDF()
    {
		try
		{
			
			$data = $this->form->getData();
			
			TTransaction::open('finansys');//db2
			
			$rp_cliente = new TRepository('cliente');
			$criteria   = new TCriteria;
			
			if($data->NOME)
			{
				$criteria->add(new TFilter('NOME', 'LIKE', "%$data->NOME%"));
			}
			
			if($data->CPF)
			{
				$criteria->add(new TFilter('CPF', '=', "$data->CPF"));
			}
			
			if($data->MATR_INTERNA)
			{
				$criteria->add(new TFilter('MATR_INTERNA', '=', "$data->MATR_INTERNA"));
			}
			$cliente = $rp_cliente->load($criteria);
			
			TTransaction::close();
			
			
			$widths = array(70, 70, 230, 60, 60);
			$table = new TTableWriterPDF ( $widths );
			$table->style = 'border:0';
		
			$table->addStyle('title', 'Arial', '10', 'BI', '#ffffff', '#407B49');
			$table->addStyle('datap', 'Arial', '10', '', '#000000', '#869FBB');
			$table->addStyle('datai', 'Arial', '10', '', '#000000', '#ffffff');
			$table->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
			$table->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
		
			$table->addRow();
			$table->addCell(('Relatório de Clientes'), 'center', 'header', 5);
			
			
			$table->addRow();
			$table->addCell('Matr. Interna',  'left', 'title');
			$table->addCell('Cpf',  'left', 'title');
			$table->addCell('Nome',  'left', 'title');
			$table->addCell('Telefone',  'left', 'title');
			$table->addCell('Celular',  'left', 'title');
			
		
			$color = FALSE;
		
			foreach($cliente as $clientes )
			{
				$style = $color ? 'datap' : 'datai';
				
				$table->addRow();
				$table->addCell( $clientes->MATR_INTERNA, 'left', $style);
				$table->addCell( $clientes->CPF, 'left', $style);
				$table->addCell( $clientes->NOME, 'left', $style);
				$table->addCell( $clientes->FONE_RES, 'left', $style);
				$table->addCell( $clientes->FONE_CEL, 'left', $style);
				
				$color = !$color;
			
			}
		
			$table->addRow();
			$table->addCell(date('d-m-Y  H:i'), 'center', 'footer', 5);//'d-m-Y h:i:s'
		
			$table->save('app/output/relatorio.pdf');
		
			parent::openFile('app/output/relatorio.pdf');
			
			$this->form->setData($data);
			
		}//try
		catch(Exception $e)	
		{
			new TMessage('error', $e->getMessage() );
			$this->datagrid->clear();
		}
			

    }//onPDF
	
	
	public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
	
	
	
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
	
	
}//Twindow

?>