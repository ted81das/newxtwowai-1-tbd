<?php

/*
 * This file is part of the 2amigos/qrcode-library project.
 *
 * (c) 2amigOS! <http://2am.tech/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace WPSecurityNinja\Plugin\Da\QrCode\Format;

/**
 * Class Bitcoin formats a string to properly create a Bitcoin URI
 *
 * @package Da\QrCode\Format
 */
class BtcFormat extends AbstractFormat
{
    /**
     * @var string the name of the receiver.
     */
    public $name;
    /**
     * @var string the message that describes the transaction.
     */
    public $message;
    /**
     * @var string the Bitcoin address
     */
    public $address;
    /**
     * @var string the payable amount
     */
    public $amount;

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        $query = http_build_query(['amount' => $this->amount, 'label' => $this->name, 'message' => $this->message]);

        return "bitcoin:{$this->address}?{$query}";
    }
}
