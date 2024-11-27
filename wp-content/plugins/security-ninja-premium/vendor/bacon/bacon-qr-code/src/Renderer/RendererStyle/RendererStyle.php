<?php
declare(strict_types = 1);

namespace WPSecurityNinja\Plugin\BaconQrCode\Renderer\RendererStyle;

use WPSecurityNinja\Plugin\BaconQrCode\Renderer\Eye\EyeInterface;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\Eye\ModuleEye;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\Module\ModuleInterface;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\Module\SquareModule;

final class RendererStyle
{
    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $margin;

    /**
     * @var ModuleInterface
     */
    private $module;

    /**
     * @var EyeInterface|null
     */
    private $eye;

    /**
     * @var Fill
     */
    private $fill;

    public function __construct(
        int $size,
        int $margin = 4,
        ?ModuleInterface $module = null,
        ?EyeInterface $eye = null,
        ?Fill $fill = null
    ) {
        $this->margin = $margin;
        $this->size = $size;
        $this->module = $module ?: SquareModule::instance();
        $this->eye = $eye ?: new ModuleEye($this->module);
        $this->fill = $fill ?: Fill::default();
    }

    public function withSize(int $size) : self
    {
        $style = clone $this;
        $style->size = $size;
        return $style;
    }

    public function withMargin(int $margin) : self
    {
        $style = clone $this;
        $style->margin = $margin;
        return $style;
    }

    public function getSize() : int
    {
        return $this->size;
    }

    public function getMargin() : int
    {
        return $this->margin;
    }

    public function getModule() : ModuleInterface
    {
        return $this->module;
    }

    public function getEye() : EyeInterface
    {
        return $this->eye;
    }

    public function getFill() : Fill
    {
        return $this->fill;
    }
}
