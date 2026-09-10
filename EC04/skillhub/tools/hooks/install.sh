#!/bin/bash
# Script d'installation du hook pre-commit
# Usage : bash tools/hooks/install.sh

REPO_ROOT=$(git rev-parse --show-toplevel)
HOOK_SRC="$(dirname "$0")/pre-commit"
HOOK_DEST="$REPO_ROOT/.git/hooks/pre-commit"

cp "$HOOK_SRC" "$HOOK_DEST"
chmod +x "$HOOK_DEST"

echo "✅ Hook pre-commit installé dans $HOOK_DEST"
