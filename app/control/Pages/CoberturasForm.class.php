<?php
class CoberturasForm Extends TPage
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
		$this->form = new BootstrapFormBuilder('formCoberturas');
		//$this->form->setFormTitle('Cadastro de Coberturas (T003)');
		$this->form->setFieldSizes('100%');
		$this->form->class = 'tform';
		
		//cria os Btn
		$btn_pesquisar = TButton::create('btn_pesquisar', array('CoberturasListe', 'onReload'), 'Pesquisar', 'fa: fa-arrow-left');
		
		$btn_incluir = TButton::create('btn_incluir', array($this, 'onIncluir'), 'Incluir', 'fa: fa-plus blue');
		
		//$btn_novo_pec    = TButton::create('btn_novo_pec', array($this, 'onAddRegraPec'), 'Incluir', 'fa: fa-plus blue' );
		
		$btn_cancelar = TButton::create('btn_cancelar', array($this, 'onCancelar'), 'Cancelar', 'far: fa-window-close red');
		
		$btn_salvar = TButton::create('btn_salvar', array($this, 'onSave'), 'Salvar', 'far:save');
		$btn_salvar->class = 'btn btn-sm  btn-primary';
		
		//$btn_novo_pec    = TButton::create('btn_novo_pec', array($this, 'onAddRegraPec'), 'Incluir', 'fa: fa-plus blue' );
		
		//Cria as ações do form
        /*$this->form->addAction('Pesquisar', new TAction(array('CoberturasListe', 'onReload')), 'fa: fa-arrow-left' );
        
		$this->form->addAction('Incluir2', new TAction(array($this, 'onIncluir')), 'fa: fa-plus blue' );
		
		$this->form->addAction('Cancelar', new TAction(array($this, 'onCancelar')), 'far: fa-window-close red' );
		
		$btn = $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'far:save' );
		$btn->class = 'btn btn-sm  btn-primary';
		
		
		$this->form->addAction('Fechar', new TAction(array('PageInicial', 'onReload')), 'fa: fa-power-off red' );*/
		
		//cria os atributos
		$codigo          = new TEntry('CODIGO');
		$descricao       = new TEntry('COBERTURA');
		$mes_reajuste    = new TEntry('MES_BASE_REAJUSTE');
	    $processo_susep  = new TDBCombo('PROCESSO_SUSEP', 'db2', 'planos_susep', 'ID_PLANOS_SUSEP', '{PROCESSO} | {GRUPO} | {RAMO} | {DESCRICAO} ', 'ID_PLANOS_SUSEP');
		$reajuste        = new TCombo('REAJUSTE');
		$ent_gar         = new TDBCombo('ENT_GAR', 'db2', 'ent_gar', 'CODIGO', '{CODIGO} | {NOME}', 'CODIGO');
		$reg_fin         = new TEntry('REG_FIN');
		$tipo            = new TCombo('TIPO');
		$tarifacao       = new TCombo('TARIFACAO');
		$sigla           = new TEntry('SIGLA');
		$cob2            = new TEntry('COB2');
		$msg_doc         = new TEntry('MSG_DOC');
		$parcelas_faixas = new TEntry('PARCELAS_FAIXAS');
		$apc             = new TCheckGroup('APC');
		$apc->setChangeAction( new TAction( array($this, 'onEnableDesc' )) );
		$desc1           = new TEntry('DESC1');
		$desc2           = new TEntry('DESC2');
		$ncomis          = new TRadioGroup('NCOMIS');
		$naocalc         = new TRadioGroup('NAOCALC');
		$grupo          = new TDBCombo('GRUPO', 'db2', 'seg_grupos', 'CODIGO','({CODIGO} )  {GRUPO}');
		$grupo->setChangeAction( new TAction(array($this, 'onCargaRamo')));
		$ramo  = new TDBCombo('RAMO', 'db2', 'seg_ramos', 'CODIGO', '({CODIGO}) {RAMO}' ,'CODIGO');//'RAMO', $filter);
		$cod_mor         = new TCombo('COD_MOR');
		$cod_inv         = new TCombo('COD_INV');
		$obs             = new TText('OBS');
		
		//validação
		$descricao->addValidation(' "Descrição" ', new TRequiredValidator );
		
		//setTip
		$tipo->setTip('Tipo de Cobertura');
		$mes_reajuste->setTip('Mês Base Reajuste');
		$ent_gar->setTip('Entidade Garantidora');
		$reg_fin->setTip('Regime Financeiro');
		$cob2->setTip('Nome Ruduzido (Boleto) ');
		$cod_mor->setTip('Código Morte tab. Biométrica ');
		$cod_inv->setTip('Cod. Invalidez tab. Biométrica ');
		
		//items TCombo e TRadioGroup
		$reajuste->addItems(array('M' => 'Mensal', 'T' => 'Trimestral', 'S' => 'Semestral', 'A' => 'Anual', 'N' => 'Não Reajusta', 'P' =>'Percentual Diferenciado' ));
		
		$tipo->addItems(array('A' => 'ASSISTÊNCIA FINANCEIRA', 'P' => 'Pecúlio', 'S' => 'Seguro' ));
		
		$tarifacao->addItems(array('INDEFINIDA' => 'INDEFINIDA', 'IDADE' => 'IDADE', 'FIXA' => 'FIXA' ));
		
		$apc->addItems(array('APC' => 'APC'));
		
		$ncomis->addItems(array('S' => 'Sim' ,'N' => 'Não'));
		$ncomis->setLayout('horizontal');
		
		$naocalc->addItems(array('S' => 'Sim' ,'N' => 'Não'));
		$naocalc->setLayout('horizontal');
		
		$cod_mor->addItems( array('0' =>  '0 - Não tem', '1' => '1 - Qualquer Causa', '2' => '2 - Qualquer Causa Adic Acidente' ));
		
		$cod_inv->addItems( array('0' =>  '0 - Não tem', '1' => '1 - Acidente somente', '2' => '2 - Doença somente', '3' => '3 - Qualquer Causa'));
		
		
		
		$tarifacao->setValue('INDEFINIDA');
		
		
		//montagem da pagina
		$row = $this->form->addFields([new TLabel('Código'), $codigo],
								      [new TLabel('Descrição'), $descricao],
								      [new TLabel('Mês reajuste'), $mes_reajuste],
									  [ new TLabel('Processo Susep'), $processo_susep]);
		$row->layout = ['col-sm-2', 'col-sm-3', 'col-sm-2', 'col-sm-5'];						
		
								
		$row = $this->form->addFields([new TLabel('Prioridade'), $reajuste ],
								      [new TLabel('Ent. Garantidora'), $ent_gar ],
									  [new TLabel('Reg. Financeiro'), $reg_fin ],
									  [new TLabel('Sigla'), $sigla ]);
		$row->layout = ['col-sm-4', 'col-sm-3', 'col-sm-3', 'col-sm-2'];							  
								
		$row = $this->form->addFields([new TLabel('Tarifação'), $tarifacao ],
							          [new TLabel('Tipo cob'), $tipo ],
							          [new TLabel('Nome Reduzido'), $cob2 ],
							          [new TLabel('Mensagem (Boleto)'), $msg_doc ]);
		$row->layout = ['col-sm-2' ,'col-sm-3' ,'col-sm-3' ,'col-sm-4'];					   

		$row = $this->form->addFields([new TLabel('Particiopantes'), $parcelas_faixas ],
							          [new TLabel('.'), $apc ],
							          [new TLabel('.'), $desc1 ],
							          [new TLabel('.'), $desc2 ] );
		$row->layout = ['col-sm-3', 'col-sm-1', 'col-sm-4', 'col-sm-4'];					   

		$row = $this->form->addFields([new TLabel('Comissionar'), $ncomis ],
                                      [new TLabel('Calcular Valor da parcela'), $naocalc ]);
		$row->layout = ['col-sm-4', 'col-sm-6'];							  

		$row = $this->form->addFields([new TLabel('Grupo'), $grupo ],
								      [new TLabel('Ramo'), $ramo ]);						
		$row->layout = ['col-sm-6', 'col-sm-6'];						
		
		$row = $this->form->addFields([new TLabel('Código Morte'), $cod_mor ],
								      [new TLabel('Cod. Invalidez'), $cod_inv ] );
		$row->layout = ['col-sm-6', 'col-sm-6'];							  
								
		$row = $this->form->addFields([ new TLabel('Obs'), $obs ]);
		$row->layout = ['col-sm-12'];
		
		$this->formFields = array($btn_pesquisar, $btn_incluir, $btn_cancelar, $btn_salvar, $codigo, $descricao, $mes_reajuste, $processo_susep, $reajuste, $ent_gar, $reg_fin, $tipo, $tarifacao, $sigla, $cob2, $msg_doc, $parcelas_faixas, $apc, $desc1, $desc2, $ncomis, $naocalc, $grupo, $ramo, $cod_mor, $cod_inv, $obs);
		$this->form->setFields( $this->formFields );
		
		
		//painel
		$painel = new TPanelGroup('Cadastro de Coberturas (T003)');
		$painel->add($this->form);
		//ativar a rolagem horizontal dentro do corpo do painell
		$painel->getBody()->style = 'overflow-x:auto';
		
		if($permissao_geral['insercao'] == 1)
		{	
			$painel->addFooter(THBox::pack($btn_pesquisar, $btn_salvar, $btn_incluir, $btn_cancelar));
		}
		else
		{
			$painel->addFooter(THBox::pack($btn_pesquisar, $btn_cancelar ));
		}
		
		//add menu BreadCrumb 
		$breadcrumb = new TXMLBreadCrumb('menu.xml','CoberturasListe');
		
		//mostra en tela
		$vbox = new TVBox;
		$vbox->style = 'width:90%';		
		$vbox->add($breadcrumb);
		$vbox->add($painel);
		
		parent::add($vbox);
		
	}//__construct
	
	/*
	Salva uma 'cobertura'
	*/
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
	
			$this->form->validate();
			
			$cobertura = $this->form->getData('cobertura');
			
			$cobertura->store();
			
			$this->form->setData($cobertura);
			
			//Atualiza O 'RAMO' e o 'GRUPO' em tela
			$obj = new STDClass;
			$obj->GRUPO = $cobertura->GRUPO; 
			$obj->RAMO  = $cobertura->RAMO; 
			
			//ATUALIZA OS DADOS NO FORM
			TForm::sendData('formCoberturas', $obj);
			
			$permisao = TSession::getValue('TS_alteracao');
			if($permisao == 0 )
			{	
				TButton::disableField('formCoberturas', 'btn_salvar');
			}
			
			
			new TMessage('info', 'Dados Salvo com Sucesso');
			
			TTransaction::close();
						
		}
		catch( Exception $e )
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onSave
	
	/*
	Instância uma 'cobertura' usando o @param['key'] como id do Objeto  
	*/
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			
			if(isset($param['key'] ) )
			{	
				$key = $param['key'];
				
				$cobertura = new cobertura($key);
				
				$this->form->setData($cobertura);
				
				//grava na sessão
				TSession::setValue('TS_data', $cobertura);
				
				//disabilita os compos
				TEntry::disableField('formCoberturas', 'DESC1');
				TEntry::disableField('formCoberturas', 'DESC2');
				$permisao = TSession::getValue('TS_alteracao');
				if($permisao == 0 )
				{	
					TButton::disableField('formCoberturas', 'btn_salvar');
				}
			
				//Mostra O 'RAMO' e 'GRUPO'
				$obj = new STDClass;
				$obj->GRUPO = $cobertura->GRUPO; 
				$obj->RAMO  = $cobertura->RAMO; 
				
				//ATUALIZA OS DADOS NO FORM
				TForm::sendData('formCoberturas', $obj);	
			
			}
			
			TTransaction::close();
			
		}//try
		catch(Exception $e )
		{
			new TMessage('error', $e->getMessage() );
			TTransaction::rollback();
		}
	}//onEdit
	
	/*
	  Limpa o form para uma nova cobertura
	*/
	public function onIncluir()
	{
		$this->form->clear();
		
	}//onIcluir
	
	/*
	  Desabilita os campo 'DESC1' E 'DESC2' e retorna os dados para o form
	*/
	public function onCancelar()
	{
		//Desabilitar os campos
		TEntry::disableField('formCoberturas', 'DESC1');
		TEntry::disableField('formCoberturas', 'DESC2');
		$permisao = TSession::getValue('TS_alteracao');
		if($permisao == 0 )
		{	
			TButton::disableField('formCoberturas', 'btn_salvar');
		}
		
		//pega os dados da sessão
		$data = TSession::getValue('TS_data');
		
		//manda os para o form
		$this->form->setData($data);
		
	}//onCancelar
	
	/*
	  aBILITA E Desabilita os campos 'DESC1' e 'DESC2'
	*/
	 public static function onEnableDesc($param)
    {
        if(isset($param['APC']))
        {
			TEntry::enableField('formCoberturas', 'DESC1');
			TEntry::enableField('formCoberturas', 'DESC2');
			//new TMessage('info', 'Click ok');
        }
		else
		{
			TEntry::disableField('formCoberturas', 'DESC1');
			TEntry::disableField('formCoberturas', 'DESC2');
		}	
        
    }//onEnableDesc
	
	/*
	Combo hierárquica, trás os grupos que pertencem ao ramo. 
	*/
	public static function onCargaRamo($param)
	{
		try
        {
            TTransaction::open('db2');
			
            if ($param['GRUPO'])
            {
                $criteria = TCriteria::create( ['GRUPO' => $param['GRUPO'] ] );
                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('formCoberturas', 'RAMO', 'db2', 'seg_ramos', 'CODIGO', '({CODIGO}) {RAMO}', 'RAMO', $criteria, TRUE);
            }
            else
            {
                TCombo::clearField('formCoberturas', 'RAMO');//'form' , 'CAMPO'
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
		
	}//onCargaRamo
	
}//TPage

?>

