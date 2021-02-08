:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
:: src
start vendor\bin\ecs check vendor/markocupic/dummy-bundle/src --fix --config vendor/markocupic/dummy-bundle/.ecs/config/default.php
:: legacy
start vendor\bin\ecs check vendor/markocupic/dummy-bundle/src/Resources/contao --fix --config vendor/markocupic/dummy-bundle/.ecs/config/legacy.php
:: templates
start vendor\bin\ecs check vendor/markocupic/dummy-bundle/src/Resources/contao/templates --fix --config vendor/markocupic/dummy-bundle/.ecs/config/template.php
::
cd vendor/markocupic/dummy-bundle/.ecs./batch/fix
