<?php
declare(strict_types=1);

// Contenido provisional: reemplazar por datos institucionales verificados.
// Posteriormente se obtendra desde MySQL usando el motor de contenedores CMS.
return [
 'colegio' => 'Colegio Patrona de Lourdes',
 'sede' => 'La Florida',
 'menu' => [
   ['texto'=>'Inicio','url'=>'#inicio'],
   ['texto'=>'Nuestro colegio','url'=>'#colegio'],
   ['texto'=>'Proyecto educativo','url'=>'#proyecto'],
   ['texto'=>'Vida escolar','url'=>'#vida-escolar'],
   ['texto'=>'Noticias','url'=>'#noticias'],
 ],
 'contenedores' => [
   ['clave'=>'hero','tipo'=>'hero','orden'=>10,'visible'=>true,'datos'=>[
     'etiqueta'=>'Colegio Patrona de Lourdes · La Florida',
     'titulo'=>'Un colegio para aprender, compartir y crecer.',
     'texto'=>'Una nueva experiencia digital para nuestra comunidad educativa.',
     'boton'=>'Descubre nuestro colegio','enlace'=>'#colegio',
     'imagen'=>'assets/imagenes/logo-colegio-1024x598.jpg',
     'video'=>'assets/videos/Principio-Explicativo-V2.prproj.mp4'
   ]],
   ['clave'=>'accesos','tipo'=>'accesos','orden'=>20,'visible'=>true,'datos'=>[
     'titulo'=>'Accesos de interés','items'=>[
       ['titulo'=>'Admisión','texto'=>'Información para nuevas familias','url'=>'#contacto','icono'=>'✦'],
       ['titulo'=>'Calendario escolar','texto'=>'Próximamente disponible','url'=>'#contacto','icono'=>'▦'],
       ['titulo'=>'Comunicados','texto'=>'Información para la comunidad','url'=>'#noticias','icono'=>'◈'],
       ['titulo'=>'Contacto','texto'=>'Conversemos','url'=>'#contacto','icono'=>'↗']
     ]
   ]],
   ['clave'=>'bienvenida','tipo'=>'presentacion','orden'=>30,'visible'=>true,'datos'=>[
     'titulo'=>'Un lugar para crecer juntos',
     'texto'=>'Queremos que nuestra nueva página refleje la vida del colegio: aprendizaje, convivencia, proyectos y participación de las familias.',
     'detalle'=>'Contenido institucional en revisión.','imagen'=>'assets/imagenes/logo-colegio-1024x598.jpg'
   ]],
   ['clave'=>'proyecto','tipo'=>'valores','orden'=>40,'visible'=>true,'datos'=>[
     'titulo'=>'Nuestro proyecto educativo','items'=>[
       ['titulo'=>'Aprendizaje','texto'=>'Espacios para descubrir y desarrollar capacidades.'],
       ['titulo'=>'Comunidad','texto'=>'Vínculos entre estudiantes, familias y educadores.'],
       ['titulo'=>'Formación','texto'=>'Crecimiento personal y compromiso con los demás.']
     ]
   ]],
   ['clave'=>'noticias','tipo'=>'noticias','orden'=>50,'visible'=>true,'datos'=>[
     'titulo'=>'La vida en nuestro colegio','items'=>[
       ['categoria'=>'Vida escolar','titulo'=>'Próximamente: actividades del colegio','texto'=>'Publicaremos aquí noticias y fotografías autorizadas.'],
       ['categoria'=>'Aprendizaje','titulo'=>'Nuestros proyectos','texto'=>'Un espacio para compartir experiencias de aprendizaje.'],
       ['categoria'=>'Comunidad','titulo'=>'Noticias para las familias','texto'=>'Información y novedades de nuestra comunidad educativa.']
     ]
   ]],
   ['clave'=>'contacto','tipo'=>'contacto','orden'=>60,'visible'=>true,'datos'=>[
     'titulo'=>'Sigamos conectados','texto'=>'Pronto encontrarás aquí nuestros canales institucionales oficiales.'
   ]]
 ]
];
