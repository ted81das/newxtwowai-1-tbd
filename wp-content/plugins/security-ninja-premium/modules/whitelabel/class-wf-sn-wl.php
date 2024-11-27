<?php

namespace WPSecurityNinja\Plugin;

if (!defined('ABSPATH')) {
	exit;
}

define('WF_SN_WL_OPTIONS_KEY', 'wf_sn_wl');


/**
 * Class Wf_Sn_Wl
 *
 * Handles white labeling functionality for the Security Ninja plugin.
 *
 * @package WPSecurityNinja\Plugin
 */
class Wf_Sn_Wl
{

	public static $options = null;


	/**
	 * Initializes the white labeling functionality.
	 *
	 * @return void
	 */
	public static function init()
	{
		self::$options = self::get_options();

		if (is_admin()) {
			// add tab to Security Ninja tabs
			add_filter('sn_tabs', array(__NAMESPACE__ . '\Wf_Sn_Wl', 'sn_tabs'));
			add_action('admin_init', array(__NAMESPACE__ . '\Wf_Sn_Wl', 'admin_init'));
			// check and set default settings
			self::default_settings(false);
		}

		if (self::is_active()) {

			add_action('admin_enqueue_scripts', array(__NAMESPACE__ . '\Wf_Sn_Wl', 'updates_core_page'));
			add_action('admin_enqueue_scripts', array(__NAMESPACE__ . '\Wf_Sn_Wl', 'do_action_admin_head_add_extra_css'));
			add_filter('all_plugins', array(__NAMESPACE__ . '\Wf_Sn_Wl', 'do_filter_all_plugins'), 9999);

			add_action('pre_current_active_plugins', array(__NAMESPACE__ . '\Wf_Sn_Wl', 'action_pre_current_active_plugins'));
		}
	}

	/**
	 * Removes plugin from list.
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @param array $plugins List of all plugins.
	 * @return array Modified list of plugins.
	 */
	public static function do_filter_all_plugins($plugins)
	{

		$keys = array('security-ninja-premium/security-ninja.php', 'security-ninja/security-ninja.php');
		foreach ($keys as $key) {
			if (isset($plugins[$key])) {
				unset($plugins[$key]);
			}
		}
		return $plugins;
	}





	/**
	 * Outputs simple CSS to hide the plugin icon.
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @return void
	 */
	public static function do_action_admin_head_add_extra_css()
	{
		if (!self::is_active()) {
			return;
		}
		wp_register_style('admin-custom-style', false, array(), Wf_Sn::get_plugin_version());
		wp_enqueue_style('admin-custom-style');
		wp_add_inline_style('admin-custom-style', self::get_custom_css());
	}

	/**
	 * enqueue_custom_admin_style.
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Tuesday, May 7th, 2024.
	 * @access	public static
	 * @return void
	 */
	public static function enqueue_custom_admin_style()
	{
		wp_register_style('admin-extra-sec-styling', false, array(), Wf_Sn::get_plugin_version());
		wp_enqueue_style('admin-extra-sec-styling');
		wp_add_inline_style('admin-extra-sec-styling', self::get_custom_css());
	}


	/**
	 * get_custom_css.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Wednesday, April 24th, 2024.
	 * @access  private static
	 * @global
	 * @return string Custom CSS.
	 */
	private static function get_custom_css()
	{
		return '/* Set max width of menu icon */
.menu-top.toplevel_page_wf-sn img,
#toplevel_page_wf-sn .wp-menu-image img {max-width:20px;}
#security-ninja-update {display:none;}
tr[data-slug="security-ninja"] .open-plugin-details-modal {display:none;}';
	}

	/**
	 * Update strings on the update-core.php page.
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, May 20th, 2024.
	 * @access	public static
	 * @return void
	 */
	public static function updates_core_page()
	{
		global $pagenow;

		if ('update-core.php' === $pagenow) {
			$default_names = ['Security Ninja', 'Security Ninja (Premium)'];
			$newtitle = self::get_new_name();
			$newicon = self::get_new_icon_url();

			if (false !== $newtitle) {
				// Escaping for security
				$newtitle_js = esc_js($newtitle);
				$default_names_js = json_encode(array_map('esc_js', $default_names));

				// Conditionally set the new icon JS
				$newicon_js = $newicon !== false ? esc_js($newicon) : '';

				wp_add_inline_script(
					'updates',
					"
                var _secnin_default_names = $default_names_js;
                var _secnin_branded_name = '$newtitle_js';
                var _secnin_icon_url = '$newicon_js';
                
                // Replace or remove image
                document.querySelectorAll( '#update-plugins-table .plugin-title img[src*=\'security-ninja\']' )
                .forEach(function(plugin) {
                    if (_secnin_icon_url) {
                        jQuery(plugin).attr('src', _secnin_icon_url).attr('width', 18);
                    } else {
                        jQuery(plugin).remove();
                    }
                });
                
                // Remove 'View details' link
                jQuery('a[href*=\'&plugin=security-ninja&\']').remove();
                
                // Renames plugin title
                document.querySelectorAll( '#update-plugins-table .plugin-title strong' )
                .forEach(function(plugin) {
                    _secnin_default_names.forEach(function(default_name) {
                        if (default_name === plugin.innerText) {
                            plugin.innerText = _secnin_branded_name;
                        }
                    });
                });
                "
				);
			}
		}
	}



	/**
	 * Hides the plugin from list of active plugins
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @return void
	 */
	public static function action_pre_current_active_plugins()
	{
		global $wp_list_table;
		$hidearr   = array('security-ninja/security-ninja.php', 'security-ninja-premium/security-ninja.php');
		$myplugins = $wp_list_table->items;
		foreach ($myplugins as $key => $val) {
			if (in_array($key, $hidearr, true)) {

				$new_name        = self::get_new_name();
				$new_url         = self::get_new_url();
				$new_author_name = self::get_new_author_name();
				$new_desc        = self::get_new_desc();
				$wl_newiconurl   = self::get_new_icon_url();

				if ($wl_newiconurl) {
					$wp_list_table->items[$key]['icons']['default'] = $wl_newiconurl;
				}

				if ($new_name) {
					$wp_list_table->items[$key]['Name'] = $new_name;
				}
				if ($new_name) {
					$wp_list_table->items[$key]['Title'] = $new_name;
				}

				if ($new_author_name) {
					$wp_list_table->items[$key]['Author'] = $new_author_name;
				}
				if ($new_author_name) {
					$wp_list_table->items[$key]['AuthorName'] = $new_author_name;
				}

				if ($new_url) {
					$wp_list_table->items[$key]['PluginURI'] = $new_url;
				}
				if ($new_url) {
					$wp_list_table->items[$key]['AuthorURI'] = $new_url;
				}

				if ($new_desc) {
					$wp_list_table->items[$key]['Description'] = $new_desc;
				}
			}
		}
	}


	/**
	 * get_options.
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @return array White label options.
	 */
	public static function get_options()
	{

		if (!is_null(self::$options)) {
			return self::$options;
		}

		$defaults = array(
			'wl_active'         => '0',
			'wl_newname'        => 'Security Ninja',
			'wl_newdesc'        => '',
			'wl_newauthor'      => '',
			'wl_newurl'         => 'https://wpsecurityninja.com/',
			'wl_newiconurl'     => '',
			'wl_newmenuiconurl' => '',
		);
		$options  = get_option('wf_sn_wl', array());
		$options  = array_merge($defaults, $options);
		return $options;
	}



	/**
	 * add new tab
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @param array $tabs Existing tabs.
	 * @return array Modified tabs.
	 */
	public static function sn_tabs($tabs)
	{

		$whitelabel_tab = array(
			'id'       => 'sn_whitelabel',
			'class'    => '',
			'label'    => esc_html__('White label', 'security-ninja'),
			'callback' => array(__NAMESPACE__ . '\\wf_sn_wl', 'do_page'),
		);

		// Check if active and then remove the tab
		if (self::is_active()) {

			$whitelabel_tab = array(
				'id'       => 'sn_whitelabel',
				'class'    => 'hide',
				'label'    => esc_html__('White label', 'security-ninja'),
				'callback' => array(__NAMESPACE__ . '\\wf_sn_wl', 'do_page'),
			);

			$tabs[] = $whitelabel_tab;
			return $tabs;
		}

		$done     = 0;
		$tabcount = count($tabs);
		for ($i = 0; $i < $tabcount; $i++) {
			if ('sn_whitelabel' === $tabs[$i]['id']) {
				$tabs[$i] = $whitelabel_tab;
				$done       = 1;
				break;
			}
		} // for
		if (!$done) {
			$tabs[] = $whitelabel_tab;
		}
		return $tabs;
	}



	/**
	 * Display admin page
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @return void
	 */
	public static function do_page()
	{
		global $wpdb, $secnin_fs;

?>
		<div class="submit-test-container card">
			<h3><?php esc_html_e('White label', 'security-ninja'); ?></h3>
			<?php
			echo '<form action="options.php" method="post">';
			settings_fields('wf_sn_wl');

			echo '<h3 class="ss_header">' . esc_html__('Settings', 'security-ninja') . '</h3>';
			echo '<table class="form-table"><tbody>';
			echo '<tr valign="top">
				<th scope="row"><label for="wf_sn_wl_active">' . esc_html__('Enable whitelabel', 'security-ninja') . '</label></th>
				<td class="sn-cf-options">';

			Wf_Sn::create_toggle_switch(
				'wf_sn_wl_wl_active',
				array(
					'saved_value' => self::$options['wl_active'],
					'option_key'  => 'wf_sn_wl[wl_active]',
				)
			);

			echo '<p class="description">' . esc_html__('This option allows you to white label the plugin, customizing it with your own branding.', 'security-ninja') . '</p></br>';

			echo '<p><strong>' . esc_html__('Warning - Enabling white labeling will make this tab disappear.', 'security-ninja') . '</strong> ' . esc_html__('To disable white labeling or change the branding, you will need to manually navigate to a specific URL in your browser and reload the page.', 'security-ninja') . '</p></br>';

			echo '<p>' . esc_html__('Bookmark the following URL to easily access the white label settings again:', 'security-ninja') . '</p></br>';

			?>
			<pre><?php echo esc_url(admin_url('admin.php?page=wf-sn')); ?><strong>#sn_whitelabel</strong></pre>
			</td>
			</tr>
			<tr>
				<th scope="row"><label for="input_id"><?php echo esc_html__('Plugin Name', 'security-ninja'); ?></label></th>
				<td><input name="<?php echo esc_attr('wf_sn_wl'); ?>[wl_newname]" type="text" id="input_id" value="<?php echo esc_attr(self::$options['wl_newname']); ?>" class="regular-text" placeholder="<?php esc_attr_e('Security Ninja', 'security-ninja'); ?>"></td>
			</tr>

			<tr>
				<th scope="row"><label for="input_id"><?php echo esc_html__('Plugin Description', 'security-ninja'); ?></label></th>
				<td><input name="<?php echo esc_attr('wf_sn_wl'); ?>[wl_newdesc]" type="text" id="input_id" value="<?php echo esc_attr(self::$options['wl_newdesc']); ?>" class="regular-text" placeholder="<?php esc_attr_e('Since 2011 Security Ninja has helped thousands of site owners like you to feel safe!', 'security-ninja'); ?>"></td>
			</tr>

			<tr>
				<th scope="row"><label for="input_id"><?php echo esc_html__('Author Name', 'security-ninja'); ?></label></th>
				<td><input name="<?php echo esc_attr('wf_sn_wl'); ?>[wl_newauthor]" type="text" id="input_id" value="<?php echo esc_attr(self::$options['wl_newauthor']); ?>" class="regular-text" placeholder="<?php esc_attr_e('WP Security Ninja', 'security-ninja'); ?>"></td>
			</tr>

			<tr>
				<th scope="row"><label for="input_id"><?php echo esc_html__('Author URL', 'security-ninja'); ?></label></th>
				<td><input name="<?php echo esc_attr('wf_sn_wl'); ?>[wl_newurl]" type="text" id="input_id" value="<?php echo esc_attr(self::$options['wl_newurl']); ?>" class="regular-text" placeholder="https://wpsecurityninja.com/"></td>
				<p class="description"><?php esc_html_e('Enter the new URL for both the author and the plugin.', 'security-ninja'); ?></p>
			</tr>

			<tr>
				<th scope="row"><label for="input_id"><?php echo esc_html__('Plugin Icon URL', 'security-ninja'); ?></label></th>
				<td>
					<input name="<?php echo esc_attr('wf_sn_wl'); ?>[wl_newiconurl]" type="text" id="input_id" value="<?php echo esc_attr(self::$options['wl_newiconurl']); ?>" class="regular-text" placeholder="">
					<p class="description"><?php esc_html_e('The little square image used to represent the plugin, eg on the update-core page.', 'security-ninja'); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="input_id"><?php echo esc_html__('Plugin Menu Icon URL', 'security-ninja'); ?></label></th>
				<td>
					<input name="<?php echo esc_attr('wf_sn_wl'); ?>[wl_newmenuiconurl]" type="text" id="input_id" value="<?php echo esc_attr(self::$options['wl_newmenuiconurl']); ?>" class="regular-text" placeholder="">
					<p class="description"><?php esc_html_e('This is the little menu icon in the sidebar', 'security-ninja'); ?></p>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<p class="submit"><input type="submit" value="<?php esc_attr_e('Save Changes', 'security-ninja'); ?>" class="input-button button-primary" name="Submit" />
				</td>
			</tr>
			</tbody>
			</table>

			</form>

		</div>
<?php
	}





	/**
	 * Sets default white label settings.
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @param bool $force Whether to force update default settings.
	 * @return void
	 */
	public static function default_settings($force = false)
	{
		$defaults = array(
			'wl_active'         => '0',
			'wl_newname'        => '',
			'wl_newdesc'        => '',
			'wl_newauthor'      => '',
			'wl_newurl'         => '',
			'wl_newiconurl'     => '',
			'wl_newmenuiconurl' => '',

		);

		$options = get_option('wf_sn_wl');

		if ($force || !$options || !$options['wl_active']) {
			update_option('wf_sn_wl', $defaults, false);
		}
	} // default_settings


	/**
	 * Performs cleanup when the plugin is deactivated.
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @return void
	 */
	public static function deactivate()
	{
		$centraloptions = Wf_Sn::get_options();
		if (!isset($centraloptions['remove_settings_deactivate'])) {
			return;
		}
		if ($centraloptions['remove_settings_deactivate']) {
			wp_clear_scheduled_hook('secnin_run_core_scanner');
			delete_option('wf_sn_wl');
		}
	}




	/**
	 * Retrieves the white labeled URL.
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @return string White labeled URL or empty string.
	 */
	public static function get_new_url()
	{
		$newurl = '';
		if ((isset(self::$options['wl_newurl']))
			&& ('' !== self::$options['wl_newurl'])
		) {
			$newurl = self::$options['wl_newurl'];
		}
		return $newurl;
	}


	/**
	 * Retrieves the white labeled icon URL.
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @access	public static
	 * @return	mixed
	 */
	public static function get_new_icon_url()
	{
		$newurl = '';
		if ((isset(self::$options['wl_newiconurl']))
			&& ('' !== self::$options['wl_newiconurl'])
		) {
			$newurl = self::$options['wl_newiconurl'];
		}
		return $newurl;
	}




	/**
	 * Retrieves the white labeled menu icon URL.
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @return string White labeled menu icon URL.
	 */
	public static function get_new_menu_icon_url()
	{
		$newmenuiconurl = '';
		if ((isset(self::$options['wl_newmenuiconurl']))
			&& ('' !== self::$options['wl_newmenuiconurl'])
		) {
			$newmenuiconurl = self::$options['wl_newmenuiconurl'];
		}
		return $newmenuiconurl;
	}





	/**
	 * Returns the whitelabel name of plugin, if any - else returns "Security";
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @return   mixed
	 */
	public static function get_new_name()
	{
		$newname = 'Security';
		if ((isset(self::$options['wl_newname']))
			&& ('' !== self::$options['wl_newname'])
		) {
			$newname = self::$options['wl_newname'];
		}
		return $newname;
	}





	/**
	 * Retrieves the white labeled plugin description.
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Sunday, January 3rd, 2021.
	 * @access   public static
	 * @return string|false White labeled description or false if not set.
	 */
	public static function get_new_desc()
	{

		if ((isset(self::$options['wl_newdesc']))
			&& ('' !== self::$options['wl_newdesc'])
		) {
			$newdesc = self::$options['wl_newdesc'];
			return $newdesc;
		}
		return false;
	}


	/**
	 * Retrieves the white labeled author name.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Friday, June 14th, 2024.
	 * @access	public static
	 * @return string White labeled author name or 'WP Security Ninja'.
	 */
	public static function get_new_author_name()
	{
		$newauthorname = 'WP Security Ninja';
		if ((isset(self::$options['wl_newauthor']))
			&& ('' !== self::$options['wl_newauthor'])
		) {
			$newauthorname = self::$options['wl_newauthor'];
		}
		return $newauthorname;
	}





	/**
	 * Checks if white labeling is active.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Friday, June 14th, 2024.
	 * @access	public static
	 * @return bool True if white labeling is active, false otherwise.
	 */
	public static function is_active()
	{
		if (!isset(self::$options['wl_active'])) {
			return false;
		}
		return (bool) self::$options['wl_active'];
	}


	/**
	 * Initializes admin-specific functionality.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Sunday, January 3rd, 2021.
	 * @version v1.0.1  Tuesday, November 14th, 2023.
	 * @access  public static
	 * @global
	 * @return void
	 */
	public static function admin_init()
	{
		register_setting('wf_sn_wl', 'wf_sn_wl', array(__NAMESPACE__ . '\\wf_sn_wl', 'sanitize_settings'));

		if (self::is_active()) {
			// Filter if whitelabel is not turned on
			global $submenu;
			// Filter out submenu items we do not want shown.
			if (isset($submenu['wf-sn'])) {
				$newwfsn = array();
				foreach ($submenu['wf-sn'] as $sfs) {
					if (!in_array($sfs[2], array('wf-sn-affiliation', 'wf-sn-account', 'wf-sn-contact', 'wf-sn-pricing', 'wf-sn-addons'), true)) {
						$newwfsn[] = $sfs;
					}
				}
				$submenu['wf-sn'] = $newwfsn; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}
	}



	/**
	 * Sanitizes white label settings before saving.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Friday, June 14th, 2024.
	 * @access	public static
	 * @param array $values Input values to sanitize.
	 * @return array Sanitized values.
	 */
	public static function sanitize_settings($values)
	{
		$old_options = get_option('wf_sn_wl');
		if (!is_array($values)) {
			$values = array();
		}
		$old_options['wl_active']         = 0;
		$old_options['wl_newname']        = '';
		$old_options['wl_newdesc']        = '';
		$old_options['wl_newauthor']      = '';
		$old_options['wl_newurl']         = '';
		$old_options['wl_newiconurl']     = '';
		$old_options['wl_newmenuiconurl'] = '';

		foreach ($values as $key => $value) {
			switch ($key) {
				case 'wl_active':
					$values[$key] = intval($value);
					break;
				case 'wl_newname':
				case 'wl_newdesc':
				case 'wl_newauthor':
				case 'wl_newurl':
				case 'wl_newiconurl':
				case 'wl_newmenuiconurl':
					$values[$key] = sanitize_text_field($value);
					break;
			}
		}

		return array_merge($old_options, $values);
	}
}
add_action('plugins_loaded', array(__NAMESPACE__ . '\Wf_Sn_Wl', 'init'));
register_deactivation_hook(WF_SN_BASE_FILE, array(__NAMESPACE__ . '\wf_sn_wl', 'deactivate'));
