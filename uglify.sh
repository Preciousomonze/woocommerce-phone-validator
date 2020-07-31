#!/bin/bash
# For generating min.js files for files in the npm_package > assets > js > js
#for name in $npm_package_assets_js_js; do file=${name%.js}; echo $name; done
# node_modules/.bin/uglifyjs gives error cause its es6
echo "minifying... 🚦🤓";
for f in $npm_package_assets_js_js; do file=${f%.js}; uglifyjs $f -c -m -o $file.min.js $f; done
echo "done minifying 😘";
#$SHELL;