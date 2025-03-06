<?php
class Saida {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Registrar saída
    public function add($data) {
        try {
            $this->db->beginTransaction();
            
            $this->db->query('INSERT INTO SAIDA (CODIGO, QTDE, ID_SERVIDOR, DATA, OBS) 
                            VALUES (:codigo, :qtde, :id_servidor, :data, :obs)');
            
            $this->db->bind(':codigo', $data['codigo']);
            $this->db->bind(':qtde', $data['qtde']);
            $this->db->bind(':id_servidor', $data['id_servidor']);
            $this->db->bind(':data', $data['data']);
            $this->db->bind(':obs', $data['obs']);
            
            $this->db->execute();
            $saida_id = $this->db->lastInsertId();
            
            // Atualizar saldo do item
            $item = new Item();
            if (!$item->updateSaldo($data['codigo'], $data['qtde'], 'saida')) {
                $this->db->rollBack();
                return false;
            }
            
            $this->db->commit();
            return $saida_id;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Erro ao registrar saída: ' . $e->getMessage());
            return false;
        }
    }

    // Atualizar saída
    public function update($data) {
        try {
            $this->db->beginTransaction();
            
            // Obter dados da saída atual
            $this->db->query('SELECT * FROM SAIDA WHERE ID = :id');
            $this->db->bind(':id', $data['id']);
            $saidaAtual = $this->db->single();
            
            if (!$saidaAtual) {
                $this->db->rollBack();
                return false;
            }
            
            // Calcular diferença de quantidade
            $qtdeDiff = $data['qtde'] - $saidaAtual['QTDE'];
            
            // Atualizar saída
            $this->db->query('UPDATE SAIDA 
                            SET QTDE = :qtde
                            WHERE ID = :id');
            
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':qtde', $data['qtde']);
            
            $this->db->execute();
            
            // Se a quantidade mudou, atualizar o saldo
            if ($qtdeDiff != 0) {
                $item = new Item();
                $operacao = $qtdeDiff < 0 ? 'entrada' : 'saida';
                $qtdeAbs = abs($qtdeDiff);
                
                if (!$item->updateSaldo($saidaAtual['CODIGO'], $qtdeAbs, $operacao)) {
                    $this->db->rollBack();
                    return false;
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Erro ao atualizar saída: ' . $e->getMessage());
            return false;
        }
    }

    // Excluir saída
    public function delete($id) {
        try {
            $this->db->beginTransaction();
            
            // Obter dados da saída
            $this->db->query('SELECT * FROM SAIDA WHERE ID = :id');
            $this->db->bind(':id', $id);
            $saida = $this->db->single();
            
            if (!$saida) {
                $this->db->rollBack();
                return false;
            }
            
            // Excluir saída
            $this->db->query('DELETE FROM SAIDA WHERE ID = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            
            // Atualizar saldo do item (devolver ao estoque)
            $item = new Item();
            if (!$item->updateSaldo($saida['CODIGO'], $saida['QTDE'], 'entrada')) {
                $this->db->rollBack();
                return false;
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Erro ao excluir saída: ' . $e->getMessage());
            return false;
        }
    }

    // Obter saídas por período
    public function getSaidasByPeriodo($dataInicio, $dataFim) {
        try {
            $this->db->query('SELECT s.*, i.NOME as item_nome, sv.NOME as servidor_nome, se.NOME as setor_nome 
                            FROM SAIDA s
                            JOIN ITENS i ON s.CODIGO = i.CODIGO
                            JOIN SERVIDOR sv ON s.ID_SERVIDOR = sv.ID
                            LEFT JOIN SETOR se ON sv.ID_SETOR = se.ID
                            WHERE s.DATA BETWEEN :data_inicio AND :data_fim
                            ORDER BY s.DATA DESC');
            
            $this->db->bind(':data_inicio', $dataInicio);
            $this->db->bind(':data_fim', $dataFim);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar saídas por período: ' . $e->getMessage());
            return [];
        }
    }

    // Obter saídas agrupadas por item
    public function getSaidasAgrupadasPorItem($dataInicio, $dataFim) {
        try {
            $this->db->query('SELECT i.CODIGO, i.NOME as item_nome, SUM(s.QTDE) as total_qtde
                            FROM SAIDA s
                            JOIN ITENS i ON s.CODIGO = i.CODIGO
                            WHERE s.DATA BETWEEN :data_inicio AND :data_fim
                            GROUP BY i.CODIGO, i.NOME
                            ORDER BY total_qtde DESC');
            
            $this->db->bind(':data_inicio', $dataInicio);
            $this->db->bind(':data_fim', $dataFim);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar saídas agrupadas por item: ' . $e->getMessage());
            return [];
        }
    }

    // Obter saídas agrupadas por setor
    public function getSaidasAgrupadasPorSetor($dataInicio, $dataFim) {
        try {
            $this->db->query('SELECT se.ID, se.NOME as setor_nome, SUM(s.QTDE) as total_qtde
                            FROM SAIDA s
                            JOIN SERVIDOR sv ON s.ID_SERVIDOR = sv.ID
                            JOIN SETOR se ON sv.ID_SETOR = se.ID
                            WHERE s.DATA BETWEEN :data_inicio AND :data_fim
                            GROUP BY se.ID, se.NOME
                            ORDER BY total_qtde DESC');
            
            $this->db->bind(':data_inicio', $dataInicio);
            $this->db->bind(':data_fim', $dataFim);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar saídas agrupadas por setor: ' . $e->getMessage());
            return [];
        }
    }

    // Obter saídas agrupadas por data
    public function getSaidasAgrupadasPorData($dataInicio, $dataFim) {
        try {
            $this->db->query('SELECT DATE(s.DATA) as data, COUNT(*) as total_saidas, SUM(s.QTDE) as total_qtde
                            FROM SAIDA s
                            WHERE s.DATA BETWEEN :data_inicio AND :data_fim
                            GROUP BY DATE(s.DATA)
                            ORDER BY data');
            
            $this->db->bind(':data_inicio', $dataInicio);
            $this->db->bind(':data_fim', $dataFim);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar saídas agrupadas por data: ' . $e->getMessage());
            return [];
        }
    }
}