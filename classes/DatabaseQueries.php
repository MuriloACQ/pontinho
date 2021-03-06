<?php
namespace classes;
require_once 'classes/Usuario.php';
require_once 'classes/Jogo.php';
require_once 'classes/JogandoFactory.php';

class DatabaseQueries {
	
	const SUCESSO = 'sucesso';
	
	private $mysqlLink;
	
	public function __construct($mysqlLink) {
		$this->mysqlLink = $mysqlLink;
	}
	
	public function cadastrar($usuario, $senha) {
		$result = mysql_db_query(DatabaseConfig::name, "INSERT INTO usuario (username, senha) VALUES ('$usuario', '$senha')",
				$this->mysqlLink);
		if($this->proxy($result)) {
			echo self::SUCESSO;
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
		$result = mysql_db_query(DatabaseConfig::name, "SELECT id FROM jogo WHERE usuario = '".$usuario->getId()."' AND status = ".Jogo::STATUS_CRIADO,
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		if(!$this->validate(mysql_num_rows($result) < DatabaseConfig::maxGames, 'maximo de '.DatabaseConfig::maxGames.' jogos abertos atingido')) return; //condicional break
		if(!$this->validate($capacidade <= DatabaseConfig::maxPlayers, 'capacidade maxima de '.DatabaseConfig::maxPlayers.' jogadores excedida')) return; //condicional break
		if(!$this->validate($capacidade >= 2, 'capacidade minima de 2 jogadores nao atendida')) return; //condicional break
		if(!$this->validate($fichas >= 0, 'fichas nao podem ser negativas')) return; //condicional break
		if(!$this->validate($fichas <= $usuario->getFichas(), 'nao possui fichas suficientes')) return; //condicional break
		$usuario->setFichas($usuario->getFichas() - $fichas);
		if(!$this->updateFichas($usuario)) return; //condicional break
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
		if(!$this->validate($jogo->getStatus() !== Jogo::STATUS_CRIADO, 'impossivel entrar no jogo')) return; //condicional break
		if(!$this->validate($jogo->getFichas() <= $usuario->getFichas(), 'nao possui fichas suficientes')) return; //condicional break
		$result = mysql_db_query(DatabaseConfig::name, "SELECT * FROM jogo_participante WHERE jogo = '$jogoId'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		if(!$this->validate(mysql_num_rows($result) < $jogo->getCapacidade(), 'maximo de '.$jogo->getCapacidade().' jogadores atingido')) return; //condicional break
		$result = mysql_db_query(DatabaseConfig::name, "INSERT INTO jogo_participante (jogo, usuario) VALUES ('$jogoId', '".$usuario->getId()."')",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$usuario->setFichas($usuario->getFichas() - $jogo->getFichas());
		if(!$this->updateFichas($usuario)) return; //condicional break
		echo self::SUCESSO;
		return true;
	}
	
	public function sairJogo($token, $jogoId) {
		$this->tokenCollectGarbage();
		$usuario = $this->getUserByToken($token);
		if(!$usuario) return; //condicional break
		$jogo = $this->getJogoById($jogoId);
		if(!$jogo) return; //condicional break
		if(!$this->validate($jogo->getStatus() == Jogo::STATUS_CRIADO, 'impossivel sair do jogo')) return; //condicional break
		if(!$this->validate($usuario->getId() !== $jogo->getDono()->getId(), 'impossivel sair do proprio jogo')) return; //condicional break
		$result = mysql_db_query(DatabaseConfig::name, "SELECT usuario FROM jogo WHERE id = '$jogoId' AND usuario = '".$usuario->getId()."'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$row = mysql_fetch_row($result);
		if(!$this->validate($row, 'usuario nao participante do jogo')) return; //condicional break
		$usuario->setFichas($usuario->getFichas()+$jogo->getFichas());
		if(!$this->updateFichas($usuario)) return; //condicional break
		$result = mysql_db_query(DatabaseConfig::name, "DELETE FROM jogo_participante WHERE jogo = '$jogoId' AND usuario = '".$usuario->getId()."'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		echo self::SUCESSO;
		return true;
	}
	
	public function excluirJogo($token, $jogoId) {
		$this->tokenCollectGarbage();
		$usuario = $this->getUserByToken($token);
		if(!$usuario) return; //condicional break
		$jogo = $this->getJogoById($jogoId, true);
		if(!$jogo) return; //condicional break
		if(!$this->validate($jogo->getStatus() == Jogo::STATUS_CRIADO, 'impossivel excluir jogo')) return; //condicional break
		if(!$this->validate($usuario->getId() == $jogo->getDono()->getId(), 'impossivel excluir jogo que nao criou')) return; //condicional break
		foreach ($jogo->getJogadores() as $jogador) {
			$jogador->setFichas($jogador->getFichas() + $jogo->getFichas());
			if(!$this->updateFichas($usuario)) return; //condicional break
		}
		$result = mysql_db_query(DatabaseConfig::name, "DELETE FROM jogo WHERE id = '$jogoId'",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		echo self::SUCESSO;
		return true;
	}
	
	public function listarJogos($token, $status = null) {
		$this->tokenCollectGarbage();
		$usuario = $this->getUserByToken($token);
		if(!$usuario) return; //condicional break
		$sql = "SELECT jogo.id, username as usuario, capacidade, jogo.fichas, status , timeout, resultado
					FROM  jogo_participante
					INNER JOIN jogo ON jogo_participante.jogo = jogo.id
					INNER JOIN usuario ON jogo.usuario = usuario.id
					WHERE jogo_participante.usuario = '".$usuario->getId()."'";
		if($status) {
			$sql.= " AND jogo.status = '$status'";
		}
		$result = mysql_db_query(DatabaseConfig::name, $sql, $this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$jogosArray = array();
		while($jogo = mysql_fetch_assoc($result)){
			$jogosArray[] = $jogo;
		}
		echo json_encode($jogosArray);
		return true;
	}
	
	public function iniciarJogo($token, $jogoId) {
		$this->tokenCollectGarbage();
		$usuario = $this->getUserByToken($token);
		if(!$usuario) return; //condicional break
		$jogo = $this->getJogoById($jogoId, true);
		if(!$jogo) return; //condicional break
		if(!$this->validate($jogo->getStatus() == Jogo::STATUS_CRIADO, 'impossivel iniciar jogo')) return; //condicional break
		if(!$this->validate($usuario->getId() == $jogo->getDono()->getId(), 'impossivel iniciar jogo que nao criou')) return; //condicional break
		$jogandoFactory = new JogandoFactory($jogo);
		$jogando = $jogandoFactory->getJogando();
		$result = mysql_db_query(DatabaseConfig::name, "INSERT INTO jogando (jogo, usuario_vez, cartas_mesa, fichas)
				 VALUES ('".$jogando->getJogoId()."', '".$jogando->getVez()."', '".$jogando->getStringMesa()."', '".$jogando->getFichas()."')",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		foreach ($jogando->getJogadores() as $jogador) {
			$result = mysql_db_query(DatabaseConfig::name, "INSERT INTO jogando_participante (jogo, usuario, cartas, fichas)
				 VALUES ('".$jogando->getJogoId()."', '".$jogador->getId()."', '".$jogador->getStringMao()."', '".$jogador->getFichas()."')",
				$this->mysqlLink);
			if(!$this->proxy($result)) return; //condicional break
		}
		if(!$this->updateJogoStatus($jogando->getJogoId(), Jogo::STATUS_INICIADO)) return; //condicional break
		echo self::SUCESSO;
		return true;
	}
	
	public function retrieveJogo($token, $jogoId) {
		$this->tokenCollectGarbage();
		$usuario = $this->getUserByToken($token);
		if(!$usuario) return; //condicional break
		$jogando = $this->getJogandoById($jogoId);
		if(!$jogando) return; //condicional break
		$isPlayer = false;
		foreach ($jogando->getJogo()->getJogadores() as $jogador) {
			if($jogador->getId() == $usuario->getId()) {
				$isPlayer = true; break;
			}
		}
		if(!$this->validate($isPlayer, "jogo invalido")) return; //condicional break
		foreach ($jogando->getTimeouts() as $timeout) {
			if(!$this->validate($timeout->getId() !== $usuario->getId(), "jogador com timeout")) return; //condicional break
		}
		if(!$this->jogandoRefresh($jogoId, $usuario->getId())) return; //condicional break
		echo json_encode($jogando->getJogandoByUserId($usuario->getId()));
		return true;
	}
	
	public function tokenRefresh($token) {
		$result = mysql_db_query(DatabaseConfig::name, "UPDATE token SET timestamp = now() WHERE id = '$token'",
				$this->mysqlLink);
		return $this->proxy($result);
	}
	
	public function jogandoRefresh($jogoId, $usuarioId) {
		$result = mysql_db_query(DatabaseConfig::name, "UPDATE jogando_participante SET timestamp = now() WHERE jogo = '$jogoId' AND usuario = '$usuarioId'",
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
			$jogadores = $this->getUsuariosByJogoId($jogoId);
			if(!$jogadores) return; //condicional break
		}
		return new Jogo($jogo['id'], $this->getUserById($jogo['usuario']), $jogo['capacidade'], $jogo['fichas'], $jogo['timeout'], $jogo['status'], $jogadores);
	}
	
	private function getUsuariosByJogoId($jogoId) {
		$result = mysql_db_query(DatabaseConfig::name, "SELECT usuario.id as id, username, fichas FROM jogo_participante INNER JOIN usuario ON jogo_participante.usuario = usuario.id WHERE jogo_participante.jogo = $jogoId",
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
	
	private  function getJogadoresById($jogoId) {
		$result = mysql_db_query(DatabaseConfig::name, "SELECT usuario.id as id, username, usuario.fichas as fichas_usuario, jogando_participante.fichas as fichas_partida, cartas, timestamp,
		(SELECT usuario_vez FROM jogando WHERE jogo = $jogoId) as vez
			FROM jogando_participante INNER JOIN usuario ON jogando_participante.usuario = usuario.id
			INNER JOIN jogo ON jogo.id = jogando_participante.jogo WHERE jogo = $jogoId AND
			timeout = 0 OR
			timestamp >= DATE_SUB(NOW(), INTERVAL timeout MINUTE)",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$jogadores = array();
		while($jogador = mysql_fetch_assoc($result)){
			$jogadores[] = $jogador;
		}
		if(!$this->validate($jogadores, 'jogo invalido')) return; //condicional break
		$jogadoresObjs = array();
		foreach ($jogadores as $jogador) {
			$jogadoresObjs[] = new Jogador(new Usuario($jogador['id'], $jogador['username'], $jogador['fichas_usuario']),
					 $jogador['fichas_partida'], explode(',', $jogador['cartas']), $jogador['vez'] == $jogador['id'], strtotime($jogador['timestamp']));
		}
		return $jogadoresObjs;
	}
	
	//TODO private
	public function getJogandoById($jogoId) {
		$jogo = $this->getJogoById($jogoId, true);
		if(!$jogo) return; //condicional break
		$jogadores = $this->getJogadoresById($jogoId);
		if(!$jogadores) return; //condicional break
		$result = mysql_db_query(DatabaseConfig::name, "SELECT cartas_mesa, fichas FROM jogando WHERE jogo = $jogoId",
				$this->mysqlLink);
		if(!$this->proxy($result)) return; //condicional break
		$jogando = mysql_fetch_assoc($result);
		if(!$this->validate($jogando, 'jogo invalido')) return; //condicional break
		$jogandoFactory = new JogandoFactory($jogo, $jogadores, explode(',', $jogando['cartas_mesa']), $jogando['fichas']);
		return $jogandoFactory->getJogando();
	}
	
	private function updateFichas(Usuario $usuario) {
		$result = mysql_db_query(DatabaseConfig::name, "UPDATE usuario SET fichas = '".$usuario->getFichas()."' WHERE id = '".$usuario->getId()."'",
				$this->mysqlLink);
		return $this->proxy($result);
	}
	
	private function updateJogoStatus($jogoId, $status) {
		$result = mysql_db_query(DatabaseConfig::name, "UPDATE jogo SET status = '$status' WHERE id = '$jogoId'",
				$this->mysqlLink);
		return $this->proxy($result);
	}
	
}