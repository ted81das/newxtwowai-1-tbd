<?php
declare(strict_types = 1);

namespace WPSecurityNinja\Plugin\BaconQrCode\Renderer\Path;

interface OperationInterface
{
    /**
     * Translates the operation's coordinates.
     */
    public function translate(float $x, float $y) : self;
}
