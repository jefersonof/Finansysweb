<?php
class Cat_RiscoForm Extends TPage
{	
	private $form;
	private $datagrid;
	private $notbook;
	
	public function __construct()
	{
		parent::__construct();
		
		TTransaction::open('permission');
		//LOGIN FORM
		
		
		
		//CARREGA AS PEMISSÕES - bota tudo no loginForm
		
		//pega as permissões padrão do grupo e grava na sessão
		/*$id_grupo = (TSession::getValue('usergroupids'));
		$grupo    = new SystemGroup($id_grupo);
		TSession::setValue('TS_permissaogrupo', $grupo);
		
		//pega as permissões especiais do usuário e grava na sessão
		//$id_user = TSession::getValue('userid');
		$id_user = 3;
		$funcao = new funcao;
		$programas_user = $funcao->buscaUserProgram($id_user);
		TSession::setValue('TS_permissaouser', $programas_user);//var_dump($programas_user[1]['id']);
		//var_dump($programas_user);*/
	
		//FECHA LOGIN FORM
		try
		{
		
			$grupo          = TSession::getValue('TS_permissaogrupo');
			$programas_user = TSession::getValue('TS_permissaouser');
			
			
			//PARTE QUE VAI FICA NA PAGIMA
			$nome_classe =  get_class($this);
			//$nome_classe =  'Cat_RiscoForm';//4
			//var_dump($nome_classe);
			/*  1 SystemGroupForm
				2 SystemGroupList
				3 SystemProgramForm
				4 SystemProgramList
				43 Cat_RiscoForm
			*/
			$funcao     = new funcao;
			$id_classe  = $funcao->buscaIdProgram($nome_classe);
			$classe_id  = (int) implode($id_classe);
			
			
			/*Percorre as permissões especiais dessa pagina na tabela 'system_user_group_program' e 'system_group', grava as permissões na variável se tiver pega as permissões especiais do usuário 'system_user_group_program' assume esse valor se nao assume o padrão do grupo 'system_group'*/
			
			////Percorre as permissões do Grupo e grava na variavel
			$permissao_geral['acesso']   = 1;
			$permissao_geral['insercao'] = $grupo->insercao;
			$permissao_geral['delecao']  = $grupo->delecao;
			
			//Percorre as permissões do Usuário ; se tiver permissões especiais pega se nao usa as permissões padrão do grupo
			$permissao_pagina = array();
			foreach($programas_user as $programa_user)
			{
				//var_dump($programa_user['system_program_id']);
				
				if( ((int) $programa_user['system_program_id'] == $classe_id) )
				{	
					$permissao_geral['acesso']   = $programa_user['acesso'];
					$permissao_geral['insercao'] = $programa_user['insercao'];
					$permissao_geral['delecao']  = $programa_user['delecao'];
					
				}	
			}
			//var_dump($permissao_geral['acesso']);
			
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
			exit ;
		}
		
		//var_dump($permissao_geral['insercao']);
		//var_dump($permissao_geral['delecao']);
		
		
		//var_dump($grupo->delecao);
		
		
		//var_dump($permissao_pagina['acesso']);
		//var_dump($permissao_pagina['acesso']);
		
		//IF TERNÁRIO DO ACESSO SE EXISTIR PEGA ELE SE NAO PEGA AS PERMISSÕES PADRÃO DO GRUPO 
		//if isset($permissao_pagina['acesso']) : ? ; 
		
		
		/*for($i = 1; $i <= $numero_row; $i++)
		{
			if($programas_user[$i]['system_program_id'] == 43  )//ponteiro $i e classe '$classe_id'
			{
				//var_dump($programas_user[$i]['acesso']);
			}	
		}*/
		
		//var_dump($id_classe[43]['id'] );
		//var_dump($id_classe[$id_classe]['id']);
		
		//var_dump($id_classe2 );
		//var_dump($classe_id);
		
		//FECHA PARTE QUE VAI FICA NO PAGIMA		
		
		//var_dump($id_user);
		
		
		//FECHA AS PEMISSÕES
		
		
		//var_dump(TSession::getValue('TS_programa'));
		//var_dump(TSession::getValue('programs'));
		
		//var_dump(TSession::getValue('usergroupids'));
		
		
		
		
		
		TTransaction::rollback();
		
		//Começa pagina
        $this->form = new BootstrapFormBuilder('formCatRisco');
		$this->form->setFormTitle('Categoria de risco');
        $this->form->setFieldSizes('100%');
        
        $codigo  = new TEntry('CODIGO');
		$tipo    = new TEntry('TIPO');
		$desc    = new TEntry('DESC');
		$ini     = new TEntry('INI');
		$fim     = new TEntry('FIM');
		$perc    = new TEntry('PERC');
		$conta   = new TEntry('CONTA');
		$usuario = new TEntry('USUARIO');
		
		//validação
		$desc->addValidation(' "Descrição" ' , new TRequiredValidator);
		
		$row =  $this->form->addFields([ new TLabel('Código'), $codigo ],
                                       [ new TLabel('Tipo'), $tipo ],
                                       [ new TLabel('Descrição'), $desc ]);
		$row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-8' ];						
								
		$row = $this->form->addFields([ new TLabel('Dias ini'), $ini ],
								      [ new TLabel('Dias fim'), $fim ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];						
								
		$row = $this->form->addFields([ new TLabel('Percentual'), $perc ],
								      [ new TLabel('Conta'), $conta ],
									  [ new TLabel('Usuario'), $usuario ]);
		$row->layout = ['col-sm-5', 'col-sm-5', 'col-sm-2'];						
								
		// define as ações do form
		
		if( ($permissao_geral['insercao']) == 1 )
		{	
			$btn = $this->form->addAction('Salvar' ,new TAction(array($this, 'onSave')), 'far:save' );
			$btn->class = 'btn btn-sm  btn-primary';//fa:floppy-o ** far fa-save
		}	
			
		
		$this->form->addAction('Cancelar' ,new TAction(array('Cat_RiscoListe', 'onReload')), 'far: fa-window-close red'); 
		
		$this->form->addAction('Limpar' , new TAction(array($this, 'onEdit')), 'fa:eraser red');//fa:eraser red
		
		
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'Cat_RiscoListe'));
        $vbox->add($this->form);

        parent::add($vbox);
		
	}//__construct'
	
	/*
	Salva uma 'cat_risco'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$catRisco = $this->form->getData('cat_risco');
			
			$catRisco->USUARIO = TSession::getValue('userid');//username 
			
			$catRisco->store();
			
			new TMessage('info', 'Registro salvo');
			
			$this->form->setData($catRisco);
			
			//desabilita a ediçaõ do codigo
			TEntry::disableField('formCatRisco', 'CODIGO');
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	/*
	 Instância uma 'cat_risco' usando o @param['key'] como id do Objeto  
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
				$catRisco = new cat_risco($key);
				
				$data->USUARIO =  TSession::getValue('username'); 
				
				$this->form->setData($catRisco);
				
				//desabilita a ediçaõ do codigo
				TEntry::disableField('formCatRisco', 'CODIGO');
			}
			else
			{
				$this->form->clear(TRUE);
				TEntry::disableField('formCatRisco', 'CODIGO');
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