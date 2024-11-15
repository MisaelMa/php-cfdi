<?php
require '../../../vendor/autoload.php';

use Sat\CFDI;
use Sat\CFDI\Xml;

echo CFDI::cfdi();
echo '<br>';
echo Xml::cfdi();
