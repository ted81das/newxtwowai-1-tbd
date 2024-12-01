<?php

namespace Studiocart;

use WP_Query;
use WPCW\Core\Enrollment;

if (!defined('ABSPATH'))
	exit;
	
class WPCourseware {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name;
	private $service_label;
    
	public function __construct() {
		$this->service_name = "wpcourseware";
		$this->service_label = "WP Courseware";
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        if ( ! class_exists( 'WPCW_Plugin' ) ) {
            return;
        }
        
        add_filter('sc_integrations', array($this, 'add_wpcourseware_service'));
        add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_action('studiocart_wpcourseware_integrations', array($this, 'add_remove_to_wpcourseware'), 10, 3);
        add_action('sc_product_print_field_scripts', array($this, 'add_admin_scripts'));
    }
    
    public function add_admin_scripts($product_id) {
        ?>
        <script>
        jQuery('document').ready(function($){
            $('.ridwpcw_access:visible').each(function(){
                if ($(this).closest(".repeater-content").find('.ridwpcw_action select').val() != 'unenroll') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('.ridwpcw_action select').on('change', function(){
                var $field = $(this).closest(".repeater-content").find('.ridwpcw_access');
                if ($(this).val() != 'unenroll') {
                    $field.show();
                } else {
                    $field.hide();
                }
            });
        });
        </script>
        <?php
    }
	
    public function add_wpcourseware_service($options) {
        $options[$this->service_name] = $this->service_label;
        return $options;
    }
    
    public function add_integration_fields($fields, $save) {
        
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'wp-course-search-custom sc-selectize multiple',
                'id'			=> 'wp_citem',
                'label'		    => esc_html__('Select Course(s)','ncs-cart'),
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
                'class'		    => 'select',
                'id'			=> 'wpcw_action',
                'label'		    => esc_html__('Action','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => array('enroll' => esc_html__('Enroll','ncs-cart'), 'unenroll' => esc_html__('Unenroll','ncs-cart')),
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
            'checkbox' =>array(
                'class'		    => '',
                'id'			=> 'wpcw_notify',
                'label'		    => esc_html__('Send new users an email about their account.','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'checkbox',
                'value'		    => '',
                'class_size'    => '',
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
	
	public function add_remove_to_wpcourseware($int, $sc_product_id, $order) {       
        $enroll = new Enrollment();
		$courses = array();
        
        if (isset($int['wp_citem'])) {
            if(count($int['wp_citem']) > 0){
                $courses = $int['wp_citem'];
            } else {
                sc_log_entry($order['id'], esc_html__( "No WP Courseware course ID found", 'ncs-cart'));  
                return;
            }
        }
        
        $create_args = array(
            'send_email'    => isset($int['wpcw_notify']), 
            'user_role'     => 'subscriber'
        );
        
        $student_id = sc_get_order_user_id($order, $create_args);
        
        if ($int['wpcw_action']=='enroll') {
            $enroll->enroll_student($student_id, $courses, 'add', $force = true); 
            sc_log_entry($order['id'], esc_html__( "User enrolled in WP Courseware course", 'ncs-cart'));  
        } else {  
            $enroll->unenroll_student( $student_id, $courses );
            sc_log_entry($order['id'], esc_html__( "User unenrolled from WP Courseware course", 'ncs-cart'));  
        }
    }   
      
    public function get_courses() {
		$selected_courses = array("" => esc_html__( "-- Select --", 'ncs-cart'));
        if ( $courses = wpcw()->courses->get_courses( array('fields' => array( 'course_id', 'course_title' ), 'number' => -1 ), true ) ) {
            foreach ( $courses as $course ) {
                $selected_courses[$course->course_id] = $course->course_title;
            }
        }
		return $selected_courses;
	} 
}