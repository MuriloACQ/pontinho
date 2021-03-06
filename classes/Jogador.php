<?php
namespace classes;
class Jogador {
	
	private $usuario;
	private $fichas;
	private $mao;
	private $vez;
	private $timestamp;
	
	public function __construct(Usuario $usuario, $fichas, $mao, $vez, $timestamp) {

		$this->usuario = $usuario;
		$this->fichas = $fichas;
		$this->mao = $mao;
		$this->vez = $vez;
		$this->timestamp = $timestamp;
	}
	
	public function getId() {
		return $this->usuario->getId();
	}
	
	public function getUsername() {
		return $this->usuario->getUsername();
	}
	
	public function getFichas() {
		return $this->fichas;
	}
	
	public function getMao() {
		return $this->mao;
	}
	
	public function getStringMao() {
		return implode(',', $this->mao);
	}
	
	public function getVez() {
		return $this->vez;
	}
	
	public function getTimestamp() {
		return $this->timestamp;
	}
}
?>