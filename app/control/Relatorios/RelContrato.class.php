<?php

class RelContrato extends TPage
{
	private $form;
	function __construct()
	{
		parent::__construct();
		
		$apolice = new TEntry('APOLICE');
		$btn_gerar = TButton::create('btn_gerar', array($this,'onRelatorio'), 'Gerar','fa: fa-check blue ');
		
		
		$this->form = new BootstrapFormBuilder('formEnt_Gar');
		//$this->form->setFormTitle('Previsão de Rendimentos (T501) ');
		$this->form->setFieldSizes('100%');
		$row = $this->form->addFields([new TLabel('Apólice'), $apolice]);
		$row->layout = ['col-sm-12'];
		
		//add fields
		$this->form->setFields(array($btn_gerar, $apolice));
		
		$painel = new TPanelGroup('Relatório');
		$painel->addFooter(THBox::pack($btn_gerar));
		$painel->add($this->form);
		
		parent::add($painel);
		
	}//__construct
	
	public function onRelatorio()
	{
		try
		{
			TTransaction::open('db2'); // abre uma transação 
            $conn = TTransaction::get(); // obtém a conexão 
			
			$data = $this->form->getData();
			$apolice = $data->APOLICE;
			
			$sql  = "SELECT C4.DT_INICIO, C4.MATR_INTERNA, CL4.NOME, c2.COBERTURA,
					C4.PARCELAS, C4.VALOR, C4.ID_CONTRATO, CC.COBERTURA_ID, CC.VL_COBERTURA, F2.PARCELA_CTO, F2.DATA_VENCIMENTO, F2.VALOR_PAGAR
					
					FROM CONTRATOS4 C4
					INNER JOIN COBCONTRATO CC ON C4.ID_CONTRATO  = CC.CONTRATO_ID
					INNER JOIN FINANR2 F2 ON C4.ID_CONTRATO      = F2.CONTRATO_ID
					INNER JOIN COBERTURAS2 C2 ON C2.CODIGO       = CC.COBERTURA_ID
					INNER JOIN CLIENTES4 CL4 ON CL4.MATR_INTERNA = C4.MATR_INTERNA
					WHERE 1 = 1
					";
					
			$parametros = array();
			
			if(!empty($apolice))
			{
				$parametros[] =  $apolice; 
				$sql .= " AND C4.ID_CONTRATO = ?";
			}//apolice
			
			$sql .= ' GROUP BY CC.COBERTURA_ID, F2.PARCELA_CTO';
			//$sql .= ' GROUP BY CC.COBERTURA_ID';
			
			$sth = $conn->prepare($sql); 
            
            //$sth->execute(array(1,1200));
            $sth->execute($parametros);
            $results = $sth->fetchAll();
			
			//var_dump($results);

			//JSON
			$cli_json = $results;
		   
			$teste_jason = json_encode($cli_json);
					   
			//var_dump($teste_jason);
			//var_dump($results[0]['DT_INICIO']);
			
			//PDF
			//$designer = new TPDFDesigner;
			$designer = new TReportHeaderFooter;// classe filha da TPDFDesigner 
			$designer->fromXml('app/reports/relContrato.pdf.xml');
			$designer->replace('{APOLICE}', $data->APOLICE );
			$designer->generate();//tras todos
			
			$fill = TRUE;
            $designer->gotoAnchorXY('items');
            $designer->SetFont('Arial', '', 10);
            $designer->setFillColorRGB('#F5F5F5');
            //$designer->style = ' border:1px solid #FF0000';
			
			foreach($results as $result) 
			{		
				$designer->gotoAnchorX('items');
				$designer->SetMargins(30,0,0);
				$designer->Cell( 60, 20, $result['PARCELA_CTO'], 0, 0, 'L', $fill);//x y 
				$designer->Cell(95, 20, utf8_decode($result['DATA_VENCIMENTO']), 0, 0, 'L', $fill);
				$designer->Cell(260, 20, utf8_decode($result['COBERTURA']), 0, 0, 'L', $fill);
				$designer->Ln(30);
				
				// grid background
				$fill = !$fill;	
			}
			
			$designer->Ln(17);
			//$designer->AddPage();
			$designer->setFillColorRGB('#F5F5F5');
			//$designer->Cell( 100, 20, 'Teste de escrita', 0, 0, 'L', $fill);//x y
			
			$designer->save('app/output/relContrato.pdf');
			
			parent::openFile('app/output/relContrato.pdf');
			
		    //mantém o form
		    $this->form->setData($data);
						
			TTransaction::close();
		}
		catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error', $e->getMessage() );
		}
		
	}//onRelatorio
	
}//TPage

?>