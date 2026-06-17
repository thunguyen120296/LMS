#!/usr/bin/env bash
set -euo pipefail

KEYCLOAK_URL="${KEYCLOAK_URL:-http://localhost:8082}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OUT_FILE="${SCRIPT_DIR}/../jwt/keycloak-public.pem"

mkdir -p "$(dirname "$OUT_FILE")"

echo "Fetching Keycloak realm public key from ${KEYCLOAK_URL}/realms/lms ..."

PUBKEY="$(curl -sf "${KEYCLOAK_URL}/realms/lms" | jq -r '.public_key')"

if [ -z "$PUBKEY" ] || [ "$PUBKEY" = "null" ]; then
  echo "Failed to fetch Keycloak public key. Is Keycloak running?" >&2
  exit 1
fi

{
  echo "-----BEGIN PUBLIC KEY-----"
  echo "$PUBKEY" | fold -w 64
  echo "-----END PUBLIC KEY-----"
} > "$OUT_FILE"

echo "Wrote ${OUT_FILE}"
