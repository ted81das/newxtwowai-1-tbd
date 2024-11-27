<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_anyone_can_register extends wf_sn_af {
    static function get_label($label) {
        $labels = array(
            'title'   => esc_html__( 'Disable Anyone can register', 'security-ninja' ),
            'fixable' => true,
            'info'    => esc_html__( 'Fix will disable the "Anyone can register" option.', 'security-ninja' ),
            'msg_ok'  => esc_html__( 'Fix applied successfully.', 'security-ninja' ),
            'msg_bad' => esc_html__( 'Unable to apply fix.', 'security-ninja' ),
        );
        return array_key_exists($label, $labels) ? $labels[$label] : '';
    }

    static function toggle_fix($enable) {
        $current_setting = get_option('users_can_register');
        $desired_setting = $enable ? 1 : 0;

        if ($current_setting === $desired_setting) {
            return self::get_label('msg_ok');
        }

        if (\update_option('users_can_register', $desired_setting)) {
            self::mark_as_fixed('anyone_can_register');
            return self::get_label('msg_ok');
        } else {
            return self::get_label('msg_bad');
        }
    }

    static function fix() {
        return self::toggle_fix(false);
    }

    static function remove_fix() {
        return self::toggle_fix(true);
    }
}