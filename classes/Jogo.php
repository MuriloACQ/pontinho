<?php
namespace classes;
class Jogo {
	
	const STATUS_CRIADO = 0;
	const STATUS_INICIADO = 1;
	const STATUS_CONCLUIDO = 2;
	
	private $id;
	private $usuarioDono;
	private $capacidade;
	private $fichas;
	private $timeout;
	private $status;
	private $jogadores;
	
	function __construct($id, Usuario $dono, $capacidade, $fichas, $timeout, $status, $jogadores = null) {
		$this->id = $id;
		$this->usuarioDono = $dono;
		$this->capacidade = $capacidade;
		$this->fichas = $fichas;
		$this->timeout = $timeout;
		$this->status = $status;
		if($jogadores) {
			$this->jogadores = $jogadores;
		} else {
			$this->jogadores = array($dono);
		}
	}
	
	function getId() {
		return $this->id;
	}
	
	function getDono() {
		return $this->usuarioDono;
	}
	
	function getCapacidade() {
		return $this->capacidade;
	}
	
	function getFichas() {
		return $this->fichas;
	}
	
	function getTimeout() {
		return $this->timeout;
	}
	
	function getStatus() {
		return $this->status;
	}
	
	function getJogadores() {
		return $this->jogadores;
	}
	
}

?>