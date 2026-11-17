#!/bin/bash
set -euo pipefail
SRC=/home3/crist668/rescate_patrona_laflorida/organizados/imagenes
DST=/home3/crist668/colegiopatronalaflorida.webnia.cl/assets/imagenes
for file in LOGO-COLEGIO_-Sin-fondo.png cropped-logo-cuadrado-sin-fondo-2-192x192.png 6-1.png feliz-inicio-de-clases-2026-1900-x-640-px-1600-x-1200-px-1-1.png; do test -f "$SRC/$file" || { echo "Falta: $file"; exit 1; }; done
mkdir -p "$DST"
cp "$SRC/LOGO-COLEGIO_-Sin-fondo.png" "$DST/logo-patrona.png"
cp "$SRC/cropped-logo-cuadrado-sin-fondo-2-192x192.png" "$DST/escudo-patrona.png"
cp "$SRC/6-1.png" "$DST/hero-patrona.png"
cp "$SRC/feliz-inicio-de-clases-2026-1900-x-640-px-1600-x-1200-px-1-1.png" "$DST/vida-escolar.png"
echo "Recursos V2 copiados"
