<?php
declare(strict_types=1);
/**
 * Adaptador público de los contenedores CMS (estructura de Tabancura).
 * Nunca publica contenido provisional si MySQL está vacío o desconectado.
 */
function patrona_cms_site(): array {
    $result=['sections'=>[],'menus'=>[],'subs'=>[],'connected'=>false];
    $file=__DIR__.'/../class/conexion_local.php';
    if (!is_file($file)) return $result;
    try {
        $cfg=require $file;
        if (!is_array($cfg) || ($cfg['db']??'')!=='crist668_colegio_patrona_laflorida') throw new RuntimeException('Base de datos no autorizada');
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        $db=new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db']);
        $db->set_charset('utf8mb4');
        $q=$db->prepare("SELECT * FROM seccion WHERE id_institucion=? AND visible='si' AND estado='activo' ORDER BY orden,id_seccion");
        $institutionId=2;$q->bind_param('i',$institutionId);$q->execute();
        $sections=$q->get_result()->fetch_all(MYSQLI_ASSOC);
        $items=$db->prepare("SELECT * FROM seccion_item WHERE id_seccion=? AND visible='si' ORDER BY orden,id_item");
        $configs=$db->prepare("SELECT clave,valor FROM seccion_config WHERE id_seccion=? ORDER BY id_config");
        foreach ($sections as $section) {
            $id=(int)$section['id_seccion'];
            $items->bind_param('i',$id);$items->execute();
            $section['items']=$items->get_result()->fetch_all(MYSQLI_ASSOC);
            $configs->bind_param('i',$id);$configs->execute();
            $section['config']=[];
            foreach ($configs->get_result()->fetch_all(MYSQLI_ASSOC) as $row) $section['config'][$row['clave']]=$row['valor'];
            $result['sections'][]=$section;
        }
        $result['menus']=$db->query('SELECT id_menu,nombre,url,icono,id_padre,orden FROM menus WHERE estado=1 ORDER BY orden,id_menu')->fetch_all(MYSQLI_ASSOC);
        $result['subs']=$db->query('SELECT id_menu,nombre,url,icono,orden FROM sub_menus WHERE estado=1 ORDER BY orden,id_sub_menu')->fetch_all(MYSQLI_ASSOC);
        $result['connected']=true;
        $db->close();
    } catch (Throwable $e) {
        error_log('Patrona CMS público: '.$e->getMessage());
        $result=['sections'=>[],'menus'=>[],'subs'=>[],'connected'=>false];
    }
    return $result;
}
