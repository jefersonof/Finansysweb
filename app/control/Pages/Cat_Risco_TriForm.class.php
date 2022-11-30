<?php
class Cat_Risco_TriForm Extends TPage 
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
		
		// create the form using TQuickForm class
        $this->form = new BootstrapFormBuilder('formCatRiscoTri');
		$this->form->setFormTitle('Categoria de Risco Questionário Trimestral SUSEP');
		$this->form->setFieldSizes('100%'); 
        
        $codigo  = new TEntry('CODIGO');
		$tipo    = new TEntry('TIPO');
		$desc    = new TEntry('DESC');
		$ini     = new TEntry('INI');
		$fim     = new TEntry('FIM');
		$perc    = new TEntry('PERC');
		$conta   = new TEntry('CONTA');
		
		//cria o botão
		$btn_salvar = TButton::create('btn_salvar', array($this, 'onSave'), 'Salvar', 'fa: fa-save' );
		
		//validação
		$desc->addValidation(' "Descrição" ' , new TRequiredValidator);
		
		$row = $this->form->addFields([new TLabel('Código'), $codigo ],
                                      [new TLabel('Tipo'), $tipo ],
                                      [new TLabel('Percentual'), $perc ]);
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];						
								
		$row = $this->form->addFields([new TLabel('Dias ini'), $ini ],
								      [ new TLabel('Dias fim'), $fim ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];						
								
		$row = $this->form->addFields( [ new TLabel('Descrição'), $desc ],
								[ new TLabel('Conta'), $conta ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];						
	
		//cria as ações do form
		if($permissao_geral['insercao'] == 1 )
		{	
			$btn = $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'far:save' );
			$btn->class = 'btn btn-sm  btn-primary';//fa:floppy-o
		}	
		
		
		$this->form->addAction('Cancelar', new TAction(array('Cat_Risco_TriListe', 'onReload')), 'far: fa-window-close red' );
		
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width:90%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'Cat_Risco_TriListe'));
        $vbox->add($this->form);

        parent::add($vbox);
		
		
	}//__construct'
	
	
	/*
	  Salva uma 'cat_risco_tri'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$catRisco = $this->form->getData('cat_risco_tri');
			
			//teste se tem permissão de alteração
			//if(isset($catRisco->CODIGO) AND ()  ) 
			//{
				$catRisco->store();
				
				$this->form->setData($catRisco);
				
				//$action = new TAction(array('AddRamosGrupos', 'onCarregar'));	
				new TMessage('info', 'Salvo com sucesso');//, $action
				
				//Desabilita a btn salvar
				TEntry::disableField('formCatRiscoTri', 'CODIGO');
			//}	
			
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formCatRiscoTri', 'btn_salvar');
				}	
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	
	/*
      Instância uma 'cat_risco_tri' usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$data = $this->form->getData();
			
			//var_dump($permissao_geral['alteracao']);
			//var_dump(TSession::setValue('TS_alteracao', $permissao_geral['alteracao']));
			
			if(isset($param['key']))
			{	
				$key = $param['key'];
				$catRisco = new cat_risco_tri($key);
				
				//manda os dados para form
				$this->form->setData($catRisco);
				
				//Desabilita a ediçaõ do codigo
				TEntry::disableField('formCatRiscoTri', 'CODIGO');
				
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formCatRiscoTri', 'btn_salvar');
				}	
				
			}//if
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('info', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onEdit
	
	
	/*
	  Recarrega a página com os parâmetros atuais
	*/
	/*public function onReload($param)
	{
		try
		{
			$data = $this->form->getData();
			$this->form->setData($data);
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}	
	}//onReload*/
	
	/*
	mostra os dados
	*/
	
	/*public function show()
	{
		if(!$this->loaded)
		{
			$this->onReload( func_get_arg(0) );
		}	
        parent::show(); 
		
	}//show	
	*/
		

}//TPage


?>