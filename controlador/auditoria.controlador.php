<?php
class ControladorAuditoria
{
    public static function ctrListar(array $filtros = [], int $limit = 100, int $offset = 0)
    {
        // Normaliza formatos de fecha YYYY-MM-DD
        $fx = [
            'usuario_id' => (isset($filtros['usuario_id']) && $filtros['usuario_id'] !== '') ? (int)$filtros['usuario_id'] : null,
            'modulo'     => isset($filtros['modulo']) ? trim($filtros['modulo']) : null,
            'accion'     => isset($filtros['accion']) ? trim($filtros['accion']) : null,
            'tabla'      => isset($filtros['tabla']) ? trim($filtros['tabla']) : null,
            'texto'      => isset($filtros['texto']) ? trim($filtros['texto']) : null,
            'desde'      => null,
            'hasta'      => null,
        ];

        if (!empty($filtros['desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtros['desde'])) {
            $fx['desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtros['hasta'])) {
            $fx['hasta'] = $filtros['hasta'];
        }

        return ModeloAuditoria::mdlListar($fx, $limit, $offset);
    }

    public static function ctrDistinct(string $campo): array
    {
        return ModeloAuditoria::mdlDistinct($campo);
    }
}
