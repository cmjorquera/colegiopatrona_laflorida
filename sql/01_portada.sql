-- ESQUEMA PROPUESTO PARA PORTADA PATRONA DE LOURDES
-- Ejecutar MANUALMENTE en una base de datos NUEVA y propia.
-- No contiene datos ni credenciales de Tabancura.
-- Revisar antes de ejecutar; no ha sido aplicado a ningún servidor.
CREATE TABLE IF NOT EXISTS portada_contenedores (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 clave VARCHAR(100) NOT NULL UNIQUE,
 tipo VARCHAR(60) NOT NULL,
 titulo VARCHAR(200) DEFAULT NULL,
 orden INT NOT NULL DEFAULT 0,
 visible TINYINT(1) NOT NULL DEFAULT 1,
 configuracion_json JSON NOT NULL,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_portada_orden (visible, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portada_medios (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 contenedor_id BIGINT UNSIGNED DEFAULT NULL,
 ruta VARCHAR(500) NOT NULL,
 tipo ENUM('imagen','video','documento') NOT NULL,
 titulo VARCHAR(200) DEFAULT NULL,
 texto_alternativo VARCHAR(255) DEFAULT NULL,
 orden INT NOT NULL DEFAULT 0,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_portada_medios_contenedor FOREIGN KEY (contenedor_id) REFERENCES portada_contenedores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
