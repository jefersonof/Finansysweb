<?php
//session_start();
//require_once 'importacaoGasto.class.php'; 

class importarPlanilha extends TPage
{
	private $form;
	private $datagrid;
	
	public function __construct()
	{
		parent::__construct();
		
		$id_planilha     = new TEntry('ID_PLANILHA');
		$nome            = new TEntry('NOME');
		$sexo            = new TEntry('SEXO');
		$data_nascimento = new TEntry('DATA_NASCIMENTO');
		$cpf             = new TEntry('CPF');
		$matricula       = new TEntry('MATRICULA');
		$valor           = new TEntry('VALOR');
		$arquivo         = new TFile ('arquivo');
		
		//formatações
		//$data_nascimento->setMask('dd/mm/yyyy');
		//$df->setMask('dd/mm/yyyy');
		//CRIA O FORM
		$this->form = new BootstrapFormBuilder('formImportar');
		$this->form->setFormTitle('Importar Planilha');
		$this->form->setFieldSizes('100%');
		
		//btn
		$btn_gravar = TButton::create('Gravar', array($this, 'onAddItem'), 'Gravar' ,'fa: fa-check blue' );//fa: fa-check
		
		//$btn_cancelar    = TButton::create('btn_cancelar', array($this, 'onCancelar'), 'Cancelar', 'ico_close.png' );
		
		//DATAGRID
		//CRIA A DATAGRID
		$this->datagrid = new TQuickGrid;;
		$this->datagrid->style = 'width:100%';
		$this->datagrid->setHeight(400);
		$this->datagrid->makeScrollable();
		$this->datagrid->DisableDefaultClick();
		$this->datagrid->style = "width:100%;margin-bottom: 10px";
		
		$this->datagrid->addQuickColumn('', 'edit', 'center', '5%');
		$this->datagrid->addQuickColumn('', 'delete', 'center', '5%');
		$this->datagrid->addQuickColumn('Nome', 'NOME', 'center', '35%');
		$this->datagrid->addQuickColumn('Sexo', 'SEXO', 'center', '10%');
		$this->datagrid->addQuickColumn('Dt. Nascimento', 'DATA_NASCIMENTO', 'center', '15%');
		$this->datagrid->addQuickColumn('CPF', 'CPF', 'center', '10%');
		$this->datagrid->addQuickColumn('Valor', 'VALOR', 'center', '10%');
		$this->datagrid->addQuickColumn('Matrícula', 'MATRICULA', 'center', '10%');
		
		//CRIA A AÇÃO DA GRID
		//$this->datagrid->addQuickAction('Editar', new TDataGridAction(array('cadContrato2', 'onEdit')), 'ID_CONTRATO', 'fa:edit blue');
		
		//$this->datagrid->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'ID_CONTRATO', 'far:trash-alt red');
		
		//ADD A GRID EM TELA
		$this->datagrid->createModel();
		
		
		$row = $this->form->addFields(['Nome', $nome],
							          ['Sexo', $sexo],
							          ['Dt nascimento', $data_nascimento],
							          ['Cpf', $cpf],
							          ['Valor', $valor],
							          ['Matricula', $matricula]
							         );
		$row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2'];
		
		$row = $this->form->addFields(['Planilha', $id_planilha]);
		$row->layout = ['col-sm-12'];
			
		
		$row = $this->form->addFields([$btn_gravar]);
		$row->layout = ['col-sm-12'];
		$row->style = 'background:#6287B9; margin:1px';	
		
		$row = $this->form->addFields(['Arquivo Excel', $arquivo]);
		$row->layout = ['col-sm-12'];
		
		$row = $this->form->addFields([$this->datagrid]);
		$row->layout = ['col-sm-12'];
		
		$this->form->addHeaderAction('Importar' ,new TAction(array($this, 'onImportar3')), 'fa: fa-plus blue' );//onImportar2
		
		$this->form->addHeaderAction('Salvar' ,new TAction(array($this, 'onSave')), 'fa: fa-save blue' );
		
		$this->form->addHeaderAction('Limpar' ,new TAction(array($this, 'onClear')), 'fa: fa-eraser red' );
		
		$this->form->addHeaderAction('Teste' ,new TAction(array($this, 'onTeste')), 'fa: fa-plus blue' );
		
		//$this->form->addHeaderAction('Teste2' ,new TAction(array($this, 'onAddItem')), 'fa: fa-plus blue' );
		
		//DEFINE OS CAMPOS DO FORMULÁRIO
        $this->formFields = array($nome, $sexo, $cpf, $matricula, $data_nascimento, $arquivo, $btn_gravar, $id_planilha); //, $numero_parcela, $valor_parc

        $this->form->setFields( $this->formFields );
		
		//$this->form->
		
		parent::add($this->form);
		
	}//__construct
	
	public function onReload($param)
	{	
		//TSession::setValue('TS_planilha', NULL);
		
		$data = $this->form->getData();
		
		$data->NOME            = '';	
		$data->SEXO            = '';	
		$data->DATA_NASCIMENTO = '';	
		$data->CPF             = '';	
		$data->MATRICULA       = '';
		$data->ID_PLANILHA     = '';
		$data->VALOR           = '';
		
		$this->form->setData($data);
		
		$data->arquivo = 'Teste';
		$this->form->setData($data);
		
		//pega os dados da sessão
		$dados_planilha = TSession::getValue('TS_planilha');
		$row_planilha   = TSession::getValue('TS_planilhaRow');
		
		//limpa a grid
		$this->datagrid->clear();
		
		if($dados_planilha)
		{
			$cont = 1;
			
			for($i = 1; $i < $row_planilha; $i++)
			{
				//Pula a linha se o index foi excluido 
				if(!empty($dados_planilha[$i]['NOME']))
				{
					$item_name = 'prod_' . $cont++;
				
					//OBJ PADRÃO DAS CLASSES
					$item = new STDClass;
					
					$action_del = new TAction(array($this, 'onDeleteItem'));
					$action_del->setParameter('list_product_id', $i);
					$action_del->setParameter('cont',$cont);

					$action_edi = new TAction(array($this, 'onEditItemProduto'));
					$action_edi->setParameter('list_product_id', $i);
					$action_edi->setParameter('cont',$cont);
					
					//CRIA OS BTN E ADD AS AÇÕES
					$button_del = new TButton('delete_product'.$cont);
					$button_del->class = 'btn btn-default btn-sm';
					$button_del->setAction( $action_del, '' );
					$button_del->setImage('far:trash-alt red');

					$button_edi = new TButton('edit_product'.$cont);
					$button_edi->class = 'btn btn-default btn-sm';
					$button_edi->setAction( $action_edi, '' );
					$button_edi->setImage('fa:edit blue fa-lg');

					//ASSOCIA O OBJ PADRÃO AOS BNT
					$item->edit    = $button_edi;
					$item->delete  = $button_del;

					$this->formFields[ $item_name.'_edit' ]   = $item->edit;
					$this->formFields[ $item_name.'_delete' ] = $item->delete;
					
					//manda os dados para a grid
					$item->NOME            = $dados_planilha[$i]['NOME'];//$i = row  | 'NOME' = coll
					$item->SEXO            = $dados_planilha[$i]['SEXO'];
					$item->DATA_NASCIMENTO = TDate::date2br($dados_planilha[$i]['DATA_NASCIMENTO']);
					$item->CPF             = $dados_planilha[$i]['CPF'];
					$item->MATRICULA       = $dados_planilha[$i]['MATRICULA'];
					$item->VALOR           = $dados_planilha[$i]['VALOR'];
					
					$this->datagrid->addItem($item);
					
					$this->loaded = TRUE;
					
					$this->form->setFields( $this->formFields );
				}
				else
				{
					
				}		
				
				
			}//for
			
		}//if
		
	}//onReload
	
	public function onAddItem($param)
	{
		$data = $this->form->getData();
		
		//id planilha
		$key = $data->ID_PLANILHA;
		
		$ts_planilha = TSession::getValue('TS_planilha');
		//TDate::date2br($ts_planilha[$key]['DATA_NASCIMENTO']);
		
		$ts_planilha[$key] = array('NOME'    => $data->NOME,
						           'SEXO'            => $data->SEXO,
						           'DATA_NASCIMENTO' => TDate::date2us($data->DATA_NASCIMENTO),
						           'CPF'             => $data->CPF,
						           'VALOR'           => $data->VALOR,
						           'MATRICULA'       => $data->MATRICULA
						           );
		
		TSession::setValue('TS_planilha', $ts_planilha);

		$data->NOME            = '';	
		$data->SEXO            = '';	
		$data->DATA_NASCIMENTO = '';	
		$data->CPF             = '';	
		$data->MATRICULA       = '';
		$data->VALOR           = '';
		
		$this->form->setData($data);

		$this->onReload($param);	
		
	}//onAddItem
	
	public function onDeleteItem($param)
	{
		//pega os dados da planilha
		$ts_planilha    = TSession::getValue('TS_planilha');
		
		//pega os dados da sessão
		$key = $param['list_product_id'];
		
		//var_dump ($key);
		
		unset($ts_planilha[$key]);
		TSession::setValue('TS_planilha', $ts_planilha);
		
		//pega o numero de linhas
		$ts_planilharow = TSession::getValue('TS_planilhaRow');
		$ts_planilharow = $ts_planilharow - 1;
		TSession::setValue('TS_planilhaRow', $ts_planilharow);
		
		
		$this->onReload($param);
		
	}//onDeleteItem
	
	public function onEditItemProduto($param)
	{
		//pega os dados do form
		$data = $this->form->getData();
		
		//pega os dados da planilha
		$ts_planilha = TSession::getValue('TS_planilha');
		
		//pega os dados da sessão
		$key = $param['list_product_id'];
		
		$data->ID_PLANILHA     = $param['list_product_id'];
		$data->NOME            = $ts_planilha[$key]['NOME'];
		$data->SEXO            = $ts_planilha[$key]['SEXO'];
		$data->DATA_NASCIMENTO = TDate::date2br($ts_planilha[$key]['DATA_NASCIMENTO']);
		$data->CPF	           = $ts_planilha[$key]['CPF'];
		$data->MATRICULA       = $ts_planilha[$key]['MATRICULA'];
		$data->VALOR           = $ts_planilha[$key]['VALOR'];
		$this->form->setData($data);
		
	}//onEditItemProduto
	
	public function onClear($param)
	{
		TSession::setValue('TS_planilha',    NULL);
		TSession::setValue('TS_planilhaRow', NULL);
		
		$this->datagrid->clear();
		$this->form->clear();
		
		$this->onReload($param);
	}//onClear
	
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db4');
			
			$data = $this->form->getData();
			$this->form->setData($data);
			
			//pega os dados da sessão
			$dados_planilha = TSession::getValue('TS_planilha');
			$row_planilha   = TSession::getValue('TS_planilhaRow');
			//$row_planilha   = 3;
			
			//$this->datagrid->clear();
			if($dados_planilha)
			{	
				for($i = 1; $i < $row_planilha; $i++)
				{	
					//se nao existir o index pula a linha
					if(!empty($dados_planilha[$i]['NOME']))
					{	
						$planilha = new planilha;
						$planilha->NOME            = $dados_planilha[$i]['NOME'];//linha/coluna
						$planilha->SEXO            = $dados_planilha[$i]['SEXO'];
						$planilha->DATA_NASCIMENTO = date("Y/m/d", strtotime($dados_planilha[$i]['DATA_NASCIMENTO']));
						$planilha->CPF             = $dados_planilha[$i]['CPF'];
						$planilha->VALOR           = $dados_planilha[$i]['VALOR'];
						$planilha->MATRICULA       = $dados_planilha[$i]['MATRICULA'];
							
						$planilha->store();
						
					}//if
				}//for
				
			}//if($dados_planilha)
			
			TTransaction::close();
			
			new TMessage('info', 'Planilha salva com sucesso!');
			$this->onReload($param);
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
	}//onSave
	
	public function onTeste($param)
	{
		$teste = TSession::getValue('TS_planilhaRow');
		
		// foreach($testes as $teste)
		// {
			var_dump($teste);
		// }
		
	}//onTeste
	
	public function onImportar3($param)
	{
		try
		{
		
			$data = $this->form->getData();
			
			if(empty($data->arquivo)) 
			{
				throw new Exception('Selecione a planilha');
			}
			
			//pega os ados da planilha
			$xls = SimpleXLSX::parse('tmp/' . $data->arquivo);
			
			//pega o numero de linhas
			$planilha_row = $xls->dimension();	
			
			$excel_arrays = array();
			$num = 1;
			if($xls)
			{
				//pega as colunas
				$excel_arrays[$num] = ( $xls->rows() );
			
				$num++;
			}else
			{
				echo SimpleXLSX::parseError();
			}
				
			//organiza a planilha
			$items_sessao = array();
			foreach($excel_arrays as $item)//VALOR
			{
				for($i = 1; $i < $planilha_row[1]; $i++)
				{	
					$items_sessao[$i]['NOME']            = $item[$i][0];
					$items_sessao[$i]['SEXO']            = $item[$i][1];
					$items_sessao[$i]['DATA_NASCIMENTO'] = $item[$i][2];
					$items_sessao[$i]['CPF']             = $item[$i][3];
					$items_sessao[$i]['MATRICULA']       = $item[$i][4];
					$items_sessao[$i]['VALOR']           = $item[$i][5];
				}
			}
				
			//grava o array de obj e o num de linhas na sessão
			TSession::setValue('TS_planilha',    $items_sessao);
			TSession::setValue('TS_planilhaRow', $planilha_row[1]);
			
			$data->arquivo = 'teste';
			$this->form->setData($data);
			
			$this->onReload($param);
			
			$this->form->setData($data);
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::close();
		}	
		
	}//onImportar3
	
	public function onImportar($param)
	{
		try
		{
			//captura e manda os dados para o form
			$data = $this->form->getData();
			//$this->form->setData($data);
			
			if(empty($data->arquivo)) 
			{
				throw new Exception('Selecione a planilha');
			}	
			
			//pega os dados da planilha
			$planilha     = SimpleXLSX::parse('tmp/'. $data->arquivo);
			
			//pega o numero de linhas
			$planilha_row = $planilha->dimension();	
			
			//percorre a planilha criando um array bruto de obj
			$array_excels = array();
			$i = 0;
			if ( $planilha ) {
				$array_excels[$i] = $planilha->rows();
				//$array_excels[$i] = $planilha->rows();
				
				//$array_excels = $planilha->rows();
				$i++;
			} else {
				echo SimpleXLSX::parseError();
			}
			
			/*if ( $xls ) {
			// ->rows()
			//echo '<h2>$xlsx->rows()</h2>';
			//echo '<pre>';
			//print_r( $xlsx->rows() );
			
			
			
			//pega as colunas
			$excel_arrays[$num] = ( $xls->rows() );
			//echo '</pre>';
			
			$num++;

			// ->rowsEx();
			// echo '<h2>$xlsx->rowsEx()</h2>';
			// echo '<pre>';
			// print_r( $xlsx->rowsEx() );
			// echo '</pre>';

		} else {
			echo SimpleXLSX::parseError();
		}*/
			
			/*echo '<pre>';
			var_dump ($array_excels[1][0]);
			echo '</pre>';*/
			//exit;
			
			//organiza o array
			/*$planilha_organiza = array();
			if($array_excels)
			{
				foreach($array_excels as $dado_planilha )
				{
					for($i = 1; $i < $planilha_row; $i++)
					{	
						
						$planilha_organiza[$item->COBERTURA_ID] = $dado_planilha->toArray();
						
						
						$planilha_organiza[]->NOME            = $dado_planilha[$i][0];//$i = row  | [0] = coll
						$item->SEXO            = $dado_planilha[$i][1];
						$item->DATA_NASCIMENTO = TDate::date2br($dado_planilha[$i][2]);
						$item->CPF             = $dado_planilha[$i][3];
						$item->MATRICULA       = $dado_planilha[$i][4];
						
						$this->datagrid->addItem($item);
					}//for
				}//foreach
			}//if*/
			
			
			//grava a o array de obj e o num de linhas na sessão
			TSession::setValue('TS_planilha',    $array_excels);
			TSession::setValue('TS_planilhaRow', $planilha_row[1]);
			
			$data->arquivo = 'teste';
			$this->form->setData($data);
			
			$this->onReload($param);
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onImportar
	
	public function onImportar2($param)
	{
		try
		{
			//captura e manda os dados para o form
			$data = $this->form->getData();
			//$this->form->setData($data);
			
			if(empty($data->arquivo)) 
			{
				throw new Exception('Selecione a planilha');
			}	
			
			//pega os dados da planilha
			$planilha     = SimpleXLSX::parse('tmp/'. $data->arquivo);
			
			//pega o numero de linhas
			$planilha_row = $planilha->dimension();	
			
			//percorre a planilha criando um array bruto de obj
			$array_excels = array();
			$i = 0;
			if ( $planilha ) {
				//$array_excels[$i] = $planilha->toArray();
				$array_excels[$i] = $planilha->rows();
				//$array_excels[$i] = $planilha->rows();
				
				//$array_excels = $planilha->rows();
				$i++;
			} else {
				echo SimpleXLSX::parseError();
			}
			
			//grava a o array de obj e o num de linhas na sessão
			TSession::setValue('TS_planilha',    $array_excels);
			TSession::setValue('TS_planilhaRow', $planilha_row[1]);
			
			//var_dump ($array_excels);
			
			$data->arquivo = 'teste';
			$this->form->setData($data);
			
			$this->onReload($param);
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onImportar2
	
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