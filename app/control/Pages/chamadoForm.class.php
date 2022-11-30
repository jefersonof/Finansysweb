<?php
class chamadoForm Extends TPage
{
	private $form;
	
	// trait with onSave, onClear, onEdit, ...
    use Adianti\Base\AdiantiStandardFormTrait;
	
	// trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;
	
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
			
		$id_chamado   = new TEntry('id_chamado');
		$nome         = new TEntry('nome');
		$setor        = new TEntry('setor');
		$observacao   = new TEntry('observacao');
		$status       = new TCombo('status');
		$data_chamado = new TDate('data_chamado');
		$data_solucao = new TDate('data_solucao');
		$foto         = new TMultiFile('foto');
		$problema     = new TEntry('problema');
		$solucao      = new TText('solucao');
		$usuario      = new TEntry('usuario');
		$hora         = new THidden('hora');
		
		
		//add items
		$status->additems(array('Finalizado' => 'Finalizado', 'Pendente' => 'Pendente'));
		
		//VALIDAÇÃO
		$nome->addValidation(' "nome " ' , new TRequiredValidator);
		$setor->addValidation(' "setor " ' , new TRequiredValidator);
		$problema->addValidation(' "problema " ' , new TRequiredValidator);
		
		//outras formatações
		$foto->setAllowedExtensions( ['png', 'jpg', 'csv'] );
		$id_chamado->setEditable(FALSE);
		$usuario->setEditable(FALSE);
		$data_chamado->setMask('dd/mm/yyyy');
		$data_chamado->setDatabaseMask('yyyy-mm-dd');
		$data_solucao->setMask('dd/mm/yyyy');
		$data_solucao->setDatabaseMask('yyyy-mm-dd');
		
		//ativa ações de barra de progresso, visualização e remoção de arquivos
        $foto->enableFileHandling();
		
		//cria o form
		$this->form = new BootstrapFormBuilder('formChamado');
		$this->form->setFieldSizes('100%');
		$this->form->setFormTitle('Formulário de chamado');	
		
		$row = $this->form->addFields(['N° chamado', $id_chamado],
							          ['Nome', $nome],
							          ['Setor', $setor],
									  ['Usuário', $usuario]
									  );
		$row->layout = ['col-sm-2','col-sm-6','col-sm-2', 'col-sm-2'];
		
		$row = $this->form->addFields(['Problema', $problema]);
		$row->layout = ['col-sm-12'];

		$row = $this->form->addFields(['Status', $status],
 		                              ['Data problema', $data_chamado],
									  ['Data solução', $data_solucao]
							         );
		$row->layout = ['col-sm-4','col-sm-4','col-sm-4'];	
		
		$row = $this->form->addFields(['Solução', $solucao]);
		$row->layout = ['col-sm-12'];
		
		//coluna oculta
		$row = $this->form->addFields([$hora]);
		$row->layout = ['col-sm-12'];
		
		//**separador
		$this->form->addContent([new TFormSeparator('Imagens')]);
		
		//scroll
		$scroll = new TScroll;
		$scroll->setSize('100%', 100);
		$scroll->add($foto);
		
		$row = $this->form->addFields([$foto]);
		$row->layout = ['col-sm-12'];	
		
		//acões do form
		$btn = $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'far:save');
		$btn->class = 'btn btn-sm btn-primary';
		
		$this->form->addAction('Listar', new TAction(array('chamadoListe', 'onReload')), 'fa:far fa-list blue');
		
		$this->form->addAction('Novo', new TAction(array($this, 'onEdit')), 'fa: fa-plus blue');
		
		//PAINEL
		// $painel = new TPanelGroup;
		// $painel->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
		// $painel->add($this->form);
		
		$vbox = new TVBox;
		$vbox->style = 'width:100%';
		$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
		$vbox->add($this->form);
		
		parent::add($vbox);
		//parent::add($painel);
		
	}//__construct
	
	public function onSave($param)
	{
		try
		{
			TTransaction::open('chamado');
			
			$this->form->Validate(); 
			$data   = $this->form->getData();
			
			$chamado = new chamado;
			
			$chamado->id_chamado   = $data->id_chamado;
			$chamado->problema     = $data->problema;
			$chamado->nome         = $data->nome;
			$chamado->setor        = $data->setor;
			$chamado->data_chamado = $data->data_chamado;
			$chamado->solucao      = $data->solucao;
			$chamado->status       = $data->status;
			//$chamado->data_solucao = $data->data_solucao;
			
			if(empty($data->usuario))
			{
				$chamado->usuario       = TSession::getValue('username');;
			}
			
			if(empty($data->hora)) 
			{
				$chamado->hora  = date('H:i:s');
			}
			else	
			{
				$chamado->hora  = $data->hora;
			}

			if(empty($data->data_chamado)) 
			{
				$chamado->data_chamado  = date('Y/m/d');
			}
			else	
			{
				$chamado->data_chamado  = $data->data_chamado;
			}

			if(empty($data->status)) 
			{
				$chamado->status  = 'Pendente';
			}
			else	
			{
				$chamado->status  = $data->status;
			}

			if(empty($data->data_solucao) and ($data->status === 'Finalizado' ) )
			{
				$chamado->data_solucao = date('Y/m/d');
			}
			
			if($data->status === 'Pendente')
			{
				$chamado->data_solucao = '';
			}	

				
			
            //$chamado->fromArray((array) $data);
			$chamado->store(); 
			
			//Salva os agregados 'chamado_foto'
			$this->saveFiles($chamado, $data, 'foto', 'files/img_chamados', 'chamado_foto' ,'foto', 'fk_chamado');
			
			//$this->saveFiles($proposta, $data, 'FOTO', 'app/img', 'proposta_foto', 'FOTO', 'PROPOSTA_ID');
			
			//$this->saveFiles($proposta, $data, 'FOTO', 'app/img', 'proposta_foto', 'FOTO', 'PROPOSTA_ID');
			
			//$this->saveFiles($chamado, $data, 'foto', '//NAS/disco01/SUPORTE/JEFERSON/db/img_chamados/', 'chamado_foto');//\\nas\disco01\db\img_chamados
			
			
			new TMessage('info', 'Salvo com sucesso');
			
			TTransaction::close();
			
			$data->id_chamado   = $chamado->id_chamado;
			$data->hora         = $chamado->hora;
			$data->data_chamado = TDate::date2br($chamado->data_chamado);
			$data->status       = $chamado->status;
			$data->usuario      = $chamado->usuario;
			$data->data_solucao = TDate::date2br($chamado->data_solucao);
			
			$this->form->setData($data);
		}
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
		
	}//onSave
	

	public function onEdit($param)
	{
		try
		{
			TTransaction::open('chamado');
			
			if(isset($param['key']))
			{	
				//pega os dados do form
				$data = $this->form->getData();
				
				//pega o id do chamado e instância o obj
				$id_chamado = $param['key'];
				$chamado = new chamado($id_chamado);
				
				$foto = array();	
				foreach($chamado->getchamado_foto() as $chamados)
				{
					$foto[$chamados->id_chamado_foto] = $chamados->foto;
					
				}
					
				$data->foto         = $foto;
				$data->id_chamado   = $chamado->id_chamado;
				$data->nome         = $chamado->nome;
				$data->setor        = $chamado->setor;
				$data->problema     = $chamado->problema;
				$data->data_chamado = TDate::date2br($chamado->data_chamado);
				$data->observacao   = $chamado->observacao;
				$data->hora         = $chamado->hora;
				$data->status       = $chamado->status;
				$data->solucao      = $chamado->solucao;
				$data->usuario      = $chamado->usuario;
				$data->data_solucao = TDate::date2br($chamado->data_solucao);
				
				TTransaction::close();
				
				//manda os dados para o form
				$this->form->setData($data);
			
			}//$param['key']
			else
			{
				$this->form->clear();
			}	
			
		}//try
		catch(Exception $e )
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}	
		
	}//onEdit

	public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }//show	
	
	public function onReload()
	{
		
		
	}//onReload
	
}//TPage


?>