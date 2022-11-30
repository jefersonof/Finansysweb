<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 
include_once('../class/tcpdf/tcpdf.php');
include_once("../class/PHPJasperXML.inc.php");
$server="localhost";
$db="C:\Sistemas\Finansys\db\DB15.FBD";
//$db="C:\\Users\\romualdo\\Documents\\teste.fdb";
$user="SYSDBA";
$pass="masterkey";
$version="0.8d";
$pchartfolder="./class/pchart2";

$xml =  simplexml_load_file("sampleib.jrxml");


$PHPJasperXML = new PHPJasperXML("en","TCPDF");
//$PHPJasperXML->debugsql=true;
$PHPJasperXML->arrayParameter=array("parameter1"=>1);
$PHPJasperXML->xml_dismantle($xml);

$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db,"ibase");
$PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file


?>
