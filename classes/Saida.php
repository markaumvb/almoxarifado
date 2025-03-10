<?php
class Saida {
    private $db;
    private $table = 'SAIDA';

    public function __construct() {
        $this->db = new Database();
    }

    // Registrar saída - Método otimizado
    public function add($data) {
        try {
            $this->db->beginTransaction();
            
            // Verificar se o item existe
            $item = new Item();
            $itemData = $item->getItemByCodigo($data['codigo']);
            
            if (!$itemData) {
                $this->db->rollBack();
                return false;
            }
            
            // Inserir a saída
            $this->db->query("INSERT INTO {$this->table} (CODIGO, QTDE, ID_SERVIDOR, DATA, OBS) 
                            VALUES (:codigo, :qtde, :id_servidor, :data, :obs)");
            
            $this->db->bindParams([
                ':codigo' => $data['codigo'],
                ':qtde' => $data['qtde'],
                ':id_servidor' => $data['id_servidor'],
                ':data' => $data['data'],
                ':obs' => $data['obs']
            ]);
            
            $this->db->execute();
            $saida_id = $this->db->lastInsertId();
            
            // Atualizar saldo do item
            if (!$item->updateSaldo($data['codigo'], $data['qtde'], 'saida')) {
                $this->db->rollBack();
                return false;
            }
            
            $this->db->commit();
            return $saida_id;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao registrar saída: ' . $e->getMessage());
            return false;
        }
    }

    // Atualizar saída - Método otimizado
    public function update($data) {
        try {
            $this->db->beginTransaction();
            
            // Obter dados da saída atual
            $this->db->query("SELECT * FROM {$this->table} WHERE ID = :id");
            $this->db->bind(':id', $data['id']);
            $saidaAtual = $this->db->single();
            
            if (!$saidaAtual) {
                $this->db->rollBack();
                return false;
            }
            
            // Calcular diferença de quantidade
            $qtdeDiff = $data['qtde'] - $saidaAtual['QTDE'];
            
            // Atualizar saída
            $this->db->query("UPDATE {$this->table} 
                            SET QTDE = :qtde, 
                                OBS = :obs,
                                ID_SERVIDOR = :id_servidor,
                                DATA = :data
                            WHERE ID = :id");
            
            $this->db->bindParams([
                ':id' => $data['id'],
                ':qtde' => $data['qtde'],
                ':obs' => $data['obs'],
                ':id_servidor' => $data['id_servidor'],
                ':data' => $data['data']
            ]);
            
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
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao atualizar saída: ' . $e->getMessage());
            return false;
        }
    }

    // Excluir saída - Método otimizado
    public function delete($id) {
        try {
            $this->db->beginTransaction();
            
            // Obter dados da saída
            $this->db->query("SELECT * FROM {$this->table} WHERE ID = :id");
            $this->db->bind(':id', $id);
            $saida = $this->db->single();
            
            if (!$saida) {
                $this->db->rollBack();
                return false;
            }
            
            // Excluir saída
            $this->db->query("DELETE FROM {$this->table} WHERE ID = :id");
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
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao excluir saída: ' . $e->getMessage());
            return false;
        }
    }

    // Obter saída por ID
    public function getSaidaById($id) {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE ID = :id");
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Erro ao buscar saída por ID: ' . $e->getMessage());
            return false;
        }
    }

    // Obter saídas por período - Método otimizado com JOIN único
    public function getSaidasByPeriodo($dataInicio, $dataFim) {
        try {
            $this->db->query("SELECT s.*, 
                            i.NOME as item_nome, 
                            sv.NOME as servidor_nome, 
                            se.NOME as setor_nome 
                        FROM {$this->table} s
                        LEFT JOIN ITENS i ON s.CODIGO = i.CODIGO
                        LEFT JOIN SERVIDOR sv ON s.ID_SERVIDOR = sv.ID
                        LEFT JOIN SETOR se ON sv.ID_SETOR = se.ID
                        WHERE s.DATA BETWEEN :data_inicio AND :data_fim
                        ORDER BY s.DATA DESC, s.ID DESC");
            
            $this->db->bind(':data_inicio', $dataInicio);
            $this->db->bind(':data_fim', $dataFim);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar saídas por período: ' . $e->getMessage());
            return [];
        }
    }

    // Adicionar múltiplas saídas (para importação) - Método otimizado
    public function addMultiple($data) {
        try {
            $result = [
                'success' => true,
                'message' => '',
                'items_saved' => 0,
                'items_failed' => 0
            ];
            
            $this->db->beginTransaction();
            
            if (empty($data['itens'])) {
                $result['success'] = false;
                $result['message'] = 'Nenhum item para registrar';
                return $result;
            }
            
            foreach ($data['itens'] as $itemData) {
                $saida_data = [
                    'codigo' => $itemData['codigo'],
                    'qtde' => $itemData['quantidade'],
                    'id_servidor' => $data['id_servidor'],
                    'data' => $data['data'],
                    'obs' => $itemData['observacao']
                ];
                
                // Verificar item
                $item = new Item();
                $itemInfo = $item->getItemByCodigo($saida_data['codigo']);
                
                if (!$itemInfo) {
                    $result['items_failed']++;
                    continue;
                }
                
                // Inserir saída
                $this->db->query("INSERT INTO {$this->table} (CODIGO, QTDE, ID_SERVIDOR, DATA, OBS) 
                                VALUES (:codigo, :qtde, :id_servidor, :data, :obs)");
                
                $this->db->bindParams([
                    ':codigo' => $saida_data['codigo'],
                    ':qtde' => $saida_data['qtde'],
                    ':id_servidor' => $saida_data['id_servidor'],
                    ':data' => $saida_data['data'],
                    ':obs' => $saida_data['obs']
                ]);
                
                if ($this->db->execute()) {
                    // Atualizar saldo
                    $novoSaldo = $itemInfo['SALDO'] - $saida_data['qtde'];
                    
                    $this->db->query("UPDATE ITENS SET SALDO = :saldo WHERE CODIGO = :codigo");
                    $this->db->bind(':saldo', $novoSaldo);
                    $this->db->bind(':codigo', $saida_data['codigo']);
                    
                    if ($this->db->execute()) {
                        $result['items_saved']++;
                    } else {
                        $result['items_failed']++;
                    }
                } else {
                    $result['items_failed']++;
                }
            }
            
            if ($result['items_failed'] > 0) {
                $this->db->rollBack();
                $result['success'] = false;
                $result['message'] = 'Erro ao registrar alguns itens.';
            } else {
                $this->db->commit();
                $result['message'] = 'Saída registrada com sucesso. ' . $result['items_saved'] . ' item(ns) registrado(s).';
            }
            
            return $result;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao registrar múltiplas saídas: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao processar: ' . $e->getMessage(),
                'items_saved' => 0,
                'items_failed' => 0
            ];
        }
    }

    // Obter saídas agrupadas por item - Método otimizado
    public function getSaidasAgrupadasPorItem($dataInicio, $dataFim) {
        try {
            $this->db->query("SELECT i.CODIGO, i.NOME as item_nome, SUM(s.QTDE) as total_qtde,
                             COUNT(s.ID) as total_saidas
                            FROM {$this->table} s
                            JOIN ITENS i ON s.CODIGO = i.CODIGO
                            WHERE s.DATA BETWEEN :data_inicio AND :data_fim
                            GROUP BY i.CODIGO, i.NOME
                            ORDER BY total_qtde DESC");
            
            $this->db->bind(':data_inicio', $dataInicio);
            $this->db->bind(':data_fim', $dataFim);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar saídas agrupadas por item: ' . $e->getMessage());
            return [];
        }
    }

    // Obter saídas agrupadas por setor - Método otimizado
    public function getSaidasAgrupadasPorSetor($dataInicio, $dataFim) {
        try {
            $this->db->query("SELECT se.ID, se.NOME as setor_nome, 
                             SUM(s.QTDE) as total_qtde,
                             COUNT(DISTINCT s.ID_SERVIDOR) as total_servidores,
                             COUNT(s.ID) as total_saidas
                            FROM {$this->table} s
                            JOIN SERVIDOR sv ON s.ID_SERVIDOR = sv.ID
                            JOIN SETOR se ON sv.ID_SETOR = se.ID
                            WHERE s.DATA BETWEEN :data_inicio AND :data_fim
                            GROUP BY se.ID, se.NOME
                            ORDER BY total_qtde DESC");
            
            $this->db->bind(':data_inicio', $dataInicio);
            $this->db->bind(':data_fim', $dataFim);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar saídas agrupadas por setor: ' . $e->getMessage());
            return [];
        }
    }

    // Obter saídas agrupadas por data - Método otimizado
    public function getSaidasAgrupadasPorData($dataInicio, $dataFim) {
        try {
            $this->db->query("SELECT DATE(s.DATA) as data, 
                              COUNT(*) as total_saidas, 
                              SUM(s.QTDE) as total_qtde,
                              COUNT(DISTINCT s.ID_SERVIDOR) as total_servidores,
                              COUNT(DISTINCT s.CODIGO) as total_itens
                            FROM {$this->table} s
                            WHERE s.DATA BETWEEN :data_inicio AND :data_fim
                            GROUP BY DATE(s.DATA)
                            ORDER BY data");
            
            $this->db->bind(':data_inicio', $dataInicio);
            $this->db->bind(':data_fim', $dataFim);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Erro ao buscar saídas agrupadas por data: ' . $e->getMessage());
            return [];
        }
    }
}