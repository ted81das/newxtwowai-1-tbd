<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;

class LearnDash {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name;

	public function __construct() {
        $this->service_name = 'learndash';
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
         if (! function_exists('ld_update_course_access')) {
            return;
        }
        
        add_filter('sc_integrations', array($this, 'add_learndash_service'));
        add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_to_learndash'), 10, 3);
        add_filter('sc_integration_service_action_field_logic_options', array($this, 'add_learndash_to_filter'));
    }
    
    public function add_learndash_service($options) {
        $options[$this->service_name] = "LearnDash";
        return $options;
    }
    
    public function add_learndash_to_filter($options) {
        $options[] = $this->service_name;
        return $options;
    }
    
    public function add_integration_fields($fields, $save) {
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'ld_course',
                'id'			=> 'ld_course',
                'label'		    => __('Course','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_courses(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'ld_group',
                'id'			=> 'ld_group',
                'label'		    => __('Groups','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_groups(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        
        return $fields;
    }
        
    public function add_remove_to_learndash($int, $sc_product_id, $order) {        
        if( empty($int['ld_course']) && empty($int['ld_group']) ){
			return;
	    }
        
        $course_id = $int['ld_course'];
        $group_id = $int['ld_group'];
            
        if($int['service_action'] == 'subscribed') {
            $user_id = sc_get_order_user_id($order, $create=true);
            if ( $course_id ) {
                self::update_add_course_access( $course_id, $user_id, $order['id'] );

                // Reset enrollment date to order date
                update_user_meta( $user_id, 'course_' . $course_id . '_access_from', time() );
                sc_log_entry($order['id'], "User added to LearnDash Course ID: " .$course_id);
            }

            if ( $group_id ) {
                self::update_add_group_access( $group_id, $user_id, $order['id'] );

                // Reset enrollment date to order date
                update_user_meta( $user_id, 'learndash_group_enrolled_' . $group_id, time() );
                sc_log_entry($order['id'], "User added to LearnDash Group ID: " .$group_id);
            }
        } else {
            $user_id = sc_get_order_user_id($order);
            if ( $course_id ) {
                ld_update_course_access( $user_id, $course_id, $remove = true );
                sc_log_entry($order['id'], "User removed from LearnDash Course ID: " .$course_id);
           }
            if ( $group_id ) {
                ld_update_group_access( $user_id, $group_id, $remove = true );
                sc_log_entry($order['id'], "User removed from LearnDash Group ID: " .$course_id);
            }
        }        
    }
    
    /**
	 * Add course access
	 * 
	 * @param int $course_id ID of a course
	 * @param int $user_id   ID of a user
	 */
	private static function update_add_course_access( $course_id, $user_id, $order_id ) {
		// check if user already enrolled
		if ( ! self::is_user_enrolled_to_course( $user_id, $course_id ) ) {
			ld_update_course_access( $user_id, $course_id );
		} elseif ( self::is_user_enrolled_to_course( $user_id, $course_id ) && ld_course_access_expired( $course_id, $user_id ) ) {
			
			// Remove access first
			ld_update_course_access( $user_id, $course_id, $remove = true );

			// Re-enroll to get new access from value
			ld_update_course_access( $user_id, $course_id );
		}
	}
    
    /**
	 * Add group access
	 * 
	 * @param  int    $group_id LearnDash group ID
	 * @param  int    $user_id  WP_User ID
	 * @param  int    $order_id WC order ID
	 * @return void
	 */
	private static function update_add_group_access( $group_id, $user_id, $order_id ) {
		if ( ! learndash_is_user_in_group( $user_id, $group_id ) ) {
			ld_update_group_access( $user_id, $group_id );
		}
	}
    
    /**
	 * Check if a user is already enrolled to a course
	 * 
	 * @param  integer $user_id   User ID
	 * @param  integer $course_id Course ID
	 * @return boolean            True if enrolled|false otherwise
	 */
	private static function is_user_enrolled_to_course( $user_id = 0, $course_id = 0 ) {
		$enrolled_courses = learndash_user_get_enrolled_courses( $user_id );

		if ( is_array( $enrolled_courses ) && in_array( $course_id, $enrolled_courses ) ) {
			return true;
		}

		return false;
	}
    
    public function get_courses() {
        $courses = array(''=>__('-- None Found --', 'ncs-cart') );
		$args = array( 'post_type' => 'sfwd-courses', 'posts_per_page' => -1 );
        $the_query = new \WP_Query( $args );
        if ( $the_query->have_posts() ) {
            $courses = array(''=>__('-- Select --', 'ncs-cart'));
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                $courses[ get_the_ID() ] = get_the_title();
            }            
        }
        wp_reset_query();
		return $courses;
	}
    
    public function get_groups() {
		$options = wp_list_pluck( learndash_get_groups(), 'post_title', 'ID' ); 
        if(is_array($options) && !empty($options)) return array('' => '- Select -') + $options;
        else return array('' => 'No groups found');
    }
}