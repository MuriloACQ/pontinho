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
	
	public function getJogo() {
		return $this->jogo;
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
	
	public function getTimeouts() {
		return $this->timeouts;
	}
	
	public function getJogandoByUserId($userId) {
		$jogando = array();
		$jogando['id'] = $this->getJogoId();
		$jogando['status'] = $this->jogo->getStatus();
		$jogando['mesa'] = $this->mesa;
		$jogando['minhaVez'] = $userId == $this->vez;
		$jogando['vez'] = $this->getUsernameById($this->vez);
		$jogando['jogadores'] = $this->getJogadoresPublicInfo();
		$jogando['montanteFichas'] = $this->fichas;
		$jogando['timeouts'] = $this->getTimeoutsPublicInfo();
		return $jogando;
	}
	
	private function getUsernameById($id) {
		$username = null;
		foreach ($this->jogadores as $jogador) {
			if($jogador->getid() == $id) {
				$username = $jogador->getUsername(); break;
			}
		}
		return $username;
	}
	
	private function getJogadoresPublicInfo() {
		$jogadores = array();
		foreach ($this->jogadores as $jogador) {
			$jogadores[] = array(
					'nome' => $jogador->getUsername(),
					'fichas' => $jogador->getFichas(),
					'cartas' => count($jogador->getMao())
			);
		}
		return $jogadores;
	}
	
	private function getTimeoutsPublicInfo() {
		$timeouts = array();
		foreach ($this->timeouts as $timeout) {
			$timeouts[] = $timeout->getUsername();
		}
		return $timeouts;
	}
}

?>