<?php
class Servidor {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Obter todos os servidores
    public function getServidores() {
        try {
            $this->db->query('SELECT s.*, st.NOME as SETOR_NOME 
                              FROM SERVIDOR s 
                              LEFT JOIN SETOR st ON s.ID_SETOR = st.ID
                              ORDER BY s.NOME');
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar servidores: ' . $e->getMessage());
            return [];
        }
    }

    // Obter servidor por ID
    public function getServidorById($id) {
        try {
            $this->db->query('SELECT * FROM SERVIDOR WHERE ID = :id');
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Erro ao buscar servidor por ID: ' . $e->getMessage());
            return false;
        }
    }

    // Adicionar servidor
    public function add($data) {
        try {
            $this->db->query('INSERT INTO SERVIDOR (NOME, MATRICULA, ID_SETOR, EMAIL, STATUS) 
                            VALUES (:nome, :matricula, :id_setor, :email, :status)');
            
            $this->db->bind(':nome', $data['nome']);
            $this->db->bind(':matricula', $data['matricula']);
            $this->db->bind(':id_setor', $data['id_setor']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':status', $data['status']);

            if($this->db->execute()) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log('Erro ao adicionar servidor: ' . $e->getMessage());
            return false;
        }
    }

    // Atualizar servidor
    public function update($data) {
        try {
            $this->db->query('UPDATE SERVIDOR 
                            SET NOME = :nome, 
                                MATRICULA = :matricula, 
                                ID_SETOR = :id_setor, 
                                EMAIL = :email, 
                                STATUS = :status 
                            WHERE ID = :id');
            
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':nome', $data['nome']);
            $this->db->bind(':matricula', $data['matricula']);
            $this->db->bind(':id_setor', $data['id_setor']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':status', $data['status']);

            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar servidor: ' . $e->getMessage());
            return false;
        }
    }

    // Deletar servidor
    public function delete($id) {
        try {
            $this->db->query('DELETE FROM SERVIDOR WHERE ID = :id');
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao deletar servidor: ' . $e->getMessage());
            return false;
        }
    }

    // Obter servidores por setor
    public function getServidoresBySetor($id_setor) {
        try {
            $this->db->query('SELECT * FROM SERVIDOR WHERE ID_SETOR = :id_setor ORDER BY NOME');
            $this->db->bind(':id_setor', $id_setor);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar servidores por setor: ' . $e->getMessage());
            return [];
        }
    }

    // Verificar se a matrícula já existe
    public function matriculaExists($matricula, $id = null) {
        try {
            if($id) {
                $this->db->query('SELECT COUNT(*) as count FROM SERVIDOR WHERE MATRICULA = :matricula AND ID != :id');
                $this->db->bind(':id', $id);
            } else {
                $this->db->query('SELECT COUNT(*) as count FROM SERVIDOR WHERE MATRICULA = :matricula');
            }
            
            $this->db->bind(':matricula', $matricula);
            $result = $this->db->single();
            
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log('Erro ao verificar matrícula: ' . $e->getMessage());
            return false;
        }
    }
}