<?php
namespace classes;

class Usuario {
	
	private $id;
	private $username;
	private $fichas;
	
	public function __construct($id, $username, $fichas) {
		$this->id = $id;
		$this->username = $username;
		$this->fichas = $fichas;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getFichas() {
		return $this->fichas;
	}
	
	public function setFichas($fichas) {
		$this->fichas = $fichas;
	}
}
?>