#!/usr/bin/env bash
# Crea la máquina Always Free (VM.Standard.A1.Flex) reintentando hasta que Oracle tenga capacidad.
# Se ejecuta en Oracle Cloud Shell (ícono >_ arriba a la derecha de la consola):
#   bash reintentar-instancia.sh
# Opcional: OCPUS=2 MEM=12 ESPERA=90 bash reintentar-instancia.sh
# Requisito: la red creada con el asistente "Create VCN with Internet Connectivity" (subred pública).
# Puedes cerrar el navegador: usa  nohup bash reintentar-instancia.sh > reintentos.log 2>&1 &  y revisa con  tail -f reintentos.log
set -uo pipefail
OCPUS="${OCPUS:-1}"; MEM="${MEM:-6}"; ESPERA="${ESPERA:-60}"; NOMBRE="${NOMBRE:-plataforma}"
C="${OCI_TENANCY:?Ejecútalo en Oracle Cloud Shell}"

echo "Buscando la subred pública, la imagen de Ubuntu y los dominios de disponibilidad..."
SUBNET="$(oci network subnet list -c "$C" --all | jq -r '[.data[] | select(."prohibit-public-ip-on-vnic" == false)][0].id // empty')"
[ -n "$SUBNET" ] || { echo "No encontré una subred pública. Crea la red con el asistente 'Create VCN with Internet Connectivity'."; exit 1; }
IMG="$(oci compute image list -c "$C" --operating-system "Canonical Ubuntu" --shape VM.Standard.A1.Flex --sort-by TIMECREATED --sort-order DESC --all \
  | jq -r '[.data[] | select(."operating-system-version" | test("^24.04|^22.04")) | select(."display-name" | test("Minimal") | not)][0].id // empty')"
[ -n "$IMG" ] || { echo "No encontré una imagen de Ubuntu para A1."; exit 1; }
mapfile -t ADS < <(oci iam availability-domain list -c "$C" | jq -r '.data[].name')

# Llave SSH: queda en Cloud Shell (~/.ssh/plataforma); desde aquí mismo puedes entrar a la máquina
mkdir -p ~/.ssh && chmod 700 ~/.ssh
[ -f ~/.ssh/plataforma.pub ] || { rm -f ~/.ssh/plataforma; ssh-keygen -t ed25519 -f ~/.ssh/plataforma -N "" -C plataforma -q; }
[ -f ~/.ssh/plataforma.pub ] || { echo "No pude crear la llave SSH en ~/.ssh/plataforma.pub"; exit 1; }

echo "Subred: ${SUBNET:0:40}…  Imagen: ${IMG:0:40}…  Dominios: ${ADS[*]}"
echo "Intentando crear '$NOMBRE' ($OCPUS OCPU, $MEM GB) cada $ESPERA s. Ctrl+C para detener."
n=0
while true; do
  for AD in "${ADS[@]}"; do
    n=$((n + 1))
    OUT="$(oci compute instance launch -c "$C" --availability-domain "$AD" --display-name "$NOMBRE" \
      --shape VM.Standard.A1.Flex --shape-config "{\"ocpus\": $OCPUS, \"memoryInGBs\": $MEM}" \
      --image-id "$IMG" --subnet-id "$SUBNET" --assign-public-ip true --boot-volume-size-in-gbs 50 \
      --ssh-authorized-keys-file ~/.ssh/plataforma.pub 2>&1)"
    if echo "$OUT" | jq -e '.data.id' >/dev/null 2>&1; then
      ID="$(echo "$OUT" | jq -r '.data.id')"
      echo "$(date '+%F %T') ¡Creada en $AD! Esperando a que arranque..."
      oci compute instance get --instance-id "$ID" --wait-for-state RUNNING --max-wait-seconds 900 >/dev/null
      IP="$(oci compute instance list-vnics --instance-id "$ID" | jq -r '.data[0]."public-ip"')"
      echo "Lista. IP pública: $IP"
      echo "Entra desde Cloud Shell con:  ssh -i ~/.ssh/plataforma ubuntu@$IP"
      exit 0
    fi
    if echo "$OUT" | grep -qi "capacity"; then
      echo "$(date '+%F %T') intento $n ($AD): sin capacidad"
    elif echo "$OUT" | grep -qi "TooManyRequests"; then
      echo "$(date '+%F %T') intento $n: Oracle pide esperar; pausa larga"; sleep 300
    elif echo "$OUT" | grep -qi "LimitExceeded\|QuotaExceeded"; then
      echo "Límite de la cuenta alcanzado (¿ya tienes otra máquina A1 o pediste más de 4 OCPU / 24 GB?)."; echo "$OUT" | tail -5; exit 1
    else
      echo "$(date '+%F %T') error inesperado:"; echo "$OUT" | tail -8; exit 1
    fi
  done
  sleep "$ESPERA"
done
