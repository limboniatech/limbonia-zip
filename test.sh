#/bin/bash

./vendor/bin/phpunit --bootstrap vendor/autoload.php --bootstrap lib/autoloader.php --testdox tests/*.php
