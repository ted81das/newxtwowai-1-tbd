<?php
namespace WPSecurityNinja\Plugin;

/**
 * Disable Application Passwords feature
 *
 * @author  Lars Koudal
 * @since   v0.0.1
 * @version v1.0.0  Wednesday, December 16th, 2020.
 * @see     wf_sn_af
 * @global
 */
class Wf_Sn_Af_Fix_Application_Passwords extends Wf_Sn_Af {
    protected static $labels;

    public static function init_labels() {
        self::$labels = [
            'title'   => esc_html__( 'Disable Application Passwords', 'security-ninja' ),
            'fixable' => false,
            'info'    => esc_html__( 'Please go to the Fixes menu to disable', 'security-ninja' ),
            'msg_ok'  => '',
            'msg_bad' => '',
        ];
    }


    public static function get_label($label) {
        if (!array_key_exists($label, self::$labels)) {
            throw new \InvalidArgumentException("Label '{$label}' not found.");
        }

        return self::$labels[$label];
    }

    public static function fix() {
        return self::get_label('info');
    }
}