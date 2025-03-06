<?php
// classes/User.php
class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Registrar usuário
    public function register($data) {
        // Preparar query
        $this->db->query("INSERT INTO usuarios (nome, username, password, email, tipo) 
                         VALUES (:nome, :username, :password, :email, :tipo)");
        
        // Hash de senha
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Bind values
        $this->db->bind(':nome', $data['nome']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':tipo', $data['tipo']);
        
        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Login de usuário
    public function login($username, $password) {
        // Preparar query
        $this->db->query("SELECT * FROM usuarios WHERE username = :username");
        
        // Bind value
        $this->db->bind(':username', $username);
        
        $row = $this->db->single();
        
        // Verificar se encontrou usuário
        if (!$row) {
            return false;
        }
        
        // Verificar senha
        $hashedPassword = $row['password'];
        if (password_verify($password, $hashedPassword)) {
            return $row;
        } else {
            return false;
        }
    }
    
    // Encontrar usuário por username
    public function findUserByUsername($username) {
        $this->db->query("SELECT COUNT(*) as count FROM usuarios WHERE username = :username");
        $this->db->bind(':username', $username);
        $row = $this->db->single();
        return $row['count'] > 0;
    }
    
    // Encontrar usuário por email
    public function findUserByEmail($email) {
        $this->db->query("SELECT COUNT(*) as count FROM usuarios WHERE email = :email");
        $this->db->bind(':email', $email);
        $row = $this->db->single();
        return $row['count'] > 0;
    }
    
    // Obter usuário por ID
    public function getUserById($id) {
        $this->db->query("SELECT * FROM usuarios WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    // Obter todos os usuários
    public function getUsers() {
        $this->db->query("SELECT * FROM usuarios ORDER BY nome");
        return $this->db->resultSet();
    }
    
    // Atualizar usuário
    public function update($data) {
        // Se a senha estiver sendo atualizada
        if (!empty($data['password'])) {
            // Preparar query com senha
            $this->db->query("UPDATE usuarios 
                             SET nome = :nome, 
                                 username = :username, 
                                 password = :password, 
                                 email = :email, 
                                 tipo = :tipo 
                             WHERE id = :id");
            
            // Hash de senha
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Bind values
            $this->db->bind(':password', $hashedPassword);
        } else {
            // Preparar query sem senha
            $this->db->query("UPDATE usuarios 
                             SET nome = :nome, 
                                 username = :username, 
                                 email = :email, 
                                 tipo = :tipo 
                             WHERE id = :id");
        }
        
        // Bind values comuns
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':nome', $data['nome']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':tipo', $data['tipo']);
        
        // Execute
        return $this->db->execute();
    }
    
    // Excluir usuário
    public function delete($id) {
        // Preparar query
        $this->db->query("DELETE FROM usuarios WHERE id = :id");
        
        // Bind value
        $this->db->bind(':id', $id);
        
        // Execute
        return $this->db->execute();
    }
}