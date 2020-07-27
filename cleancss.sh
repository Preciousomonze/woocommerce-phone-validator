#!/bin/bash
# For generating min.css files for files in npm_package > assets > styles > css
#for name in $npm_package_assets_js_js; do file=${name%.js}; echo $name; done
# node_modules/.bin/uglifyjs gives error cause its es6
echo "minifying... 🚦🤓";
for f in $npm_package_assets_styles_css; do file=${f%.css}; node_modules/.bin/cleancss $f -o $file.min.css; done
echo "done minifying 😘";
#$SHELL;