<?php
declare(strict_types=1);
// Vista pública estática. Este componente se presenta dentro del layout de index.php.
?>
<div class="fr-page" id="contenido-formacion">
  <section class="fr-hero" aria-labelledby="fr-page-heading">
    <div class="fr-hero-glow" aria-hidden="true"></div>
    <div class="fr-wrap fr-hero-grid">
      <div class="fr-hero-copy">
        <nav class="fr-breadcrumb" aria-label="Ruta de navegación">
          <a href="index.php">Inicio</a><i class="bi bi-chevron-right" aria-hidden="true"></i><span aria-current="page">Formación Religiosa</span>
        </nav>
        <p class="fr-kicker"><span class="fr-kicker-line"></span> Comunidad, fe y encuentro</p>
        <h1 id="fr-page-heading">Formación <em>Religiosa</em></h1>
        <p class="fr-hero-lead">Una invitación a descubrir el sentido de la vida, compartir la fe y construir amistades que acompañan el crecimiento de nuestros estudiantes.</p>
        <div class="fr-hero-actions">
          <a class="fr-button fr-button-gold" href="#club-grial">Conocer nuestra propuesta <i class="bi bi-arrow-down-right" aria-hidden="true"></i></a>
          <a class="fr-text-link" href="#etapas-formativas">Explorar por nivel <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="fr-hero-stats" aria-label="Niveles de la propuesta">
          <span><i class="bi bi-people" aria-hidden="true"></i> Desde 5.º básico</span>
          <span><i class="bi bi-heart" aria-hidden="true"></i> Formación en comunidad</span>
        </div>
      </div>
      <div class="fr-hero-art" aria-hidden="true">
        <div class="fr-arch fr-arch-outer"></div>
        <div class="fr-arch fr-arch-inner"></div>
        <div class="fr-art-disc"></div>
        <svg class="fr-chalice" viewBox="0 0 320 330" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M85 64H235L221 122C213 155 190 175 160 175C130 175 107 155 99 122L85 64Z" stroke="currentColor" stroke-width="7" stroke-linejoin="round"/>
          <path d="M160 176V253M119 264H201M160 252L119 264M160 252L201 264M108 282H212" stroke="currentColor" stroke-width="7" stroke-linecap="round"/>
          <path d="M93 92H227" stroke="currentColor" stroke-width="5" stroke-linecap="round" opacity=".5"/>
          <path d="M160 25V5M140 15H180M50 81L34 70M270 81L286 70" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
          <circle cx="160" cy="110" r="23" stroke="currentColor" stroke-width="4" opacity=".75"/>
        </svg>
        <div class="fr-art-caption"><span>CLUB</span><strong>Buscadores<br>del Grial</strong></div>
        <span class="fr-art-star fr-art-star-one">✦</span><span class="fr-art-star fr-art-star-two">✧</span>
      </div>
    </div>
    <div class="fr-hero-edge" aria-hidden="true"></div>
  </section>

  <section class="fr-intro fr-section" id="club-grial" aria-labelledby="fr-club-heading">
    <div class="fr-wrap fr-intro-grid">
      <div class="fr-intro-side fr-reveal">
        <span class="fr-eyebrow">Nuestra identidad</span>
        <span class="fr-index-mark" aria-hidden="true">01</span>
        <h2 id="fr-club-heading">Club <em>Buscadores</em> del Grial</h2>
        <p class="fr-lead">Una búsqueda compartida de aquello que da sentido a la vida.</p>
      </div>
      <div class="fr-story-card fr-reveal">
        <span class="fr-story-icon"><i class="bi bi-stars" aria-hidden="true"></i></span>
        <h3>¿Por qué nuestro nombre?</h3>
        <p>El nombre del club se inspira en la leyenda del Santo Grial: una historia sobre el valor de hacerse las preguntas esenciales y salir en búsqueda de lo verdaderamente importante.</p>
        <p>Como aquellos buscadores, los estudiantes son invitados a reconocer sus ideales, descubrir el valor de la amistad y emprender con otros un camino hacia la felicidad.</p>
        <div class="fr-story-quote">
          <i class="bi bi-quote" aria-hidden="true"></i>
          <strong>Buscar juntos aquello que hace grande la vida.</strong>
        </div>
      </div>
    </div>
  </section>

  <section class="fr-levels fr-section" id="etapas-formativas" aria-labelledby="fr-levels-heading">
    <div class="fr-wrap">
      <div class="fr-heading fr-reveal">
        <span class="fr-eyebrow">Un camino para cada etapa</span>
        <h2 id="fr-levels-heading">Crecer en la fe, <em>crecer en comunidad</em></h2>
        <p>La propuesta acompaña las distintas etapas escolares, desde la primera experiencia de encuentro hasta los grupos juveniles.</p>
      </div>
      <div class="fr-levels-layout fr-reveal">
        <div class="fr-level-tabs" role="tablist" aria-label="Niveles de formación religiosa" aria-orientation="vertical">
          <button type="button" class="fr-level-tab is-active" id="fr-tab-1" role="tab" aria-selected="true" aria-controls="fr-panel-1" tabindex="0" data-level="1">
            <span class="fr-tab-number">01</span><span class="fr-tab-text"><strong>5.º y 6.º básico</strong><small>Primer encuentro</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i>
          </button>
          <button type="button" class="fr-level-tab" id="fr-tab-2" role="tab" aria-selected="false" aria-controls="fr-panel-2" tabindex="-1" data-level="2">
            <span class="fr-tab-number">02</span><span class="fr-tab-text"><strong>7.º y 8.º básico</strong><small>Las Caballerías</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i>
          </button>
          <button type="button" class="fr-level-tab" id="fr-tab-3" role="tab" aria-selected="false" aria-controls="fr-panel-3" tabindex="-1" data-level="3">
            <span class="fr-tab-number">03</span><span class="fr-tab-text"><strong>1.º a 4.º medio</strong><small>Grupo juvenil</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i>
          </button>
        </div>
        <div class="fr-level-content">
          <article class="fr-level-panel" id="fr-panel-1" role="tabpanel" aria-labelledby="fr-tab-1" tabindex="0">
            <div class="fr-level-panel-header"><span class="fr-level-icon"><i class="bi bi-compass" aria-hidden="true"></i></span><span class="fr-chip">5.º y 6.º básico</span></div>
            <h3>El valor del encuentro</h3>
            <p>Una experiencia voluntaria de formación cristiana para estudiantes que desean compartir una comunidad de amistad en Cristo, acompañados por adultos que orientan su camino.</p>
            <p>El encuentro y la vida en comunidad ayudan a reconocer la fe como respuesta a las inquietudes del corazón y a descubrir su presencia en las circunstancias cotidianas.</p>
            <div class="fr-level-note"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Tras dos años de participación, quienes estén bautizados podrán prepararse para recibir la Eucaristía.</span></div>
          </article>
          <article class="fr-level-panel" id="fr-panel-2" role="tabpanel" aria-labelledby="fr-tab-2" tabindex="0" hidden>
            <div class="fr-level-panel-header"><span class="fr-level-icon"><i class="bi bi-lightbulb" aria-hidden="true"></i></span><span class="fr-chip">7.º y 8.º básico</span></div>
            <h3>Las Caballerías</h3>
            <p>Este espacio invita a profundizar la experiencia de fe, tanto a quienes continúan desde los cursos anteriores como a quienes desean integrarse por primera vez.</p>
            <p>En una etapa marcada por preguntas sobre la identidad, la existencia y el sentido de lo que vivimos, los estudiantes buscan comprender la realidad a la luz del encuentro con Jesucristo.</p>
            <div class="fr-level-note"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Una propuesta de diálogo, reflexión, amistad y formación cristiana.</span></div>
          </article>
          <article class="fr-level-panel" id="fr-panel-3" role="tabpanel" aria-labelledby="fr-tab-3" tabindex="0" hidden>
            <div class="fr-level-panel-header"><span class="fr-level-icon"><i class="bi bi-people" aria-hidden="true"></i></span><span class="fr-chip">1.º a 4.º medio</span></div>
            <h3>Caminar juntos en la fe</h3>
            <p>Un grupo juvenil que propone vivir la fe en comunidad y expresarla también mediante gestos concretos de servicio y caridad.</p>
            <p>Los jóvenes son invitados a descubrir personalmente qué significado tiene la fe para su vida. Quienes lo deseen pueden prepararse, según su proceso, para el Bautismo, la Primera Comunión o la Confirmación.</p>
            <div class="fr-level-note"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Participación comunitaria, acompañamiento y preparación sacramental.</span></div>
          </article>
        </div>
      </div>
    </div>
  </section>

  <section class="fr-pillars fr-section" aria-labelledby="fr-pillars-heading">
    <div class="fr-wrap">
      <div class="fr-heading fr-reveal">
        <span class="fr-eyebrow">Lo que nos reúne</span>
        <h2 id="fr-pillars-heading">Tres maneras de <em>vivir el encuentro</em></h2>
      </div>
      <div class="fr-pillar-grid">
        <article class="fr-pillar fr-reveal"><span class="fr-pillar-icon"><i class="bi bi-person-hearts" aria-hidden="true"></i></span><span class="fr-pillar-order">01 /</span><h3>Amistad</h3><p>Aprender a caminar con otros, escuchar y construir vínculos de confianza.</p></article>
        <article class="fr-pillar fr-reveal"><span class="fr-pillar-icon"><i class="bi bi-chat-square-heart" aria-hidden="true"></i></span><span class="fr-pillar-order">02 /</span><h3>Diálogo</h3><p>Dar espacio a las preguntas importantes y reflexionar sobre el sentido de la vida.</p></article>
        <article class="fr-pillar fr-reveal"><span class="fr-pillar-icon"><i class="bi bi-hands" aria-hidden="true"></i></span><span class="fr-pillar-order">03 /</span><h3>Servicio</h3><p>Compartir la fe mediante acciones concretas de solidaridad y caridad.</p></article>
      </div>
    </div>
  </section>

  <section class="fr-final fr-section" aria-labelledby="fr-final-heading">
    <div class="fr-wrap fr-final-inner fr-reveal">
      <div><span class="fr-eyebrow">Colegio Patrona de Lourdes · La Florida</span><h2 id="fr-final-heading">Una experiencia para <em>vivir en comunidad.</em></h2><p>Conoce más sobre la formación religiosa y las actividades del colegio.</p></div>
      <a class="fr-button fr-button-gold" href="#contacto">Contáctanos <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </div>
  </section>
</div>
<script>
(function(){
  const root=document.querySelector('.fr-page');if(!root)return;
  const tabs=[...root.querySelectorAll('.fr-level-tab')];
  const show=(button,focus)=>{
    tabs.forEach(tab=>{const on=tab===button;tab.classList.toggle('is-active',on);tab.setAttribute('aria-selected',String(on));tab.tabIndex=on?0:-1;document.getElementById(tab.getAttribute('aria-controls')).hidden=!on;});
    if(focus)button.focus();
  };
  tabs.forEach((tab,i)=>{
    tab.addEventListener('click',()=>show(tab,false));
    tab.addEventListener('keydown',e=>{
      if(!['ArrowDown','ArrowUp','ArrowRight','ArrowLeft','Home','End'].includes(e.key))return;
      e.preventDefault();
      const n=e.key==='Home'?0:e.key==='End'?tabs.length-1:(i+(e.key==='ArrowDown'||e.key==='ArrowRight'?1:-1)+tabs.length)%tabs.length;
      show(tabs[n],true);
    });
  });
  const els=root.querySelectorAll('.fr-reveal');
  if(window.matchMedia('(prefers-reduced-motion: reduce)').matches||!('IntersectionObserver' in window)){els.forEach(el=>el.classList.add('is-visible'));return}
  const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('is-visible');observer.unobserve(entry.target)}}),{threshold:.1,rootMargin:'0px 0px -25px 0px'});
  els.forEach(el=>observer.observe(el));
})();
</script>
