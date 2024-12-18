<?php

/*
 * This file is part of the 2amigos/qrcode-library project.
 *
 * (c) 2amigOS! <http://2am.tech/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace WPSecurityNinja\Plugin\Da\QrCode\Writer;

use WPSecurityNinja\Plugin\BaconQrCode\Renderer\Image\EpsImageBackEnd;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\ImageRenderer;
use WPSecurityNinja\Plugin\BaconQrCode\Renderer\RendererStyle\RendererStyle;
use WPSecurityNinja\Plugin\BaconQrCode\Writer;
use WPSecurityNinja\Plugin\Da\QrCode\Contracts\QrCodeInterface;

class EpsWriter extends AbstractWriter
{
    /**
     * EpsWriter constructor.
     */
    public function __construct()
    {
        parent::__construct(new EpsImageBackEnd());
    }

    /**
     * @inheritdoc
     */
    public function writeString(QrCodeInterface $qrCode): string
    {
        $renderer = $this->buildRenderer($qrCode);

        $writer = new Writer($renderer);

        return  $writer->writeString(
            $qrCode->getText(),
            $qrCode->getEncoding(),
            $this->convertErrorCorrectionLevel($qrCode->getErrorCorrectionLevel())
        );
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): string
    {
        return 'image/eps';
    }
}
