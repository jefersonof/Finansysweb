<?php
class Ent_GarForm Extends  TPage
{
	private $form;
	private $datagrid;
	private $pageNavigation;
	
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
		
		$this->form = new BootstrapFormBuilder('formEnt_Gar');
		//$this->form->setFormTitle('Entidades Garantidoras (T001)');
		$this->form->setFieldSizes('100%');
		
		//cria os btn
		$btn_salvar = TButton::create('btn_salvar', [$this, 'onSave'], 'Salvar', 'far:save');
		$btn_salvar->class = 'btn btn-sm btn-primary';
		$btn_fechar = TButton::create('btn_fechar', ['Ent_GarListe', 'onReload'], 'Fechar', 'far: fa-window-close red');
		
		/*
		$btn = $this->form->addAction('Salvar' , new TAction(array($this, 'onSave')), 'far:save ');
		$btn->class = 'btn btn-sm  btn-primary';//fa:floppy-o
		
		$this->form->addAction('Fechar' , new TAction(array('Ent_GarListe', 'onReload')), 'far: fa-window-close red');
		*/
		
		//cria os atributos
		$codigo      = new TEntry('CODIGO');
		$nome        = new TEntry('NOME');
		$responsavel = new TEntry('RESPONSAVEL');
		$cnpj        = new TEntry('CNPJ');
		$ie          = new TEntry('IE');
		$telefone    = new TEntry('TELEFONE');
		$celular     = new TEntry('CELULAR');
		$fax         = new TEntry('FAX');
		$contato     = new TEntry('CONTATO');
		$endereco    = new TEntry('ENDERECO');
		$cep         = new TEntry('CEP');
		$bairro      = new TEntry('BAIRRO');
		$cidade      = new TEntry('CIDADE');
		$estado      = new TEntry('ESTADO');
		$grupo       = new TEntry('GRUPO');
		$obs         = new TEntry('OBS');
		
		$row = $this->form->addFields([new TLabel('Código'), $codigo],
		                              [new TLabel('Nome/ Razão Social'), $nome],
									  [new TLabel('Responsável'), $responsavel],
							          [new TLabel('CNPJ'), $cnpj]
							          );
		$row->layout = ['col-sm-1','col-sm-5','col-sm-3','col-sm-3'];
		
		$row = $this->form->addFields([new TLabel('Insc. Estadual'), $ie],
		                              [new TLabel('Telefone'), $telefone],
									  [new TLabel('Celular'), $celular],
							          [new TLabel('Fax'), $fax]
							          );
		$row->layout = ['col-sm-3','col-sm-3','col-sm-3','col-sm-3'];
		
		$row = $this->form->addFields([new TLabel('Endereço'), $endereco],
		                              [new TLabel('Cep'), $cep]
							          );
		$row->layout = ['col-sm-9','col-sm-3'];
		
		$row = $this->form->addFields([new TLabel('Bairro'), $bairro],
		                              [new TLabel('Cidade'), $cidade],
									  [new TLabel('Estado'), $estado],
							          [new TLabel('Grupo'), $grupo]
							          );
		$row->layout = ['col-sm-3','col-sm-3','col-sm-3','col-sm-3'];
		
		$this->formFields = [$btn_salvar, $btn_fechar, $codigo, $nome, $responsavel, $cnpj, $ie, $telefone, $celular, $fax, $contato, $endereco, $cep, $bairro, $cidade, $estado, $grupo, $obs];
		$this->form->setFields($this->formFields);
		
		$painel = new TPanelGroup('Entidades Garantidoras (T001)');
		$painel->add($this->form);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_salvar, $btn_fechar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_fechar));//$this->saveButton, btn_fechar
		}
		
		//ativar a rolagem horizontal dentro do corpo do painel
		$painel->getBody()->style = "overflow-x:auto";
		
		
		
		$vbox = new TVBox;
		$vbox->style = '90%';
		$vbox->add(new TXMLBreadCrumb('menu.xml', 'Ent_GarListe' ));
		$vbox->add($painel);
		
		parent::add($vbox);
		
	
	}//__construct
	
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			if(isset($param['key']))
			{
				$key = $param['key'];
				
				
				$ent_gar = new ent_gar($key);
				
				$ent_gar->ESTADO = $ent_gar->UF;
				$this->form->setData($ent_gar);
			}
			
			TTransaction::close();
			
		}//try
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onReload
	
	public function onSave()
	{
		try
		{
			TTransaction::open('db2');
			
			$ent_gar = $this->form->getData('ent_gar');
			
			$ent_gar->UF = $ent_gar->ESTADO;
			$ent_gar->store();
			
			$this->form->setData($ent_gar);
			
			TTransaction::close();
			
			new TMessage('info', 'Salvo com sucesso');
			
		}
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage());
		}
		
	}//onSave
	
	/*
	captura as parametros da URL e atualiza o onReload
	*/
	/*
	public function show()
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