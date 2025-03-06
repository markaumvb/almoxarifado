<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private $stmt;
    private $error;
    private $inTransaction = false;

    public function __construct() {
        // Usar constantes definidas no config.php
        $this->host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $this->db_name = defined('DB_NAME') ? DB_NAME : 'almoxarifado';
        $this->username = defined('DB_USER') ? DB_USER : 'almoxarifado';
        $this->password = defined('DB_PASS') ? DB_PASS : 'Almoxarifado1*';
        
        // Criar conexão
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            die("ERRO: Não foi possível conectar ao banco de dados. " . $this->error);
        }
    }

    // Preparar declaração com query
    public function query($sql) {
        $this->stmt = $this->conn->prepare($sql);
        return $this;
    }

    // Bind values
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    // Executar prepared statement
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            throw new PDOException($this->error);
        }
    }

    // Obter resultados como array de objetos
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // Obter registro único como objeto
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // Obter contagem de linhas
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Obter último ID inserido
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }

    // Iniciar transação
    public function beginTransaction() {
        $this->inTransaction = true;
        return $this->conn->beginTransaction();
    }

    // Commit transação
    public function commit() {
        $this->inTransaction = false;
        return $this->conn->commit();
    }

    // Rollback transação
    public function rollBack() {
        $this->inTransaction = false;
        return $this->conn->rollBack();
    }

    // Verificar se há uma transação em andamento
    public function inTransaction() {
        return $this->inTransaction;
    }

    // Verificar se uma tabela existe
    public function tableExists($table) {
        $sql = "SHOW TABLES LIKE :table";
        $this->query($sql);
        $this->bind(':table', $table);
        $this->execute();
        return $this->rowCount() > 0;
    }
}