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
        
        // Criar conexão com opções otimizadas
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Usar prepared statements nativos
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password, $options);
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

    // Bind values com tipagem melhorada
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value) || (is_numeric($value) && intval($value) == $value):
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

    // Bind múltiplos parâmetros de uma vez
    public function bindParams($params) {
        foreach ($params as $param => $value) {
            $this->bind($param, $value);
        }
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
    
    // Verificar se uma coluna existe
    public function columnExists($table, $column) {
        $sql = "SHOW COLUMNS FROM `$table` LIKE :column";
        $this->query($sql);
        $this->bind(':column', $column);
        $this->execute();
        return $this->rowCount() > 0;
    }
}