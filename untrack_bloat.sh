#!/bin/bash
cd "C:/Users/Dell/Documents/ShoeInventorySystem"
# Untrack all node_modules and vendor files (keeps them on disk)
git ls-files | grep -E "node_modules/|/vendor/" | while read f; do git rm --cached --quiet "$f"; done
echo "remaining tracked bloat: $(git ls-files | grep -cE 'node_modules/|/vendor/')"
git add -A
echo "=== staged file count ==="
git diff --cached --name-only | wc -l
