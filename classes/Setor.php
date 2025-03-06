<?php
class Setor {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Obter todos os setores
    public function getSetores() {
        try {
            $this->db->query('SELECT * FROM SETOR ORDER BY NOME');
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar setores: ' . $e->getMessage());
            return [];
        }
    }

    // Obter setor por ID
    public function getSetorById($id) {
        try {
            $this->db->query('SELECT * FROM SETOR WHERE ID = :id');
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Erro ao buscar setor por ID: ' . $e->getMessage());
            return false;
        }
    }

    // Adicionar setor
    public function add($data) {
        try {
            $this->db->query('INSERT INTO SETOR (NOME) VALUES (:nome)');
            $this->db->bind(':nome', $data['nome']);

            if($this->db->execute()) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log('Erro ao adicionar setor: ' . $e->getMessage());
            return false;
        }
    }

    // Atualizar setor
    public function update($data) {
        try {
            $this->db->query('UPDATE SETOR SET NOME = :nome WHERE ID = :id');
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':nome', $data['nome']);

            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar setor: ' . $e->getMessage());
            return false;
        }
    }

    // Deletar setor
    public function delete($id) {
        try {
            // Verificar se há servidores associados
            $this->db->query('SELECT COUNT(*) as count FROM SERVIDOR WHERE ID_SETOR = :id');
            $this->db->bind(':id', $id);
            $result = $this->db->single();
            
            if($result['count'] > 0) {
                return false; // Não permite excluir se houver servidores associados
            }
            
            $this->db->query('DELETE FROM SETOR WHERE ID = :id');
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao deletar setor: ' . $e->getMessage());
            return false;
        }
    }
}