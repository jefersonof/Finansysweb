<?php
class Planos_SusepForm Extends TPage
{	
	private $form;
	private $datagrid;
	private $alertBox;
	
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
		//$tipo           = new TEntry('TIPO');
		$teste          = new TEntry('TESTE');
		$grupo          = new TDBCombo('GRUPO', 'db2', 'seg_grupos', 'CODIGO','({CODIGO} )  {GRUPO}');
		$grupo->setChangeAction( new TAction(array($this, 'onCargaRamo')));
	    $ramo  = new TDBCombo('RAMO', 'db2', 'seg_ramos', 'CODIGO', '({CODIGO}) {RAMO}' ,'CODIGO');
		$tipo           = new TCombo('TIPO');
		$tipo_produto   = new TEntry('TIPO_PRODUTO');
		$prazo_pag      = new TEntry('PRAZO_PAG');
		$taxa_juros     = new TEntry('TAXA_JUROS');
		$tab_servicos   = new TEntry('TAB_SERVICO');
		$lb_dados_plan  = new TLabel('Dados do Plano');//, 'black', 30, 'b'
		$regime_finan   = new TDBCombo('REGIME_FINANCEIRO', 'db2', 'reg_fin', 'CODIGO','({CODIGO} )  {REGIME}');
		//TDBCombo('nome_objeto','banco_de_dados','classe_model','campo_id','campo_descricao');
		
		//CRIA OS BTN
		$btn_salvar   = TButton::create('btn_salvar' ,array($this, 'onSave'), 'Salvar', 'far:save' );
		$btn_salvar->class = 'btn btn-sm  btn-primary';
		
		$btn_cancelar = TButton::create('btn_cancelar' ,array('Planos_SusepListe', 'onReload'), 'Cancelar', 'far: fa-window-close red' );
		
		//formatações
		$tipo->additems( array('P' => 'P | Pecúlio', 'S' => 'S | Seguro'));
		$ativo->addItems(array('S' => 'Ativo', 'N' => 'Inativo')) ;
		$lb_dados_plan->style = 'color:#FFF; width:100%';
		
		//validação
		$tipo->addValidation(' "PLANO" ', new TRequiredValidator);
		
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
								
		$row = $this->form->addFields([new TLabel('Plano'), $tipo ],
		                              [new TLabel('Grupo'), $grupo ],
                                      [new TLabel('Ramo'), $ramo ]);
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];						

		$row = $this->form->addFields([new TLabel('Situação'), $ativo ],
                                      [new TLabel('Nro Processo'), $processo2 ],
                                      [new TLabel('Carregamento%'), $carreg ]);
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];						
		
		
		//add o campo que não esta no addQuickField
		$this->form->setFields(array($id_planos, $processo, $descricao, $pln_codigo, $regime_finan,$tipo, $grupo, $ramo, $ativo, $processo2, $carreg, $btn_salvar, $btn_cancelar) );
		
        //CRIA PANEL GROUP
		$panel = new TPanelGroup('Cadastro de Planos SUSEP (T203)2');
        $panel->add($this->form);
		
		//var_dump($permissao_geral['insercao']);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$panel->addFooter(THBox::pack($btn_salvar,$btn_cancelar));
		}
		else
		{
			$panel->addFooter(THBox::pack($btn_cancelar));
		}	
		
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
    }//onSend
	
	/*
	Salva um 'planos_susep'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//Validação do form
			$this->form->validate();
			
			if(isset($planos_susep->RAMO ) and ($planos_susep->RAMO == ''))
			{
				$planos_susep->RAMO = '-';
			}
			
			if(isset($planos_susep->GRUPO ) and ($planos_susep->GRUPO == ''))
			{
				$planos_susep->GRUPO = '-';
			}
			
			
			//pega os dados do form e salva
			$planos_susep = $this->form->getData('planos_susep');
			$planos_susep->store();
			
			//$action = new TAction(array('AddRamosGrupos', 'onCarregar'));	
			new TMessage('info', 'Salvo com sucesso');//, $action
			
			TTransaction::close();
			
			//$data->LB_ID = $planos_susep->ID_PLANOS_SUSEP;
			
			//manda os dados para o form
			$this->form->setData($planos_susep);
			
			
			//Mostra O 'RAMO' e 'GRUPO'
			$obj = new STDClass;
			$obj->GRUPO = $planos_susep->GRUPO; 
			$obj->RAMO  = $planos_susep->RAMO; 
			
			//ATUALIZA OS DADOS NO FORM
			TForm::sendData('Formplanos_susep', $obj);
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('Formplanos_susep', 'btn_salvar');
			}
			
			
			//ATUALIZA OS DADOS NO FORM
			//TForm::sendData('Formplanos_susep', $planos_susep);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	/*
	 Instância um planos_susep, usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->clear();
			
			if(isset($param['key']))
			{
				$key = $param['key'];
				
				$planos_susep = new planos_susep($key);
								
				//MANDA OS DADOS PARA O FORM
				$this->form->setData($planos_susep);
				
				//GRAVA NA SESSÃO
				TSession::setValue('TS_data', $planos_susep);
				
				//disabilita o 'CODIGO'
				TEntry::disableField('formBancos', 'CODIGO');
				
				//Mostra O 'RAMO' e 'GRUPO'
				$obj = new STDClass;
				$obj->GRUPO = $planos_susep->GRUPO; 
				$obj->RAMO  = $planos_susep->RAMO; 
				
				//ATUALIZA OS DADOS NO FORM
				TForm::sendData('Formplanos_susep', $obj);
				
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('Formplanos_susep', 'btn_salvar');
				}
				
				if($permisao == 1 )
				{	
					TButton::enableField('Formplanos_susep', 'btn_salvar');
				}
					
			}//if $param['key'
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onEdit
	
	/*
	Combo hierárquica, trás os ramos que pertencem ao grupo. 
	*/
	public static function onCargaRamo($param)
	{
		try
        {
            TTransaction::open('db2');
			
            if(isset($param['GRUPO']))
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
	
	/*public function onReload($param)
	{
		$data = TSession::getValue('TS_data');
		
		$id_grupo = TSession::getValue('TS_id_grupo');
		$id_ramo  = TSession::getValue('TS_id_ramo');
	    
		//$id_grupo = TSession::setValue('TS_id_grupo' , $id_grupo);	
		//$id_ramo  = TSession::setValue('TS_id_ramo', $id_ramo);
		
		//$obj = new stdClass; 
		$data->RAMO = $id_ramo; 
		TForm::sendData('Formplanos_susep', $data); 
		
		$this->form->setData($data);
		
		//$this->onEdit($param);
		
		/*$rp_seg_ramos = new TRepository('seg_ramos');
		$criteria = new TCriteria;
		$criteria->add(new TFilter('GRUPO', '=', $id_grupo));
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
		
		new TMessage('info', 'Teste ');*/
		
	//}//onReload*/
	
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
	
	
	
	//TESTE CARREGAMENTO COMBO no onEdit
	
	/*public static function onCargaRamoEdit($id_ramo, $id_grupo)
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
		
	}//onCargaRamoEdit*/
	
	/*public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
		
    }//show*/
	
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