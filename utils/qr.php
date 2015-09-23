<?php

require_once __DIR__ . '/phpqrcode/qrlib.php';

class QR
{
    public function __construct()
    {

    }

    public static function generar($url, $cacheDir, $dni)
    {
        $filename = 'qrcode' . $dni . '.png';
        $qrFile =  $cacheDir . '/' . $filename;

        QRCode::png($url, $qrFile, 'L', '4', '4');

        return $qrFile;
    }
}
