#!/usr/bin/env bash
# ============================================================================
#  Faoxima — publish docs/wiki/ to the GitHub Wiki
# ----------------------------------------------------------------------------
#  GitHub does NOT let you create the *first* wiki page over git. Do this once:
#    1) Open https://github.com/hamed9898/Faoxima/wiki  and click "Create the
#       first page", type anything, and Save. (This initializes the wiki repo.)
#    2) Then run this script from the repo root:  bash docs/wiki/publish-wiki.sh
#  It copies every page in docs/wiki/ into the wiki repo and pushes it.
# ============================================================================
set -euo pipefail

REPO="${1:-hamed9898/Faoxima}"
SRC_DIR="$(cd "$(dirname "$0")" && pwd)"
WIKI_URL="https://github.com/${REPO}.wiki.git"
WORK="$(mktemp -d)"

echo "→ Cloning wiki: ${WIKI_URL}"
git clone "${WIKI_URL}" "${WORK}" || {
  echo "✗ Could not clone the wiki. Create the first page via the web UI first:"
  echo "  https://github.com/${REPO}/wiki"
  exit 1
}

echo "→ Copying pages from ${SRC_DIR}"
# Copy all wiki pages (skip this script and the local README).
find "${SRC_DIR}" -maxdepth 1 -name '*.md' ! -name 'README.md' -exec cp {} "${WORK}/" \;

cd "${WORK}"
git add -A
if git diff --cached --quiet; then
  echo "✓ Wiki already up to date — nothing to push."
else
  git commit -m "docs(wiki): sync Faoxima wiki from docs/wiki"
  git push origin HEAD
  echo "✓ Wiki published: https://github.com/${REPO}/wiki"
fi

rm -rf "${WORK}"
