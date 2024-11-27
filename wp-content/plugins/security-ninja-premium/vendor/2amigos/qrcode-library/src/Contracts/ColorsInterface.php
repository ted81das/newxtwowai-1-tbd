<?php

namespace WPSecurityNinja\Plugin\Da\QrCode\Contracts;

use WPSecurityNinja\Plugin\BaconQrCode\Renderer\Color\Alpha;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\Color\ColorInterface;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\Color\Rgb;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\RendererStyle\Fill;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\RendererStyle\Gradient;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\RendererStyle\GradientType;

interface ColorsInterface
{
    public const GRADIENT_VERTICAL = 'vertical';
    public const GRADIENT_HORIZONTAL = 'horizontal';
    public const GRADIENT_RADIAL = 'radial';
    public const GRADIENT_DIAGONAL = 'diagonal';
    public const GRADIENT_INVERSE_DIAGONAL = 'diagonal_inverse';

    /**
     * @param Alpha|Rgb|Gradient $color
     * @return void
     */
    public function setForegroundColor($color): void;

    /**
     * @return Rgb|Alpha|Gradient
     */
    public function getForegroundColor();

    /**
     * @param $color
     * @return void
     */
    public function setForegroundEndColor($color): void;

    /**
     * @return mixed
     */
    public function getForegroundEndColor();

    /**
     * @param Alpha|Rgb $color
     * @return void
     */
    public function setBackgroundColor($color): void;

    /**
     * @return void
     */
    public function unsetForegroundEndColor(): void;

    /**
     * @return Rgb|Alpha
     */
    public function getBackgroundColor();

    /**
     * @param string $type
     * @return void
     */
    public function setGradientType(string $type): void;

    /**
     * @return GradientType
     */
    public function getGradientTye();

    /**
     * @return Fill
     */
    public function buildFillColor();

    /**
     * @return void
     */
    public function forceUniformRgbColors(): void;
}
