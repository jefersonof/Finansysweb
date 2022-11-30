<?php

class consultarContrato extends TPage
{
	private $form;
	private $dados_contrato;
	
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
		
		//cria as atributos
		$nome           = new TEntry('NOME');
		$cpf            = new TEntry('CPF');
		$matr_interna   = new TEntry('MATR_INTERNA');
		$matr_orgao     = new TEntry('MATR_ORGAO');
		$apolice        = new TEntry('APOLICE');
		$contrato       = new TEntry('CONTRATO');
		$cep            = new TEntry('CEP_RES');
		$valor_ini      = new TEntry('VL_PARCELA1');
		$valor_fim      = new TEntry('VL_PARCELA2');
		$cad_ini        = new TDate('CAD_INI');
		$cad_fim        = new TDate('CAD_FIM');
		$conta_corrente = new TDate('CONTA_CORRENTE');
		
		$id_agente      = new TDBSeekButton('CODIGO', 'db2', 'formConsultaContrato', 'fornecedor', 'NOME', 'CODIGO', 'NOME_AGENTE');
		$nome_agente    = new TEntry('NOME_AGENTE');
		
		$cod_int         = new TDBSeekButton('COD_INT', 'db2', 'formConsultaContrato', 'entidades', 'RAZAO_SOCIAL', 'COD_INT', 'RAZAO_SOCIAL');
		$rz_social     = new TEntry('RAZAO_SOCIAL');
		
		$cod_plano     = new TDBSeekButton('CODIGO2', 'db2', 'formConsultaContrato', 'plano', 'PLANO', 'CODIGO2', 'PLANO');
		$plano         = new TEntry('PLANO');
		
		//formatações
		$cad_ini->setValue( TSession::getValue('relacao_data_ini'));
		$cad_fim->setValue( TSession::getValue('relacao_data_fim'));
		$cpf->setValue( TSession::getValue('relacao_cpf'));//busca_cpf
		$nome->setValue( TSession::getValue('relacao_nome'));//busca_nome
		$matr_orgao->setValue( TSession::getValue('relacao_matr_orgao'));
		$id_agente->setValue( TSession::getValue('relacao_id_agente'));
		$nome_agente->setValue( TSession::getValue('relacao_nome_agente'));
		$cod_int->setValue( TSession::getValue('relacao_id_entidade'));
		$rz_social->setValue( TSession::getValue('relacao_entidade'));
		$matr_interna->setValue( TSession::getValue('relacao_matricula'));
		$cod_plano->setValue( TSession::getValue('relacao_plano'));	 
		$apolice->setValue( TSession::getValue('relacao_apolice'));	 
		$contrato->setValue( TSession::getValue('relacao_contrato'));
		$conta_corrente->setValue( TSession::getValue('busca_conta_corrente'));	 
		$valor_ini->setValue( TSession::getValue('relacao_valor_ini'));//localiza_valor_ini
		$valor_fim->setValue( TSession::getValue('relacao_valor_fim'));
		//$cep->setValue( TSession::getValue('busca_cep'));
		
		
		$cad_ini->setMask('dd/mm/yyyy');
		// $cad_ini->setDataBaseMask('YY/mm/dd');
		$cad_fim->setMask('dd/mm/yyyy');
		// $cad_fim->setDataBaseMask('YY/mm/dd');
		
		//apaga as sessões da pagina 'cadContrato'
		// TSession::setValue('TS_vencimentos', NULL);
		// TSession::setValue('TS_cobertura', NULL);
	
		//CRIA A DATAGRID
		$this->dados_contrato = new TQuickGrid;;
		$this->dados_contrato->DisableDefaultClick();
		$this->dados_contrato->style = "width:100%;margin-bottom: 10px";
		
		$this->dados_contrato->addQuickColumn('Cadastro', 'DT_INICIO', 'center');
		$this->dados_contrato->addQuickColumn('Apólice', 'ID_CONTRATO', 'center');
		$this->dados_contrato->addQuickColumn('Entidade', 'ENTIDADE_COLETIVA', 'left');
		$this->dados_contrato->addQuickColumn('Plano', 'TP_PLANO', 'left');
		$this->dados_contrato->addQuickColumn('Nome', '{cliente->NOME}', 'left');
		$this->dados_contrato->addQuickColumn('Parcela', 'VL_PARCELA', 'left');
		$this->dados_contrato->addQuickColumn('valor', 'VALOR', 'le ft');
		
		//CRIA A AÇÃO DA GRID
		$this->dados_contrato->addQuickAction('Editar', new TDataGridAction(array('cadContrato2', 'onEdit')), 'ID_CONTRATO', 'fa:edit blue');
		
		$this->dados_contrato->addQuickAction('Excluir', new TDataGridAction(array($this, 'onDelete')), 'ID_CONTRATO', 'far:trash-alt red');
		
		//ADD A GRID EM TELA
		$this->dados_contrato->createModel();
		
		//formatações
		$nome_agente->setEditable(FALSE);
		$rz_social->setEditable(FALSE);
		$plano->setEditable(FALSE);
		
		//cria o form
		$this->form = new BootstrapFormBuilder('formConsultaContrato');
		$this->form->setFieldSizes('100%');
		//$this->form->setFormTitle('Lista de chamados');
		
		//cria o form
		$row = $this->form->addFields(['Nome', $nome],
							   ['Cpf', $cpf],   
							   ['Matr. Interna', $matr_interna],
							   ['Matr. Orgão', $matr_orgao]   
								);
		$row->layout = ['col-sm-6', 'col-sm-2', 'col-sm-2', 'col-sm-2'];	

		$row = $this->form->addFields(['Id Agente', $id_agente],
									  ['Nome agente', $nome_agente],
									  ['Contrato', $contrato],
									  ['Apolice', $apolice]
									  );
		$row->layout = ['col-sm-2', 'col-sm-6', 'col-sm-2', 'col-sm-2'];
		
		$row = $this->form->addFields(['Cod Plano', $cod_plano],
									  ['Plano', $plano]	
		);
		$row->layout = ['col-sm-2', 'col-sm-10'];
		
		$row = $this->form->addFields(['Cod. Entidade', $cod_int],
									  ['Nome Entidade', $rz_social],
									  ['Cadastramento', $cad_ini],
									  ['e', $cad_fim]
									  );
		$row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-3', 'col-sm-3'];
		
		//Divisão de página
		/*$label1 = new TLabel('Outras opções');
		$ln = $this->form->addContent($ln =  [$label1 ] );
		$ln->style=' width:100%; border-bottom:1px solid #666; margin:0 0 10px 5px';
		
		$row = $this->form->addFields(['Conta corrente', $cod_int],
									  ['Nome Entidade', $rz_social],
									  ['Cadastramento', $cad_ini],
									  ['e', $cad_fim]
									  );*/
		//ações do dorm
		$this->form->addAction('Pesquisar', new TAction(array($this, 'onSearch')), 'fa:search blue' );
		$this->form->addAction('Limpar', new TAction(array($this, 'onClear')), 'fa: fa-eraser red' );
		
		//pageNavigation
		$this->pageNavigation = new TPageNavigation();
		$this->pageNavigation->enableCounters();
		$this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
		//$this->pageNavigation->setWidth($this->datagrid->getWidth());
		
		//container
		$painel = new TPanelgroup('Lista de chamados');
		$painel->add($this->form);	
		$painel->add($this->dados_contrato);
		$painel->addFooter(THBox::pack($this->pageNavigation));	
		
		$vbox = new TVBox('Consultar Contrato');
		$vbox->style = 'width:100%';
		$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
		$vbox->add($painel);
		
		parent::add($vbox);
		
	}//__construct
	
	public function onSim($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['ID_CONTRATO'];
			$contrato = new contratos2($key);
			
			$contrato->delete();
			
			new TMessage('info', 'Contrato Deletado');
			
			TTransaction::close();
			
			$this->onReload($param);
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onSim
	
	public function onDelete($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$key = $param['key'];
			$contrato = new contratos2($key);
			$contrato_nome = $contrato->cliente->NOME;
			//$contrato_nome->cliente->NOME;
			
			$onsim = new TAction(array($this, 'onSim')); 
			$onsim->setParameter('ID_CONTRATO', $key);
			
			new TQuestion('Deseja apagar o contrato de '. ' " ' . $contrato_nome . ' " ', $onsim);
			
			TTransaction::close();
			
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onDelete
	
	public function onClear($param)
	{
		TSession::setvalue('busca_nome', NULL);
		TSession::setvalue('relacao_nome', NULL);
		TSession::setvalue('busca_cpf', NULL);
		TSession::setvalue('relacao_cpf', NULL);
		TSession::setvalue('relacao_matricula', NULL);
		TSession::setvalue('busca_matricula', NULL);
		TSession::setvalue('relacao_matr_orgao', NULL);
		TSession::setvalue('busca_matr_orgao', NULL);
		TSession::setvalue('busca_agente', NULL);
		TSession::setvalue('relacao_id_agente', NULL);
		TSession::setvalue('relacao_nome_agente', NULL);
		TSession::setvalue('relacao_entidade', NULL);
		TSession::setvalue('relacao_id_entidade', NULL);
		TSession::setvalue('busca_entidade', NULL);
		TSession::setvalue('relacao_plano', NULL);
		TSession::setvalue('busca_plano', NULL);
		TSession::setvalue('busca_contrato', NULL);
		TSession::setvalue('relacao_contrato', NULL);
		
		$this->form->clear();
		
		$this->onReload($param);
		//$this->dados_contrato->clear();
		
	}//onDelete
	
	public function onReload($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$rp_contrato = new TRepository('Contratos2');
			
			$criteria = new TCriteria;
			$criteria->setProperty('direction','ASC');
						 
			$criteria->setProperties( $param );
			$criteria->setProperty('limit',8);
			
			if(TSession::getValue('busca_nome'))
			{	
				$criteria->add(TSession::getValue('busca_nome'));
			}
			
			if(TSession::getValue('busca_cpf'))
			{	
				$criteria->add(TSession::getValue('busca_cpf'));
			}
			
			if(TSession::getValue('busca_matricula'))
			{	
				$criteria->add(TSession::getValue('busca_matricula'));
			}
			
			if(TSession::getValue('busca_matr_orgao'))
			{	
				$criteria->add(TSession::getValue('busca_matr_orgao'));
			}
			
			if(TSession::getValue('busca_contrato'))
			{	
				$criteria->add(TSession::getValue('busca_contrato'));
			}
			
			if(TSession::getValue('busca_agente'))
			{	
				$criteria->add(TSession::getValue('busca_agente'));
			}
			
			if(TSession::getValue('busca_plano'))
			{	
				$criteria->add(TSession::getValue('busca_plano'));
			}
			
			if(TSession::getValue('busca_entidade'))
			{	
				$criteria->add(TSession::getValue('busca_entidade'));
			}
			
			if(TSession::getValue('busca_data_ini') )
			{
				$criteria->add(TSession::getValue('busca_data_ini'));
				//$criteria->add(TSession::getValue('busca_data_fim'));
			}
			
			
	
			$contratos = $rp_contrato->load($criteria);
				
			$this->dados_contrato->clear();
			if($contratos)
			{
				foreach($contratos as $contrato)
				{
					//$contrato->NOME = 
					$this->dados_contrato->addItem($contrato);
				}
			}

			$criteria->resetProperties();
			$count = $rp_contrato->count( $criteria ); 

			$this->pageNavigation->setCount ( $count );
			$this->pageNavigation->setProperties ( $param );
			$this->pageNavigation->setlimit(8);//paginação	
			
			TTransaction::close();
		}//try
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
	}//onReload
	
	public function onSearch($param)
	{
		
		$data = $this->form->getData();
		
		$data->NOME = strtoupper($data->NOME);      
         if($data->NOME)
		 {
			$filter = new TFilter('(SELECT NOME from CLIENTES4 WHERE MATR_INTERNA=CONTRATOS2.MATR_INTERNA)', 'like', "{$data->NOME}%");
			
			//GRAVA O FILTRO NA SESSÃO
			TSession::setvalue('busca_nome',$filter);
			TSession::setvalue('relacao_nome',$data->NOME);
		}
        else
        {
            TSession::setValue('busca_nome', NULL);//reseta a  TSession acima 
            TSession::setValue('relacao_nome', NULL);//reseta a TEntry do form 
        }
		
		
		if($data->CPF)
		{
			$filter = new TFilter('CPF', '=', "$data->CPF");
			TSession::setValue('busca_cpf', $filter);
			TSession::setValue('relacao_cpf', $data->CPF);
		}
		else
		{
			TSession::setValue('busca_cpf', NULL);
			TSession::setValue('relacao_cpf', NULL);
		}

		if($data->MATR_INTERNA)
		{
			$filter = new TFilter('MATR_INTERNA', '=', "$data->MATR_INTERNA");
			TSession::setValue('busca_matricula', $filter);
			TSession::setValue('relacao_matricula', $data->MATR_INTERNA);
		}
		else
		{
			TSession::setValue('busca_matricula', NULL);
			TSession::setValue('relacao_matricula', NULL);
		}

		if($data->MATR_ORGAO)
		{
			$filter = new TFilter('MATR_ORGAO', '=', "$data->MATR_ORGAO");
			TSession::setValue('busca_matr_orgao', $filter);
			TSession::setValue('relacao_matr_orgao', $data->MATR_ORGAO);
		}
		else
		{
			TSession::setValue('busca_matr_orgao', NULL);
			TSession::setValue('relacao_matr_orgao', NULL);
		}
		
		if($data->CONTRATO)
		{
			$filter = new TFilter('ID_CONTRATO', '=', "$data->CONTRATO");
			TSession::setValue('busca_contrato', $filter);
			TSession::setValue('relacao_contrato', $data->CONTRATO);
		}
		else
		{
			TSession::setValue('busca_contrato', NULL);
			TSession::setValue('relacao_contrato', NULL);
		}

		if($data->CODIGO)
		{
			$filter = new TFilter('AGENTE', '=', "$data->CODIGO");
			TSession::setValue('busca_agente', $filter);
			TSession::setValue('relacao_id_agente', $data->CODIGO);
			TSession::setValue('relacao_nome_agente', $data->NOME_AGENTE);
		}
		else
		{
			TSession::setValue('busca_agente', NULL);
			TSession::setValue('relacao_id_agente', NULL);
			TSession::setValue('relacao_nome_agente', NULL);
		}

		if($data->COD_INT)
		{
			$filter = new TFilter('ENTIDADE_COLETIVA', '=', "$data->COD_INT");
			TSession::setValue('busca_entidade', $filter);
			TSession::setValue('relacao_id_entidade', $data->COD_INT);
			TSession::setValue('relacao_entidade', $data->RAZAO_SOCIAL);
		}
		else
		{
			TSession::setValue('busca_entidade', NULL);
			TSession::setValue('relacao_id_entidade', NULL);
			TSession::setValue('relacao_entidade', NULL);
		}

		if($data->CODIGO2)
		{
			$filter = new TFilter('TP_PLANO', '=', "$data->CODIGO2");
			TSession::setValue('busca_plano', $filter);
			TSession::setValue('relacao_id_plano', $data->CODIGO2);
			TSession::setValue('relacao_plano', $data->PLANO);
		}
		else
		{
			TSession::setValue('busca_plano', NULL);
			TSession::setValue('relacao_id_plano', NULL);
			TSession::setValue('relacao_plano', NULL);
		}


		if (($data->CAD_INI) AND ($data->CAD_FIM) )//nome d compo a ser buscado
        {
			$filter = new TFilter('DT_INICIO','BETWEEN', $data->CAD_INI, $data->CAD_FIM); 
			TSession::setvalue('busca_data_ini',$filter);
			//TSession::setvalue('busca_data_fim',$filter);
			
			//DEIXA A DATA NO FORMATO BRASILEIRO PRA EXIBIÇÃO
			//$data->CAD_INI = TDate::date2br($data->CAD_INI);
		    //$data->CAD_FIM = TDate::date2br($data->CAD_FIM);
			
			//GUARDA A DATA NA SESSÃO
			TSession::setvalue('relacao_data_ini',$data->CAD_INI);
			TSession::setvalue('relacao_data_fim',$data->CAD_FIM);
        }
        else
        {
			TSession::setValue('busca_data_ini', NULL);//reseta a  TSession acima 
			TSession::setValue('busca_data_fim', NULL);//reseta a  TSession acima 
			
			TSession::setValue('relacao_data_ini', NULL);//reseta a TEntry do form 
			TSession::setValue('relacao_data_fim', NULL);//reseta a TEntry do form 
        }
		
		/*if(($data->VL_PARCELA1) AND ($data->VL_PARCELA2) )
        {
			$filter = new TFilter('VL_PARCELA','BETWEEN', $data->VL_PARCELA1, $data->VL_PARCELA2); 
			TSession::setvalue('localiza_valor_ini',$filter);
			TSession::setvalue('localiza_valor_fim',$filter);			
			
			//GUARDA A DATA NA SESSÃO
			TSession::setvalue('relacao_valor_ini',$data->VL_PARCELA1);
			TSession::setvalue('relacao_valor_fim',$data->VL_PARCELA2);
        }
        else
        {
			TSession::setValue('localiza_valor_ini', NULL);
			TSession::setValue('localiza_valor_fim', NULL);
			
			TSession::setValue('relacao_valor_ini', '');
			TSession::setValue('relacao_valor_fim', '');
        }*/		
		
		/*
		$nome->setValue( TSession::getValue('relacao_nome'));
		$nome->setValue( TSession::getValue('busca_nome'));
		$cpf->setValue( TSession::getValue('busca_cpf'));
		$cpf->setValue( TSession::getValue('relacao_cpf'));
		$matr_interna->setValue( TSession::getValue('relacao_matricula'));
		$matr_interna->setValue( TSession::getValue('busca_matricula'));
		$matr_orgao->setValue( TSession::getValue('relacao_matr_orgao'));
		$matr_orgao->setValue( TSession::getValue('busca_matr_orgao'));
		$id_agente->setValue( TSession::getValue('busca_agente'));
		$id_agente->setValue( TSession::getValue('relacao_id_agente'));
		$nome_agente->setValue( TSession::getValue('relacao_nome_agente'));
		$cod_int->setValue( TSession::getValue('relacao_entidade'));
		$cod_int->setValue( TSession::getValue('busca_entidade'));
		$cod_plano->setValue( TSession::getValue('relacao_plano'));
		$cod_plano->setValue( TSession::getValue('busca_plano'));
		
		
		$dt_ini->setValue( TSession::getValue('relacao_data_ini'));
		$dt_fim->setValue( TSession::getValue('relacao_data_fim'));
		
		
		
		
		
		$matr_interna->setValue( TSession::getValue('relacao_matricula'));
		$cod_plano->setValue( TSession::getValue('relacao_plano'));	 
		$apolice->setValue( TSession::getValue('relacao_apolice'));	 
		$contrato->setValue( TSession::getValue('relacao_contrato'));
		$conta_corrente->setValue( TSession::getValue('busca_conta_corrente'));	 
		$cep->setValue( TSession::getValue('busca_cep'));	 
		$valor_ini->setValue( TSession::getValue('relacao_valor_ini'));
		$valor_fim->setValue( TSession::getValue('relacao_valor_fim'));
		*/
		
		$this->form->setdata($data);
	  
	    $param = array();
        $param['offset'] = 0;
        $param['first_page'] = 1;//paginação
	    $this->form->getdata();
        $this->onReload( $param ); 
		
	}//onSearch
	
	/*
	  captura as parametros da URL e atualiza o onReload
	*/
	public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }//show
	
}//TPage

?>