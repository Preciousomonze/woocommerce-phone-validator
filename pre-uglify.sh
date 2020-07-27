#!/bin/bash
# For deleting min.js files in npm_package > assets > js > min
# rm -f $npm_package_assets_js_min
echo "Deleting min file(s)... 🚦🤓";
for f in $npm_package_assets_js_min; do file=${f%.js}; rm -f $f ; done
echo "done deleting. 😘";
#$SHELL;