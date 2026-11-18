-- 16_equipo_docente.sql
-- EJECUTAR MANUALMENTE en crist668_colegio_patrona_laflorida (previo respaldo).
-- Crea el contenedor sin reemplazar datos. Idempotente.
-- No publica nombres ni fotos reales sin autorización.
INSERT INTO seccion
(id_institucion,nombre_interno,titulo_admin,tipo_seccion,visible,estado,editable,orden)
SELECT 2,'equipo_docente_home','Organización del Colegio · Equipo docente','contenido','si','activo','si',
       COALESCE((SELECT MAX(orden)+1 FROM (SELECT orden FROM seccion WHERE id_institucion=2) t),50)
WHERE NOT EXISTS (SELECT 1 FROM seccion WHERE id_institucion=2 AND nombre_interno='equipo_docente_home');

INSERT INTO seccion_config(id_seccion,clave,valor)
SELECT s.id_seccion,'titulo_bloque','Organización del Colegio'
FROM seccion s
WHERE s.id_institucion=2 AND s.nombre_interno='equipo_docente_home'
AND NOT EXISTS(SELECT 1 FROM seccion_config c WHERE c.id_seccion=s.id_seccion AND c.clave='titulo_bloque');

INSERT INTO seccion_config(id_seccion,clave,valor)
SELECT s.id_seccion,'subtitulo_bloque','Conoce a nuestro equipo educativo por ciclo.'
FROM seccion s
WHERE s.id_institucion=2 AND s.nombre_interno='equipo_docente_home'
AND NOT EXISTS(SELECT 1 FROM seccion_config c WHERE c.id_seccion=s.id_seccion AND c.clave='subtitulo_bloque');

-- Datos de ejemplo identificados; reemplazar antes de publicación definitiva.
INSERT INTO seccion_item(id_seccion,etiqueta,titulo,subtitulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,d.ciclo,d.nombre,d.cargo,d.descripcion,'','','si',d.orden
FROM seccion s
JOIN (
 SELECT 'jardin' ciclo,'[DEMO] Coordinación de Jardín' nombre,'Coordinación' cargo,'<p>Presentación de ejemplo del equipo de educación inicial.</p>' descripcion,1 orden
 UNION ALL SELECT 'jardin','[DEMO] Profesora jefe A','Profesor jefe · Jardín A','<p>Ficha demostrativa.</p>',2
 UNION ALL SELECT 'basica_1','[DEMO] Coordinación de 1.er ciclo','Coordinación','<p>Presentación de ejemplo.</p>',3
 UNION ALL SELECT 'basica_1','[DEMO] Profesor jefe 1.º básico','Profesor jefe · 1.º básico','<p>Ficha demostrativa.</p>',4
 UNION ALL SELECT 'basica_2','[DEMO] Coordinación de 2.º ciclo','Coordinación','<p>Presentación de ejemplo.</p>',5
 UNION ALL SELECT 'basica_2','[DEMO] Profesora jefe 5.º básico','Profesor jefe · 5.º básico','<p>Ficha demostrativa.</p>',6
 UNION ALL SELECT 'media','[DEMO] Coordinación de Enseñanza Media','Coordinación','<p>Presentación de ejemplo.</p>',7
 UNION ALL SELECT 'media','[DEMO] Profesor jefe 1.º medio','Profesor jefe · 1.º medio','<p>Ficha demostrativa.</p>',8
) d ON 1=1
WHERE s.id_institucion=2 AND s.nombre_interno='equipo_docente_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo=d.nombre);

-- Verificación:
SELECT s.id_seccion,s.nombre_interno,COUNT(i.id_item) total_fichas
FROM seccion s LEFT JOIN seccion_item i ON i.id_seccion=s.id_seccion
WHERE s.id_institucion=2 AND s.nombre_interno='equipo_docente_home'
GROUP BY s.id_seccion,s.nombre_interno;

-- Limpieza opcional de fichas demo (NO ejecutar mientras se quieran visualizar):
-- DELETE i FROM seccion_item i JOIN seccion s ON s.id_seccion=i.id_seccion
-- WHERE s.id_institucion=2 AND s.nombre_interno='equipo_docente_home' AND i.titulo LIKE '[DEMO]%';
