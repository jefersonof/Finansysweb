<?php
class CodRetBancoForm extends TPage
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
		
		//cria o form
		$this->form = new BootstrapFormBuilder('formCodRetBanco');
		$this->form->setFormTitle('Código de retorno dos bancos (T013)');//BootstrapFormBuilder
		$this->form->setFieldSizes('100%');
		
		//cria os atributos
		$codigo      = new TEntry('CODIGO');
		$descricao   = new TEntry('DESCRICAO');
		
		//cria as validações
		$descricao->addValidation(' "Descricão" ', new TRequiredValidator );//
		
		//add os campos no form
		$row = $this->form->addFields([new TLabel('Código'), $codigo ],
							  [new TLabel('Descricão'), $descricao ] );
		$row->layout = ['col-sm-4', 'col-sm-8'];					  
		
		//ações do form
		if($permissao_geral['insercao'] == 1)
		{	
			$btn = $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'far:save');
			$btn->class = 'btn btn-sm  btn-primary';
			
			$this->form->addAction('Limpar', new TAction(array($this, 'onLimpar')), 'fa:eraser red');
		}
		
		$this->form->addAction('Retornar', new TAction(array('CodRetBancoListe', 'onReload')), 'fa: fa-arrow-left blue');
		
		//cria menu BreadCrumb
		$breadcrumb = new TXMLBreadCrumb('menu.xml', 'CodRetBancoListe');
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';		
		$vbox->add($breadcrumb);
		$vbox->add($this->form);
		
		parent::add($vbox);
		
	}//__construct
	
	/*
	Salva um 'cod_ret_banco'
	*/
	public function onSave()
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$codRetBanco = $this->form->getData('cod_ret_banco');
			
			$codRetBanco->store();
			
			$this->form->setData($codRetBanco);
			
			//Desativa campos
			TEntry::disableField('formCodRetBanco', 'CODIGO');
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formCodRetBanco', 'btn_salvar');
			}
			
			TTransaction::close();
			
		}
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	
	/*
	Limpa o form 
	*/
	public function onLimpar()
	{
		$this->form->clear();
		
		//Desativa campos
		//TEntry::disableField('formCodRetBanco', 'CODIGO');
		
	}//onLimpar
	
	/*
	Instância um 'cod_ret_banco' usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			if(isset($param['key']) )
			{
				//pega o id pelo URL
				$key = $param['key'];
				
				//Instância e manda os dados pelo método 'setData'
				$rp_cod_ret_banco = new cod_ret_banco($key);
				$this->form->setData($rp_cod_ret_banco);
				
				//Desativa campos
				TEntry::disableField('formCodRetBanco', 'CODIGO');
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formCodRetBanco', 'btn_salvar');
				}
				
			}
			else
			{
				$this->form->clear();
			}	
			
			TTransaction::close();
		}//try
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onEdit
	
}//TPage

?>