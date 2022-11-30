<?php
class Reg_FinForm Extends TPage 
{	
	private $form;
	private $datagrid;
	private $notbook;
	
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
		$this->form = new BootstrapFormBuilder('formRegimeFinan');
		$this->form->setFieldSizes('100%');
		
		//cria os atributos
		$codigo     = new TEntry('CODIGO');
		$regime     = new TEntry('REGIME');
		$tabela     = new TEntry('TABELA');
		$prazo_pgto = new TEntry('PRAZO_PGTO');
		
		//validação
		//$tabela->addValidation(' "REGIME" ' ,new TRequiredValidator);
		$regime->addValidation(' "REGIME" ', new TRequiredValidator);
		
		//CRIA OS BOTÕES
		$btn_cancelar = TButton::create('btn_cancelar' ,array('Reg_FinListe', 'onReload'), 'Cancelar', 'fa:window-close red' );
		
		$btn_salvar = TButton::create('btn_salvar' ,array($this,'onSave'), 'Salvar', 'far:save' );//fa: fa-save green
		$btn_salvar->class = 'btn btn-sm  btn-primary';
		
		$btn_fechar = TButton::create('btn_fechar' ,array('PageInicial', 'onReload'), 'Fechar', 'fa: fa-power-off red' );
		
		//** PAGE ADD **//
		$row = $this->form->addFields([new TLabel('Cod'), $codigo ],
								      [new TLabel('Prazo Pgto'), $prazo_pgto ]);
		$row->layout = ['col-sm-4','col-sm-8'];							  
		
		$row = $this->form->addFields([new TLabel('Regime'), $regime ],
								      [new TLabel('Tabela'), $tabela ]);
		$row->layout = ['col-sm-6','col-sm-6'];						
								
		//DEFINE OS CAMPOS DO FORMULÁRIO
        $this->form->setFields(array($codigo, $regime, $tabela, $prazo_pgto, $btn_cancelar, $btn_salvar, $btn_fechar) );
		
		//$this->formFields = array($codigo, $regime, $tabela, $prazo_pgto, $btn_cancelar, $btn_salvar, $btn_fechar);
        //$this->form->setFields( $this->formFields );						
		
		
		//Empacotamento
		$painel = new TPanelGroup('Regime Financeiro (T012)');
		$painel->add($this->form);
		
		//Radapé
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_salvar,$btn_cancelar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_cancelar));
		}
		
		//Ativa scroll horizontal
		$painel->getBody()->style = 'overflow-x:auto';
		
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
		$vbox->add(new TXMLBreadCrumb('menu.xml', 'Reg_FinListe'));
		$vbox->add($painel);
		
		parent::add($vbox);
		
	}//__construct'
	
	/*
	  Salva um 'reg_fin'
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
				TButton::disableField('formCatRisco', 'btn_salvar');
			}
			
			$reg_fin = $this->form->getData('reg_fin');
			
			$reg_fin->store();
			
			TTransaction::close();
			
			
			new TMessage('info', 'Registro salvo');
			
			//manda os dados para o form
			$this->form->setData($reg_fin);
			
			//desabilita a ediçaõ do codigo
		    //TEntry::disableField('formRegimeFinan', 'CODIGO');
		}//try
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
			
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
			
			$data = $this->form->getData();
			if(isset($param['key']))
			{
				$key = $param['key'];
				$reg_fin = new reg_fin($key);
				
				$data->CODIGO     = $reg_fin->CODIGO;
				$data->REGIME     = $reg_fin->REGIME;
				$data->TABELA     = $reg_fin->TABELA;
				$data->PRAZO_PGTO = $reg_fin->PRAZO_PGTO;
				
				//desabilita a ediçaõ do codigo
				TEntry::disableField('formRegimeFinan', 'CODIGO');
				
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formRegimeFinan', 'btn_salvar');
				}
				
				$this->form->setData($data);
			
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
	  limpa o form
	*/
	public function onCancelar()
	{
		//limpa o form
		$this->form->clear();
		
		//vira a pg
		
		
	}//onCancelar
		

}//TPage


?>