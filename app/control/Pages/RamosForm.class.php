<?php

class RamosForm extends TPage 
{
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
		
        $this->form = new BootstrapFormBuilder('formRamos');
        
        //cria os atributos
		$codigo    = new TEntry('CODIGO'); 
		$ramo      = new TEntry('RAMO');
		$gruporamo = new TEntry('GRUPORAMO');
		$lb_ramos  = new TLabel('Alteração de Ramos');
	    $grupo     = new TDBCombo('GRUPO', 'db2', 'seg_grupos', 'CODIGO','({CODIGO} )  {GRUPO}');
		//EX => TDBCombo('nome_objeto','banco_de_dados','classe_model','campo_id','campo_descricao');
		
		//add validação
		$ramo->addValidation(' "RAMO" ', new TRequiredValidator);
		
		$this->form->addFields( [ new TLabel('Código') ],
                                [ $codigo],
                                [ new TLabel('Ramo') ],
                                [ $ramo] );
		
		$this->form->addFields( [ new TLabel('Grupo e Ramos') ],
                                [ $gruporamo],
                                [ new TLabel('Grupo') ],
                                [ $grupo] );	
								
		// define the form action
		$btn_save = TButton::create('btn_save' ,array($this, 'onSave'), 'Salvar', 'far:save' );
		$btn_save->class = 'btn btn-sm  btn-primary';
		
		$btn_cancelar = TButton::create('btn_cancelar' ,array('RamosGruposListe', 'onCarregar'), 'Cancelar', 'far: fa-window-close red');
		
		//$btn_save->class = 'btn btn-success';
		
        $panel = new TPanelGroup('Cadastro de Ramos de Seguro');
        //$panel->style = 'width:100%';
        $panel->add($this->form);
        
		if($permissao_geral['insercao'] == 1)
		{	
			$panel->addFooter(THBox::pack($btn_save,$btn_cancelar));
		}
		else
		{
			$panel->addFooter(THBox::pack($btn_cancelar));
		}
		
		
		$this->form->setFields(array($btn_save, $btn_cancelar, $codigo, $grupo, $ramo, $gruporamo) );
        //$this->alertBox = new TElement('div');
		
		//menu breadCrumb manual
		$menuBread = new TBreadCrumb();
        $menuBread->setHomeController('PageInicial');
        $menuBread->addHome();
        $menuBread->addItem('Cadastros');
        $menuBread->addItem('Seguradora');
        $menuBread->addItem('Add Ramo');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($panel);

        parent::add($vbox);						
		
	}
	
    /*
	Instância um 'seg_ramos', usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			if(isset($param['key']) )
			{
				TTransaction::open('db2');
				
				$key = $param['key']; 
				$seg_ramos = new seg_ramos($key);
				
				$this->form->setData($seg_ramos);
				
				//Desabilita o campo código
				//TEntry::disableField('formRamos', 'CODIGO');
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formRamos', 'btn_save');
				}
				
				TTransaction::close();
				
			}	
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}	
			
	}//onEdit

	/*
	  salva um 'seg_ramos'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//Validação do form
			$this->form->validate();
			
			$seg_ramos = $this->form->getData('seg_ramos');
			
			$seg_ramos->store();
			
			$this->form->setData($seg_ramos);
			
			new TMessage('info', 'Salvo com sucesso');

			//Desabilita o campo código
			TEntry::disableField('formRamos', 'RAMO');	
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	
}//TPage

?>