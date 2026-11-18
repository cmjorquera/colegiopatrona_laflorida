-- DEMOSTRACIÓN DE CMS - COLEGIO PATRONA DE LOURDES (LA FLORIDA)
-- SOLO PARA ENTORNO DE PRUEBAS O CON AUTORIZACIÓN PARA PUBLICAR DEMOS.
-- No elimina ni reemplaza registros existentes. No crea tablas.
-- IMPORTANTE: fotos genéricas externas de picsum.photos (NO son del colegio).
-- Para usar fotografías reales, subirlas a assets/imagenes/ y reemplazar sus URL.
-- Las secciones inexistentes se omiten; no se inventan identificadores.
-- Los registros DEMO pueden eliminarse luego usando el prefijo [DEMO].
START TRANSACTION;

-- El modal debe existir previamente (sql/11_modal_institucional.sql).
-- Inserta únicamente si existe la sección y no hay ya un item con igual título.

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'Vida escolar','[DEMO] Feria de ciencias: estudiantes presentan sus proyectos','<p>Durante una jornada de demostración, los cursos presentan experimentos, maquetas e ideas para aprender investigando.</p><p><strong>Contenido ficticio para evaluar el diseño del sitio.</strong></p>','https://picsum.photos/id/180/1000/650','','si',1
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='noticias_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Feria de ciencias: estudiantes presentan sus proyectos');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'Deporte','[DEMO] Encuentro deportivo y trabajo en equipo','<p>Una actividad recreativa reúne a estudiantes en distintas disciplinas, promoviendo el respeto y la vida saludable.</p>','https://picsum.photos/id/1058/1000/650','','si',2
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='noticias_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Encuentro deportivo y trabajo en equipo');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'Comunidad','[DEMO] Jornada de lectura compartida','<p>Familias y docentes participan en una jornada de cuentos y lectura para fomentar la imaginación.</p>','https://picsum.photos/id/24/1000/650','','si',3
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='noticias_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Jornada de lectura compartida');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'Arte','[DEMO] Muestra artística de fin de semestre','<p>Exposición ficticia de trabajos visuales y musicales realizados por estudiantes.</p>','https://picsum.photos/id/1067/1000/650','','si',4
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='noticias_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Muestra artística de fin de semestre');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Formación integral','<p>Promovemos el desarrollo de habilidades, valores y talentos en cada etapa escolar.</p>','assets/imagenes/LOGO-COLEGIO--Sin-fondo.png','','si',1
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='sellos_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Formación integral');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Comunidad y servicio','<p>Aprender a convivir, colaborar y aportar a la comunidad.</p>','assets/imagenes/LOGO-COLEGIO--Sin-fondo.png','','si',2
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='sellos_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Comunidad y servicio');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Aprendizaje activo','<p>Experiencias que invitan a explorar, crear y reflexionar.</p>','assets/imagenes/LOGO-COLEGIO--Sin-fondo.png','','si',3
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='sellos_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Aprendizaje activo');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'familias','[DEMO] Centro de familias','<p>Espacio para promover la participación de apoderados en actividades y proyectos escolares.</p>','https://picsum.photos/id/64/900/650','','si',1
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='tab_comunidad_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Centro de familias');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'familias','[DEMO] Talleres para apoderados','<p>Charlas y encuentros de apoyo al aprendizaje y la convivencia familiar.</p>','https://picsum.photos/id/20/900/650','','si',2
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='tab_comunidad_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Talleres para apoderados');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'familias','[DEMO] Voluntariado escolar','<p>Iniciativas de colaboración entre estudiantes, docentes y familias.</p>','https://picsum.photos/id/1011/900/650','','si',3
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='tab_comunidad_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Voluntariado escolar');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'✦','[DEMO] Admisión','Conoce los pasos y fechas de postulación.','','index.php#noticias-home','si',1
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='tab_destacados_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Admisión');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'▦','[DEMO] Calendario','Próximas actividades y encuentros escolares.','','index.php#noticias-home','si',2
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='tab_destacados_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Calendario');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'◈','[DEMO] Comunidad','Actividades para estudiantes y familias.','','index.php#comunidad-escolar','si',3
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='tab_destacados_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Comunidad');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'↗','[DEMO] Contacto','Canales de información institucional.','','index.php#comunidad-escolar','si',4
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='tab_destacados_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Contacto');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'Más información','[DEMO] Bienvenidos a nuestra nueva página','<p>Este es un <strong>comunicado de demostración</strong> para probar el modal institucional automático.</p><p>El contenido definitivo se administrará desde el CMS.</p>','https://picsum.photos/id/20/1000/600','index.php#noticias-home','si',1
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='modal_institucional'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Bienvenidos a nuestra nueva página');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Conversaciones sobre aprendizaje','<p>Episodio ficticio para comprobar el diseño del contenedor. El enlace dirige a la plataforma general.</p>','https://picsum.photos/id/367/900/650','https://open.spotify.com/','si',1
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='spotify_podcast_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Conversaciones sobre aprendizaje');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Familias y comunidad educativa','<p>Conversación de muestra sobre acompañamiento escolar.</p>','https://picsum.photos/id/24/900/650','https://open.spotify.com/','si',2
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='spotify_podcast_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Familias y comunidad educativa');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Galería escolar 1','Fotografía genérica de demostración.','https://picsum.photos/id/10/1000/700','','si',1
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='galeria_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Galería escolar 1');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Galería escolar 2','Fotografía genérica de demostración.','https://picsum.photos/id/20/1000/700','','si',2
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='galeria_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Galería escolar 2');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Galería escolar 3','Fotografía genérica de demostración.','https://picsum.photos/id/24/1000/700','','si',3
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='galeria_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Galería escolar 3');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Galería escolar 4','Fotografía genérica de demostración.','https://picsum.photos/id/42/1000/700','','si',4
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='galeria_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Galería escolar 4');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Galería escolar 5','Fotografía genérica de demostración.','https://picsum.photos/id/64/1000/700','','si',5
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='galeria_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Galería escolar 5');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'','[DEMO] Galería escolar 6','Fotografía genérica de demostración.','https://picsum.photos/id/180/1000/700','','si',6
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='galeria_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Galería escolar 6');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'100%','[DEMO] Compromiso','Valor ilustrativo, no representa una estadística real.','','','si',1
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='estadisticas_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Compromiso');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'7','[DEMO] Proyectos','Número ficticio para evaluar la tarjeta.','','','si',2
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='estadisticas_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Proyectos');

INSERT INTO seccion_item (id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden)
SELECT s.id_seccion,'12','[DEMO] Actividades','Número ficticio para evaluar la tarjeta.','','','si',3
FROM seccion s WHERE s.id_institucion=2 AND s.nombre_interno='estadisticas_home'
AND NOT EXISTS(SELECT 1 FROM seccion_item i WHERE i.id_seccion=s.id_seccion AND i.titulo='[DEMO] Actividades');

-- Galería y estadísticas: admitir nombres internos alternativos existentes.
-- Si la tabla seccion usa un nombre diferente a galeria_home o estadisticas_home,
-- consultar el listado y ajustar esas dos claves ANTES de ejecutar.

-- Opcional: activar contenedores de contenido para ver la demostración.
-- No se activa el modal automáticamente para evitar interrumpir visitas reales.
UPDATE seccion SET visible='si' WHERE id_institucion=2
AND nombre_interno IN ('noticias_home','sellos_home','tab_comunidad_home','galeria_home','spotify_podcast_home','estadisticas_home');

COMMIT;

-- Revisión de lo cargado:
SELECT s.nombre_interno,COUNT(i.id_item) AS total_demo
FROM seccion s LEFT JOIN seccion_item i ON i.id_seccion=s.id_seccion AND i.titulo LIKE '[DEMO]%'
WHERE s.id_institucion=2 GROUP BY s.id_seccion,s.nombre_interno ORDER BY s.orden;

-- Para eliminar SOLO los elementos de demostración (ejecutar por separado, cuando corresponda):
-- DELETE i FROM seccion_item i JOIN seccion s ON s.id_seccion=i.id_seccion
-- WHERE s.id_institucion=2 AND i.titulo LIKE '[DEMO]%';
