<?php
//require('fpdf.php');

class TReportHeaderFooter extends TPDFDesigner
{
	// Page header
	function Header()
	{	
	
		//PRIMEIRA LINHA
		$this->Cell(5,15,utf8_encode('Uniao seguradora S/A') ,0,1,'L');
		$this->SetFont('Arial','B', 10);
		 // Escopo: SetMargins($left, $top, $right=null)
		$this->SetMargins(15,30,10);
		
		$this->Cell(0,15,utf8_encode('95.611.141/0001-57') ,0,0,'C');
		$this->SetFont('Arial','', 10);
		
		$this->Cell(0,15,utf8_encode(date("m/d/Y H:i")) ,0,1,'R');
		
		$this->Cell(0,15,utf8_encode('Previsao de Recebimento'),0,1,'L');
		
		
		//SEGUNDA LINHA
		$this->Cell(0,15,utf8_decode('Página ') .$this->PageNo().'/{nb}',0,1,'R');
		$this->SetFont('Arial','',10);
		//$this->SetMargins(10,0,0);
        //$this->setFillColorRGB('#666');
		
		// $this->Cell(0,15,utf8_encode('Contrato') ,0,1,'L');
		$cor = TRUE;
		$this->setFillColorRGB('#000000');
		$this->SetTextColor(255, 255, 255);//SetTextColor(int r [, int g, int b])
		$this->MultiCell(0,20,utf8_decode('    Parcela                     Vencimento                                  Cobertura                              '),0,'L', $cor);
		// $this->Cell(0,20,utf8_encode('Contrato') ,0,0,'L', $cor);
		// $this->Cell(-550,20,utf8_encode('Nome') ,0,1,'C', $cor);
		//$this->SetFillColor(135,206,235);
		//Rect(float x, float y, float w, float h [, string style])
		//$this->Rect(20, 65, 190, 20, 'F');
		//$this->SetDrawColor(50, 70, 80);
		$this->SetFont('Arial','', 10);
		
		// Line break
		$this->Ln(15);
		
	}//Header
	
	// function Header()
	// {
		// // Logo
		// //$this->Image('files/images/logo.jpg',20,10,50);
		// // Arial bold 15
		// $this->SetFont('Arial','',10);
		// $this->SetMargins(0,0,0,500);
		
		// // Page number
		// $this->Cell(0,15,utf8_encode('Pagina ') .$this->PageNo().'/{nb}',0,0,'C');
		
		// // Move to the right
		// //$this->Cell(15);
		// // Title
		// //$this->Cell(130,50, utf8_encode('Uniao Seguradora S/A') ,0,0,'C');
		
		// // Line break
		// $this->Ln(15);
		// //$this->Ln(10);
		
	// }//Header

	// Page footer
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		$this->SetMargins(10,10,10);
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		//Page number
		//$this->Cell(0,10,utf8_encode('Pagina ') .$this->PageNo().'/{nb}',0,0,'C');
		
		//$this->Cell(0,15,utf8_encode('Teste') ,0,0,'C');
		
	}//Footer
	
}//TReportHeaderFooter2


?>