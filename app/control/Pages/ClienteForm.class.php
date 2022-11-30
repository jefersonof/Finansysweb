<?php
class ClienteForm Extends TPage
{	
	private $notebook;
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
        $this->form = new BootstrapFormBuilder('formAssociado');
		
        //$this->form->setFormTitle('Cadastro de Associados (T029)');
        $this->form->setFieldSizes('100%');
        $this->form->class = 'tform';
		
		//captura a aba ativa  
		$this->form->setTabAction( new TAction(array($this, 'onTabClick')));
		
		/*/*
		$btn = $this->form->addAction('Salvar' ,new TAction(array($this, 'onSave')), 'far:save ');
		$btn->class = 'btn btn-sm  btn-primary';
		
		//add ações do form
		
		$this->form->addAction('Incluir' ,new TAction(array($this, 'onIncluir')), 'fa:plus blue fa-lg' );//fa:plus green fa-lg
		
		$this->form->addAction('Alterar' ,new TAction(array($this, 'onAlterar')), 'fa:edit blue' );
        
		$this->form->addAction('Cancelar' ,new TAction(array($this, 'onCancelar')), 'far: fa-window-close red' );// fa:window-close red
		
		$this->form->addAction('Retornar' ,new TAction(array('ClienteListe', 'onReload')), 'fa: fa-reply blue' );// ** fa: fa-arrow-left fa-share 
		
		
		$this->form->addAction('Avançar' ,new TAction(array($this, 'onAvancar')), 'fa: fa-share blue' );// ** fa: fa-arrow-left ** fa-share **  fa:arrow-circle-o-left 
		*/
		
		//$this->form->addAction('Relatorio' ,new TAction(array($this, 'onRel')), 'fa: fa-share blue' );// ** fa: fa-arrow-left ** fa-share **  fa:arrow-circle-o-left */
		
		//criando od btn
		$btn = $btn_salvar    = TButton::create('btn_salvar' ,array($this, 'onSave'), 'salvar', 'fa: fa-save');
		$btn->class = 'btn btn-sm btn-primary';
		
		$btn_incluir   = TButton::create('btn_incluir' ,array($this, 'onIncluir'), 'Incluir', 'fa: fa-plus blue fa-lg');
		
		$btn_alterar   = TButton::create('btn_alterar' ,array($this, 'onAlterar'), 'Alterar', 'fa: fa-edit blue');
		
		$btn_cancelar  = TButton::create('btn_cancelar' ,array($this, 'onCancelar'), 'Cancelar', 'far: fa-window-close blue');
		
		$btn_retornar  = TButton::create('btn_retornar' ,array('ClienteListe', 'onReload'), 'Retornar', 'fa: fa-reply blue');
		
		$btn_avancar  = TButton::create('btn_avancar' ,array($this, 'onAvancar'), 'Avançar', 'fa: fa-share blue');
		
		//cria os atributos
		$matr_interna  = new TEntry('MATR_INTERNA');
		$tipo          = new TCombo('TIPO');
		$cpf           = new TEntry('CPF');
		$nome          = new TEntry('NOME');
		$dt_cadastro   = new TDate('DT_CADASTRO');
		$nascimento    = new TDate('NASCIMENTO');
		$idade         = new TEntry('IDADE');
		$identidade    = new TEntry('IDENTIDADE');
		$orgao_emissor = new TEntry('ORGAO_EMISSOR');
		$dt_emissao    = new TDate('DT_EMISSAO');
		$instrucao     = new TEntry('INSTRUCAO');//TIPO
		$sexo          = new TCombo('SEXO');
		$estado_civil  = new TCombo('ESTADO_CIVIL');
		$cpf_conj      = new TEntry('CPF_CONJ');
		$conj          = new TEntry('CONJ');
		$status        = new TDBCombo('STATUS', 'db2', 'clientes_status', 'CODIGO', 'STATUS');
     	/*//EX => TDBCombo('nome_objeto','banco_de_dados','classe_model','campo_id','campo_descricao');*/
		$nacionalidade = new TEntry('NACIONALIDADE');
		$fat_mens      = new TEntry('FAT_MENS');
		$nome_pai      = new TEntry('NOME_PAI');
		$nome_mae      = new TEntry('NOME_MAE');
		$cep_res       = new TEntry('CEP_RES');
		$end_res       = new TEntry('END_RES');
		$end_nro       = new TEntry('END_NRO');
		$end_compl     = new TEntry('END_COMPL');
		$bairro_res    = new TEntry('BAIRRO_RES');
		$cidade_res    = new TEntry('CIDADE_RES');
		$uf_res        = new TDBCombo('UF_RES', 'db2', 'uf', 'COD', 'ESTADO');
		$uf_res->setChangeAction(new TAction(array($this , 'onUF')));
		$uf_res2       = new TEntry('UF_RES2');//UF_RES2 * CIDADE_RES
		$ddd_res       = new TEntry('DDD_RES');
		$fone_res      = new TEntry('FONE_RES');
		$ddd_cel       = new TEntry('DDD_CEL');
		$fone_cel      = new TEntry('FONE_CEL');
		$e_mail        = new TEntry('E_MAIL');
		$obs_bancaria  = new TEntry('OBS_BANCARIA');
		
		//pg_prof		
		$nome_emp    	  = new TEntry('NOME_EMP');
		$cnpj_emp    	  = new TEntry('CNPJ_EMP');
		$tipo_emp    	  = new TCombo('TIPO_EMP');
		$cep_emp     	  = new TEntry('CEP_EMP');
		$end_emp     	  = new TEntry('END_EMP');
		$emp_nro     	  = new TEntry('EMP_NRO');
		$emp_compl   	  = new TEntry('EMP_COMPL');
		$cidade_emp  	  = new TEntry('CIDADE_EMP');
		$bairro_emp  	  = new TEntry('BAIRRO_EMP');
		$uf_emp      	  = new TEntry('UF_EMP');
		$cargo       	  = new TEntry('CARGO');
		$ddd_emp     	  = new TEntry('DDD_EMP');
		$fone_emp    	  = new TEntry('FONE_EMP');
		$ramal_emp   	  = new TEntry('RAMAL_EMP');
		$fax         	  = new TEntry('FAX');
		$dt_admissao 	  = new TDate('DT_ADMISSAO');
		$profissao   	  = new TEntry('PROFISSAO');
		$matr_orgao   	  = new TEntry('MATR_ORGAO');
		$vl_salario   	  = new TEntry('VL_SALARIO');
		$vl_outros_rend   = new TEntry('VL_OUTROS_REND');
		$desc_outros_rend = new TEntry('DESC_OUTROS_REND');
		$tp_at_cli        = new TEntry('TP_AT_CLI');
		$nome_ref1        = new TEntry('NOME_REF1');
		$ddd_ref1         = new TEntry('DDD_REF1');
		$fone_ref1        = new TEntry('FONE_REF1');
		$grau_ref1        = new TCombo('GRAU_REF1');
		$nome_ref2        = new TEntry('NOME_REF2');
		$ddd_ref2         = new TEntry('DDD_REF2');
		$fone_ref2        = new TEntry('FONE_REF2');
		$grau_ref2        = new TCombo('GRAU_REF2');
		
		//pg_juridica
		$cpf1             = new TEntry('CPF1');
		$rz1              = new TEntry('RZ1');
		$cpf2             = new TEntry('CPF2');
		$rz2              = new TEntry('RZ2');
		$cpf3             = new TEntry('CPF3');
		$rz3              = new TEntry('RZ3');
		$dt_fund          = new TDate('DT_FUND');		
		$patr             = new TEntry('PATR');
		$cod_ativ         = new TEntry('COD_ATIV');
		$ramo_ativ        = new TEntry('RAMO_ATIV');
						
		//pg_info
		$lim_cred         = new TEntry('LIM_CRED');
		$cep_corr         = new TEntry('CEP_CORR');
		$end_corr         = new TEntry('END_CORR');
		$end_nro_corr     = new TEntry('END_NRO_CORR');
		$end_compl_corr   = new TEntry('END_COMPL_CORR');
		$bairro_corr      = new TEntry('BAIRRO_CORR');
		$cidade_corr      = new TEntry('CIDADE_CORR');
		$uf_corr          = new TEntry('UF_CORR');
		
		
		//VALIDAÇÃO
		$nome->addValidation(' "NOME" ' , new TRequiredValidator );
		
		//PLACEHOLDER
		$profissao->placeholder = 'Função/ Profissão';
		$fone_res->placeholder = 'Telefone';
		$fone_cel->placeholder = 'Celular';
		$fone_ref1->placeholder = 'Telefone';
		$fone_ref2->placeholder = 'Telefone';
		$grau_ref1->placeholder = 'Grau de Relacionamento';
		$grau_ref2->placeholder = 'Grau de Relacionamento';
		
		//setTipe
		$cpf_conj->setTip('CPF Conjuge (sem pontos ou hífen) ');
		$status->setTip('Status do associado');
		$desc_outros_rend->setTip('Descrição outros rendimentos');
		$tp_at_cli->setTip('Atividade Profissional');
		$cpf1->setTip('CPF/CNPJ Sócio1');
		$cpf2->setTip('CPF/CNPJ Sócio2');
		$cpf3->setTip('CPF/CNPJ Sócio3');
		$rz1->setTip('Nome/ Razão Social Sício 1');
		$rz2->setTip('Nome/ Razão Social Sício 2');
		$rz3->setTip('Nome/ Razão Social Sício 3');
		$cod_ativ->setTip('Código Atividade Conf. CNPJ');
		$ramo_ativ->setTip('Descrição do Principal Ramo de Atividade da Empresa');
		$fone_res->setTip('Telefone');
		$fone_cel->setTip('Celular');
		$fone_ref1->setTip('Telefone');
		$fone_ref2->setTip('Telefone');
		$grau_ref1->setTip('Grau de Relacionamento');
		$grau_ref2->setTip('Grau de Relacionamento');
		
		
		//**DataMask
		/*$di->setMask('dd/mm/yyyy');
		$di->setDatabaseMask('dd/mm/yyyy');*/	
		//$dt_cadastro->setDataBaseMask('yyyy/mm/dd');
		$dt_admissao->setMask('dd/mm/yyyy', TRUE);
		$dt_cadastro->setMask('dd/mm/yyyy', TRUE);
		$nascimento->setMask('dd/mm/yyyy', TRUE);
		$dt_emissao->setMask('dd/mm/yyyy', TRUE);
		$dt_fund->setMask('dd/mm/yyyy', TRUE);
		
		$nascimento->setDataBaseMask('dd/mm/yyyy');
		//$dt_emissao->setDataBaseMask('dd/mm/yyyy');
		
		//addItems 'TCombo'
		$tipo->addItems( ['F' =>'Pessoal Física', 'J' => 'Pessoa Jurídica' ] );
		$sexo->addItems( ['1' =>'Masculino', '2' => 'Feminino' ] );
		$estado_civil->addItems( ['C' =>'Casado', 'S' => 'Solteiro' ] );
		$tipo_emp->addItems( ['Estatal' =>'Estatal', 'Governo' => 'Governo' , 'Municipal' => 'Municipal' , 'Privada' => 'Privada' ] );		
		$grau_ref1->addItems([ 'Amigo' => 'Amigo', 'Parente' => 'Parente', 'Vizinho' => 'Vizinho', 'Colega' => 'Colega', ]);		
		$grau_ref2->addItems([ 'Amigo' => 'Amigo', 'Parente' => 'Parente', 'Vizinho' => 'Vizinho', 'Colega' => 'Colega', ]);
		
		$fat_mens->setNumericMask(2, '.', ',', TRUE);
		$vl_outros_rend->setNumericMask(2, '.', ',', TRUE);
		$vl_salario->setNumericMask(2, '.', ',', TRUE);
		$lim_cred->setNumericMask(2, '.', ',', TRUE);
		$patr->setNumericMask(2, '.', ',', TRUE);
		
		
		//** PAGE  PESSOAL **//
 	    $this->form->appendPage('Pessoal');
		
		$row = $this->form->addFields([new TLabel('Matrícula'), $matr_interna],
								      [new TLabel('Tipo'), $tipo],
								      [new TLabel('CPF'), $cpf],
									  [new TLabel('Nome'), $nome] );
		$row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-6'];						

		$row = $this->form->addFields([new TLabel('Cadastramento2'), $dt_cadastro],
									  [new TLabel('Nascimento'), $nascimento],
									  [new TLabel('Idade'), $idade],
									  [new TLabel('Identidade'), $identidade],
									  [new TLabel('Orgão emissor'), $orgao_emissor],
									  [new TLabel('Data emissão'), $dt_emissao]);
		$row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2' , 'col-sm-2', 'col-sm-2', 'col-sm-2' ];

		$row = $this->form->addFields([new TLabel('Tipo Identidade'), $instrucao],
									  [new TLabel('sexo'), $sexo ],
									  [new TLabel('Estado civil'), $estado_civil],
									  [new TLabel('CPF conjuge '), $cpf_conj],
									  [new TLabel('Nome conjuge'), $conj ]);
		$row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2' , 'col-sm-2', 'col-sm-4'];
		
		$row = $this->form->addFields([new TLabel('Situação associado'), $status],
									  [new TLabel('Status CPF'), $nacionalidade],
									  [new TLabel('Faturamento médio '), $fat_mens],
									  [new TLabel('Nome do pai '), $nome_pai]);
		$row->layout = ['col-sm-4', 'col-sm-2', 'col-sm-2' , 'col-sm-4'];
		
		
		$row = $this->form->addFields([new TLabel('Nome da mãe'), $nome_mae],
									  [new TLabel('cep'), $cep_res],
									  [new TLabel('Rua'), $end_res],
									  [new TLabel('Número'), $end_nro],
									  [new TLabel('Complemento '), $end_compl],
									  [new TLabel('Bairro'), $bairro_res ]);
		$row->layout = ['col-sm-3', 'col-sm-2', 'col-sm-2' , 'col-sm-1', 'col-sm-2', 'col-sm-2' ];
		
		$row = $this->form->addFields([new TLabel('Cidade*'), $cidade_res],
									  [new TLabel('Estado*'), $uf_res],
									  [new TLabel('.'), $uf_res2],
									  [new TLabel('DDD'), $ddd_res],
									  [new TLabel('Telefone1'), $fone_res ]);
		$row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-2' , 'col-sm-2', 'col-sm-2'];
		
		$row = $this->form->addFields([new TLabel('DDD'), $ddd_cel],
									  [new TLabel('Telefone'), $fone_cel],
									  [new TLabel('e-mail'), $e_mail] );
		$row->layout = ['col-sm-2', 'col-sm-3', 'col-sm-7'];
		
		$row = $this->form->addFields([new TLabel('obs_bancaria'), $obs_bancaria] );
		$row->layout = ['col-sm-12'];
		


		//** PAGE PROFISSIONAL **//
		$this->form->appendPage('Profissional');
		
		$row = $this->form->addFields([new TLabel('Empresa'), $nome_emp ],
								      [new TLabel('CNPJ'), $cnpj_emp ],
								      [new TLabel('Tipo empresa'), $tipo_emp ] );
		$row->layout = ['col-sm-5', 'col-sm-4', 'col-sm-3', ];						
		
		$row = $this->form->addFields( [new TLabel('Cep'), $cep_emp ],
								       [new TLabel('Rua'), $end_emp ],
								       [new TLabel('Número'), $emp_nro ],
									   [ new TLabel('Complemento'), $emp_compl ] );
		$row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-2', 'col-sm-4'];						
								

		$row = $this->form->addFields([new TLabel('Bairro'), $bairro_emp ],
								     [new TLabel('Cidade'), $cidade_emp ],
								     [new TLabel('Estado'), $uf_emp ],
									 [new TLabel('DDD'), $ddd_emp ],
								     [new TLabel('Telefone'), $fone_emp ] );
		$row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-2', 'col-sm-2', 'col-sm-2' ];

		$row = $this->form->addFields([new TLabel('Ramal'), $ramal_emp ],
								      [new TLabel('FAX'), $fax ],
								      [new TLabel('Admissão'), $dt_admissao ],
                                 	  [new TLabel('Função/profissão'), $profissao ],
									  [new TLabel('Cargo'), $cargo ] );
		$row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-3', 'col-sm-3'];
		
		$row = $this->form->addFields([new TLabel('Matrícula'), $matr_orgao ],
							          [new TLabel('Salario'), $vl_salario ],
							          [new TLabel('Outros rendimentos'), $vl_outros_rend ],
							          [ new TLabel('Desc rendimentos'), $desc_outros_rend ] );
		$row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];							  

		$row = $this->form->addFields([new TLabel('Atividade Profissional'), $tp_at_cli ] );
		$row->layout = ['col-sm-12'];
								
		//ADD DIVISÃO DE PAGINA
		$label1 = new TLabel('Referências Pessoais', '#000', 10, 'bi');//, '#7D78B6', 8, 'bi'
        
		$ln = $this->form->addContent($ln =  [$label1 ] );
		$ln->style=' width:100%; border-bottom:1px solid #666; margin:0 0 10px 5px';
		
		$row = $this->form->addFields([new TLabel('Nome'), $nome_ref1],
								      [new TLabel('DDD'), $ddd_ref1],
							          [new TLabel('Telefone'), $fone_ref1],
								      [new TLabel('Grau de referência'), $grau_ref1]);
		$row->layout = ['col-sm-4', 'col-sm-2', 'col-sm-3', 'col-sm-3'];

		$row = $this->form->addFields([new TLabel('Nome'), $nome_ref2],
								      [new TLabel('DDD'), $ddd_ref2],
							          [new TLabel('Telefone'), $fone_ref2],
								      [new TLabel('Grau de referência'), $grau_ref2 ]);
		$row->layout = ['col-sm-4', 'col-sm-2', 'col-sm-3', 'col-sm-3'];
		
        //** PAGE JURÍDICA **//
		$this->form->appendPage('Pessoa Jurídica');	

		$row = $this->form->addFields([new TLabel('CPF/CNPJ 1'), $cpf1 ],
							          [new TLabel('Nome / razão social 1'), $rz1 ],
								      [new TLabel('CPF/CNPJ 2'), $cpf2 ],
							          [new TLabel('Nome / razão social 2'), $rz2 ]);
		$row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-2', 'col-sm-4'];						
		
		$row = $this->form->addFields([ new TLabel('CPF/CNPJ 3'), $cpf3 ],
							          [ new TLabel('Nome / razão social 3'), $rz3 ],
								      [ new TLabel('Data Fundação'), $dt_fund ],
								      [ new TLabel('Patrimônio Líguido'), $patr ]);
		$row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-2', 'col-sm-4'];
		
		$row = $this->form->addFields([ new TLabel('Cod Atividade'), $cod_ativ ],
								      [ new TLabel('Descrição do ramo'), $ramo_ativ ]);
		$row->layout = ['col-sm-6', 'col-sm-6'];						
							
		//** PAGE OUTRAS INFO **//
		
		$this->form->appendPage('Outras info');	

		$row = $this->form->addFields( [ new TLabel('Limite crédito'), $lim_cred ]);
		$row->layout = ['col-sm-12'];

		//add divisão de pagina
		$label2 = new TLabel('Endereço de correspondência', '#000', 10, 'bi');//, '#7D78B6', 8, 'bi'
        
		$ln2 = $this->form->addContent([$label2 ] );
		$ln2->style=' width:100%; border-bottom:1px solid #666; margin:100px 0 10px 5px';

		$row = $this->form->addFields([new TLabel('Cep'), $cep_corr ],
							          [new TLabel('Rua'), $end_corr ],
							          [new TLabel('Número'), $end_nro_corr ],
									  [new TLabel('Complemento'), $end_compl_corr ]);
		$row->layout = ['col-sm-2', 'col-sm-5', 'col-sm-1', 'col-sm-4'];
		
		$row = $this->form->addFields([new TLabel('Bairro'), $bairro_corr],
							          [new TLabel('Cidade'), $cidade_corr],
							          [new TLabel('Estado'), $uf_corr] );
		$row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];
		
		$this->formFields = array($btn_salvar, $btn_alterar, $btn_avancar, $btn_cancelar, $btn_incluir, $btn_retornar, $matr_interna, $tipo, $cpf, $nome, $dt_cadastro, $nascimento, $idade, $identidade, $orgao_emissor, $dt_emissao, $instrucao, $sexo, $estado_civil, $cpf_conj, $conj, $status, $nacionalidade, $fat_mens, $nome_pai, $nome_mae, $cep_res, $end_nro, $end_compl, $bairro_res, $cidade_res, $uf_res, $uf_res2, $ddd_res, $fone_res, $ddd_cel, $fone_cel, $e_mail, $obs_bancaria, $nome_emp, $cnpj_emp, $tipo_emp, $cep_emp, $end_emp, $emp_nro, $emp_compl, $cidade_emp, $bairro_emp, $uf_emp, $cargo, $ddd_emp, $fone_emp, $ramal_emp, $fax, $dt_admissao, $profissao, $matr_orgao, $vl_salario, $vl_outros_rend, $desc_outros_rend, $tp_at_cli, $nome_ref1, $ddd_ref1, $fone_ref1, $grau_ref1, $nome_ref2, $ddd_ref2, $fone_ref2, $grau_ref2, $cpf1, $rz1, $cpf2, $rz2, $cpf3, $rz3, $dt_fund, $patr, $cod_ativ, $ramo_ativ, $lim_cred, $cep_corr, $end_corr, $end_nro_corr, $end_compl_corr, $bairro_corr, $cidade_corr, $uf_corr);
		$this->form->setFields($this->formFields);
			
		
		//add menu Tbreadcrumb manual
		$breadcrumb = new TXMLBreadCrumb( 'menu.xml', 'ClienteListe' );
		
		$painel = new TPanelGroup('Cadastro de Associados (T029)');
		$painel->add($this->form);
		
		//barra footer
		
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_salvar, $btn_incluir, $btn_alterar,	$btn_cancelar, $btn_retornar ,$btn_avancar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_retornar));//$this->saveButton, btn_fechar
		}
		
		//ativar a rolagem horizontal dentro do corpo do painel
		$painel->getBody()->style = "overflow-x:auto" ;
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
		$vbox->add($breadcrumb);
		$vbox->add($painel);
		
		parent::add($vbox);
		
		
	}//__construct
	
	public function onLoadSession()
	{
		$data = TSession::getValue('TS_data_cliente');
		
		//manda os dados para o form
		$this->form->setData($data);
		
	}//onLoadForm
	
	public function onAvancar()
	{
		$data = $this->form->getData();
		$this->form->setData($data);
		
		//grava os dados do form na sessão
		TSession::setValue('TS_data_cliente', $data);
		
		//apaga os dados de sessão da pg cadContrato2
		TSession::setValue('TS_ultimaData', NULL);
	    TSession::setValue('TS_ultimaNumParc', NULL);
	    TSession::setValue('TS_vencimentos', NULL);
	    TSession::setValue('TS_cobertura', NULL);
	    TSession::setValue('TS_barraVence', NULL);
		
		
		
		//Passa os dados por array para 'cadContrato'
        AdiantiCoreApplication::loadPage('cadContrato', 'onLoadForm', (array) $data);
		
	}//onAvancar
	
	public function onRel()
	{
		$ts_rel = TSession::getValue('TS_cliente');
		
		var_dump($ts_rel);
	}
	
	/*
	    Instância um 'cliente' usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('finansys');//db2
			
			//$data = $this->form->getData();
			
			if(isset($param['key']) )
			{
				$key = $param['key'];
				
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formAssociado', 'btn_salvar');
				}
				
				$cliente = new cliente($key);
				
				//Mostra a 'UF_RES'
				$obj = new STDClass;
				$obj->UF_RES2     = $cliente->nome_uf->UF; 
				$obj->NOME        = utf8_encode($cliente->NOME);
				
				//Conversão da data 
				$obj->DT_CADASTRO = TDate::date2br($cliente->DT_CADASTRO);
				$obj->DT_EMISSAO  = TDate::date2br($cliente->DT_EMISSAO);
				$obj->DT_ADMISSAO = TDate::date2br($cliente->DT_ADMISSAO);
				$obj->NASCIMENTO  = TDate::date2br($cliente->NASCIMENTO);
				$obj->DT_FUND     = TDate::date2br($cliente->DT_FUND);
				
				//ADICIONA EM TELA A 'UF_RES'
				TForm::sendData('formAssociado', $obj);
				
				//manda os dados pro form
				$this->form->setData($cliente);
				
				//define aba atual
				$ts_current_page = 0;
				$this->form->setCurrentPage($ts_current_page);
				
				//grava aba na sessão
				TSession::setValue('TS_current_page_cli', $ts_current_page);
				
				//grava os dados na sessão
				//TSession::setValue('TS_data', $data);
				TSession::setValue('TS_data', $cliente);
				
				//Desabilita os campos
				//TEntry::disableField('formAssociado', 'NOME');
				//TEntry::disableField('formAssociado', 'MATR_INTERNA');
			}	
				
			
			TTransaction::close();
		}
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onEdit
	
	/*
	Recupera os dados do form
	*/
	public function onCancelar($param)
	{
		
		$data = TSession::getValue('TS_data');
		
		TEntry::disableField('formAssociado', 'NOME');
		TEntry::disableField('formAssociado', 'MATR_INTERNA');
		
		$this->form->setData($data);
		
	}//onCancelar
	
	/*
	Desabilita campos para Update
	*/
	public function onAlterar($param)
	{
		$data = $this->form->getData();
		
		TEntry::enableField('formAssociado', 'NOME');
		//TButton::enableField('formAssociado', 'NOME');
		//$this->onEdit($param);
		
		$this->form->setData($data);
		
	}//onAlterar
	
	/*
	Limpa o form para um Insert
	*/
	public function onIncluir($param)
	{
		//$this->form->clear();
		
		
		
	}//onIncluir
	
	/*
	Salva uma 'cliente'
	*/
	public function onSave($param)
	{
		try
		{	
			TTransaction::open('finansys');
			
			$data = $this->form->getData();
			
			$this->form->validate();
			
			
			//captura os dados pelo getData
			$cliente = $this->form->getData('cliente');
			
			//$cliente->UF_RES = $param['UF_RES2']; 
			//decodifica pra salvar
			$cliente->NOME = utf8_decode($data->NOME); 
			
		
			//TRATA A DATA ANTES DE SALVAR
			$cliente->DT_CADASTRO = TDate::date2us($data->DT_CADASTRO);
			$cliente->DT_ADMISSAO = TDate::date2us($data->DT_ADMISSAO);
			$cliente->NASCIMENTO  = TDate::date2us($data->NASCIMENTO);
			$cliente->DT_FUND     = TDate::date2us($data->DT_FUND);
			$cliente->DT_EMISSAO  = TDate::date2us($data->DT_EMISSAO);
			
			$cliente->store();	
			
			//TRATA A DATA ANTES DE MOSTRA
			$cliente->NOME = utf8_encode($cliente->NOME);
			$cliente->DT_CADASTRO = TDate::date2br($cliente->DT_CADASTRO);
			$cliente->DT_ADMISSAO = TDate::date2br($cliente->DT_ADMISSAO);
			$cliente->NASCIMENTO  = TDate::date2br($cliente->NASCIMENTO);
			$cliente->DT_FUND     = TDate::date2br($cliente->DT_FUND);
			$cliente->DT_EMISSAO  = TDate::date2br($cliente->DT_EMISSAO);
			$this->form->setData($cliente);
			
			//define aba atual
			$ts_current_page = TSession::getValue('TS_current_page_cli');
			$this->form->setCurrentPage($ts_current_page);
			
			//DESATIVA AS TEntry
			TEntry::disableField('formAssociado', 'NOME');
			TEntry::disableField('formAssociado', 'MATR_INTERNA');
			
			TTransaction::close();
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formAssociado', 'btn_salvar');
			}
			
			new TMessage('info', 'Salvo com sucesso');
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
		
	}//onSave
	
	/*
	Ação de saída do campo ESTADO 'UF_RES'  
	*/
	public static function onUF($param)
	{
		try
		{
			TTransaction::open('db2');//db2
			
			if($param['UF_RES'] )
			{
				//pega o id da uf
				$key = $param['UF_RES'];
				
				//new TMessage('info', 'param key = ' . $key);
				
				//$uf = new uf($key);
				//instacia o obj
				$uf = uf::find($key);
				
				//pega o nome
				$nome_uf = $uf->UF;
				
				//manda pro form
				$obj = new StdClass;
				$obj->UF_RES2 = $nome_uf;
				
				//ADICIONA EM TELA AS VALORES
				TForm::sendData('formAssociado', $obj);
				
			TTransaction::close();
				
			}	
			
		}//try
		catch(Exception $e)
		{
			new TMessage('error', 'info');
			TTransaction::rollback();
		}
		
	}//onUF
	
	
	
	/**
     * Captura e grava na sessão o ID da aba ativa
     */
    public static function onTabClick($param)
    {
        //new TMessage('info', '<b>You have clicked at the tab</b>:  <br><br>' . $param['current_page'] . str_replace(',', '<br>', json_encode($param)));
		
		//grava a aba na sessão a cada troca 
		TSession::setValue('TS_current_page_cli', $param['current_page'] );
		
    }
	
	/*
	public function onReload($param)
	{
		$this->onEdit($param);
	}
	*/
	
}//TWindow


?>
