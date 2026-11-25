<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/rich_editor.php';
require_once __DIR__.'/portada.php';
require_once __DIR__.'/../includes/patrona_modal_media.php';

function patrona_render_modal_institucional(array $sections, bool $preview = false): void
{
    foreach ($sections as $section) {
        if (($section['nombre_interno'] ?? '') === 'modal_institucional') {
            break;
        }
    }
    if (($section['nombre_interno'] ?? '') !== 'modal_institucional') return;

    $config = $section['config'] ?? [];
    $slides = [];
    foreach ($section['items'] ?? [] as $item) {
        if (!$preview && ($item['visible'] ?? '') !== 'si') continue;
        $title = trim((string)($item['titulo'] ?? ''));
        $subtitle = trim((string)($item['subtitulo'] ?? ''));
        $description = trim((string)($item['descripcion'] ?? ''));
        $media = patrona_modal_local_media(trim((string)($item['imagen'] ?? '')));
        $youtube = patrona_modal_youtube_id((string)($item['video_youtube'] ?? ''));
        $url = patrona_modal_link($item['url'] ?? '');
        if ($title === '' && $subtitle === '' && $description === '' && $media === '' && $youtube === '' && $url === '#') continue;
        $slides[] = [
            'title' => $title ?: ((string)($config['titulo_bloque'] ?? '') ?: 'Comunicado institucional'),
            'subtitle' => $subtitle ?: (string)($config['subtitulo_bloque'] ?? ''),
            'description' => $description ?: (string)($config['texto'] ?? ''),
            'media' => $media,
            'youtube' => $youtube,
            'url' => $url === '#' ? patrona_modal_link($config['enlace'] ?? '') : $url,
            'hidden' => ($item['visible'] ?? '') !== 'si',
        ];
    }

    if (!$slides) {
        if ($preview) echo '<p>Agrega un ítem con texto, imagen o video para ver el modal.</p>';
        return;
    }

    $fit = ($config['modal_encuadre'] ?? '') === 'cover' ? 'cover' : 'contain';
    $color = ($config['modal_color'] ?? '') === 'marino' ? 'navy' : 'blue';
    $label = trim((string)($config['boton_texto'] ?? '')) ?: 'Más información';
    $count = count($slides);
    ?>
    <link rel="stylesheet" href="assets/css/patrona-modal-institucional.css?v=1">
    <div class="<?= $preview ? 'pm-preview' : 'pm-backdrop' ?>" data-patrona-modal <?= $preview ? '' : 'hidden' ?>>
      <section class="pm-dialog pm-fit-<?= $fit ?> pm-color-<?= $color ?>"
        <?= $preview ? 'aria-label="Vista previa del comunicado"' : 'role="dialog" aria-modal="true" aria-label="Comunicado institucional"' ?>>
        <?php if (!$preview): ?>
        <button class="pm-close" type="button" aria-label="Cerrar comunicado" data-pm-close>×</button>
        <?php endif; ?>
        <?php foreach ($slides as $index => $slide): ?>
        <article data-pm-slide <?= $index === 0 ? '' : 'hidden' ?>>
          <?php if ($slide['youtube'] !== ''): ?>
          <iframe class="pm-video" title="<?=patrona_e($slide['title'])?>" src="about:blank"
            data-pm-video="https://www.youtube-nocookie.com/embed/<?=patrona_e($slide['youtube'])?>" allowfullscreen></iframe>
          <?php elseif ($slide['media'] !== '' && patrona_modal_is_video($slide['media'])): ?>
          <video class="pm-video" src="<?=patrona_e($slide['media'])?>" controls playsinline preload="none"></video>
          <?php elseif ($slide['media'] !== ''): ?>
          <img class="pm-image" src="<?=patrona_e($slide['media'])?>" alt="">
          <?php endif; ?>
          <div class="pm-body">
            <?php if ($preview && $slide['hidden']): ?><small>Ítem oculto en el sitio</small><?php endif; ?>
            <h2><?=patrona_e($slide['title'])?></h2>
            <?php if ($slide['subtitle'] !== ''): ?><p><?=patrona_e($slide['subtitle'])?></p><?php endif; ?>
            <?php if ($slide['description'] !== ''): ?><div><?=patrona_rich_clean($slide['description'])?></div><?php endif; ?>
            <?php if ($slide['url'] !== '#'): ?>
            <a class="pm-action" href="<?=patrona_e($slide['url'])?>"
              <?=preg_match('~^https?://~i', $slide['url']) ? 'target="_blank" rel="noopener noreferrer"' : ''?>><?=patrona_e($label)?></a>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
        <?php if ($count > 1): ?>
        <nav class="pm-controls" aria-label="Diapositivas del comunicado">
          <button type="button" data-pm-step="-1" aria-label="Anterior">‹</button>
          <span data-pm-counter aria-live="polite">1 / <?=$count?></span>
          <button type="button" data-pm-step="1" aria-label="Siguiente">›</button>
        </nav>
        <?php endif; ?>
      </section>
    </div>
    <script src="assets/js/patrona-modal-institucional.js?v=1" defer></script>
    <?php
}
