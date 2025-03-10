<?php
require_once __DIR__ . '/Database.php';

class Item {
    private $db;
    private $table = 'ITENS';

    public function __construct() {
        $this->db = new Database();
    }

    // Obter todos os itens com informação da unidade
    public function getItems() {
        try {
            $this->db->query("SELECT i.*, u.NOME as unidade_nome 
                FROM {$this->table} i 
                LEFT JOIN UNIDADE_MEDIDA u ON i.ID_UNIDADE = u.ID
                ORDER BY i.NOME");
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar itens: ' . $e->getMessage());
            return [];
        }
    }

    // Obter item por ID
    public function getItemById($id) {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE ID = :id");
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Erro ao buscar item por ID: ' . $e->getMessage());
            return false;
        }
    }

    // Obter item por código - Método otimizado
    public function getItemByCodigo($codigo) {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE CODIGO = :codigo");
            $this->db->bind(':codigo', $codigo);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Erro ao buscar item por código: ' . $e->getMessage());
            return false;
        }
    }
    
    // Buscar itens por texto (nome ou código) - Método otimizado
    public function searchItems($search) {
        try {
            $search = '%' . trim($search) . '%';
            $this->db->query("SELECT i.*, u.NOME as unidade_nome 
                             FROM {$this->table} i 
                             LEFT JOIN UNIDADE_MEDIDA u ON i.ID_UNIDADE = u.ID
                             WHERE i.NOME LIKE :search 
                             OR i.CODIGO LIKE :search
                             ORDER BY 
                                CASE WHEN i.CODIGO = :exact_match THEN 0
                                     WHEN i.CODIGO LIKE :start_match THEN 1
                                     ELSE 2
                                END,
                                i.NOME
                             LIMIT 15");
            
            // Bind todos os parâmetros de uma vez
            $this->db->bindParams([
                ':search' => $search,
                ':exact_match' => trim($search, '%'),
                ':start_match' => trim($search, '%') . '%'
            ]);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao pesquisar itens: ' . $e->getMessage());
            return [];
        }
    }
    
    // Converter tipo numérico para texto
    public function getTipoText($tipo) {
        $tipos = [
            1 => 'Consumo',
            2 => 'Equipamento',
            3 => 'Empenho'
        ];
        
        return isset($tipos[$tipo]) ? $tipos[$tipo] : 'Desconhecido';
    }

    // Adicionar item
    public function add($data) {
        try {
            $this->db->query("INSERT INTO {$this->table} 
                (CODIGO, NOME, TIPO, ID_UNIDADE, SALDO, SALDO_MINIMO, SALDO_MAXIMO) 
                VALUES (:codigo, :nome, :tipo, :id_unidade, :saldo, :saldo_minimo, :saldo_maximo)");
            
            // Bind todos os parâmetros de uma vez
            $this->db->bindParams([
                ':codigo' => $data['codigo'],
                ':nome' => $data['nome'],
                ':tipo' => $data['tipo'],
                ':id_unidade' => $data['id_unidade'],
                ':saldo' => $data['saldo'],
                ':saldo_minimo' => $data['saldo_minimo'],
                ':saldo_maximo' => $data['saldo_maximo']
            ]);

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
            $this->db->query("UPDATE {$this->table} 
                            SET CODIGO = :codigo, 
                                NOME = :nome, 
                                TIPO = :tipo, 
                                ID_UNIDADE = :id_unidade, 
                                SALDO = :saldo, 
                                SALDO_MINIMO = :saldo_minimo, 
                                SALDO_MAXIMO = :saldo_maximo 
                            WHERE ID = :id");
            
            // Bind todos os parâmetros
            $this->db->bindParams([
                ':id' => $data['id'],
                ':codigo' => $data['codigo'],
                ':nome' => $data['nome'],
                ':tipo' => $data['tipo'],
                ':id_unidade' => $data['id_unidade'],
                ':saldo' => $data['saldo'],
                ':saldo_minimo' => $data['saldo_minimo'],
                ':saldo_maximo' => $data['saldo_maximo']
            ]);

            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar item: ' . $e->getMessage());
            return false;
        }
    }

    // Deletar item
    public function delete($id) {
        try {
            // Verificar se o item está sendo usado em saídas
            $this->db->query("SELECT i.CODIGO FROM {$this->table} i 
                              WHERE i.ID = :id");
            $this->db->bind(':id', $id);
            $item = $this->db->single();
            
            if (!$item) {
                return false;
            }
            
            $this->db->query("SELECT COUNT(*) as count FROM SAIDA 
                             WHERE CODIGO = :codigo");
            $this->db->bind(':codigo', $item['CODIGO']);
            $result = $this->db->single();
            
            if ($result && $result['count'] > 0) {
                return false; // Item tem saídas registradas
            }

            $this->db->query("DELETE FROM {$this->table} WHERE ID = :id");
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
            $this->db->beginTransaction();
            
            $item = $this->getItemByCodigo($codigo);
            
            if(!$item) {
                $this->db->rollBack();
                return false;
            }
            
            $novoSaldo = $operacao == 'entrada' 
                ? $item['SALDO'] + $qtde 
                : $item['SALDO'] - $qtde;
            
            $this->db->query("UPDATE {$this->table} SET SALDO = :saldo WHERE CODIGO = :codigo");
            $this->db->bind(':saldo', $novoSaldo);
            $this->db->bind(':codigo', $codigo);
            
            $result = $this->db->execute();
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao atualizar saldo: ' . $e->getMessage());
            return false;
        }
    }
    
    // Método para obter itens com estoque baixo
    public function getItensEstoqueBaixo() {
        try {
            $this->db->query("SELECT i.*, u.NOME as unidade_nome 
                FROM {$this->table} i 
                LEFT JOIN UNIDADE_MEDIDA u ON i.ID_UNIDADE = u.ID
                WHERE i.SALDO <= i.SALDO_MINIMO AND i.SALDO_MINIMO > 0
                ORDER BY i.SALDO/i.SALDO_MINIMO ASC");
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar itens com estoque baixo: ' . $e->getMessage());
            return [];
        }
    }
}