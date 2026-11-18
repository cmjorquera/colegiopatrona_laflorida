-- DEMO: cinco docentes ficticios para Jardín, con retratos ilustrativos externos.
-- Ejecutar MANUALMENTE en phpMyAdmin, tras respaldar la BD.
-- No son personas ni datos reales del colegio; retirar antes de publicar oficialmente.
-- No modifica fichas reales. Puede ejecutarse más de una vez.
INSERT INTO seccion_item(id_seccion,etiqueta,titulo,subtitulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,d.ciclo,d.nombre,d.cargo,d.descripcion,d.imagen,'','si',d.orden
FROM seccion s JOIN (
 SELECT 'jardin' ciclo,'[DEMO] Carolina Rojas' nombre,'Profesora jefe · Jardín A' cargo,'<p>Ficha de demostración.</p>' descripcion,'https://i.pravatar.cc/400?img=47' imagen,11 orden
 UNION ALL SELECT 'jardin','[DEMO] Felipe Araya','Educador · Jardín B','<p>Ficha de demostración.</p>','https://i.pravatar.cc/400?img=12',12
 UNION ALL SELECT 'jardin','[DEMO] Valentina Torres','Profesora jefe · Jardín C','<p>Ficha de demostración.</p>','https://i.pravatar.cc/400?img=44',13
 UNION ALL SELECT 'jardin','[DEMO] Paula Herrera','Asistente de aula · Jardín','<p>Ficha de demostración.</p>','https://i.pravatar.cc/400?img=13',14
 UNION ALL SELECT 'jardin','[DEMO] Rodrigo Muñoz','Equipo de apoyo · Jardín','<p>Ficha de demostración.</p>','https://i.pravatar.cc/400?img=49',15
) d ON 1=1
WHERE s.id_institucion=2 AND s.nombre_interno='equipo_docente_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo=d.nombre);
-- Opcional: ocultar la antigua ficha sin imagen si ya existe.
-- UPDATE seccion_item i JOIN seccion s ON s.id_seccion=i.id_seccion
-- SET i.visible='no' WHERE s.id_institucion=2 AND s.nombre_interno='equipo_docente_home'
-- AND i.titulo='[DEMO] Profesora jefe A';
