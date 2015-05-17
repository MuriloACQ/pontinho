<?php
namespace classes;

class Jogando {
	
	private $jogo;
	private $jogadores;
	private $mesa;
	private $fichas;
	private $vez;
	private $timeouts;
	
	public function __construct(Jogo $jogo, $jogadores, $mesa, $fichas, $vez, $timeouts) {
		$this->jogo = $jogo;
		$this->jogadores = $jogadores;
		$this->mesa = $mesa;
		$this->fichas = $fichas;
		$this->vez = $vez;
		$this->timeouts = $timeouts;
	}
	
	public function getJogoId() {
		return $this->jogo->getId();
	}
	
	public function getVez() {
		return $this->vez;
	}
	
	public function getStringMesa() {
		return implode(",", $this->mesa);
	}
	
	public function getFichas() {
		return $this->fichas;
	}
	
	public function getJogadores() {
		return $this->jogadores;
	}
}

?>