<?php
class Tipo_CtoForm Extends TPage
{	
	private $datagrid;
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
		//cria o form
		$this->form = new BootstrapFormBuilder('formTipoCto'); 
		$this->form->setFieldSizes('100%');
		
		//cria os atributos
		$id        = new TEntry('ID');
		$codigo    = new TEntry('CODIGO');
		$descricao = new TEntry('DESCRICAO');
		$tipo      = new TEntry('TIPO');
		
		//cria os botões
		$btn = $btn_salvar = TButton::create('btn_salvar' ,array($this ,'onSave'), ('Salvar'), 'far:save' );
		$btn->class = 'btn btn-sm  btn-primary';
		
		$btn_cancelar = TButton::create('btn_cancelar' ,array('Tipo_CtoListe', 'onReload'), 'Cancelar', 'far: fa-window-close red' );
		
		$btn_novo = TButton::create('btn_novo' ,array($this, 'onNovo'), 'Novo', 'fa: fa-plus blue' );
		
		$descricao->addValidation(' "DESCRICAO" ', new TRequiredValidator );
		
		//Empacotamento
		$painel = new TPanelGroup('Tipo de Contrato(T017)');
		$painel->add($this->form);
		
		//monta pagina
		$row = $this->form->addFields([new TLabel('Id'), $id ],
									  [new TLabel('Código'), $codigo ],
								      [new TLabel('Descrição'), $descricao ],
									  [new TLabel('Tipo'), $tipo ]);
		$row->layout = ['col-sm-1', 'col-sm-3', 'col-sm-5', 'col-sm-3'];							  
		
		//add os compos do form
		$this->formFields = array($btn_salvar, $btn_cancelar, $btn_novo, $codigo, $id, $descricao, $tipo);
		$this->form->setFields($this->formFields);
		
		//add o btn no footer da pagina
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_salvar, $btn_novo, $btn_cancelar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_cancelar));
		}
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
		$vbox->add(new TXMLBreadCrumb('menu.xml', 'Tipo_CtoListe') );
		$vbox->add($painel);
		
		parent::add($vbox);
		
		
	}//__construct
	
	/*
	  Salva um 'tipo_cto'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			//valida o form
			$this->form->validate();
			
			//instacia o obj
			$tipo_cto = $this->form->getData('tipo_cto');
			
			//salva o obj
			$tipo_cto->store();
			
			//manda os dados para o form
			$this->form->setData($tipo_cto);
			
			new TMessage('info', 'Registro Salvo');
			
			TEntry::DisableField('formTipoCto', 'ID');
			
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formTipoCto', 'btn_salvar');
			}
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
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
				$key = $param['key'];
				
				$tipo_cto = new tipo_cto($key);
				
				$this->form->setData($tipo_cto);
				
                TEntry::disableField('formTipoCto', 'ID');

				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formTipoCto', 'btn_salvar');
				}	
			
			}//if		
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
	}//onEdit
	
	/*
	limpa o form e Habilita o campo 'ID'
	*/
	public function onNovo()
	{
		$this->form->clear();
		TEntry::enableField('formTipoCto', 'ID');
		
	}//onNovo
	
	
}//TWindow


?>