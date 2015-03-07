<?php
namespace classes;
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
	}
	
	public function logout($token) {
		$result = mysql_db_query(DatabaseConfig::name, "DELETE FROM token WHERE id = '$token'", $this->mysqlLink);
		if($this->proxy($result)) echo "sucesso";
	}
	
	public function tokenRefresh($token) {
		$result = mysql_db_query(DatabaseConfig::name, "UPDATE token SET timestamp = now() WHERE id = '$token'",
				$this->mysqlLink);
		$this->proxy($result);
	}
	
	public function tokenCollectGarbage() {
		$result = mysql_db_query(DatabaseConfig::name, "DELETE FROM token WHERE timestamp < DATE_SUB(now(), INTERVAL
				".DatabaseConfig::tokenTime." HOUR)", $this->mysqlLink);
		$this->proxy($result);
	}
	
	private function proxy($result) {
		if(!$result) echo '{erro: '.mysql_error($this->mysqlLink).'}';
		return $result;
	}
	
	private function validate($data, $erroMsg) {
		if(!$data) echo "{erro: $erroMsg}";
		return $data;
	}
}