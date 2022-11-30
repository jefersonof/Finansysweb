<?php
class EntidadesForm Extends TPage
{
	private $form;
	
	public function __construct()
	{
		parent::__construct();
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
		$obs          = new TText('OBS');
		
		
		
		//FORMATAÇÕES
		$razao_social->addValidation('"RAZÃO SOCIAL "', new TRequiredValidator);
		//$cnpj->addValidation('" CNPJ "', new TRequiredValidator);
		
		
		//CRIA OS BOTÕES
		$btn_cancelar = TButton::create('btn_cancelar' ,array('EntidadesListe', 'onReload'), ('Cancelar'), 'ico_delete.png' );//onClear
		
		$btn_salvar = TButton::create('btn_salvar' ,array($this, 'onSave'), ('Salvar'), 'ico_save.png' );
		
		//** PAGE LISTAR  **//
		$label1 = new TLabel('Dados do cadastrais');//, '#7D78B6', 8, 'bi'
        $label1->style='text-align:left; width:100%; color:#FFF';
        
        //$this->form->appendPage('Page 1');
        $ln = $this->form->addContent($ln =  [$label1 ] );
		$ln->style='text-align:left; width:100%;background:#6287B9; color:#FFF';
		
		$row = $this->form->addFields([new TLabel('Código'), $codigo ],
								      [new TLabel('Nome'), $razao_social ],
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

		$row = $this->form->addFields([ new TLabel('Observações'), $obs ]);
		$row->layout = ['col-sm-12'];					  
							
		
		//EMPACOTAMENTO
		$painel = new TPanelGroup('Estipulantes (T204)');
		$painel->add($this->form);
		
		$painel->addFooter( THBox::pack($btn_salvar, $btn_cancelar ) );
		
		
		$this->formFields = array($razao_social, $codigo, $cnpj, $endereco, $bairro, $cidade, $cep, $estado, $telefone, $fax, $cod_federal, $perc_desc, $obs, $btn_cancelar, $btn_salvar);
		
		$this->form->setFields($this->formFields);
		
		$vbox = new TVBox;
		$vbox->style = 'width:90%';
		$vbox->add(new TXMLBreadCrumb('menu.xml','EntidadesListe'));
		$vbox->add($painel);
		
		parent::add($vbox);
		
	}//__construct
	
	public function onEdit($param)
	{
		try
		{
			TTransaction::open('db2');
			$data = $this->form->getData();
			
			if(isset($param['key']))
			{	
				$key = $param['key'];
				
				//TEntry::disableField('formEstipulante', 'COD_INT');
				
				$entidades = new entidades($key);
				$data->COD_INT       = $entidades->COD_INT;      
				$data->RAZAO_SOCIAL  = $entidades->RAZAO_SOCIAL;
				$data->CNPJ          = $entidades->CNPJ;
				$data->ENDERECO      = $entidades->ENDERECO;
				$data->BAIRRO        = $entidades->BAIRRO;
				$data->CIDADE        = $entidades->CIDADE;
				$data->CEP           = $entidades->CEP;
				$data->ESTADO        = $entidades->ESTADO;
				$data->TELEFONE      = $entidades->TELEFONE;
				$data->FAX           = $entidades->FAX;
				$data->COD_FEDERAL   = $entidades->COD_FEDERAL;
				$data->PERC_DESC     = $entidades->PERC_DESC;
				$data->OBS           = $entidades->OBS;
				
			}
			else
			{
				$this->form->Clear();
			}		
			
			$this->form->setData($data);
			
			TTransaction::close();
			
			// $this->form->setData($data);
		}//try
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
		
	}//onEdit
	
	
	public function onSave($param)
	{
		try
		{
			TTransaction::open('db2');
			
			$this->form->validate();
			
			$entidades = $this->form->getData('entidades');
			 
			//$entidades->TIPO         = 'E'; 
			$entidades->store();
			
			//$action = new TAction(array('PesquisaEntidade', 'onReload'));
			new TMessage('info', 'Registro Salvo');
			
			$this->form->setData($entidades);
			
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