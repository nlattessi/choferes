<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$classMap = array(
    'FPDF' => __DIR__.'/../utils/fpdf/fpdf.php',
    'PdfHtml' => __DIR__.'/../utils/PdfHtml.php',
    'QR' => __DIR__.'/../utils/qr.php',
);
$loader->addClassMap($classMap);

return $loader;
