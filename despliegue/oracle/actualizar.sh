#!/usr/bin/env bash
# Trae la última versión desde GitHub y la deja funcionando.
#   bash despliegue/oracle/actualizar.sh            (rama actual)
#   bash despliegue/oracle/actualizar.sh main       (cambia a otra rama)
set -euo pipefail
APP_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$APP_DIR"
if [ -n "${1:-}" ]; then git fetch origin && git checkout "$1"; fi
git pull --ff-only
sudo systemctl reload apache2
echo "Actualizado a $(git rev-parse --short HEAD) ($(git branch --show-current))."
