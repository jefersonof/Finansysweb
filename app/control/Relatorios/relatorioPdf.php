<?php


class relatorioPdf extends TPage
{
	private $form;
	private $datagrid;
	
	public function __construct()
	{
		parent::__construct();
		
		$nome            = new TEntry('NOME');
		//formatações
		//$data_nascimento->setMask('dd/mm/yyyy');
		//$df->setMask('dd/mm/yyyy');
		//CRIA O FORM
		$this->form = new BootstrapFormBuilder('formRelPdf');
		$this->form->setFormTitle('Relatório PDF');
		$this->form->setFieldSizes('100%');
		
		$row = $this->form->addFields(['Nome', $nome]
							         );
		$row->layout = ['col-sm-12'];
		
		$this->form->addAction('Relatório MySQL' ,new TAction(array($this, 'onRelatorio')), 'fa: fa-plus blue' );//onImportar2
		
		$this->form->addAction('Relatório Firebird' ,new TAction(array($this, 'onRelatorio2')), 'fa: fa-plus blue' );//onImportar2
		
		$this->form->addAction('Relatório Firebird1' ,new TAction(array($this, 'onRelatorio1')), 'fa: fa-plus blue' );//onImportar2
		
		$this->form->addAction('Relatório Firebird4' ,new TAction(array($this, 'onRelatorio4')), 'fa: fa-plus blue' );//onImportar2
		
		$this->form->addAction('Relatório Firebird3' ,new TAction(array($this, 'onRelatorio3')), 'fa: fa-plus blue' );//onImportar2
		
		
		//DEFINE OS CAMPOS DO FORMULÁRIO
        //$this->formFields = array($nome, $sexo, $cpf, $matricula, $data_nascimento, $arquivo,  $id_planilha); //, $numero_parcela, $valor_parc

        //$this->form->setFields( $this->formFields );
		
		
		parent::add($this->form);
		
	}//__construct
	
	public function onTeste()
	{
		
	}//onTeste
	
	public function onRelatorio($param=null)
	{		
		include_once("app/lib/PHPJasperXML/PHPJasperXML.inc.php");
		include_once ('setting.php');

		$PHPJasperXML = new PHPJasperXML();
		// $PHPJasperXML->debugsql=true;
		////$PHPJasperXML->arrayParameter=array("parameter1"=>3);
		//$PHPJasperXML->load_xml_file("app/control/Relatorios/sample1.jrxml");
		
		$xml =  simplexml_load_file("app/control/Relatorios/sample1.jrxml");
		$PHPJasperXML->xml_dismantle($xml);

		$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db);
		//$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db);
		//$PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file
			$PHPJasperXML->outpage("F","files/teste2.pdf");    //page output method I:standard output  D:Download file
			TPage::openFile("files/teste2.pdf");
		
	}//onRelatorio
	
	public function onRelatorio1($param=null)
	{		
		    
			include_once("app/lib/PHPJasperXML/PHPJasperXML.inc.php");
			$PHPJasperXML = new PHPJasperXML("en","TCPDF");
			//$PHPJasperXML = new PHPJasperXML();
			//$PHPJasperXML->debugsql=true;
			$PHPJasperXML->arrayParameter=array("parameter1"=>1);
			$PHPJasperXML->xml_dismantle($xml);
			
			//$PHPJasperXML->load_xml_file("app/control/Relatorios/sampleib.jrxml");
			//$PHPJasperXML->load_xml_file("app/control/Relatorios/sample1.jrxml");
			
			/*$xml =  simplexml_load_file("app/control/Relatorios/sampleib.jrxml");
			$PHPJasperXML->xml_dismantle($xml);*/

			//$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db,"ibase");
			
			//$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db);
	  	    //$PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file
			$PHPJasperXML->outpage("F","files/teste2.pdf");    //page output method I:standard output  D:Download file
			TPage::openFile("files/teste2.pdf");
		
	}//onRelatorio
	
	public function onRelatorio2($param)
	{
		try
		{
			
			include_once('app/lib/PHPJasperXML/tcpdf/tcpdf.php');
			include_once("app/lib/PHPJasperXML/PHPJasperXML.inc.php");
			$server="127.0.0.1";
			//$server="localhost";
			$db="app/contol/relatorios/db/DB15.FBD";
			//$db="C:\\Users\\romualdo\\Documents\\teste.fdb";
			$user="SYSDBA";
			$pass="masterkey";
			$version="0.8d";
			$pchartfolder="./class/pchart2";

			$xml =  simplexml_load_file("app/control/Relatorios/sampleib.jrxml");


			$PHPJasperXML = new PHPJasperXML("en","TCPDF");
			//$PHPJasperXML = new PHPJasperXML();
			//$PHPJasperXML->debugsql=true;
			$PHPJasperXML->arrayParameter=array("parameter1"=>1);
			$PHPJasperXML->xml_dismantle($xml);

			$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db,"ibase");
			$PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file
 
			
		}catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error',$e->getMessage() );
		}
	}//onRelatorio
	
	public function onRelatorio3($param)
	{
		try
		{
			
			/*include_once('app/lib/PHPJasperXML/tcpdf/tcpdf.php');
			include_once("app/lib/PHPJasperXML/PHPJasperXML.inc.php");
			$server="127.0.0.1";
			//$server="localhost";
			$db="app/contol/relatorios/db/DB15.FBD";
			//$db="C:\\Users\\romualdo\\Documents\\teste.fdb";
			$user="SYSDBA";
			$pass="masterkey";
			$version="0.8d";
			$pchartfolder="./class/pchart2";*/
			
			/*$server="127.0.0.1";
			$db = "app/contol/relatorios/db/DB15.FBD";
			$user = 'SYSDBA';
			$pass = 'masterkey';*/
			// Connect to database
			//$dbh = ibase_connect($db, $username, $password);
			//$sql = 'SELECT login, email FROM users';
			// Execute query
			//$rc = ibase_query($dbh, $sql);
			// Get the result row by row as object
			//while ($row = ibase_fetch_object($rc)) {
			  //echo $row->email, "\n";
			//}
			// Release the handle associated with the result of the query
			//ibase_free_result($rc);
			// Release the handle associated with the connection
			//ibase_close($dbh);
			
			$server="127.0.0.1";
			//$db = 'firebird:dbname=127.0.0.1:C:\app\control\relatorios\db\DB15.FBD';//$db="app/contol/relatorios/db/DB15.FBD";
			
			$db = 'jdbc:firebirdsql:localhost/[3050]:C://Sistemas/Finansys/db/DB15.FBD';
			$pass = 'masterkey';
			$user = 'SYSDBA';
			
			include_once("app/lib/PHPJasperXML/PHPJasperXML.inc.php");
			//include_once("app/lib/PHPJasperXML/tcpdf/tcpdf.php");
			$xml =  simplexml_load_file("app/control/Relatorios/sampleib.jrxml");


			$PHPJasperXML = new PHPJasperXML("en","TCPDF");
			//$PHPJasperXML = new PHPJasperXML();
			//$PHPJasperXML->debugsql=true;
			$PHPJasperXML->arrayParameter=array("parameter1"=>1);
			$PHPJasperXML->xml_dismantle($xml);

			$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db,"ibase");
			$PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file
 
			
		}catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error',$e->getMessage() );
		}
	}//onRelatorio
	
	public function onRelatorio4($param)
	{
		try
		{
			
			//TTransaction::open('db'); // abre uma transação 
            //$conn = TTransaction::get(); // obtém a conexão 
			
			include_once("app/lib/PHPJasperXML/PHPJasperXML.inc.php");
			//include_once("app/lib/PHPJasperXML/tcpdf/tcpdf.php");
			include_once ('setting3.php');
			
			/*
			$server="localhost";
			$db="C:\Sistemas\Finansys\db\DB15.FBD";
			//$db="C:\\Users\\romualdo\\Documents\\teste.fdb";
			$user="SYSDBA";
			$pass="masterkey";
			$version="0.8d";
			$pchartfolder="./class/pchart2";*/

			//$xml =  simplexml_load_file("sampleib.jrxml");
			
			$PHPJasperXML = new PHPJasperXML("en","TCPDF");
			//$PHPJasperXML = new PHPJasperXML();
			//$PHPJasperXML->debugsql=true;
			$PHPJasperXML->arrayParameter=array("parameter1"=>1);
			
			$xml =  simplexml_load_file("app/control/Relatorios/sampleib2.jrxml");
			$PHPJasperXML->xml_dismantle($xml);
			
			

			$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db,"ibase");
			
			//$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db);
	  	    //$PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file
			$PHPJasperXML->outpage("F","files/teste2.pdf");    //page output method I:standard output  D:Download file
			TPage::openFile("files/teste2.pdf");
			
			
		   
		   //TTransaction::close(); //fecha a transação.
 
			
		}catch(Exception $e)
		{
			TTransaction::rollback();
			new TMessage('error',$e->getMessage() );
		}
	}//onRelatorio4
	
	
}//TPage

?>	
	
	
