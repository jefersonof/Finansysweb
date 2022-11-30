<?php
class PlanosForm Extends TPage
{	
	private $form;
	private $datagridPc;
	private $datagridPf;
	private $notbook;
	
	public function __construct()
	{
		parent::__construct();
		
        $this->form = new BootstrapFormBuilder('formCatRisco');
		$this->form->setFormTitle('Cadastro de Planos (T010)');
        $this->form->setFieldSizes('100%');
        
		$codigo   = new TEntry('CODIGO');
		$plano    = new TEntry('PLANO');
		$status   = new TCombo('STATUS');
		$tipo     = new TCombo('TIPO');
		$tipo2    = new TDBCombo('TIPO2', 'db2', 'tipo_cto', 'CODIGO','({COD} )  {DESCRICAO}');
		$processo = new TDBCombo('PROCESSO_SUSEP', 'db2', 'planos_susep', 'ID_PLANOS_SUSEP', '{PROCESSO} | {DESCRICAO} ');
		
		$cobertura_id   = new TDBSeekButton('COD_COBERTURA', 'DB2', 'formCatRisco', 'COBERTURA', 'COBERTURA', 'COD_COBERTURA', 'COBERTURA_NOME');
		$cobertura_nome = new TEntry('COBERTURA_NOME');
		
		$faixa  = new TEntry('FAIXA');
		$id_ini  = new TEntry('ID_INI');
		$id_fim  = new TEntry('ID_FIM');
		$pu_puro = new TEntry('PU_PURO');
		//TDBCombo('nome_objeto','banco_de_dados','classe_model','campo_id','campo_descricao');
		
		
		//cria os btn
		$btn_add_cob = TButton::create('btn_add_cob',array($this, 'onAddCob'), 'incluir', 'fa:plus blue' );
		
		$btn_gravar_cob = TButton::create('btn_gravar_cob',array($this, 'onGravarCob'), 'Gravar', 'fa: fa-check blue' );
		
		$btn_gravar_faixa = TButton::create('btn_gravar_faixa',array($this, 'onGravarFaixa'), 'Gravar', 'fa: fa-check blue' );
		
		
		//formatações
		$status->addItems(array('S' => 'SIM', 'N' => 'NÃO' ));
		$tipo->addItems(array('S' => 'SIM', 'N' => 'NÃO' ));
		
		$tipo->setTip('Sempre mudar faixa etária no reajuste');
		
		//VALIDAÇÃO
		$plano->addValidation(' "Descrição" ' , new TRequiredValidator);
		
		//COMEÇA A PAGINA
		
		$row =  $this->form->addFields([ new TLabel('Código'), $codigo ],
                                       [ new TLabel('Descrição'), $plano ],
                                       [ new TLabel('Ativo'), $status ]);
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-4' ];

		$row =  $this->form->addFields([ new TLabel('Sempre mudar faixa etária'), $tipo ],
                                       [ new TLabel('Tipo de plano'), $tipo2 ],
									   [new TLabel('Processo Susep'), $processo]);
		$row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-6' ];
		
		//LINHA DIVISÓRIA 'COBERTURAS'
		$lb_coberturas = new TLabel('Coberturas');
		$lb_coberturas->style = 'color:#3c8dbc';
		$row = $this->form->addFields([$lb_coberturas]);
		$row->layout = ['col-sm-12'];
		$row->style = 'border-bottom:1px solid #D5D5D5; margin:0 0 5px 1px;';
		
		$row = $this->form->addFields([new TLabel('Código'), $cobertura_id ],
							          [new TLabel('Cobertura'), $cobertura_nome ]);
		$row->layout = ['col-sm-2','col-sm-10' ];
		
		//BARRA MENU COBERTURAS
		$row = $this->form->addFields([$btn_add_cob], [$btn_gravar_cob] );
		$row->layout = ['col-sm-1', 'col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';
		
		//CRIA A GRID 'PLANOS_COB'
		$this->datagridPc = new TQuickGrid;
		$this->datagridPc->style = 'width:100%';
		
		$this->datagridPc->addQuickColumn('', 'edit', 'center', '10%');
		$this->datagridPc->addQuickColumn('', 'delete', 'center', '10%');
		$this->datagridPc->addQuickColumn('Código', 'COD_COBERTURA', 'center', '10%');
		$this->datagridPc->addQuickColumn('Cobertura', 'COBERTURA_NOME', 'center');
		
		$this->datagridPc->CreateModel();
		
		$row = $this->form->addFields([$this->datagridPc ] );
		$row->layout = ['col-sm-12'];
		
		//LINHA DIVISÓRIA 'FAIXAR ETÁRIAS'
		$lb_faixa = new TLabel('Faixas etárias');
		$lb_faixa->style = 'color:#3c8dbc';
		$row = $this->form->addFields([$lb_faixa]);
		$row->layout = ['col-sm-12'];
		$row->style = 'border-top:1px solid #C1CDCD;';
		$row->style = 'border-bottom:1px solid #C1CDCD; margin:0 0 5px 1px;';
		
		$row = $this->form->addFields([new TLabel('Faixa'), $faixa ],
							          [new TLabel('Idade incial'), $id_ini ],
									  [new TLabel('Idade final'), $id_fim ],
									  [new TLabel('PU puro'), $pu_puro ]);
		$row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];
		
		//barra menu coberturas
		$row = $this->form->addFields([$btn_gravar_faixa] );
		$row->layout = ['col-sm-1', 'col-sm-1'];
		$row->style = 'background:#D5D5D5; margin:0 0 5px 1px';
		
		//CRIA A GRID 'FAIXAS'
		$this->datagridPf = new TQuickGrid;
		$this->datagridPf->style = 'width:100%';
		
		$this->datagridPf->addQuickColumn('', 'edit', 'center', '10%');
		$this->datagridPf->addQuickColumn('', 'delete', 'center', '10%');
		$this->datagridPf->addQuickColumn('Faixa', 'FAIXA', 'center' );
		$this->datagridPf->addQuickColumn('Idade inicial', 'ID_INI', 'center');
		$this->datagridPf->addQuickColumn('Idade final', 'ID_FIM', 'center');
		$this->datagridPf->addQuickColumn('PU puro', 'PU_PURO', 'center');
		
		$this->datagridPf->CreateModel();
		
		$row = $this->form->addFields([$this->datagridPf ] );
		$row->layout = ['col-sm-12'];
							
								
		// define as ações do form
		$this->form->addAction('Salvar' ,new TAction(array($this, 'onSave')), 'ico_save.png' );
		
		$this->form->addAction('Cancelar' ,new TAction(array('PlanosListe', 'onReload')), 'ico_delete.png');
		
		//ADD OS CAMPOS DO FORM
		$this->formFields = array($codigo, $plano, $status, $tipo, $tipo2, $processo, $cobertura_id, $cobertura_nome, $faixa, $id_ini, $id_fim, $pu_puro,  $btn_add_cob, $btn_gravar_cob, $btn_gravar_faixa);
		
		$this->form->setFields($this->formFields );
		
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'PlanosListe'));
        $vbox->add($this->form);

        parent::add($vbox);
		
	}//__construct'
	
	/*
	Deleta um item PEC da sessão, 
	mas nao deleta da base
	*/
	public function onDeleteCob($param)
	{
		$data = $this->form->getData();

        $this->form->setData( $data );

		//LE ITENS DA SESSÃO
		$ts_plano_cob = TSession::getValue('TS_plano_cob');
		
        //'unset' APAGA OS ITEMS DA SESSÃO DE ACORDOCOM SEU ID
        unset($ts_plano_cob[ (int) $param['list_product_id'] ] );
        
		//GRAVA NA SESSÃO SEM O OBJ DO 'unset'
        TSession::setValue('TS_plano_cob', $ts_plano_cob);
		
        // RECARREGAR OS ITENS DA VENDA
        $this->onReload( $param );
		
	}//onDeleteCob
	
	
	public function onGravarFaixa()
    {
		
	}
	
	/*
	Grava um novo 'plano_cobetura'
	*/
	public function onGravarCob($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			//pega as regra atuais
			$ts_plano_cob = TSession::getValue('TS_plano_cob');
			
			//continua o carrinho de compras
			$cobertura = new cobertura($data->COD_COBERTURA);//CODIGO
			
			//Da uma ID para nova regra
			$key = (int) $data->COD_COBERTURA;//campo oculto
			
			//add novo regra
			$ts_plano_cob[ $key ] = array('CODIGO'         => $param['CODIGO'],
			                              'COBERTURA_NOME' => $cobertura->COBERTURA,
										  'COD_PLANO'      => $param['CODIGO'],
										  'COD_COBERTURA'  => $param['COD_COBERTURA']
									     );  
			
					
			TTransaction::close();		
									   
			//grava a nova regra na sessão
			TSession::setValue('TS_plano_cob', $ts_plano_cob);
			
			$ts_plano_cob = TSession::getValue('TS_plano_cob');
			
			//$data->COD_COBERTURA  = '';
			//$data->COBERTURA_NOME = '';
			
			
			//Desabilita campos
			//TButton::disableField('formAssociado', 'btn_gravar_pec');
			
			$this->form->setData($data);
			
			//recarrega a página 
			$this->onReload( $param ); // reload is items sale items
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onGravarCob
	
	public function onAddCob()
	{
		
	}//onAddCob
	
	
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$plano = $this->form->getData('plano');
			
			//PEGA AS REGRAS PARA OS AGREGAOS AF
			$ts_plano_cob = TSession::getValue('TS_plano_cob');
			if ($ts_plano_cob)
			{	
				foreach($ts_plano_cob as $lista_cob)
				{
					//$cm_af = new cm_af;
					$plano_cob = new plano_cob;
					
					$plano_cob->COD_PLANO     = $lista_cob['COD_PLANO'];
					$plano_cob->COD_COBERTURA = $lista_cob['COD_COBERTURA'];
					
					$plano->addPlano_Cob($plano_cob);
					
				}//foreach
				
			}//$ts_plano_cob
			
			
			$plano->store();
			
			new TMessage('info', 'Registro salvo');
			
			$this->form->setData($plano);
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	/*
	Mandas os dados das faixas das coberturas para datagridPf, o id da cobertura é usado como parâmetro 
	*/
	
	public static function onEditItemCob ($param)
	{
		//abastace a datagridPf
		
		//$ts_plano_cob_faixa = TSession::getValue('TS_plano_cob_faixa');

		/*
		foreach($ts_plano_cob_faixa as $plano_cob_faixa )
		{
		
			if($plano_cob_faixa['COD_COBERTURA'] == $param['CODIGO'] )
			{	
				//ADD OS ITEMS NA GRID datagrid_af 
				$item = StdClass;
				$item->FAIXA   = $list_product2['FAIXA'];
				$item->ID_INI  = $list_product2['ID_INI'];
				$item->ID_FIM  = $list_product2['ID_FIM'];
				//$item->PU_PURO = $codcob_faixa;
				$item->PU_PURO = $list_product2['PU_PURO'];
				
				$this->datagridPf->addItem( $item );
				
			}
		
		}
		*/
		
		
		$data = $this->form->getData();
		
		//pega os items da sessão
		$ts_plano_cobs = TSession::getValue('TS_plano_cob');
		
        //OBTEM A COBERTURA 
		$ts_plano_cob  = $ts_plano_cobs[ (int) $param['list_product_id'] ];
		
		$data->COD_COBERTURA  = $ts_plano_cob['CODIGO'];
        $data->COBERTURA_NOME = $ts_plano_cob['COD_COBERTURA'];
		
		$this->form->setData($data);
		
		//DESABILITA OS BTN
		//TButton::disableField('formAssociado', 'btn_novo_af');
		
	}//onEditItemCob
	
	public function onReload($param)
	{
		//paga a variavel de sessão 
	    $ts_plano_cob       = TSession::getValue('TS_plano_cob');
	    $ts_plano_cob_faixa = TSession::getValue('TS_plano_cob_faixa');
        $data 			    = TSession::getValue('TS_data');
		
		// LIMPA AS GRIDS 
		$this->datagridPc->clear();
		$this->datagridPf->clear();
		
		//CARREGA OS DADOS DOS plano_cob's, GRID 'datagridPc'
		if ($ts_plano_cob)
        {
            $cont = 1;
            foreach ($ts_plano_cob as $list_product_id => $list_product)
            {
                $item_name = 'prod_' . $cont++;
                //OBJ PADRÃO DAS CLASSES
				$item = new StdClass;

				//CRIA AS AÇÕES DOS BTN
                $action_del = new TAction(array($this, 'onDeleteCob'));
                $action_del->setParameter('list_product_id', $list_product_id);
				$action_del->setParameter('cont',$cont);

				$action_edi = new TAction(array($this, 'onEditItemCob'));
                $action_edi->setParameter('list_product_id', $list_product_id);
				$action_edi->setParameter('cont',$cont);
				
				//CRIA OS BTN E ADD AS AÇÕES
                $button_del = new TButton('delete_product'.$cont);
                $button_del->class = 'btn btn-default btn-sm';
                $button_del->setAction( $action_del, '' );
                $button_del->setImage('fa:trash-o red fa-lg');

                $button_edi = new TButton('edit_product'.$cont);
                $button_edi->class = 'btn btn-default btn-sm';
                $button_edi->setAction( $action_edi, '' );
                $button_edi->setImage('fa:edit blue fa-lg');

                //ASSOCIA O OBJ PADRÃO AOS BNT  
				$item->edit    = $button_edi;
                $item->delete  = $button_del;

                $this->formFields[ $item_name.'_edit' ]   = $item->edit;
                $this->formFields[ $item_name.'_delete' ] = $item->delete;
				
				//ADD OS ITEMS NA GRID datagrid_af 
				$item->CODIGO         = $list_product['CODIGO'];
				$item->COD_COBERTURA  = $list_product['COD_COBERTURA'];
				$item->COBERTURA_NOME = $list_product['COBERTURA_NOME'];
				$item->COD_PLANO      = $list_product['COD_PLANO'];
					
				$this->datagridPc->addItem( $item );
					
				//TSession::setValue('TS_codCob_faixa', $list_product['COD_COBERTURA']);
                
				$this->form->setData($data);
				
            }//foreach ($ts_plano_cob)
			
            $this->form->setFields( $this->formFields );
		    //$this->form->setData($data);
			
        }//if ($ts_plano_cob)
			
		//CARREGA OS DADOS DOS plano_cob_faxa's, para GRID 'datagridPf'
		if ($ts_plano_cob_faixa)
        {
            $cont = 1;//list_product
            foreach ($ts_plano_cob_faixa as $list_product_id => $list_product2)
            {
                $item_name = 'prod_' . $cont++;
                //OBJ PADRÃO DAS CLASSES
				$item = new StdClass;

				//CRIA AS AÇÕES DOS BTN
                $action_del = new TAction(array($this, 'onDeleteCob'));
                $action_del->setParameter('list_product_id', $list_product_id);
				$action_del->setParameter('cont',$cont);

				$action_edi = new TAction(array($this, 'onEditItemCob'));
                $action_edi->setParameter('list_product_id', $list_product_id);
				$action_edi->setParameter('cont',$cont);
				
				//CRIA OS BTN E ADD AS AÇÕES
                $button_del = new TButton('delete_product'.$cont);
                $button_del->class = 'btn btn-default btn-sm';
                $button_del->setAction( $action_del, '' );
                $button_del->setImage('fa:trash-o red fa-lg');

                $button_edi = new TButton('edit_product'.$cont);
                $button_edi->class = 'btn btn-default btn-sm';
                $button_edi->setAction( $action_edi, '' );
                $button_edi->setImage('fa:edit blue fa-lg');

                //ASSOCIA O OBJ PADRÃO AOS BNT  
				$item->edit    = $button_edi;
                $item->delete  = $button_del;

                $this->formFields[ $item_name.'_edit' ]   = $item->edit;
                $this->formFields[ $item_name.'_delete' ] = $item->delete;
			
				$codcob_faixa =  TSession::getValue('TS_codCob_faixa');
				//if($list_product2['COD_COBERTURA'] == $codcob_faixa )
				if($list_product2['COD_COBERTURA'] == $codcob_faixa )
				{	
					//ADD OS ITEMS NA GRID datagrid_af 
					$item->FAIXA   = $list_product2['FAIXA'];
					$item->ID_INI  = $list_product2['ID_INI'];
					$item->ID_FIM  = $list_product2['ID_FIM'];
					//$item->PU_PURO = $codcob_faixa;
					$item->PU_PURO = $list_product2['PU_PURO'];
					
					$this->datagridPf->addItem( $item );
					
				}
                
				$this->form->setData($data);
				
            }//foreach ($ts_plano_cob_faixa)
			
            $this->form->setFields( $this->formFields );
		    //$this->form->setData($data);
			
        }//if ($ts_plano_cob_faixa)
			
		$this->loaded = TRUE;
		
	}//onReload
	
	/*
	 Instância um 'plano' usando o @param['key'] como id do Objeto
     e COD_COBERTURA como index para variavel de sessão dos 'plano_cob';	 
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//$data = $this->form->getData();
			
			if(isset($param['key']))
			{	
				$key = $param['key'];
				$plano = new plano($key);
				
				//** PLANOS_COB **//
				$ts_plano_cob = array();
				foreach($plano->getPlano_Cob() as $plano_cob )//addcm_pec
				{
					$ts_plano_cob[$plano_cob->COD_COBERTURA]                   = $plano_cob->toArray();
					
					$ts_plano_cob[$plano_cob->COD_COBERTURA]['CODIGO']         = $plano_cob->CODIGO;
					
					$ts_plano_cob[$plano_cob->COD_COBERTURA]['COD_PLANO']      = $plano_cob->COD_PLANO;
					
					$ts_plano_cob[$plano_cob->COD_COBERTURA]['COBERTURA_NOME'] = $plano_cob->plano_cobertura->COBERTURA;
					
					$ts_plano_cob[$plano_cob->COD_COBERTURA]['COD_COBERTURA']  = $plano_cob->COD_COBERTURA;
					
					//TSession::setValue('TS_codCob_faixa', $plano_cob->COD_COBERTURA);
					
				}//getPlano_Cob
				//GRAVA OS 'plano_cob' NA SESSÃO
				TSession::setValue('TS_plano_cob', $ts_plano_cob);
				
				//** PLANOS_COB_FAIXA **//
				$ts_plano_cob_faixa = array();
				foreach($plano->getPlano_Cob_Faixa() as $plano_cob_faixa )//plano_cob
				{
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO] = $plano_cob_faixa->toArray();
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['FAIXA'] = $plano_cob_faixa->FAIXA;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['ID_INI'] = $plano_cob_faixa->ID_INI;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['ID_FIM'] = $plano_cob_faixa->ID_FIM;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['PU_PURO'] = $plano_cob_faixa->PU_PURO;
					
					$ts_plano_cob_faixa[$plano_cob_faixa->CODIGO]['COD_COBERTURA'] = $plano_cob_faixa->COBERTURA;
					
					TSession::setValue('TS_codCob_faixa', $plano_cob_faixa->COBERTURA);
					
					//$ts_plano_cob_faixa[$plano_cob->CODIGO]['PU_PURO'] = $plano_cob->PU_PURO;
					
				}//getPlano_Cob
				//GRAVA OS 'plano_cob' NA SESSÃO
				TSession::setValue('TS_plano_cob_faixa', $ts_plano_cob_faixa);
				
				
				
				//GRAVA OS DADOS DO FORM NA SESSÃO
				TSession::setValue('TS_data', $plano);
				
				$this->form->setData($plano);
				
				$this->onReload( $param );
				TTransaction::close();
				
				
				//desabilita a ediçaõ do codigo
				//TEntry::disableField('formCatRisco', 'CODIGO');
				
			}//(isset($param['key']
			
			
		}//try
		catch(Exception $e)
		{
			new TMessage('info', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onEdit
	
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