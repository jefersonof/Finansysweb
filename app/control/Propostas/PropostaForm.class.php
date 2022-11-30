<?php
/**
 * Product Form
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PropostaForm extends TPage
{
    protected $form;
    
    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;
    
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Product');
        $this->form->setFormTitle(_t('Product'));
        //$this->form->setClientValidation(true);
        
        // create the form fields
        // create the form fields
        $ID_PROPOSTA   = new TEntry('ID_PROPOSTA');
        $FUNCIONARIO   = new TEntry('FUNCIONARIO');
        $DESCRICAO     = new TEntry('DESCRICAO');
		$ID_CORRETOR   = new TDBSeekButton('CODIGO', 'DB2', 'formProposta', 'fornecedor', 'NOME', 'CODIGO', 'NOME_CORRETOR');
		$NOME_CORRETOR = new TEntry('NOME_CORRETOR');
		$NOME          = new TEntry('NOME');
		$CPF           = new TEntry('CPF');
		$STATUS        = new TCombo('STATUS');
		$FOTO          = new TMultiFile('FOTO');
		
		//$images      = new TMultiFile('images');
        
        // allow just these extensions
        $FOTO->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg', 'pdf'] );
        //$images->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        
        // enable progress bar, preview, and gallery mode
        $FOTO->enableFileHandling();
        $FOTO->enableImageGallery();
        $FOTO->enablePopover('Preview', '<img style="max-width:300px" src="download.php?file={file_name}">');
        
        $ID_PROPOSTA->setEditable( FALSE );
        
        // add the form fields
        $row = $this->form->addFields(['Id Proposta', $ID_PROPOSTA],
							          ['Descrição', $DESCRICAO, ],
							          ['Nome', $NOME ],
									  ['Cpf', $CPF ]);
		$row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];
			
		$row = $this->form->addFields([$FOTO ]);
		$row->layout = ['col-sm-12'];
        
        $ID_PROPOSTA->setSize('50%');
        
        // $description->addValidation('Description', new TRequiredValidator);
        // $stock->addValidation('Stock', new TRequiredValidator);
        // $sale_price->addValidation('Sale Price', new TRequiredValidator);
        // $unity->addValidation('Unity', new TRequiredValidator);
        
        // add the actions
        $this->form->addAction( 'Salvar', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addActionLink( 'Limpar', new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addActionLink( 'Listar', new TAction(['PropostaListe', 'onReload']), 'fa:table blue');
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'PropostaListe'));
        $vbox->add($this->form);
        parent::add($vbox);
    }
	
	public function onReload()
	{
		$ts_current_page = TSession::getValue('TS_current_page');
		$this->form->setCurrentPage($ts_current_page);
		
	}//onReload
	
	/**
     * Clear2 form data
     * @param $param Request
     */
    public function onClear2( $param )
    {
        //APAGA AS SESSÕES
		TSession::setValue('TS_key', NULL);
		TSession::setValue('TS_foto', NULL);
		TSession::setValue('TS_data', NULL);
		
		//LIMPA O FORM
		$this->form->clear();
		
		//grava 
		$data = $this->form->getData();
		
		// $data->ID_CORRETOR   = '';
		// $data->ID_PROPOSTA   = '';
		// $data->CPF           = '';
		// $data->USER_ID       = '';
		// $data->USER_NAME     = '';
		// $data->NOME_CORRETOR = '';
		// $data->NOME          = '';
		// $data->STATUS        = '';
		// $data->CODIGO        = '';
		// //$data->FOTO          = '';
		// $data->DESCRICAO     = '';
		
		//se for corretor trás o id e o nome
		//pega o id do grupo do usuário
		$grupo_id = TSession::getValue('TS_grupo_id');
		
		if($grupo_id == 5)
		{
			$codigo = TSession::getValue('TS_cod_corretor');
			$nome   = TSession::getValue('TS_nome_corretor');
			
			$data->CODIGO        = $codigo;
			$data->NOME_CORRETOR = $nome;
		}	
		/*TSession::setValue('TS_cod_corretor', $codigo);
					TSession::setValue('TS_nome_corretor', $nome);*/
		
		
		TSession::setValue('TS_data', $data);
		
		$this->form->setData($data);
		$this->onReload($param);
		
    }//onClear2
    
    /**
     * Overloaded method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open('db2');
            
            // form validations
            $this->form->validate();
            
            // get form data
            $data   = $this->form->getData();
            
            // store product
            $object = new proposta;
            $object->fromArray( (array) $data);
            $object->store();
            
            // copy file to target folder
            //$this->saveFile($object, $data, 'photo_path', 'files/images');
            
            $this->saveFiles($object, $data, 'FOTO', 'files/images', 'proposta_foto', 'FOTO', 'PROPOSTA_ID');
            
            // send id back to the form
            $data->ID_PROPOSTA = $object->ID_PROPOSTA;
            $this->form->setData($data);
            
            TTransaction::close();
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e)
        {
            $this->form->setData($this->form->getData());
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                TTransaction::open('db2');
                $object = new proposta( $param['key'] );
                $object->FOTO = proposta_foto::where('PROPOSTA_ID', '=', $param['key'])->getIndexedArray('FOTO');
                $this->form->setData($object);
                TTransaction::close();
                return $object;
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
