#!/bin/sh
php -f RunTest.php ; dot -Tpng graph.dot > output.png
