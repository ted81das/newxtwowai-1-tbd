<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_bruteforce_login extends wf_sn_af {
    static function get_label($label) {
        $labels = array(
            'title'   => esc_html__( 'Fix weak user passwords', 'security-ninja' ),
            'fixable' => true,
            'info'    => '',
            'msg_ok'  => esc_html__( 'Fix applied successfully.', 'security-ninja' ),
            'msg_bad' => esc_html__( 'Could not change the username.', 'security-ninja' ),
        );

        if ($label == 'info') {
            $return = array();
            $max_users_attack = 5;
            $passwords = file(WF_SN_PLUGIN_DIR . 'includes/10k-most-common.txt', FILE_IGNORE_NEW_LINES);
            $bad_usernames = array();
            $roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
            $users = [];

            foreach ($roles as $role) {
                $users = array_merge($users, get_users(['role' => $role]));
                if (count($users) >= $max_users_attack) {
                    break;
                }
            }

            foreach (array_slice($users, 0, $max_users_attack) as $user) {
                $passwords[] = $user->user_login;
                foreach ($passwords as $password) {
                    if (wf_sn_tests::try_login($user->user_login, $password)) {
                        $bad_usernames[] = $user->user_login;
                        break;
                    }
                }
            }

            $current_user = wp_get_current_user();
            $return = esc_html__( 'This fix can change the password for users that have a weak one. Enter the new desired password for each user or leave the input field blank to assign a randomly generated password.', 'security-ninja' ).'<br /><br />';
            foreach ($bad_usernames as $user) {
                $inputField = '<input type="text" id="users_' . $user . '" name="' . $user . '" value="" />';
                if ($current_user->user_login == $user) {
                    $return .= '<label for="users_' . $user . '"><strong>' . esc_html( $user ) . ':</strong></label> ' . $inputField . ' <span style="color:#F00; font-size:12px;">' . esc_html__( 'You are currently logged in as %s. Cannot set random password. If you leave this field empty the current password will not be changed.', 'security-ninja' ) . '</span><br />';
                    $return = sprintf( $return, esc_html( $user ) );
                } else {
                    $return .= '<label for="users_' . $user . '"><strong>' . $user . ':</strong></label> ' . $inputField . '<br />';
                }
            }
            return $return;
        }

        return array_key_exists($label, $labels) ? $labels[$label] : '';
    }

    static function fix() {
        global $wpdb;
        $fields = json_decode(stripslashes($_GET['fields']), true);
        $return_msg = '';

        foreach ($fields as $user => $password) {
            $user_id = $wpdb->get_var($wpdb->prepare('SELECT ID FROM ' . $wpdb->users . ' WHERE user_login = %s', $user));
            $current_user = wp_get_current_user();

            if (strlen($password) > 0) {
                $return_msg .= sprintf(
                    esc_html__( 'Password for user %s set to %s.', 'security-ninja' ),
                    esc_html( $user ),
                    esc_html( $password )
                ) . '<br />';
            } elseif ($current_user->user_login != $user) {
                $password = wp_generate_password();
                $return_msg .= sprintf(
                    esc_html__( 'Password for user %s set to %s.', 'security-ninja' ),
                    esc_html( $user ),
                    esc_html( $password )
                ) . '<br />';
            } else {
                $password = false;
                $return_msg .= esc_html__( 'Password for user %s was not changed.', 'security-ninja' );
                $return_msg = sprintf( $return_msg, esc_html( $user ) ) . '<br />';
            }

            if ($password) {
                wp_set_password($password, $user_id);
            }
        }

        $bad_users = wf_sn_tests::bruteforce_login();
        if ($bad_users['status'] == 10) {
            wf_sn_af::mark_as_fixed('bruteforce_login');
            return $return_msg . '<br />' . self::get_label('msg_ok');
        } else {
            $return_msg .= '<br /><span style="color:#F00">' . esc_html__( 'Some users still have weak passwords.', 'security-ninja' ) . '</span>';
            return $return_msg;
        }
    }
}