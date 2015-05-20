<?php
namespace classes;
require_once 'classes/Jogador.php';
require_once 'classes/Jogando.php';
class JogandoFactory {
	
	private $cards = array('101','201','301','401',
							'102','202','302','402',
							'103','203','303','403',
							'104','204','304','404',
							'105','205','305','405',
							'106','206','306','406',
							'107','207','307','407',
							'108','208','308','408',
							'109','209','309','409',
							'110','210','310','410',
							'111','211','311','411',
							'112','212','312','412',
							'113','213','313','413');
	
	private $jogando;
	
	public function __construct(Jogo $jogo, $jogadores = null, $mesa = null, $fichas = null) {
		
		if($jogadores !== null && $mesa !== null && $fichas !== null) {
			$this->recuperarJogando($jogo, $jogadores, $mesa, $fichas);
		} else {
			$this->createNewJogando($jogo);
		}
	}
	
	public function getJogando() {
		return $this->jogando;
	}
	
	private function createNewJogando(Jogo $jogo) {
		$numJogadores = count($jogo->getJogadores());
		$resto = 52%$numJogadores;
		for($i=0;$i<$resto;$i++) {
			array_shift($this->cards);
		}
		$arrayJogadores = array();
		$jogadorVezId = null;
		if(shuffle($this->cards)){
			$offset = 0;
			$numCartas = count($this->cards)/$numJogadores;
			foreach ($jogo->getJogadores() as $jogador) {
				$cartas = array_slice($this->cards, $offset, $numCartas);
				$offset+= $numCartas;
				sort($cartas);
				$jogadorObj = new Jogador($jogador, $jogo->getFichas(), $cartas, in_array('107', $cartas), time());
				$arrayJogadores[] = $jogadorObj;
				if($jogadorObj->getVez()) $jogadorVezId = $jogadorObj->getId();
			}
			$this->jogando = new Jogando($jogo, $arrayJogadores, array(), 0, $jogadorVezId, array());
		}
	}
	
	private function recuperarJogando(Jogo $jogo, $jogadores, $mesa, $fichas) {
		$jogadorVezId = null;
		foreach ($jogadores as $jogador) {
			if($jogador->getVez()){ $jogadorVezId = $jogador->getId(); break;}
		}
		$timeouts = array();
		if(count($jogo->getJogadores()) > count($jogadores)) {
			foreach ($jogo->getJogadores() as $usuario) {
				$timeout = true;
				foreach ($jogadores as $jogador) {
					if($usuario->getId() == $jogador->getId()) {
						$timeout = false; break;
					}
				}
				if($timeout) $timeouts[] = $usuario;
			}
		}
		if($mesa && !$mesa[0]) $mesa = array(); //avoiding empty string array
		$this->jogando = new Jogando($jogo, $jogadores, $mesa, $fichas, $jogadorVezId, $timeouts);
	}
	
}

?>