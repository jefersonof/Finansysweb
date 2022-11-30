<?php
/**
 * SystemGroupProgram
 *
 * @version    1.0
 * @package    model
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
 
 
 
class SystemUserGroupProgram extends TRecord 
{
    const TABLENAME = 'system_user_group_program';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
	
	private $programa;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('system_user_id');//fk user
        parent::addAttribute('system_program_id');// fk controller system_program_id
        
		parent::addAttribute('acesso');
		parent::addAttribute('insercao');
		parent::addAttribute('alteracao');
		parent::addAttribute('delecao');
		
		//parent::addAttribute('sale_price');
		//parent::addAttribute('desconto');
		
    }//__construct
	
	/*
	    Retorna o nome do programa 
	*/
	public function get_programa()
	{
		if(empty($this->programa))
		{
			$this->programa = new SystemProgram($this->system_program_id);
		}
		
		return $this->programa;	
	}
	
	
}//SystemUserGroupProgram
