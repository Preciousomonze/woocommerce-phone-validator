#!/bin/bash
# For linting js files for files in the npm_package > assets > js > js
# eslint $npm_package_assets_js_js
echo "linting... 🚦🤓";
for f in $npm_package_assets_js_js; do file=${f%.js}; eslint --fix $f; done
echo "done linting 😘";
$SHELL;