<?php
namespace classes;
require_once 'classes/Usuario.php';

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
			echo mysql_insert_id($this->mysqlLink);
			return true;
		}
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
	
	private function updateFichas(Usuario $usuario) {
		$result = mysql_db_query(DatabaseConfig::name, "UPDATE usuario SET fichas = '".$usuario->getFichas()."' WHERE id = '".$usuario->getId()."'",
				$this->mysqlLink);
		$this->proxy($result);
	}
}