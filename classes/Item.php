<?php
require_once __DIR__ . '/Database.php';
class Item {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Obter todos os itens
    public function getItems() {
        try {
            $this->db->query('SELECT i.*, u.NOME as UNIDADE_NOME 
            FROM ITENS i 
            LEFT JOIN UNIDADE_MEDIDA u ON i.ID_UNIDADE = u.ID
            ORDER BY i.NOME');
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar itens: ' . $e->getMessage());
            return [];
        }
    }

    // Obter item por ID
    public function getItemById($id) {
        try {
            $this->db->query('SELECT * FROM ITENS WHERE ID = :id');
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Erro ao buscar item por ID: ' . $e->getMessage());
            return false;
        }
    }

    public function getItemByCodigo($codigo) {
        try {
            $this->db->query('SELECT * FROM ITENS WHERE CODIGO = :codigo');
            $this->db->bind(':codigo', $codigo);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Erro ao buscar item por código: ' . $e->getMessage());
            return false;
        }
    }
    
    // Buscar itens por texto (nome ou código)
    public function searchItems($search) {
        try {
            $this->db->query('SELECT i.*, u.NOME as unidade_nome 
                             FROM ITENS i 
                             LEFT JOIN UNIDADE_MEDIDA u ON i.ID_UNIDADE = u.ID
                             WHERE i.NOME LIKE :search 
                             OR i.CODIGO LIKE :search
                             ORDER BY i.NOME
                             LIMIT 15');
            
            $searchPattern = '%' . $search . '%';
            $this->db->bind(':search', $searchPattern);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao pesquisar itens: ' . $e->getMessage());
            return [];
        }
    }
    
    // Verificar se há saldo suficiente
    public function checkSaldo($codigo, $quantidade) {
        try {
            $item = $this->getItemByCodigo($codigo);
            
            if (!$item) {
                return false;
            }
            
            return $item['SALDO'] >= $quantidade;
        } catch (PDOException $e) {
            error_log('Erro ao verificar saldo: ' . $e->getMessage());
            return false;
        }
    }

    // Adicionar item
    public function add($data) {
        try {
            $this->db->query('INSERT INTO ITENS (CODIGO, NOME, TIPO, ID_UNIDADE, SALDO, SALDO_MINIMO, SALDO_MAXIMO) 
                            VALUES (:codigo, :nome, :tipo, :id_unidade, :saldo, :saldo_minimo, :saldo_maximo)');
            
            $this->db->bind(':codigo', $data['codigo']);
            $this->db->bind(':nome', $data['nome']);
            $this->db->bind(':tipo', $data['tipo']);
            $this->db->bind(':id_unidade', $data['id_unidade']);
            $this->db->bind(':saldo', $data['saldo']);
            $this->db->bind(':saldo_minimo', $data['saldo_minimo']);
            $this->db->bind(':saldo_maximo', $data['saldo_maximo']);

            if($this->db->execute()) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log('Erro ao adicionar item: ' . $e->getMessage());
            return false;
        }
    }

    // Atualizar item
    public function update($data) {
        try {
            $this->db->query('UPDATE ITENS 
                            SET CODIGO = :codigo, 
                                NOME = :nome, 
                                TIPO = :tipo, 
                                ID_UNIDADE = :id_unidade, 
                                SALDO = :saldo, 
                                SALDO_MINIMO = :saldo_minimo, 
                                SALDO_MAXIMO = :saldo_maximo 
                            WHERE ID = :id');
            
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':codigo', $data['codigo']);
            $this->db->bind(':nome', $data['nome']);
            $this->db->bind(':tipo', $data['tipo']);
            $this->db->bind(':id_unidade', $data['id_unidade']);
            $this->db->bind(':saldo', $data['saldo']);
            $this->db->bind(':saldo_minimo', $data['saldo_minimo']);
            $this->db->bind(':saldo_maximo', $data['saldo_maximo']);

            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar item: ' . $e->getMessage());
            return false;
        }
    }


    public function delete($id) {
        try {
            $this->db->query('DELETE FROM ITENS WHERE ID = :id');
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao deletar item: ' . $e->getMessage());
            return false;
        }
    }

    // Atualizar saldo do item
    public function updateSaldo($codigo, $qtde, $operacao = 'saida') {
        try {
            $item = $this->getItemByCodigo($codigo);
            
            if(!$item) {
                return false;
            }
            
            $novoSaldo = $operacao == 'entrada' 
                ? $item['SALDO'] + $qtde 
                : $item['SALDO'] - $qtde;
            
            $this->db->query('UPDATE ITENS SET SALDO = :saldo WHERE CODIGO = :codigo');
            $this->db->bind(':saldo', $novoSaldo);
            $this->db->bind(':codigo', $codigo);
            
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar saldo: ' . $e->getMessage());
            return false;
        }
    }

}