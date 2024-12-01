<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;

class Elementor {
    
	private $service_name;
	private $service_label;

	public function __construct() {
        $this->service_name = 'elementor';
        $this->service_label = 'Elementor';
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {

        // Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));
        
        // Register widgets
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        add_action('sc_do_orderbump', [ $this, 'maybe_do_elementor_bump_template'], 9);

        add_filter("sc_product_setting_tab_orderbump_fields",  [ $this, 'add_elementor_bump_template_field']);

        add_filter('sc_orderbump_args', [$this, 'add_ele_template_arg']);
    }

    public function add_ele_template_arg($args) {
        global $scp;
        if($args['key'] == 'main' && isset($scp->ele_ob_template)) {
            $args['ele_template'] = $scp->ele_ob_template;
        } else if($args['key'] != 'main' && isset($scp->order_bump_options[$args['key']]['ele_ob_template'])) {
            $args['ele_template'] = $scp->order_bump_options[$args['key']]['ele_ob_template'];
        }
        return $args;
    }

    public function add_elementor_bump_template_field($fields) {
        $ret = array();

        foreach($fields as $field) {
            
            if($field['id'] == '_sc_order_bump_options') {
                $new = array();
                foreach($field['fields'] as $rep) {
                    $new[] = $rep;
                    if(count($new) == 1) {
                        $new[] = array('select'=>array(
                            'class'		    => '',
                            'description'	=> __('Used on Elementor pages only','ncs-cart'),
                            'id'			=> 'ele_ob_template',
                            'label'	    	=> __('Elementor Template','ncs-cart'),
                            'placeholder'	=> '',
                            'type'		    => 'select',
                            'value'		    => '',
                            'selections'    => self::get_pages(),
                            'class_size'=> '',
                        ));
                    }
                }
                $field['fields'] = $new;
            }

            $ret[] = $field;
            if($field['id'] == '_sc_order_bump') {
                $ret[] = array(
                    'class'		    => '',
                    'description'	=> __('Used on Elementor pages only','ncs-cart'),
                    'id'			=> '_sc_ele_ob_template',
                    'label'	    	=> __('Elementor Template','ncs-cart'),
                    'placeholder'	=> '',
                    'type'		    => 'select',
                    'value'		    => '',
                    'selections'    => self::get_pages(),
                    'class_size'=> ''
                );
            } 
        }
        return $ret;
    }

    /**
	 * Register the elementor widgets.
	 *
	 * @since    1.0.0
	 */
    public function register_widgets() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '../vendor/elementor/checkoutform.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '../vendor/elementor/countdown.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '../vendor/elementor/bumpheading.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '../vendor/elementor/bumptext.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '../vendor/elementor/bumpcheckbox.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '../vendor/elementor/bumpimage.php';
    }
    
    public function maybe_do_elementor_bump_template($args) {

        global $sc_bump, $post;

        add_action('sc_do_orderbump', 'sc_do_bump_template', 10);

        if ( !\Elementor\Plugin::$instance->documents->get( $post->ID )->is_built_with_elementor() ) {
            return;
        }

        if(isset($args['ele_template'])) {
            $id = $args['ele_template'];
        } else if(!$id = get_option('_sc_'.$this->service_name.'-bump-template', false)) {
            return;
        }

        $sc_bump = $args;
    
        $attributes = array(
            'id' => $id
        );
        
        remove_action('sc_do_orderbump', 'sc_do_bump_template');
        
        echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display(  $attributes['id'] );
            
    }

    public function settings_section($intigrations) {   
        $intigrations[$this->service_name] = $this->service_label;
        return $intigrations;
    }
    
    public function service_settings($options) {
        $options[$this->service_name] = array(
            $this->service_name.'-bump-template' => array(
                'class' 		=> 'wide-fat',
                'type'          => 'select',
                'label'         => esc_html__( 'Default Bump Template', 'ncs-cart' ),
                'settings'      => array(
					'id'            => '_sc_'.$this->service_name.'-bump-template', 
					'value'         => '',
					'selections'    => self::get_pages(),					
					'description'   => '',
				),
                'tab'=>'integrations'
            ),
        );
        return $options;
    }

    private static function get_pages(){
        $args = array(
            'post_type'      => 'elementor_library',
            'posts_per_page' => 30,
            'tabs_group' => 'library',
            'elementor_library_type' => 'section',
          );
          
        $elementor_templates = get_posts($args);

        $options = array('' => __('Use Default', 'ncs-cart'));
        foreach ( $elementor_templates as $page ) {
            $options[$page->ID] = $page->post_title . ' (ID: '.$page->ID.')';
        }
        return $options;
	}
}