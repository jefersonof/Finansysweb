<?php
class chamadoListe extends TPage
{
	private $form;
	private $datagrid;
	private $scroll;
	
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
		$this->form = new BootstrapFormBuilder('formAberturaChamado');
		$this->form->setFieldSizes('100%');
		$this->form->setFormTitle('Lista de chamados');
		
		//atributos
		$id_chamado = new TEntry('id_chamado');
        $nome       = new TEntry('nome');
		$status     = new TCombo('status');
		
		//mantém as TEntry preenchidas
		$status->setValue(TSession::getValue('busca_status'));
		$id_chamado->setValue(TSession::getValue('busca_nome'));
		$id_chamado->setValue(TSession::getValue('busca_id'));
     
		//add items
		$status->additems(array('Finalizado' => 'Finalizado', 'Pendente' => 'Pendente')); 
		
		//validação
		$nome->addValidation('Nome', new TRequiredValidator);
		
		//monta o form
		$row  = $this->form->addFields(['N° do chamado', $id_chamado],
							           ['Nome', $nome],
							           ['Status', $status]
							          );
		$row->layout = ['col-sm-3', 'col-sm-7', 'col-sm-2'];

		$btn = $this->form->addAction('Buscar', new TAction(array($this, 'onSearch')), 'fa:search');
		$btn->class = 'btn btn-sm btn-primary';
		
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');
		
		$this->form->addAction('Incluir', new TAction(array('chamadoForm', 'onEdit')), 'fa: fa-plus blue');
		
		//$this->form->addAction('Relatório', new TAction(array($this, 'onGenerate')), 'fa:print green');
		
		//cria a grid
		// cria as colunas na datagrid 
	    $this->datagrid = new TDataGrid;
        $this->datagrid->style = 'width: 100%';
		$this->datagrid->DisableDefaultClick();
		
        // make scrollable and define height
        $this->datagrid->setHeight(250);
        
        // create the datagrid columns
        $id_chamado = new TDataGridColumn('id_chamado', 'N° Chamado', 'center', '10%');
        $nome       = new TDataGridColumn('nome', 'Nome', 'center', '15%');
        $descricao  = new TDataGridColumn('problema', 'Problema', 'center', '35%');
        $setor      = new TDataGridColumn('setor', 'Setor', 'center', '10%');
        $data       = new TDataGridColumn('data_chamado', 'Data', 'center', '10%');
        $hora       = new TDataGridColumn('hora', 'Hora', 'center', '10%');
        $status     = new TDataGridColumn('status', 'Status', 'center', '10%');
		
        $status->setTransformer(array($this, 'onFormatafont'));
        
        // add the columns to the datagrid
        $this->datagrid->addColumn($id_chamado);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($descricao);
        $this->datagrid->addColumn($setor);
        $this->datagrid->addColumn($data);
        $this->datagrid->addColumn($hora);
        $this->datagrid->addColumn($status);
		
		//ações da grid
        $act_edit = new TDataGridAction(array('chamadoForm', 'onEdit'));
        $act_edit->setLabel('Editar');
        $act_edit->setImage('fa:edit blue');
        $act_edit->setField('id_chamado');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($act_edit);

		$del_edit = new TDataGridAction(array($this, 'onDelete'));
        $del_edit->setLabel('Deletar');
        $del_edit->setImage('far:trash-alt red');
        $del_edit->setField('id_chamado');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($del_edit);
		
        // creates the datagrid model
        $this->datagrid->createModel();
		
		//add a grid no scroll 
		$this->scroll = new TScroll;
		$this->scroll->setSize('100%', '100%');
		$this->scroll->add($this->datagrid);
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));		
		$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		
		$panel = new TPanelGroup;
        $panel->add($this->scroll);
        $panel->addFooter($this->pageNavigation);
		
		//add scroll horizontal 
		$panel->getBody()->style = 'overflow-x:auto';

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
		
	}//__construct
	
	public function onReload($param)
	{
		try
		{
			TTransaction::open('chamado');
			
			$rp_chamado = new TRepository('chamado');
			$criteria = new TCriteria;
			
			$criteria->setProperty('order', 'id_chamado');
		    $criteria->setProperty('direction','desc');
		   
		    $criteria->setProperties( $param );
		    $criteria->setProperty('limit',8);
		   
		   
			if(TSession::getValue('filter_id'))
			{
			   $criteria->add(TSession::getValue('filter_id'));
			}
			
			if(TSession::getValue('filter_nome'))
			{
			   $criteria->add(TSession::getValue('filter_nome'));
			}
			
			if(TSession::getValue('filter_status'))
			{
			   $criteria->add(TSession::getValue('filter_status'));
			}
			
			$objects    = $rp_chamado->load( $criteria );       
			$this->datagrid->clear();
			
			//grava na sessão para relatório
			TSession::setValue('TS_relatorio', $objects);
        
			if ($objects)
			{
			   foreach ($objects as $object)
			   {
				$object->data_chamado = TDate::date2br($object->data_chamado);//FORMATA A DATA   
				
				$this->datagrid->addItem( $object );// DEPOIS ADICIONA NA GRID
				 
			   }
			}
			
			
			//$this->form->setData($data);           
			$criteria->resetProperties();
			$count = $rp_chamado->count($criteria);          
			  
			$this->pageNavigation->setCount ($count);
			$this->pageNavigation->setProperties ($param);
			$this->pageNavigation->setlimit(8);//numero de registros         
		   
		    TTransaction::close();
		    $this->loaded = TRUE; 
			
		}//try
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onReload
	
	public function onSearch($param)
	{
		$data = $this->form->getData();
		
		if($data->nome)
		{
			$filter = new TFilter('nome', 'like', "%$data->nome%");
			TSession::setValue('filter_nome', $filter);
			TSession::setValue('busca_nome', $data->nome);
		}
		else
		{
			TSession::setValue('filter_nome', NULL);
			TSession::setValue('busca_nome', NULL);
		}
		
		if($data->id_chamado)
		{
			$filter = new TFilter('id_chamado', '=', $data->id_chamado);
			TSession::setValue('filter_id', $filter);
			TSession::setValue('busca_id', $data->id_chamado);
		}
		else
		{
			TSession::setValue('filter_id', NULL);
			TSession::setValue('busca_id', NULL);
		}
		
		if($data->status)
		{
			$filter = new TFilter('status', '=', $data->status);
			TSession::setValue('filter_status', $filter);
			TSession::setValue('busca_status', $data->status);
		}
		else
		{
			TSession::setValue('filter_status', NULL);
			TSession::setValue('busca_status', NULL);
		}

		$this->form->setdata($data);
      
		$param = array();
		$param['offset'] = 0;
		$param['first_page'] = 1;
		$this->onReload( $param );
	}//onSearch
	
	//
	public function onPendente($param)
	{	
		//set a combo como Pendente
		$pendente = 'Pendente';//filter_status
		TSession::setValue('busca_status', $pendente);
		
		//add o filtro sql 'pendente'
		$filter = new TFilter('status', '=', $pendente);
		TSession::setValue('filter_status', $filter);
		
		//manda os dados 'pendente' para o form
		$data = $this->form->getData();
		$data->status = 'Pendente';
		TForm::sendData('formAberturaChamado', $data);
		
		//recarrega pagina
		$this->onReload($param);
	}//onPendente
	
	function onGenerate()
    {
        try
        {
            // open a transaction with database 'samples'
            TTransaction::open('chamado');
            
            // load all customers
            $repository = new TRepository('chamado');
            $criteria   = new TCriteria;
            //$customers = $repository->load($criteria);
            
            $data = $this->form->getData();
            //$this->form->validate();
            
            $designer = new TPDFDesigner;
            $designer->fromXml('app/reports/Rel501_2.pdf.xml');
            $designer->generate();
            
            $fill = TRUE;
            $designer->gotoAnchorXY('details');
            $designer->SetFont('Arial', '', 10);
            $designer->setFillColorRGB( '#F9F9FF' );
            
			$customers = TSession::getValue('TS_relatorio');
            if ($customers)
            {
                foreach($customers as $customer)
                {
                    $designer->gotoAnchorX('details');
                    $designer->Cell( 34, 12, $customer->id_chamado, 1, 0, 'C', $fill);
                    $designer->Cell(160, 12, utf8_decode($customer->nome), 1, 0, 'L', $fill);
                    //$designer->Cell(152, 12, utf8_decode($customer->problema), 1, 0, 'L', $fill);
                    //$designer->Cell(152, 12, utf8_decode(date_format($customer->data_chamado)), 1, 0, 'L', $fill);
                    $designer->Ln(12);
                    
                    // grid background
                    $fill = !$fill;
                }
            }
            
            $file = 'app/output/Rel501_2.pdf';
            
            if (!file_exists($file) OR is_writable($file))
            {
                $designer->save($file);
                //parent::openFile($file);
                
                $window = TWindow::create(_t('Designed PDF report'), 0.8, 0.8);
                $object = new TElement('object');
                $object->data  = $file;
                $object->type  = 'application/pdf';
                $object->style = "width: 100%; height:calc(100% - 10px)";
                $window->add($object);
                $window->show();
            }
            else
            {
                throw new Exception(_t('Permission denied') . ': ' . $file);
            }
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
	
	//
	
	public function onRelatorio($param)
	{
		$designer = new TPDFDesigner;
		$designer->fromXml('app/reports/rel501_2.pdf.xml');
		
		//add conteudo
		$object = TSession::getValue('TS_relatorio');
		
		$array_nome = array();
		foreach($object as $objects)
		{
			//$array_nome =  $objects->nome;

			$designer->replace('{nome}', $objects->nome);
			
			$designer->generate();//tras todos	
		}
		
		// $designer->replace('{nome}', $array_nome);
			
		$designer->generate();//tras todos
		
		$designer->save('app/output/rel501_2.pdf');
			
		parent::openFile('app/output/rel501_2.pdf');
		
	}
	
	public function onFormatafont($status, $object, $row)
    {
        //$number = number_format($stock, 2, ',', '.');
        $var_status = $status;
        
        if ($var_status == 'Pendente'  )
        {
            return "<span style='color:red'>$status</span>";
        }
        
        if ($var_status == 'Finalizado'  )
        {
            return "<span style='color:#088A29'>$status</span>";
        }
		
    }//onFormatafont
	
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
    }
	
	public function onTeste()
	{
		
	}
	
	public function onDelete($param)
	{
		try
		{
			try
			{
				TTransaction::open('chamado');
				
				$key = $param['id_chamado'];
				
				$chamado = new chamado($key);
				$chamado_nome = $chamado->nome;	
				
				$ac_sim = new TAction( array($this, 'onSim') );
				$ac_sim->setParameter('id_chamado', $key);
				
				
				new TQuestion('Apagar o  chamado do(a) ' . $chamado_nome, $ac_sim);
				$this->onReload($param);
				//new TMessage('info', 'Apagar o Registro '. $key );
			}
			catch(Exception $e)
			{
				TTransaction::rollback();
				new TMessage('error', $e->getMessage() );
			}	
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
			TTransaction::open('chamado');
			
			$key = $param['id_chamado'];
			$chamado = new chamado($key);
			
			$chamado->delete();
			
			TTransaction::close();
			
			//APAGA AS IMAGENS DA PASTA E DEPOIS A PRÓPRIA PASTA 
			function ApagaArq($dir){
				if($objs = glob($dir."/*"))
				{
					foreach($objs as $obj)
					{
						is_dir($obj)? ApagaArq($obj) : unlink($obj);
					}
				}
				
				//se existir o diretório apaga
				if (file_exists($dir)) {
					rmdir($dir);
				}
				
			}
			
			//$pasta = 'files/images/'.$key; 
			//$pasta = '//nas/disco01/SUPORTE/JEFERSON/db/img_chamados/'.$key; 
			$pasta = 'files/img_chamados/'.$key; 
			ApagaArq($pasta);
			
			
			//MENSAGEM
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
	
	public function onClear($param)
	{
		$this->form->clear();
		
		TSession::setValue('filter_id', NULL);
		TSession::setValue('filter_nome', NULL);
		TSession::setValue('filter_status', NULL);
		TSession::setValue('busca_id', NULL);
		TSession::setValue('busca_nome', NULL);
		TSession::setValue('busca_status', NULL);
		
		
		$this->onReload($param);
		
	}//onClear

		
	
}//TPage

?>