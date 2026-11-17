-- ETAPA 3: ejecutar manualmente en phpMyAdmin, BD crist668_colegio_patrona_laflorida
-- No modifica tablas existentes ni carga contenido ficticio.
CREATE TABLE IF NOT EXISTS patrona_hero_config (
 id_seccion INT NOT NULL PRIMARY KEY,
 id_institucion INT NOT NULL,
 datos_json LONGTEXT NOT NULL,
 actualizado_por INT NULL,
 actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT chk_patrona_hero_json CHECK (JSON_VALID(datos_json)),
 INDEX idx_patrona_hero_institucion (id_institucion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Verificar el identificador real antes de guardar:
SELECT id_seccion, nombre_interno, editable FROM seccion
WHERE id_institucion=2 AND nombre_interno='hero_principal';
