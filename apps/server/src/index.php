<?php
require '../../../vendor/autoload.php';

use Sat\CFDI;
use Sat\Cfdi\Xml;
use Sat\Cfdi\Complementos\Iedu;

echo CFDI::cfdi();
echo '<br>';
echo Xml::cfdi();
echo '<br>';
echo Iedu::iedu();
