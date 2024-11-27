<?php

namespace WPSecurityNinja\Plugin\Da\QrCode\Enums;

use WPSecurityNinja\Plugin\MabeEnum\Enum;

final class Gradient extends Enum
{
    public const GRADIENT_VERTICAL = 'vertical';
    public const GRADIENT_HORIZONTAL = 'horizontal';
    public const GRADIENT_RADIAL = 'radial';
    public const GRADIENT_DIAGONAL = 'diagonal';
    public const GRADIENT_INVERSE_DIAGONAL = 'diagonal_inverse';
}
