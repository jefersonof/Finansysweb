<?php
class Motivos_CancelamentoForm extends TPage
{	
	private $form;
	
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
        
        // creates the form
        $this->form = new BootstrapFormBuilder('formMotivos');
        $this->form->setFormTitle('Cadastro de Motivos de cancelamento (T007)');
		$this->form->setFieldSizes('100%');
        
		//PAGE pg_ConfigBoleto
		$codigo  = new TEntry('CODIGO');
		$motivo  = new TEntry('MOTIVO');
		$status  = new TCombo('STATUS');
		$com_fed = new TCombo('COM_FED');
		$obs     = new TText('OBS');
		$reinc   = new TCombo('REINC');
		$statusa = new TCombo('STATUSA');
		$altpec  = new TCombo('ALTPEC');
		$na      = new TCombo('NA');
		$apo_seg = new TCombo('APO_SEG');
	
		//validação
		$motivo->addValidation( ' " Motivo " ' , new TRequiredValidator );
		
		//FORMATAÇÕES
		
		//placeHolder
		//$dig_ag->placeholder   = 'Dígito';
		
		//setTipe
		//$dig_ag->setTip('Dígito');
		
		//addItems
		$opt_SN = ['T'=>'Sim', 'F' => 'Não'];
		$apo_seg->addItems($opt_SN);
		$altpec->addItems($opt_SN);
		$reinc->addItems($opt_SN);
		$na->addItems($opt_SN);
		
		$opt_com_fed = ['0'=>'0 - Sem comando', '2' => '2 - Exclusão de todas seguências', '3' => '3 - Exclusão de uma seguência', '4' => '4 - Inclusão', '5' => '5 - Alteração'];
		$com_fed->addItems($opt_com_fed);
		
		$opt_status = ['A' => 'Contrato ativo', 'S' => 'Contrato suspenso', 'C' => 'Contrato cancelado', 'P' => 'Contrato pago', 'X' => 'Contrato transferido'];
		$status->addItems($opt_status);
		
		$opt_statusa = ['A' => 'Contrato ativo', 'S' => 'Contrato suspenso', 'C' => 'Contrato cancelado', 'P' => 'Contrato pago'];
		$statusa->addItems($opt_statusa);
		
        //** PAGE Configuração do Boleto 
        $row = $this->form->addFields([new TLabel('Código'), $codigo ],
                                      [new TLabel('Motivo'), $motivo ],
    								  [new TLabel('Comando arq. federal'), $com_fed ]);
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-4' ];
		
		$row = $this->form->addFields([new TLabel('Aparece em apólice de seguro'), $apo_seg ],
                                      [new TLabel('Cobertura de sinistro por morte'), $altpec ],
    								  [new TLabel('status de contrato de pecúlio'), $status ],
									  [new TLabel('status de contrato de assist. financeira'), $statusa ]);
		$row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3' ];
		
		$row = $this->form->addFields([new TLabel('Considerar inclusão no relatório 241'), $reinc ],
                                      [new TLabel('Não pode ser alterardo após o período de lançamento'), $na ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];
		
		$row = $this->form->addFields([new TLabel('Observação'), $obs ]);
		$row->layout = ['col-sm-12'];
        
        //CRIA AS AÇÕES DO FORM
		if($permissao_geral['insercao'] == 1)
		{	
			$btn = $this->form->addAction('Salvar' ,new TAction(array($this, 'onSave')), 'far:save' );
			$btn->class = 'btn btn-sm  btn-primary';
		}
		
		
		$this->form->addAction('Voltar', new TAction(array('Motivos_CancelamentoListe', 'onReload')), 'fa: fa-arrow-left');
		
		$this->form->addAction('Fechar' ,new TAction(array('PageInicial', 'onReload')) , 'fa: fa-power-off red' ); 
		
		//MENU BREADCRUMB
		$menuBread = new TXMLBreadCrumb('menu.xml', 'Motivos_CancelamentoListe');
        
		// wrap the page content using vertical box
        $vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($this->form);

        parent::add($vbox);
		
	}//__construct
	
	/*
	Salva um 'motivo_cancelamento'
	*/
	public function onSave()
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formMotivos', 'btn_salvar');
			}
			
			$motivo = $this->form->getData('motivo_cancelamento');
			
			$motivo->store();
			
			$this->form->setData($motivo);
			
			new TMessage('info', 'Salvo com sucesso');
			
			//Desabilita o 'CODIGO'
			TEntry::disableField('formMotivos', 'CODIGO');
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	/*
	  Instância um 'cliente' usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			if(isset($param['key'])) 
			{
				//pega o id o objeto pelo URL
				$key = $param['key'];
				
				//Desabilita o 'CODIGO'
				TEntry::disableField('formMotivos', 'CODIGO');
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formMotivos', 'btn_salvar');
				}
				
				$motivo = new motivo_cancelamento($key);
				
				$this->form->setData($motivo);
			}
	
			//$this->notebook->setCurrentPage(1);//setCurrentPage
			TTransaction::close();
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onEdit
	
}//TPage



?>