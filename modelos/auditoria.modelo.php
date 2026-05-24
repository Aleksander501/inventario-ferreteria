<?php
require_once "conexion.php";

class ModeloAuditoria
{
    /**
     * Lista registros con filtros. Columnas esperadas en la tabla 'auditoria':
     * id_auditoria, usuario_id, nombre_usuario, fecha_hora, modulo, accion,
     * tabla_afectada, registro_id, descripcion, campos_modificados, valor_anterior, valor_nuevo
     */
    public static function mdlListar(array $fx, int $limit = 100, int $offset = 0)
    {
        $pdo = Conexion::Conectar();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $limit  = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        $sql = "
            SELECT
                id_auditoria,
                usuario_id,
                nombre_usuario,
                fecha_hora,
                modulo,
                accion,
                tabla_afectada,
                registro_id,
                descripcion,
                campos_modificados,
                valor_anterior,
                valor_nuevo
            FROM auditoria
            WHERE 1=1
        ";

        $params = [];

        if (!empty($fx['usuario_id'])) {
            $sql .= " AND usuario_id = :usuario_id";
            $params[':usuario_id'] = (int)$fx['usuario_id'];
        }
        if (!empty($fx['modulo'])) {
            $sql .= " AND modulo = :modulo";
            $params[':modulo'] = $fx['modulo'];
        }
        if (!empty($fx['accion'])) {
            $sql .= " AND accion = :accion";
            $params[':accion'] = $fx['accion'];
        }
        if (!empty($fx['tabla'])) {
            $sql .= " AND tabla_afectada LIKE :tabla";
            $params[':tabla'] = "%{$fx['tabla']}%";
        }

        // Rango de fechas (fecha_hora es DATETIME/TIMESTAMP)
        if (!empty($fx['desde'])) {
            $sql .= " AND fecha_hora >= :desde";
            $params[':desde'] = $fx['desde'] . " 00:00:00";
        }
        if (!empty($fx['hasta'])) {
            $sql .= " AND fecha_hora <= :hasta";
            $params[':hasta'] = $fx['hasta'] . " 23:59:59";
        }

        // Búsqueda de texto (LIKE)
        if (!empty($fx['texto'])) {
            $sql .= " AND (
                nombre_usuario     LIKE :q OR
                descripcion        LIKE :q OR
                modulo             LIKE :q OR
                tabla_afectada     LIKE :q OR
                accion             LIKE :q OR
                campos_modificados LIKE :q
                -- Si quieres incluir JSON (puede ser pesado):
                -- OR valor_anterior LIKE :q
                -- OR valor_nuevo    LIKE :q
            )";
            $params[':q'] = "%" . $fx['texto'] . "%";
        }

        $sql .= " ORDER BY fecha_hora DESC, id_auditoria DESC
                  LIMIT {$limit} OFFSET {$offset}";

        $stmt = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            if ($k === ':usuario_id') {
                $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Obtiene valores distintos para un campo (modulo, accion, tabla_afectada)
     */
    public static function mdlDistinct(string $campo): array
    {
        $permitidos = ['modulo', 'accion', 'tabla_afectada'];
        if (!in_array($campo, $permitidos, true)) return [];

        $pdo = Conexion::Conectar();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->query("SELECT DISTINCT {$campo} AS val FROM auditoria WHERE {$campo} IS NOT NULL AND {$campo} <> '' ORDER BY {$campo}");
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}
