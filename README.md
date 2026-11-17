# Colegio Patrona de Lourdes — La Florida

Sitio público independiente en PHP, con componentes de portada reutilizables y estructura de datos preparada para un CMS MySQL. **Etapa 1: portada; administración pendiente.**

## Desarrollo
- PHP 8.1+ y Apache. Abrir `index.php` en un servidor PHP.
- Sin credenciales ni dependencias externas obligatorias.
- Los contenidos demostrativos están en `config/portada.php`. Son **ejemplos**, no información oficial verificada.
- Los contenedores están en `componentes/portada.php`; estilos en `assets/css/patrona.css`.
- El esquema propuesto se encuentra en `sql/01_portada.sql`. **No ha sido ejecutado**. No ejecutar sin revisar la base de datos de destino.
- La etapa siguiente conectará la portada a MySQL, usando la lógica reutilizable del CMS Tabancura, sin copiar su diseño, contenido, cuentas ni credenciales.

## Seguridad
No publicar credenciales, datos personales, imágenes de alumnos sin autorización ni documentos internos. No hay despliegue automático ni conexión a Tabancura.
