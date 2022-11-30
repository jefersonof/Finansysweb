<?php
class PageInicial Extends TPage
{
	public function __construct()
	{
		parent::__construct();
		
		//$painel = new TPanelGroup() 
		$id_grupo = TSession::getValue('usergroupids');
		var_dump($id_grupo);
		
		
		//cria as tabelas
		$tabela = new TTable;
		
		$img_finan = new TImage('lib\adianti\images\ico_finansys.png');
		$img_uniao = new TImage('lib\adianti\images\logo_uniao.jpg');
		
		$img_finan->style = 'margin:0 0 0 1000px';
		$img_uniao->style = 'margin:400px 0 0 1000px';
		
		$tabela->addRowSet(new TLabel(''), array( $img_finan ) );
		$tabela->addRowSet(new TLabel(''), array( $img_uniao ) );
		
		parent::add($tabela);
	}//__construct
	
	public function onReload()
	{
		
	}//onReload
	
}//PageInicial

?>

