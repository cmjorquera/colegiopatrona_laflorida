-- ETAPA 3 - ALINEAR HERO CON TABANCURA
-- Ejecutar manualmente en phpMyAdmin sobre crist668_colegio_patrona_laflorida.
-- No crea tablas ni toca otros colegios ni otros contenedores.
-- Revisión previa (solo lectura):
SELECT id_seccion,id_institucion,nombre_interno,tipo_seccion,usa_config,usa_items
FROM seccion WHERE id_institucion=2 AND nombre_interno='hero_principal';
-- Ajustar metadatos a la estructura de Tabancura.
UPDATE seccion SET tipo_seccion='carousel', usa_config='si', usa_items='si'
WHERE id_institucion=2 AND nombre_interno='hero_principal';
-- No insertar configuraciones por defecto: el editor permite guardarlas.
-- Verificación:
SELECT id_seccion,nombre_interno,tipo_seccion,usa_config,usa_items
FROM seccion WHERE id_institucion=2 AND nombre_interno='hero_principal';
