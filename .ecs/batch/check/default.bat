:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
:: src
start vendor\bin\ecs check vendor/markocupic/dummy-bundle/src --config vendor/markocupic/dummy-bundle/.ecs/config/default.php
::
cd vendor/markocupic/dummy-bundle/.ecs./batch/fix
