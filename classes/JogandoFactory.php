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
	
	public function __construct(Jogo $jogo) {
		
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
				$jogadorObj = new Jogador($jogador, $jogo->getFichas(), $cartas, in_array('107', $cartas));
				$arrayJogadores[] = $jogadorObj;
				if($jogadorObj->getVez()) $jogadorVezId = $jogadorObj->getId();
			}
			$this->jogando = new Jogando($jogo, $arrayJogadores, array(), 0, $jogadorVezId, array());
		}
	}
	
	public function getJogando() {
		return $this->jogando;
	}
	
}

?>