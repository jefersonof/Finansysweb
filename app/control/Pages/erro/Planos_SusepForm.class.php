<?php
class Planos_SusepForm Extends TPage
{	
	private $form;
	private $datagrid;
	private $alertBox;
	
	public function __construct()
	{
		parent::__construct();
		
        $this->form = new BootstrapFormBuilder('Formplanos_susep'); // classe 
		$this->form->setFieldSizes('100%');
        
		//cria a tabela
		$tabela = new TTable;
		$tabela->style = 'width:99%; background:#6287B9; heigth:10%';
		$this->form->add($tabela);
		
		//cria os atributos
		$id_planos      = new THidden('ID_PLANOS_SUSEP'); 
		$teste2         = new TEntry('TESTE2'); 
		$processo       = new TEntry('PROCESSO');
		$descricao      = new TEntry('DESCRICAO');
		$ativo          = new TCombo('ATIVO');
		$pln_codigo     = new TEntry('PLNCODIGO');
		$processo2      = new TEntry('PROCESSO2');
		$carreg         = new TEntry('CARREG');
		$tipo           = new TEntry('TIPO');
		$teste          = new TEntry('TESTE');
		$grupo          = new TDBCombo('GRUPO', 'db2', 'seg_grupos', 'CODIGO','({CODIGO} )  {GRUPO}');
		$grupo->setChangeAction( new TAction(array($this, 'onCargaRamo')));
		
		/*$exit_grupo = new TAction(array($this, 'onCargaRamo'));//onExitAction
		$grupo->setExitAction($exit_grupo);*/
		
		$filter = new TCriteria;
        $filter->add(new TFilter('GRUPO', '=', '1'));
	    $ramo  = new TDBCombo('RAMO', 'db2', 'seg_ramos', 'CODIGO', '({CODIGO}) {RAMO}' ,'CODIGO');//'RAMO', $filter);
		$tipo_plano     = new TCombo('TIPO_PLANO');
		$tipo_produto   = new TEntry('TIPO_PRODUTO');
		$prazo_pag      = new TEntry('PRAZO_PAG');
		$taxa_juros     = new TEntry('TAXA_JUROS');
		$tab_servicos   = new TEntry('TAB_SERVICO');
		$lb_dados_plan  = new TLabel('Dados do Plano');//, 'black', 30, 'b'
		$regime_finan   = new TDBCombo('REGIME_FINANCEIRO', 'db2', 'reg_fin', 'CODIGO','({CODIGO} )  {REGIME}');
		//TDBCombo('nome_objeto','banco_de_dados','classe_model','campo_id','campo_descricao');
		
		//CRIA OS BTN
		$btn_salvar   = TButton::create('btn_salvar' ,array($this, 'onSave'), 'Salvar', 'fa:floppy-o' );
		$btn_salvar->class = 'btn btn-sm  btn-primary';
		
		$btn_cancelar = TButton::create('btn_cancelar' ,array('Planos_SusepListe', 'onReload'), 'Cancelar', 'ico_delete.png' );
		
		//formatações
		$tipo_plano->additems( array('P' => 'P | Pecúlio', 'S' => 'S | Seguro'));
		$ativo->addItems(array('S' => 'Ativo', 'N' => 'Inativo')) ;
		$lb_dados_plan->style = 'color:#FFF; width:100%';
		
		//validação
		$tipo_plano->addValidation(' "PLANO" ', new TRequiredValidator);
		
		//** MONTAGEM DA PAGINA **//
		//Cabeçalho
		
		$label1 = new TLabel('Dados do Contrato');//, '#7D78B6', 8, 'bi'
        $label1->style='text-align:left; width:100%; color:#FFF';
        
		//topo da page
		$row = $this->form->addFields( [$label1], [$id_planos]  );
		$row->layout = ['col-sm-8','col-sm-4'];
		$row->style = 'background:#6287B9; margin:0 0 5px 1px; ';
		
		//Corpo
		$row = $this->form->addFields([new TLabel('N° Processo'), $processo ],
                                      [new TLabel('Plano / Benefício'), $descricao ],
                                      [new TLabel('Código no Fip'), $pln_codigo ],
									  [new TLabel('Refime Finan'), $regime_finan ]);
		$row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-2', 'col-sm-4'];							  
								
		$row = $this->form->addFields([new TLabel('Plano'), $tipo_plano ],
		                              [new TLabel('Grupo'), $grupo ],
                                      [new TLabel('Ramo'), $ramo ]);
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];						

		$row = $this->form->addFields([new TLabel('Situação'), $ativo ],
                                      [new TLabel('Nro Processo'), $processo2 ],
                                      [new TLabel('Carregamento%'), $carreg ]);
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];						
		
		
		//add o campo que não esta no addQuickField
		$this->form->setFields(array($id_planos, $processo, $descricao, $pln_codigo, $regime_finan,$tipo_plano, $grupo, $ramo, $ativo, $processo2, $carreg, $btn_salvar, $btn_cancelar) );
		
        //CRIA PANEL GROUP
		$panel = new TPanelGroup('Cadastro de Planos SUSEP (T203)');
        $panel->add($this->form);
		
		$panel->addFooter(THBox::pack($btn_salvar, $btn_cancelar) );
		
		//add scroll Horizontal
		$panel->getBody()->style = 'overflow-x:auto' ;
	
        //BOX ALERT
		$this->alertBox = new TElement('div');
        
        //wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'Width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'Planos_SusepListe'));
        $vbox->add($panel);

        parent::add($vbox);
		
		
		
	}//__construct
	
	 /**
     * Send data
     */
    public function onSend($param)
    {
        try
        {
            $data = $this->form->getData(); // optional parameter: active record class
            
            $this->form->validate();
            
            // put the data back to the form
            $this->form->setData($data);
            
            // creates a string with the form element's values
            $message = 'Id: '           . $data->id . '<br>';
            $message.= 'Description : ' . $data->description . '<br>';
            $message.= 'Date1: '        . $data->date . '<br>';
            $message.= 'Color : '       . $data->color . '<br>';
            $message.= 'List : '        . $data->list . '<br>';
            $message.= 'Text : '        . $data->text . '<br>';
            
            // show the message
            new TMessage('info', $message);
        }
        catch (Exception $e)
        {
            $this->alertBox->add( new TAlert('danger', $e->getMessage()) );
        }
    }
	
	
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			$data = $this->form->getData();
			
			if($data->RAMO == '')
			{
				$data->RAMO = '-';
			}	
			
			$this->form->validate();
			
			$planos_susep = new planos_susep;

			$planos_susep->ID_PLANOS_SUSEP   = $data->ID_PLANOS_SUSEP;
			$planos_susep->PROCESSO          = $data->PROCESSO;
			$planos_susep->DESCRICAO         = $data->DESCRICAO;
			$planos_susep->PLNCODIGO         = $data->PLNCODIGO;
			$planos_susep->RAMO              = $data->RAMO;
			$planos_susep->GRUPO             = $data->GRUPO;
			$planos_susep->TIPO              = $data->TIPO_PLANO;
			$planos_susep->REGIME_FINANCEIRO = $data->REGIME_FINANCEIRO;
			$planos_susep->ATIVO             = $data->ATIVO;
			$planos_susep->PROCESSO2         = $data->PROCESSO2;
			$planos_susep->CARREG            = $data->CARREG;
			
			$planos_susep->store();
			
			//$action = new TAction(array('AddRamosGrupos', 'onCarregar'));	
			new TMessage('info', 'Salvo com sucesso');//, $action
			
			
			//$data->LB_ID = $planos_susep->ID_PLANOS_SUSEP;
			$this->form->setData($data);	
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onSave
	
	/*
	 Instância um Plano Susep, usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			
			if(isset($param['key']) )
			{
				$key = $param['key'];
				
				$planos_susep = new planos_susep($key);
				
				$data->ID_PLANOS_SUSEP   = $planos_susep->ID_PLANOS_SUSEP;
				$data->TESTE2            = $planos_susep->ID_PLANOS_SUSEP;
				$data->PROCESSO   		 = $planos_susep->PROCESSO;
				$data->DESCRICAO  		 = $planos_susep->DESCRICAO;
				$data->PLNCODIGO         = $planos_susep->PLNCODIGO;
				//$data->RAMO              = $planos_susep->RAMO;
				$data->GRUPO             = $planos_susep->GRUPO;
				$data->TIPO_PLANO        = $planos_susep->TIPO;
				$data->REGIME_FINANCEIRO = $planos_susep->REGIME_FINANCEIRO;
				$data->ATIVO             = $planos_susep->ATIVO;
				$data->PROCESSO2         = $planos_susep->PROCESSO2;
				$data->CARREG            = $planos_susep->CARREG;
				
				$id_grupo = $planos_susep->GRUPO;
				$id_ramo  = $planos_susep->RAMO;
				
				//teste
				
				//pega o plano certo como primeira opção
				
				if($planos_susep->RAMO != '-')
				{		
					/*$item = array();
					$item[1] = '( '.($planos_susep->RAMO) . ' ) ' . $planos_susep->nome_ramo->RAMO ;
					
					//trás os outros planos pra alimenta a combo				
					$item[2] = 'teste2';
					TCombo::reload('Formplanos_susep', 'RAMO', $item);*/
					
					///
					
					
					$rp_seg_ramos = new TRepository('seg_ramos');
					$criteria = new TCriteria;
					$criteria->add(new TFilter('GRUPO', '=', $planos_susep->GRUPO));
					//$criteria->add(new TFilter('CODIGO', '=', $id_ramo));
					
					$obj = $rp_seg_ramos->load($criteria);
					
					$options = array();
					foreach($obj as $objs)
					{
						//$options[$planos->COD_COBERTURA] = $planos->cobertura->COBERTURA;
					    //$options[9999] = $planos_susep->nome_ramo->RAMO;
						$options[$objs->CODIGO] = $planos_susep->nome_ramo->RAMO;
						$options[$objs->CODIGO] = $objs->RAMO;
						//({CODIGO}) {RAMO}
					}
					
					TCombo::reload('Formplanos_susep', 'RAMO', $options);//form + obj + vetor
					///
					
					
				
			    }
				
				
				//$item[] = ;
				//$item[] = ;
				
				
				//$this->onCargaRamoEdit($id_ramo, $id_grupo);
					
			}
			else
			{
				
			}		
			
			$this->form->setData($data);
			
			TTransaction::close();
			

		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onEdit
	
	/*
	public function onIncluir()
	{
		$this->form->clear();
		
		$data = $this->form->getData();
		$data = new StdClass;
        $data->TIPO_PLANO = 'P | Pecúlio';//CORRIGIR
        //$data->list = 'a'; 
        $this->form->setData($data);
		
		
	}//onIncluir
	*/	
	
	/*
	fecha a TWindow
	*/
	public function onFechar()
	{
		TWindow::closeWindow();
		
	}//onFechar 
	
	/*
	Combo hierárquica, trás os grupos que pertencem ao ramo. 
	*/
	public static function onCargaRamo($param)
	{
		try
        {
            TTransaction::open('db2');
			
            if ($param['GRUPO'])
            {
                $criteria = TCriteria::create( ['GRUPO' => $param['GRUPO'] ] );
                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('Formplanos_susep', 'RAMO', 'db2', 'seg_ramos', 'CODIGO', '({CODIGO}) {RAMO}', 'RAMO', $criteria, TRUE);
            }
            else
            {
                TCombo::clearField('Formplanos_susep', 'RAMO');//'form' , 'CAMPO'
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
		
	}//onCargaRamo
	
	//TESTE CARREGAMENTO COMBO no onEdit
	
	public static function onCargaRamoEdit($id_ramo, $id_grupo)
	{
		try
        {	
			TTransaction::open('db2');
			
			
			$rp_seg_ramos = new TRepository('seg_ramos');//TRepository
			
			//$grupo = 9;
			
			$criteria = new TCriteria;
			$criteria->add(new TFilter('GRUPO', '=', $id_grupo));
			//$criteria->add(new TFilter('CODIGO', '=', $id_ramo));
			
			$obj = $rp_seg_ramos->load($criteria);
			
			$options = array();
			foreach($obj as $objs)
			{
				//$options[$planos->COD_COBERTURA] = $planos->cobertura->COBERTURA;
			$options[$objs->CODIGO] = $objs->RAMO;
				//({CODIGO}) {RAMO}
			}
			
			//$options[$planos->COD_COBERTURA] = $planos->cobertura->COBERTURA;
			///$nomeGrupo = array(1 => $obj);
			
			//$rp_seg_ramos
			
			TCombo::reload('Formplanos_susep', 'RAMO', $options);//form + obj + vetor
			
			
            TTransaction::close();
			

		   // TTransaction::open('db2');
			
			// //$data = $this->form->getData();
			
			// $seg_ramo = new seg_ramos(29);
			// //$nomeRamo = $seg_ramo->RAMO;
			
			// $nomeGrupo = $seg_ramo->nome_grupo->GRUPO;
			
			// $teste = array(1 => $nomeGrupo);
			
			// TCombo::reload('planos_susep', 'RAMO', $teste);//form + obj + var
			
			// echo $nomeGrupo;
			
            // TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
		
	}//onCargaRamoEdit
	
	//TERMINA TESTE
	
	/*
	
			/*
			TTransaction::open('db2');
			
            //$key = $param['key'];
			
			//TCombo::reload('customform', 'TIPO_COBERTURA', $opt);//form + obj + var
			
			//$rp_planosSusep = new seg_ramos($id_grupo);
			$seg_ramos = new seg_ramos($id);
			
			$ramo  = 'Ramo Teste';
			$grupo = 9;
			if  (!empty($ramo))
            {
                
				//$criteria = TCriteria::create('RAMO', 'LIKE', $ramo);
				//$criteria = TCriteria::create( ['GRUPO' => $grupo ] );
				$criteria = TCriteria::create( ['RAMO'  => $ramo ] );
				
				$order = 'Viagem';
                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('planos_susep', 'RAMO', 'db2', 'seg_ramos', 'CODIGO', '({CODIGO}) {RAMO}', 'RAMO', $criteria, FALSE);
            }
            else
            {
                TCombo::clearField('planos_susep', 'RAMO');//'form' , 'CAMPO'
				
            }
            
            TTransaction::close();
			*/
	
	
}//TWindow


?>