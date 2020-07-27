#!/bin/bash
# For deleting min.css files in npm_package > assets > styles > min
# rm -f $npm_package_assets_styles_min
echo "Deleting min file(s)... 🚦🤓";
for f in $npm_package_assets_styles_min; do file=${f%.js}; rm -f $f ; done
echo "done deleting. 😘";
#$SHELL;