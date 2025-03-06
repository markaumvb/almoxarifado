<?php
class Unidade {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Obter todas as unidades
    public function getUnidades() {
        try {
            $this->db->query('SELECT * FROM UNIDADE_MEDIDA ORDER BY NOME');
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar unidades: ' . $e->getMessage());
            return [];
        }
    }

    // Obter unidade por ID
    public function getUnidadeById($id) {
        try {
            $this->db->query('SELECT * FROM UNIDADE_MEDIDA WHERE ID = :id');
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Erro ao buscar unidade por ID: ' . $e->getMessage());
            return false;
        }
    }

    // Adicionar unidade
    public function add($data) {
        try {
            $this->db->query('INSERT INTO UNIDADE_MEDIDA (NOME, SIGLA) VALUES (:nome, :sigla)');
            $this->db->bind(':nome', $data['nome']);
            $this->db->bind(':sigla', $data['sigla']);

            if($this->db->execute()) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log('Erro ao adicionar unidade: ' . $e->getMessage());
            return false;
        }
    }

    // Atualizar unidade
    public function update($data) {
        try {
            $this->db->query('UPDATE UNIDADE_MEDIDA SET NOME = :nome, SIGLA = :sigla WHERE ID = :id');
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':nome', $data['nome']);
            $this->db->bind(':sigla', $data['sigla']);

            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar unidade: ' . $e->getMessage());
            return false;
        }
    }

    // Deletar unidade
    public function delete($id) {
        try {
            // Verificar se há itens associados
            $this->db->query('SELECT COUNT(*) as count FROM ITENS WHERE ID_UNIDADE = :id');
            $this->db->bind(':id', $id);
            $result = $this->db->single();
            
            if($result['count'] > 0) {
                return false; // Não permite excluir se houver itens associados
            }
            
            $this->db->query('DELETE FROM UNIDADE_MEDIDA WHERE ID = :id');
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao deletar unidade: ' . $e->getMessage());
            return false;
        }
    }
}