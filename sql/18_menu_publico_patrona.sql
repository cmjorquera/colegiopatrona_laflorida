-- 18_menu_publico_patrona.sql
-- Colegio Patrona de Lourdes · La Florida
-- EJECUTAR MANUALMENTE en phpMyAdmin DESPUES de respaldar la base.
-- Utiliza las tablas preexistentes menus y sub_menus de San Pablo.
-- NO elimina ni modifica menus existentes, solo añade nombres faltantes.
-- Rutas # son provisionales hasta que se creen las páginas de destino.
-- IMPORTANTE: las columnas y restricciones deben coincidir con las tablas de San Pablo.

START TRANSACTION;

INSERT INTO menus (nombre,url,icono,orden,estado,fecha_creacion,hora_creacion,ip_creacion)
SELECT d.nombre,d.url,'',d.orden,1,CURDATE(),CURTIME(),'127.0.0.1'
FROM (
 SELECT 'Colegio' nombre,'#colegio' url,1 orden
 UNION ALL SELECT 'Jardín','#jardin',2
 UNION ALL SELECT 'Formación Religiosa','#formacion-religiosa',3
 UNION ALL SELECT 'Matrícula 2027','#matricula-2027',4
 UNION ALL SELECT 'Admisión 2027','#admision-2027',5
 UNION ALL SELECT 'Notasnet','#notasnet',6
) d
WHERE NOT EXISTS(SELECT 1 FROM menus m WHERE m.nombre=d.nombre);

INSERT INTO sub_menus (id_menu,nombre,url,icono,orden,estado,fecha_creacion,hora_creacion,ip_creacion)
SELECT m.id_menu,d.nombre,d.url,'',d.orden,1,CURDATE(),CURTIME(),'127.0.0.1'
FROM menus m JOIN (
 SELECT 'Colegio' padre,'Quiénes Somos' nombre,'#quienes-somos' url,1 orden
 UNION ALL SELECT 'Colegio','Proyecto Educativo','#proyecto-educativo',2
 UNION ALL SELECT 'Colegio','Organización del Colegio','#organizacion-colegio',3
 UNION ALL SELECT 'Colegio','Infraestructura','#infraestructura',4
 UNION ALL SELECT 'Colegio','Documentos Reglamentarios','#documentos-reglamentarios',5
 UNION ALL SELECT 'Colegio','Preguntas Frecuentes','#preguntas-frecuentes',6
 UNION ALL SELECT 'Jardín','Organización del Jardín','#organizacion-jardin',1
 UNION ALL SELECT 'Jardín','Infraestructura','#infraestructura-jardin',2
 UNION ALL SELECT 'Jardín','Preguntas Frecuentes','#preguntas-frecuentes-jardin',3
) d ON m.nombre=d.padre
WHERE NOT EXISTS(SELECT 1 FROM sub_menus s WHERE s.id_menu=m.id_menu AND s.nombre=d.nombre);

COMMIT;

-- Verificación
SELECT m.id_menu,m.nombre AS menu,m.orden,s.id_sub_menu,s.nombre AS submenu,s.url
FROM menus m LEFT JOIN sub_menus s ON s.id_menu=m.id_menu
WHERE m.nombre IN ('Colegio','Jardín','Formación Religiosa','Matrícula 2027','Admisión 2027','Notasnet')
ORDER BY m.orden,s.orden;

-- Si ya habías guardado las opciones provisionales en seccion_item de
-- header_principal, NO las elimines: el código prioriza menus y sub_menus.
