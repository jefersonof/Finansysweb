<?php
class FormasPagForm Extends TPage
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
		
        $this->form = new BootstrapFormBuilder('formFormasPag');
		$this->form->setFormTitle('Formas de Pagamento (T023)');
        $this->form->setFieldSizes('100%');
        
        $codigo    = new TEntry('CODIGO');
		$forma     = new TEntry('FORMA');
		$liberacao = new TCombo('LIB');
		$inclusao  = new TCombo('CAD');
		
		//validação
		$forma->addValidation(' "FORMA" ' , new TRequiredValidator);
		
		$item = array('T' => 'sim' , 'F' => 'Não');
		$liberacao->addItems($item);
		$inclusao->addItems($item);
		
		$row =  $this->form->addFields([new TLabel('Código'), $codigo ],
                                       [new TLabel('Forma'), $forma ],
                                       [new TLabel('Liberação'), $liberacao ],
									   [new TLabel('Inclusão'), $inclusao ]);
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-2', 'col-sm-2' ];						
								
		// define as ações do form
		if($permissao_geral['insercao'] == 1)
		{	
			$btn = $this->form->addAction('Salvar' ,new TAction(array($this, 'onSave')), 'far:save' );
			$btn->class = 'btn btn-sm  btn-primary';
		}
		
		
		$this->form->addAction('Cancelar' ,new TAction(array('FormasPagListe', 'onReload')), 'far: fa-window-close red');
		
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'FormasPagListe'));
        $vbox->add($this->form);

        parent::add($vbox);
		
	}//__construct'
	
	/*
	Salva um 'formaspag'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formFormasPag', 'btn_salvar');
			}
			
			$formaspag = $this->form->getData('formaspag');
			$formaspag->store();
			
			new TMessage('info', 'Registro salvo');
			
			$this->form->setData($formaspag);
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	/*
	 Instância uma 'formaspag' usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			if(isset($param['key']))
			{	
				$key = $param['key'];
				$formaspag = new formaspag($key);
				
				$this->form->setData($formaspag);
				
				//desabilita a ediçaõ do codigo
				TEntry::disableField('formFormasPag', 'CODIGO');
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formFormasPag', 'btn_salvar');
				}	
			}
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('info', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onEdit
		

}//TPage


?>