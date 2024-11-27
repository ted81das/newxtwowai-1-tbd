<?php

namespace WPSecurityNinja\Plugin\Da\QrCode\Enums;

use WPSecurityNinja\Plugin\Da\QrCode\Format\BookMarkFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\BtcFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\GeoFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\ICalFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\MailMessageFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\MailToFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\MeCardFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\MmsFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\PhoneFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\SmsFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\VCardFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\WifiFormat;
use WPSecurityNinja\Plugin\Da\QrCode\Format\YoutubeFormat;
use WPSecurityNinja\Plugin\MabeEnum\Enum;

final class Format extends Enum
{
    public const TEXT = 'text';
    public const BOOK_MARK = BookMarkFormat::class;
    public const BTC = BtcFormat::class;
    public const GEO = GeoFormat::class;
    public const I_CAL = ICalFormat::class;
    public const MAIL_MESSAGE = MailMessageFormat::class;
    public const MAIL_TO = MailToFormat::class;
    public const ME_CARD = MeCardFormat::class;
    public const MMS = MmsFormat::class;
    public const PHONE_FORMAT = PhoneFormat::class;
    public const SNS_FORMAT = SmsFormat::class;
    public const V_CARD = VCardFormat::class;
    public const WIFI = WifiFormat::class;
    public const YOUTUBE = YoutubeFormat::class;
}
