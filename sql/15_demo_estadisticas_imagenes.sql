-- Opcional: imágenes de demostración para estadísticas.
-- Ejecutar manualmente en crist668_colegio_patrona_laflorida.
-- Solo afecta a los tres ítems [DEMO] específicos.
UPDATE seccion_item i JOIN seccion s ON s.id_seccion=i.id_seccion
SET i.imagen=CASE i.titulo
 WHEN '[DEMO] Compromiso' THEN 'https://picsum.photos/id/180/1000/650'
 WHEN '[DEMO] Proyectos' THEN 'https://picsum.photos/id/20/1000/650'
 WHEN '[DEMO] Actividades' THEN 'https://picsum.photos/id/24/1000/650'
 ELSE i.imagen END
WHERE s.id_institucion=2 AND s.nombre_interno LIKE '%estadisticas%'
AND i.titulo IN ('[DEMO] Compromiso','[DEMO] Proyectos','[DEMO] Actividades');
-- Lista de útiles: cargar URL real cuando esté disponible:
-- INSERT INTO seccion_config(id_seccion,clave,valor)
-- SELECT id_seccion,'url_utiles_escolares','/assets/documentos/lista-utiles.pdf'
-- FROM seccion WHERE id_institucion=2 AND nombre_interno='header_principal';
-- No ejecutar esta línea hasta subir el PDF real y confirmar la ruta.
