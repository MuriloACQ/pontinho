<?php
namespace classes;
require_once 'classes/Usuario.php';
require_once 'classes/Jogo.php';

class DatabaseQueries {
	private $mysqlLink;
	
	public function __construct($mysqlLink) {
		$this->mysqlLink = $mysqlLink;
	}
	
	public function cadastrar($usuario, $senha) {
		$result = mysql_db_query(DatabaseConfig::name, "INSERT INTO usuario (username, senha) VALUES ('$usuario', '$senha')",
				$this->mysqlLink);
		if($this->proxy($result)) {
			echo 'sucesso';
			return true;
		}
	}
	
	public function login($usuario, $senha) {
		$this->tokenCollectGarbage();
		$result = mysql_db_query(DatabaseConfig::name, "SELECT id FROM usuario WHERE username = '$usuario' AND senha = '$senha'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$row = mysql_fetch_row($result);
		if(!$this->validate($row, 'usuario ou senha incorreto'))  return; //condicional break;
		$usuarioId = $row[0];
		$result = mysql_db_query(DatabaseConfig::name, "SELECT id FROM token WHERE usuario = '$usuarioId' AND timestamp > DATE_SUB(now(), INTERVAL
				".DatabaseConfig::tokenTime." HOUR)", $this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$row = mysql_fetch_row($result);
		if($row) {
			$token = $row[0];
			$this->tokenRefresh($token);
		} else {
			$token = md5(uniqid(rand(), true));
			$result = mysql_db_query(DatabaseConfig::name, "INSERT INTO token (id, usuario) VALUES ('$token', '$usuarioId')",
					$this->mysqlLink);
			if(!$this->proxy($result)) return; //condicional break
		}
		echo $token;
		return true;
	}
	
	public function logout($token) {
		$result = mysql_db_query(DatabaseConfig::name, "DELETE FROM token WHERE id = '$token'", $this->mysqlLink);
		if($this->proxy($result)) echo "sucesso";
		return true;
	}
	
	public function criarJogo($token, $capacidade, $fichas, $timeout) {
		$this->tokenCollectGarbage();
		$usuario = $this->getUserByToken($token);
		if(!$usuario) return; //condicional break
		$result = mysql_db_query(DatabaseConfig::name, "SELECT id FROM jogo WHERE usuario = '".$usuario->getId()."' AND status = 0",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		if(!$this->validate(mysql_num_rows($result) < DatabaseConfig::maxGames, 'maximo de '.DatabaseConfig::maxGames.' jogos abertos atingido')) return; //condicional break
		if(!$this->validate($capacidade <= DatabaseConfig::maxPlayers, 'capacidade maxima de '.DatabaseConfig::maxPlayers.' jogadores excedida')) return; //condicional break
		if(!$this->validate($capacidade >= 2, 'capacidade minima de 2 jogadores nao atendida')) return; //condicional break
		if(!$this->validate($fichas >= 0, 'fichas nao podem ser negativas')) return; //condicional break
		if(!$this->validate($fichas <= $usuario->getFichas(), 'nao possui fichas suficientes')) return; //condicional break
		$usuario->setFichas($usuario->getFichas() - $fichas);
		$this->updateFichas($usuario);
		$result = mysql_db_query(DatabaseConfig::name, "INSERT INTO jogo (usuario, capacidade, fichas, timeout) VALUES ('".$usuario->getId()."', '$capacidade', '$fichas', '$timeout')",
				$this->mysqlLink);
		if($this->proxy($result)) {
			$jogoId = mysql_insert_id($this->mysqlLink);
			$result = mysql_db_query(DatabaseConfig::name, "INSERT INTO jogo_participante (jogo, usuario) VALUES ('$jogoId', '".$usuario->getId()."')",
				$this->mysqlLink);
			if(!$this->proxy($result)) return; //condicional break
			echo $jogoId;
			return true;
		}
	}
	
	public function entrarJogo($token, $jogoId) {
		$this->tokenCollectGarbage();
		$usuario = $this->getUserByToken($token);
		if(!$usuario) return; //condicional break
		$jogo = $this->getJogoById($jogoId);
		if(!$jogo) return; //condicional break
		if(!$this->validate($jogo->getStatus() !== 0, 'impossivel entrar no jogo')) return; //condicional break
		if(!$this->validate($jogo->getFichas() <= $usuario->getFichas(), 'nao possui fichas suficientes')) return; //condicional break
		$result = mysql_db_query(DatabaseConfig::name, "SELECT * FROM jogo_participante WHERE jogo = '$jogoId'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		if(!$this->validate(mysql_num_rows($result) < $jogo->getCapacidade(), 'maximo de '.$jogo->getCapacidade().' jogadores atingido')) return; //condicional break
		$result = mysql_db_query(DatabaseConfig::name, "INSERT INTO jogo_participante (jogo, usuario) VALUES ('$jogoId', '".$usuario->getId()."')",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$usuario->setFichas($usuario->getFichas() - $jogo->getFichas());
		$this->updateFichas($usuario);
		echo 'sucesso';
		return true;
	}
	
	public function sairJogo($token, $jogoId) {
		$this->tokenCollectGarbage();
		$usuario = $this->getUserByToken($token);
		if(!$usuario) return; //condicional break
		$jogo = $this->getJogoById($jogoId);
		if(!$jogo) return; //condicional break
		if(!$this->validate($jogo->getStatus() !== 0, 'impossivel sair do jogo')) return; //condicional break
		if(!$this->validate($usuario->getId() != $jogo->getDono()->getId(), 'impossivel sair do proprio jogo')) return; //condicional break
		$result = mysql_db_query(DatabaseConfig::name, "SELECT usuario FROM jogo WHERE id = '$jogoId' AND usuario = '".$usuario->getId()."'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$row = mysql_fetch_row($result);
		if(!$this->validate($row, 'usuario nao participante do jogo')) return; //condicional break
		$usuario->setFichas($usuario->getFichas()+$jogo->getFichas());
		$this->updateFichas($usuario);
		$result = mysql_db_query(DatabaseConfig::name, "DELETE FROM jogo_participante WHERE jogo = '$jogoId' AND usuario = '".$usuario->getId()."'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		echo 'sucesso';
		return true;
	}
	
	public function excluirJogo($token, $jogoId) {
		$this->tokenCollectGarbage();
		$usuario = $this->getUserByToken($token);
		if(!$usuario) return; //condicional break
		$jogo = $this->getJogoById($jogoId, true);
		if(!$jogo) return; //condicional break
		if(!$this->validate($jogo->getStatus() !== 0, 'impossivel excluir jogo')) return; //condicional break
		if(!$this->validate($usuario->getId() == $jogo->getDono()->getId(), 'impossivel excluir que nao criou')) return; //condicional break
		foreach ($jogo->getJogadores() as $jogador) {
			$jogador->setFichas($jogador->getFichas() + $jogo->getFichas());
			$this->updateFichas($jogador);
		}
		$result = mysql_db_query(DatabaseConfig::name, "DELETE FROM jogo WHERE id = '$jogoId'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		echo 'sucesso';
		return true;
	}
	
	public function tokenRefresh($token) {
		$result = mysql_db_query(DatabaseConfig::name, "UPDATE token SET timestamp = now() WHERE id = '$token'",
				$this->mysqlLink);
		return $this->proxy($result);
	}
	
	public function tokenCollectGarbage() {
		$result = mysql_db_query(DatabaseConfig::name, "DELETE FROM token WHERE timestamp < DATE_SUB(now(), INTERVAL
				".DatabaseConfig::tokenTime." HOUR)", $this->mysqlLink);
		return $this->proxy($result);
	}
	
	private function proxy($result) {
		if(!$result) echo '{erro: '.mysql_error($this->mysqlLink).'}';
		return $result;
	}
	
	private function validate($data, $erroMsg) {
		if(!$data) echo "{erro: $erroMsg}";
		return $data;
	}
	
	private function getUserByToken($token) {
		$result = mysql_db_query(DatabaseConfig::name, "SELECT usuario.id, username, fichas FROM token INNER JOIN usuario ON token.usuario = usuario.id WHERE token.id = '$token' AND timestamp > DATE_SUB(now(), INTERVAL
				".DatabaseConfig::tokenTime." HOUR)", $this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$usuario = mysql_fetch_assoc($result);
		if(!$this->validate($usuario, 'token invalido')) return; //condicional break
		$this->tokenRefresh($token);
		return new Usuario($usuario['id'], $usuario['username'], $usuario['fichas']);
	}
	
	private function getUserById($id) {
		$result = mysql_db_query(DatabaseConfig::name, "SELECT * FROM usuario WHERE id = '$id'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$usuario = mysql_fetch_assoc($result);
		if(!$this->validate($usuario, 'usuario invalido')) return; //condicional break
		return new Usuario($usuario['id'], $usuario['username'], $usuario['fichas']);
	}
	
	private function getJogoById($jogoId, $fetchJogadores = false) {
		$result = mysql_db_query(DatabaseConfig::name, "SELECT * FROM jogo WHERE id = '$jogoId'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$jogo = mysql_fetch_assoc($result);
		if(!$this->validate($jogo, 'jogo invalido')) return; //condicional break
		$jogadores = null;
		if($fetchJogadores) {
			$jogadores = $this->getJogadoresByJogoId($jogoId);
			if(!$jogadores) return; //condicional break
		}
		return new Jogo($jogo['id'], $this->getUserById($jogo['usuario']), $jogo['capacidade'], $jogo['fichas'], $jogo['timeout'], $jogo['status'], $jogadores);
	}
	
	private function getJogadoresByJogoId($jogoId) {
		$result = mysql_db_query(DatabaseConfig::name, "SELECT usuario.id, username, fichas FROM jogo_participante INNER JOIN usuario ON jogo_participante.usuario = usuario.id WHERE jogo_participante.jogo = $jogoId",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$jogadores = array();
		while($jogador = mysql_fetch_assoc($result)){
			$jogadores[] = $jogador;
		}
		if(!$this->validate($jogadores, 'jogo invalido')) return; //condicional break
		$jogadoresUsuarios = array();
		foreach ($jogadores as $jogador) {
			$jogadoresUsuarios[] = new Usuario($jogador['id'], $jogador['username'], $jogador['fichas']);
		}
		return $jogadoresUsuarios;
	}
	
	private function updateFichas(Usuario $usuario) {
		$result = mysql_db_query(DatabaseConfig::name, "UPDATE usuario SET fichas = '".$usuario->getFichas()."' WHERE id = '".$usuario->getId()."'",
				$this->mysqlLink);
		$this->proxy($result);
	}
	
}