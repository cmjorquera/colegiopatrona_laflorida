-- Ejecutar MANUALMENTE en phpMyAdmin sobre crist668_colegio_patrona_laflorida.
-- Reutiliza la tabla seccion del CMS de Tabancura. No crea tablas.
-- Modal desactivado por defecto: no aparecerá hasta que lo actives y publiques contenido.
INSERT INTO seccion
    (id_institucion, nombre_interno, titulo_admin, tipo_seccion, visible, estado, editable, orden)
SELECT
    2, 'modal_institucional', 'Modal institucional', 'content', 'no', 'activo', 'si',
    COALESCE((SELECT MAX(s2.orden)+1 FROM seccion s2 WHERE s2.id_institucion=2), 12)
WHERE NOT EXISTS (
    SELECT 1 FROM seccion s3
    WHERE s3.id_institucion=2 AND s3.nombre_interno='modal_institucional'
);

-- Comprobación:
SELECT id_seccion,nombre_interno,titulo_admin,tipo_seccion,visible,estado,editable,orden
FROM seccion WHERE id_institucion=2 AND nombre_interno='modal_institucional';
