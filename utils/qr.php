<?php

require_once __DIR__ . '/phpqrcode/qrlib.php';

function generarQR($url) {
    $tempDir = __DIR__ . '/';

    $fileName = "qrcode.png";

    $pngAbsoluteFilePath = $tempDir . $fileName;

    QRCode::png($url, $pngAbsoluteFilePath, 'L', '4', '4');

    return $pngAbsoluteFilePath;
}
