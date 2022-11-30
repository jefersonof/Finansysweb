<?php
class compartilhaDoc extends TPage
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
		
		//atributos
		$titulo    = new TEntry('titulo');
		$descricao = new TEntry('descricao');
		$categoria = new TDBCombo('category_id', 'communication', 'SystemDocumentCategory', 'id', 'name');
		$datahj    = new TEntry('datahj');
		$usuario   = new TDBMultiSearch('user_ids', 'permission', 'SystemUser', 'id', 'name');
		$grupo     = new TDBCheckGroup('group_ids', 'permission', 'SystemGroup', 'id', 'name');
		$doc       = new TMultiFile('doc');
		
		//configurações
		$grupo->setLayout('horizontal');
		$usuario->setMinLength(1);
		$doc->setAllowedExtensions( ['png', 'jpg', 'csv'] );
        $doc->enableFileHandling();
        $doc->enableImageGallery();
        $doc->enablePopover('Preview', '<img style="max-width:300px" src="download.php?file={file_name}">');
		
		//form
		$this->form = new BootstrapFormBuilder('Compartilhamento de arquivo');
		$this->form->setFieldSizes('100%');
		
		$row = $this->form->addFields(['Título', $titulo],
								      ['categoria', $categoria],
								      ['Data', $datahj]);
		$row->layout = ['col-sm-6', 'col-sm-3', 'col-sm-3'];
		
		$row = $this->form->addFields(['Descrição', $descricao]);
		$row->layout = ['col-sm-12'];
								
		$label1 = new TLabel('Permissão', '#5A73DB', 12, '');
        $label1->style='text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $this->form->addContent( [$label1] );

		$row = $this->form->addFields(['Usuário', $usuario]);
		$row->layout = ['col-sm-12'];
		
		$row = $this->form->addFields(['Grupo', $grupo]);
		$row->layout = ['col-sm-12'];
		
		$label2 = new TLabel('Arquivos', '#5A73DB', 12, '');
        $label2->style='text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $this->form->addContent( [$label2] );
		
		$row = $this->form->addFields(['Arquivos', $doc]);
		$row->layout = ['col-sm-12'];
		
		$this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'far:check-circle green');
		
		//$this->form->addAction('Send', new TAction(array($this, 'onSend')), 'far:check-circle green');
		

		$tvbox = new TVBox;
		$tvbox->style = 'width:90%';
		$tvbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__)); //TXMLBreadCrumb
		$tvbox->add($this->form);
		
		parent::add($tvbox);
		
		
    }//__construct 
	
	public function onSave()
	{
		try
		{
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
	}
	
}//TPage
