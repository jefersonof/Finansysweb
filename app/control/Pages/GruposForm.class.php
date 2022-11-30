<?php

class GruposForm extends TPage 
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
		
		$this->form = new BootstrapFormBuilder('formGrupos');
        
        // create the form fields
        $codigo    = new TEntry('CODIGO'); 
		$grupo     = new TEntry('GRUPO');
		$lbl_color = new TLabel('Grupo');
		
		//formatações
		$grupo->addValidation(' "GRUPO" ', new TRequiredValidator);
        $lbl_color->setFontColor('green');
        $lbl_color->setFontStyle('b');
        
		$this->form->addFields( [ new TLabel('Código') ],
                                [ $codigo],
                                [ new TLabel('Grupo') ],
                                [ $grupo ] );
		
        
        // define the form action
		$btn_save = TButton::create('btn_save' ,array($this, 'onSave'), 'Salvar', 'far:save' );
		$btn_save->class = 'btn btn-sm  btn-primary';
		
		$btn_cancelar = TButton::create('btn_cancelar' ,array('RamosGruposListe', 'onReload'), 'Cancelar', 'far: fa-window-close red');
		
		//$btn_save->class = 'btn btn-success';
		
		//$this->form->addAction('Send', new TAction(array($this, 'onSend')), 'fa:check-circle-o green');
        //$btn_save = $this->form->addQuickAction('Send', new TAction(array($this, 'onSend')), 'fa:check-circle-o green');
        
        $panel = new TPanelGroup('Cadastro de Grupo de Seguro');
        //$panel->style = 'width:100%';
        $panel->add($this->form);
        
		if($permissao_geral['insercao'] == 1)
		{	
			$panel->addFooter(THBox::pack($btn_save, $btn_cancelar));
		}
		else
		{
			$panel->addFooter(THBox::pack($btn_cancelar));
		}
		
		$this->form->setFields(array($btn_save, $btn_cancelar, $codigo, $grupo) );
        $this->alertBox = new TElement('div');
		
		//menu breadCrumb manual
		$menuBread = new TXMLBreadCrumb('menu.xml', 'RamosGruposListe');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width:90%';
        $vbox->add($menuBread);
        $vbox->add($this->alertBox);
        $vbox->add($panel);

        parent::add($vbox);
		
	}
	
	/**
     * Post data
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
			//$this->alertBox->add( new TAlert('info', 'Salvo com sucesso'));
        }
        catch (Exception $e)
        {
            $this->alertBox->add( new TAlert('danger', $e->getMessage()) );
        }
    }
	
	 /*
	Instância um Grupo, usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			if(isset($param['key']) )
			{
				TTransaction::open('db2');
				
				$key = $param['key']; 
				$seg_grupos = new seg_grupos($key);
				
				$this->form->setData($seg_grupos);
				
				//Desabilita o campo código
				TEntry::disableField('formGrupos', 'CODIGO');
				
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formGrupos', 'btn_save');
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
	Salva um 'Grupo'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$seg_grupos = $this->form->getData('seg_grupos');
			
			$seg_grupos->store();
			
			new TMessage('info', 'Salvo com sucesso');
			
			//$this->alertBox->add( new TAlert('success', 'Salvo com sucesso'));

			$this->form->setData($seg_grupos);	
			
			//Desabilita o campo código
			TEntry::disableField('formGrupos', 'CODIGO');
			
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