<?php

require_once __DIR__ . '/phpqrcode/qrlib.php';

class QR
{
    public function __construct()
    {

    }

    public static function generar($url)
    {
        $qrFile =  __DIR__ . '/' . "qrcode.png";

        QRCode::png($url, $qrFile, 'L', '4', '4');

        return $qrFile;
    }
}
