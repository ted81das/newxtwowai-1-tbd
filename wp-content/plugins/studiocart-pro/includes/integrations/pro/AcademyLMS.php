<?php

namespace AcademyLMS;

if (!defined('ABSPATH'))
	exit;

class AcademyLMS {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name;
	private $service_label;
	private $api_key;

	public function __construct() {
        $this->service_name = 'academylms';
        $this->service_label = 'Academy LMS';
        add_action('plugins_loaded', array( $this, 'init'));
    }
    
    public function init() {
        if ( !class_exists('Academy') ){
            return;
        }
        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
		add_filter( 'sc_integrations', array($this, 'add_service' ) );
		add_filter( 'sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_action( 'studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_student_to_academy'), 10, 3);
        
    }

    public function settings_section($integrations) {   
        $integrations[$this->service_name] = $this->service_label;
        return $integrations;
    }

    public function add_service($options) {
        $options[$this->service_name] = $this->service_label;
        return $options;
    }

    public function add_integration_fields($fields, $save){
        
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'academyaction',
                'id'			=> 'academy_action',
                'label'		    => __('Action','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ['add'=>'Add student to course', 'remove'=>'Remove student from course'],
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'academylms',
                            'compare' => '=',
                        )
                )
            ),
        );

        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'academy_course',
                'id'			=> 'academy_course',
                'label'		    => __('Courses','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_course(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'academylms',
                            'compare' => '=',
                        )
                )
            )
        );

        return $fields;
    }

    public function add_remove_student_to_academy($int, $sc_product_id, $order ){
        if( $int['services'] != 'academylms' ){
			return;
	    }
        $user_id = sc_get_order_user_id($order, ['send_email' => null, 'user_role' => 'academy_student']);
        $course_id = $int['academy_course'] ?? '';
        if( $int['academy_action'] == 'add') {
            \Academy\Helper::do_enroll( $course_id, $user_id );
        } else {
            \Academy\Helper::cancel_course_enroll($course_id, $user_id);
        }
    }

    public function get_course(){
        $posts = get_posts(array(
            'post_type' => 'academy_courses',
            'posts_per_page' => -1,
        ));

        $options = array(''=>'No course found');
        $options = array();
        foreach( $posts as $post ){
            $options[$post->ID] = $post->post_title;
        }
        return $options; 
    }
    
}