#!/usr/bin/env bash
set -Eeuo pipefail

# Instalar solo desde el directorio raíz real del subdominio nuevo.
REPO="https://github.com/cmjorquera/colegiopatrona_laflorida.git"
BRANCH="main"
ROOT="$(pwd -P)"

if [[ ! -f "$ROOT/index.php" || ! -f "$ROOT/README.md" ]]; then
  echo "ERROR: Ejecuta este script desde el directorio raíz del colegio (index.php y README.md requeridos)." >&2
  exit 1
fi

if [[ ! -d "$ROOT/.git" ]]; then
  echo "Este directorio no es un repositorio Git."
  echo "Para una instalación nueva, crea el subdominio vacío y clona directamente allí:"
  echo "  cd /home3/crist668"
  echo "  git clone --branch main --single-branch $REPO NUEVO_DIRECTORIO"
  exit 1
fi

if [[ "$(git rev-parse --show-toplevel)" != "$ROOT" ]]; then
  echo "ERROR: ruta Git diferente del directorio actual." >&2; exit 1
fi

URL="$(git remote get-url origin)"
if [[ "$URL" != "$REPO" && "$URL" != "git@github.com:cmjorquera/colegiopatrona_laflorida.git" ]]; then
  echo "ERROR: origin apunta a otro repositorio: $URL" >&2; exit 1
fi

if [[ "$(git branch --show-current)" != "$BRANCH" ]]; then
  echo "ERROR: la rama actual no es main." >&2; exit 1
fi

if [[ -n "$(git status --porcelain --untracked-files=no)" ]]; then
  echo "ERROR: hay cambios locales en archivos versionados; no se actualizó nada." >&2; exit 1
fi

git pull --ff-only origin "$BRANCH"
echo "Actualizado. Último commit: $(git log -1 --oneline)"
