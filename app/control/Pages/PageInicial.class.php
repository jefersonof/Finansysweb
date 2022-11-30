<?php
class PageInicial Extends TPage
{
	public function __construct()
	{
		parent::__construct();
		
		//$painel = new TPanelGroup() 
		$id_grupo = TSession::getValue('usergroupids');
		//var_dump($id_grupo);
		
		
		$html = new THtmlRenderer('app/resources/page_inicial.html');
		$repleces = [];
		$html->enableSection('main', $repleces);
			
		parent::add($html);
	}//__construct
	
	public function onReload()
	{
		
	}//onReload
	
}//PageInicial

?>

