<?php
class BancosForm extends TPage
{	
	private $form;
	private $notebook;
	
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
        
        // creates the form
        $this->form = new BootstrapFormBuilder('formBancos');
        //$this->form->setFormTitle('Cadastro de Bancos (T016)');
		$this->form->setFieldSizes('100%');
		
		//Captura a aba ativa
		$this->form->setTabAction(new TAction(array($this, 'onTabClickBancos')));
		
		//PAGE pg_ConfigBoleto
		$codigo    = new TEntry('CODIGO');
		$banco     = new TEntry('BANCO');
		$valor_doc = new TEntry('VALOR_DOC');
		$cnab      = new TEntry('CNAB');
		$linha1    = new TEntry('LINHA1');
		$linha2    = new TEntry('LINHA2');
		$linha3    = new TEntry('LINHA3');
		$linha4    = new TEntry('LINHA4');
		$linha5    = new TEntry('LINHA5');
		$linha6    = new TEntry('LINHA6');
		$pref      = new TEntry('PREF');
		$nro   	   = new TEntry('NRO');
		$carteira  = new TEntry('CARTEIRA');
		$agencia   = new TEntry('AGENCIA');
		$dig_ag    = new TEntry('DIG_AG');
		$cc_cn     = new TEntry('CC_CN');
		$protd_cn  = new TEntry('PROTD_CN');
		$cedente   = new TEntry('CEDENTE');
		$cedente2  = new TEntry('CEDENTE2');
		$dig_ced   = new TEntry('DIG_CED');
		$nome_ced  = new TEntry('NOME_CED');
		$prot_cn   = new TRadioGroup('PROT_CN');
		$multa     = new TEntry('MULTA');
		$mora_dia  = new TEntry('MORA_DIA');
		
		//PAGE pg_DebConta
		$cod_convenio = new TEntry('COD_CONVENIO');
		$sequencia    = NEW TEntry('SEQUENCIA'); 
		$taxa_deb     = NEW TEntry('TAXA_DEB'); 
		$cnpj_l       = NEW TEntry('CNPJ_L'); 
		$cemp_cn      = NEW TEntry('CEMP_CN'); 
		
		//cria od Btn
		$btn_salvar = TButton::create('btn_salvar', array($this, 'onSave'), 'Salvar', 'fa: fa-save');
		$btn_salvar->class = 'btn btn-sm btn-primary';
		$btn_voltar = TButton::create('btn_voltar', array('BancosListe', 'onReload'), 'Voltar', 'fa: fa-arrow-left blue');
		$btn_fechar = TButton::create('btn_fechar', array('PageInicial', 'onReload'), 'Salvar', 'fa: fa-power-off red');
		
		//FORMATAÇÕES
		//setSize
		$codigo->setSize('100%');
		$banco->setSize('100%');
		$valor_doc->setSize('100%');
		$cnab->setSize('100%');
		$linha1->setSize('100%');
		$linha2->setSize('100%');
		$linha3->setSize('100%');
		$linha4->setSize('100%');
		$linha5->setSize('100%');
		$linha6->setSize('100%');
		$cedente->setSize('100%');
		$cedente2->setSize('50%');
		$dig_ced->setSize('46%');
		
		//validação
		$banco->addValidation( ' "Banco" ', new TRequiredValidator);
		
		//placeHolder
		$dig_ag->placeholder   = 'Dígito';
		$protd_cn->placeholder = 'Dígito';
		
		//setTipe
		$dig_ag->setTip('Dígito');
		$protd_cn->setTip('Dígito');
		$dig_ced->setTip('Dígito');
		
		//addItems
		$options = ['T'=>'Sim', 'F' => 'Não'];
		$prot_cn->addItems($options);
		$prot_cn->setLayout('horizontal');
		
		//add uma aba pra página
 	    $this->form->appendPage('Configuração do Boleto');
        
        //** PAGE Configuração do Boleto 
        $row = $this->form->addFields([new TLabel('Código'), $codigo ],
                                      [new TLabel('Banco'), $banco ],
    								  [new TLabel('CNAB 240/400'), $cnab ],
									  [new TLabel('Taxa Banco'), $valor_doc ] );
		$row->layout = ['col-sm-1', 'col-sm-5', 'col-sm-3', 'col-sm-3' ];						
		$row = $this->form->addFields([new TLabel('Linha1'), $linha1 ] ); 
		$row->layout = ['col-sm-12' ];  						
							   
		$row = $this->form->addFields([new TLabel('Linha2'), $linha2 ] );
        $row->layout = ['col-sm-12' ];
		
		$row = $this->form->addFields([new TLabel('Linha3'), $linha3 ] );
        $row->layout = ['col-sm-12' ];
		
		$row = $this->form->addFields([new TLabel('Linha4'), $linha4 ] );
        $row->layout = ['col-sm-12' ];
		
		$row = $this->form->addFields([new TLabel('Linha2'), $linha5 ] );
        $row->layout = ['col-sm-12' ];
		
		$row = $this->form->addFields([new TLabel('Linha2'), $linha6 ] );
        $row->layout = ['col-sm-12' ];
		

		$row = $this->form->addFields([new TLabel('Convênio'), $pref ],
								      [new TLabel('Próximo nosso nro'), $nro ],
								      [new TLabel('Carteira'), $carteira ] );
		$row->layout = ['col-sm-6', 'col-sm-3', 'col-sm-3' ];							   
								
		$row = $this->form->addFields([new TLabel('Agência'), $agencia ],
								      [new TLabel('Dígito'), $dig_ag],
								      [new TLabel('Conta'), $cc_cn ],
								      [new TLabel('Dígito'), $protd_cn ] );
		$row->layout = ['col-sm-4', 'col-sm-2', 'col-sm-4', 'col-sm-2' ];
								
								
		$row = $this->form->addFields([new TLabel('Cedente'), $cedente ],
							          [new TLabel('.'),$cedente2 ],	
							          [new TLabel('Dígito'), $dig_ced ] );	
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];						
						

		$row = $this->form->addFields([new TLabel('Nome cedente'), $nome_ced ],
							          [new TLabel('Protestar título não pago'),$prot_cn ] );
		$row->layout = ['col-sm-8', 'col-sm-4'];												

		$row = $this->form->addFields([new TLabel('Multa por atraso'), $multa ],
							          [new TLabel('Multa por dia de atraso'), $mora_dia ] );
		$row->layout = ['col-sm-6', 'col-sm-6'];						

        
		 // ** PAGE Débito em conta
        $this->form->appendPage('Débito em Conta');
       
        $row = $this->form->addFields([new TLabel('Código do convênio'), $cod_convenio ],
								      [new TLabel('Seguência'), $sequencia ],
								      [new TLabel('Taxa débito'), $taxa_deb ] );
		$row->layout = ['col-sm-4', 'col-sm-4','col-sm-4' ];						
								
		$row = $this->form->addFields([new TLabel('Versão layout'), $cnpj_l ],
								      [new TLabel('Agência Oper. Conta'), $cemp_cn ] );			
		$row->layout = ['col-sm-6', 'col-sm-6'];
		
		//add os campos do formulário
		$this->formFields = array($btn_salvar, $btn_voltar, $btn_fechar, $banco, $valor_doc, $cnab, $linha1,$linha2, $linha3, $linha4, $linha5, $linha6, $pref, $nro, $carteira, $agencia, $dig_ag, $cc_cn, $protd_cn, $cedente, $cedente2, $dig_ced, $nome_ced, $prot_cn, $multa, $mora_dia, $codigo, $cod_convenio, $sequencia, $taxa_deb, $cnpj_l, $cemp_cn);//formFields
		$this->form->setFields( $this->formFields );

		//panel
		$painel = new TPanelGroup('Cadastro de Bancos (T016)');
		$painel->add($this->form);
		//ativar a rolagem horizontal dentro do corpo do painell
		$painel->getBody()->style = 'overflow-x:auto';
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_salvar, $btn_voltar, $btn_fechar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_fechar));
		}
		
		//MENU BREADCRUMB
		$menuBread = new TXMLBreadCrumb('menu.xml', 'BancosListe');
        
		// wrap the page content using vertical box
        $vbox = new TVBox;
		$vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($painel);

        parent::add($vbox);
		
	}//__construct
	
	
	/*
	  Salva um 'banco'
	*/
	public function onSave()
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$banco =  $this->form->getData('banco');
			
			$banco->store();
			
			$this->form->setData($banco);
			
			new TMessage('info', 'Salvo com sucesso');
			
			//Desabilita o 'CODIGO'
			TEntry::disableField('formBancos', 'CODIGO');
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formBancos', 'btn_salvar');
			}
			
			TTransaction::close();
			
			//mantém na mesma aba
			$this->form->setCurrentPage(TSession::getValue('TS_current_page_banco'));//
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	/*
	 Instância um 'banco' usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//$data = $this->form->getData('banco');
			
			if(isset($param['key'])) 
			{
				//pega o id o objeto pelo URL
				$key = $param['key'];
				
				$banco = new banco($key);
				
				$this->form->setData($banco);
				
				//Desabilita o 'CODIGO'
			    TEntry::disableField('formBancos', 'CODIGO');
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formBancos', 'btn_salvar');
				}
			}
	
			$this->form->setCurrentPage(0);//
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onEdit
	
	/**
     * Executado quando traca a aba do form 
     */
    public static function onTabClickBancos($param)
    {
		
        //new TMessage('info', '<b>You have clicked at the tab</b>:  <br><br>' . $param['current_page'] . str_replace(',', '<br>', json_encode($param)));
		
		
		//grava a aba na sessão a cada troca 
		TSession::setValue('TS_current_page_banco', $param['current_page'] );
		
    }//onTabClickForm
	
}//TPage

?>