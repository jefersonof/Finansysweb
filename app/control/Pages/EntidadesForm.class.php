<?php
class EntidadesForm Extends TPage
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
		//CRIA O FORM
		$this->form  = new BootstrapFormBuilder('formEstipulante');
		$this->form->setFieldSizes('100%');
		
		//CRIA OS ATRIBUTOS
		$lb_dados_cad = new TLabel('Dados Cadastrais');
		$razao_social = new TEntry('RAZAO_SOCIAL');
		$codigo       = new TEntry('COD_INT');
		$cnpj         = new TEntry('CNPJ');
		$endereco     = new TEntry('ENDERECO');
		$bairro       = new TEntry('BAIRRO');
		$cidade       = new TEntry('CIDADE');
		$cep          = new TEntry('CEP');
		$estado       = new TEntry('ESTADO');
		$telefone     = new TEntry('TELEFONE');
		$fax          = new TEntry('FAX');
		$cod_federal  = new TEntry('COD_FEDERAL');
		$perc_desc    = new TEntry('PERC_DESC');
		$tipo         = new TCombo('TIPO');
		$obs          = new TText('OBS');
		//$obs          = new THtmlEditor('OBS');
		
		
		
		//FORMATAÇÕES
		$razao_social->addValidation('"RAZÃO SOCIAL "', new TRequiredValidator);
		//$cnpj->addValidation('" CNPJ "', new TRequiredValidator);
		
		$tipo->addItems(array('E' => 'E','C' => 'C'));
		$tipo->setValue('E');
		
		
		//CRIA OS BOTÕES
		$btn_cancelar = TButton::create('btn_cancelar' ,array('EntidadesListe', 'onReload'), ('Cancelar'), 'far: fa-window-close red' );//onClear
		
		$btn_salvar = TButton::create('btn_salvar' ,array($this, 'onSave'), ('Salvar'), 'far:save' );
		$btn_salvar->class = 'btn btn-sm  btn-primary';
		
		//** PAGE LISTAR  **//
		$label1 = new TLabel('Dados do cadastrais');//, '#7D78B6', 8, 'bi'
        $label1->style='text-align:left; width:100%; color:#FFF';
        
		//$this->form->appendPage('Page 1');
        $ln = $this->form->addContent($ln =  [$label1 ] );
		$ln->style='text-align:left; width:100%;background:#6287B9; color:#FFF';
		
		$row = $this->form->addFields([new TLabel('Código'), $codigo ],
								      [new TLabel('Nome / Razão Social'), $razao_social ],
									  [ new TLabel('CNPJ'), $cnpj ],
									  [ new TLabel('Telefone'), $telefone ]);
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-2', 'col-sm-2' ];					  

		$row = $this->form->addFields([new TLabel('Fax'), $fax ],
									  [new TLabel('Endereço'), $endereco ],
									  [new TLabel('Bairro'), $bairro ]);
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-4'];					  

		$row = $this->form->addFields([new TLabel('Cidade'), $cidade ],		
								      [ new TLabel('Cep'), $cep ],
								      [ new TLabel('Estado'), $estado ]);
		$row->layout = ['col-sm-8', 'col-sm-3', 'col-sm-1' ];

		$row = $this->form->addFields([new TLabel('Código Capitalização'), $cod_federal ],		
								      [new TLabel('PU Capitalização'), $perc_desc ],
									  [new TLabel('Tipo'), $tipo ]);
		$row->layout = ['col-sm-5', 'col-sm-5', 'col-sm-2'];

		$row = $this->form->addFields([ new TLabel('Observações'), $obs ]);
		$row->layout = ['col-sm-12'];					  
							
		
		//EMPACOTAMENTO
		$painel = new TPanelGroup('Estipulantes (T204)');
		$painel->add($this->form);
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter( THBox::pack($btn_salvar, $btn_cancelar ) );
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_cancelar));
		}
		
		
		$this->formFields = array($razao_social, $codigo, $cnpj, $endereco, $bairro, $cidade, $cep, $estado, $telefone, $fax, $cod_federal, $perc_desc, $obs, $tipo, $btn_cancelar, $btn_salvar);
		
		$this->form->setFields($this->formFields);
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
		$vbox->add(new TXMLBreadCrumb('menu.xml','EntidadesListe'));
		$vbox->add($painel);
		
		parent::add($vbox);
		
	}//__construct
	
	/*
	Instância uma 'entidades' usando o @param['key'] como id do Objeto;
	se não limpa o form e seta  $data->TIPO = 'E';
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
				
				$entidades = new entidades($key);
				
				$this->form->setData($entidades);
				
				//Desabilita o campo código
				TEntry::disableField('formEstipulante', 'COD_INT');
				
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formEstipulante', 'btn_salvar');
				}
				
			}
			else
			{
				$this->form->Clear();
				$data = $this->form->getData();
				$data->TIPO = 'E';
				$this->form->setData($data);
			}
			
			TTransaction::close();
			
			// $this->form->setData($data);
		}//try
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
		
	}//onEdit
	
	/*
	Salva uma 'entidades'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$entidades = $this->form->getData('entidades');
			
			$entidades->store();
			
			$this->form->setData($entidades);
			
			new TMessage('info', 'Registro Salvo');
			
			//Desabilita o campo código
			TEntry::disableField('formEstipulante', 'COD_INT');
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formEstipulante', 'btn_salvar');
			}
			
			TTransaction::close();
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	
	// public function show()
    // {
        // // check if the datagrid is already loaded
        // if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        // {
            // $this->onReload( func_get_arg(0) );
        // }
        // parent::show();
    // }
	
	
}//TPage 

?>