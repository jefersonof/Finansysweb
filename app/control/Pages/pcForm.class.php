<?php
class pcForm extends TPage
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
		
		//Atributos
		$id          =  new TEntry('id');
		$nome        = new TEntry('nome');
		$setor       = new TEntry('setor');
		$computador  = new TEntry('computador');
		$ip          = new TEntry('ip');
		$obs         = new TEntry('obs');
		$data_cad    = new TDate('data_cad');
		$mac_address = new TEntry('mac_address');
		$office      = new TEntry('office');
		
		//Formatações
		$id->setEditable(FALSE);
		$nome->addValidation(' "nome " ' , new TRequiredValidator);
		$setor->addValidation(' "setor " ' , new TRequiredValidator);
		$computador->addValidation(' "PC " ' , new TRequiredValidator);
		$computador->placeholder = 'Nome do computador'  ;
		$mac_address->placeholder = 'Mac Address'  ;
		$data_cad->setMask('dd/mm/yyyy');
		$data_cad->setDataBaseMask('yyyy-mm-dd');
		$ip->setMask('xxx.xxx.x.xxx');
		$mac_address->setMask('xx-xx-xx-xx-xx');
		
		//form
		$this->form = new BootstrapFormBuilder('formPc');
		$this->form->setFormTitle('Formulário de computadores');
		$this->form->setFieldSizes('100%'); 
		
		$row = $this->form->addFields(['Id', $id],
							          ['Nome', $nome],
							          ['Setor', $setor],
							          ['Cadastro', $data_cad]
							         );
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-2', 'col-sm-2'];
		
		$row = $this->form->addFields(['Computador', $computador],
							          ['Ip', $ip],
							          ['Mac', $mac_address],
							          ['Office', $office]
							         );
		$row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];
		
		$row = $this->form->addFields(['Obs', $obs]
							         );
		$row->layout = ['col-sm-12'];
		
		//acões do form
		$btn = $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:floppy-o');
		$btn->class = 'btn btn-sm btn-primary';
		
		$this->form->addAction('Listar', new TAction(array('pcListe', 'onReload')), 'fa:far fa-list blue');
		
		$this->form->addAction('incluir', new TAction(array($this, 'onEdit')), 'bs:plus-sign green');
		
		//container
		$vbox = new TVBox;
		$vbox->style = 'width:100%';
		$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__ ) );
		$vbox->add($this->form);
		
		parent::add($vbox);
		
	}//__construct
	
	public function onSave($param)
	{
		try
		{
			$data = $this->form->getData();
			$this->form->Validate(); 
			TTransaction::open('chamado');
			
			$pc = new pc;
			
			$pc->id          = $data->id;
			$pc->nome        = $data->nome;
			$pc->computador  = $data->computador;
			$pc->ip          = $data->ip;
			$pc->setor       = $data->setor;
			$pc->obs         = $data->obs;
			$pc->data_cad    = $data->data_cad;
			$pc->mac_address = $data->mac_address;
			$pc->office      = $data->office;
			
			if(empty($data->data_cad))
			{
				$pc->data_cad  = date('Y-m-d');
			}
			else
			{
				$pc->data_cad = $data->data_cad;
			}		
			
			$pc->store();
			
			TTransaction::close();
			
			new TMessage('info', 'Registro Salvo');
			
			$data->data_cad = TDate::date2br($pc->data_cad);
			$this->form->setData($pc);
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage());
			TTransaction::rollback();
		}
		
	}//onSave
	
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('chamado');
			
			if(isset($param['key']))
			{	
				$key = $param['key'];	
				$pc = new pc($key);
				
				$pc->data_cad = TDate::date2br($pc->data_cad);
				$this->form->setData($pc);
			}
			else
			{
				$this->form->clear();
			}	
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
}//TPage

?> 