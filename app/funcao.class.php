<?php
 class funcao 
 { 
	/*Pega todos os programas do usuário
	usando o seu id com parâmetro*/
	public function buscaUserProgram2($id_user)
	{	
		//Versão simplificada carrega os objetos SystemUserGroup relacionados (1-N)
		$system_user_programs = SystemUser::find($id_user)
		->hasMany('SystemUserGroupProgram');
		
		$programas = array();
		foreach($system_user_programs as $system_user_program )
		{
			$programas['system_program_id'] = $system_user_program->system_program_id;
			//$programas['acesso']            = $system_user_program->acesso;
		}
		
		return $programas;//
		
		//$id_grupo = implode($id_grupo);
		//var_dump($id_grupo);
		
	}//grupoUsuario	
	
   /*Pega todos os programas do usuário
	usando o seu id com parâmetro */
	public function buscaUserProgram($id_user)
 	{
		$system_programs = array();
        
        // load the related System_program objects
        $repository = new TRepository('SystemUserGroupProgram');
        
		$criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_id', '=', $id_user));
		
        $obj_programas = $repository->load($criteria);
        if ($obj_programas)
        {
            foreach ($obj_programas as $obj_programa)
            {	
				$system_programs[$obj_programa->system_program_id]                         = $obj_programa->toArray();
				
				$system_programs[$obj_programa->system_program_id] ['system_user_id']      = $obj_programa->system_user_id;
				
				$system_programs[$obj_programa->system_program_id] ['system_program_id']   = $obj_programa->system_program_id;
				
				$system_programs[$obj_programa->system_program_id] ['system_program_name'] = $obj_programa->programa->name;
				
				$system_programs[$obj_programa->system_program_id] ['system_controller']   = $obj_programa->programa->controller;
				
				$system_programs[$obj_programa->system_program_id] ['acesso']              = $obj_programa->acesso;
				
				$system_programs[$obj_programa->system_program_id] ['insercao']            = $obj_programa->insercao;
				
				$system_programs[$obj_programa->system_program_id] ['delecao']             = $obj_programa->delecao;
				
            }
        }
        
        return $system_programs;
		
	}//buscaUserProgram
	
	/*
		Pega o id de um programa usando o seu nome como parâmetro (Usado nas permissões de programa)
	*/
	public function buscaIdProgram($nome_classe)
	{
		$repository = new TRepository('SystemProgram');
		
		$criteria = new TCriteria;
		$criteria->add( new TFilter('controller', 'like', $nome_classe));
		
		$obj_noma_progs = $repository->load($criteria);
		
		$obj_prog = array();
		if($obj_noma_progs)
		{
			foreach($obj_noma_progs as $obj_noma_prog )
			{
				$obj_prog[$obj_noma_prog->id] = $obj_noma_prog->id;
				//$obj_prog[$obj_noma_prog->id] = $obj_noma_prog->toArray();
				
				//$obj_prog[$obj_noma_prog->id] = $obj_noma_prog->toArray();
			}
		}
		
		return $obj_prog;
		
		// $obj_prog = array();
		// if($obj_noma_progs)
		// {	
			// foreach($obj_noma_progs as $obj_noma_prog )
			// {
				// $obj_prog[$obj_noma_prog->id]  = $obj_noma_prog->toArray(); 
				
				// $obj_prog[$obj_noma_prog->id] ['id']          = $obj_noma_prog->id; 
				// $obj_prog[$obj_noma_prog->id] ['controller']  = $obj_noma_prog->controller; 
				// $obj_prog[$obj_noma_prog->id] ['name']        = $obj_noma_prog->name; 
				
			// }
		// }	
				
		// return $obj_prog;
		
	}//buscaIdProgram
	
	/*
		Busca o nome dos fornecedores
	*/
	public function buscaCorretor($id_login)
	{
		$repository = new TRepository('fornecedor');
		//$repository = new TRepository('corretor');
		
		$criteria   = new TCriteria;
		$criteria->add(new TFilter('USER_ID', '=',  $id_login ));
		
		$obj_corretores = $repository->load($criteria);
		
		$nome_corretor = array();
		if($obj_corretores)
		{
			foreach($obj_corretores as $obj_corretor )
			{
				$nome_corretor[$obj_corretor->CODIGO] = $obj_corretor->toArray();
				//$nome_corretor[ $obj_corretor->ID_CORRETOR ] = $obj_corretor->toArray();
				
				//$nome_corretor[ $obj_corretor->ID_CORRETOR ] ['ID_CORRETOR']  = $obj_corretor->ID_CORRETOR;
				//$nome_corretor[ $obj_corretor->ID_CORRETOR ] ['NOME']         = $obj_corretor->NOME;
			}
		}
		
		return $nome_corretor;
		
	}//buscaCorretor
	
	/*Pega os grupos do usuário usando o $id_user como parâmetro*/
	public function grupoUsuario($id_user)
	{	
		//Versão simplificada carrega os objetos SystemUserGroup relacionados (1-N)
		$system_user_group = SystemUser::find($id_user)
		->hasMany('SystemUserGroup');
		
		$id_grupo = array();
		foreach($system_user_group as $system_user_groups )
		{
			$id_grupo['system_group_id'] = $system_user_groups->system_group_id;
		}
		
		return $id_grupo;
		
		//$id_grupo = implode($id_grupo);
		//var_dump($id_grupo);
		
	}//grupoUsuario	
	
	/*Gera vencimentos de parcelas*/
	
	// function vencimento($data, $meses = integer)
	// {
		// if(!preg_match("#\d{2}/\d{2}/\d{4}#", $data)) {
			// return false;
		// }
		// if(!is_int($meses)) {
			// return false;
		// }
		// $data = implode("-", array_reverse(explode("/", $data)));	
		// for($i = 1; $i <= $meses; $i++) {
			// $dat['DATA_VENCIMENTO'] = date("d/m/Y", strtotime($data ." +$i month"));	
			// $dat['PARCELA']         = $i ;	
		// }
		// return $dat;
		
	// }//vencimento
	
	function vencimento($data, $meses = integer)
	{
		if(!preg_match("#\d{2}/\d{2}/\d{4}#", $data)) {
			return false;
		}
		if(!is_int($meses)) {
			return false;
		}
		$data = implode("-", array_reverse(explode("/", $data)));	
		for($i = 1; $i <= $meses; $i++) {
			$dat[] = date("d/m/Y", strtotime($data ." +$i month"));	
		}
		return $dat;
	}//vencimento
	
	
 }//funcao


?>