<?php
declare(strict_types = 1);

namespace WPSecurityNinja\Plugin\BaconQrCode\Renderer;

use WPSecurityNinja\Plugin\BaconQrCode\Encoder\QrCode;

interface RendererInterface
{
    public function render(QrCode $qrCode) : string;
}
