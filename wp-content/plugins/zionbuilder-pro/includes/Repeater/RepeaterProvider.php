<?php

namespace ZionBuilderPro\Repeater;

class RepeaterProvider {
	public $config = null;
	public $query  = [
		'items' => [],
		'query' => [],
	];

	// Looping helpers
	public $active_loops = [];

	public function __construct( $config = null ) {
		if ( $config !== null ) {
			$this->config = $config;

			// Preform the query
			$this->perform_query();
		}
	}

	public function get_query() {
		return $this->query;
	}

	public function perform_query() {
		return [
			'query' => [],
			'items' => [],
		];
	}

	public function get_active_consumer_data( $field = null ) {
		$active_consumer_data = (array) $this->get_active_item();
		if ( $field ) {
			return isset( $active_consumer_data[$field] ) ? $active_consumer_data[$field] : null;
		} else {
			return $active_consumer_data;
		}
	}

	public static function perform_custom_query( $config ) {
		global $wp_query;

		$singular = null;

		// Check to see if we are in page mode
		if ( is_front_page() && isset( $wp_query->query_vars['page'] ) ) {
			if ( isset( $wp_query->query_vars['paged'] ) ) {
				$config['paged'] = (int) $wp_query->query_vars['page'];
			}
		} elseif ( isset( $wp_query->query_vars['paged'] ) ) {
			$config['paged'] = (int) $wp_query->query_vars['paged'];
		}

		if ( isset( $config['exclude_current_post'] ) && $config['exclude_current_post'] ) {
			$config['post__not_in'] = [ get_the_ID() ];
			unset( $config['exclude_current_post'] );
		}

		if ( isset( $config['offset'] ) && $config['offset'] !== 0 ) {
			if (isset( $config['paged'] )) {
				// check for pagination
				$current_page = max( 1, $config['paged'] );
				$per_page = $config['posts_per_page'] ?? 10;
				$offset_start = $config['offset'];
				$offset = ( $current_page - 1 ) * $per_page + $offset_start;

				$config['offset'] = $offset;
				
				// Recalculate the number of found posts to avoid pagination issues
				$singular = function ($found_posts) use ( $offset_start, $config, &$singular ) {
					remove_filter('found_posts', $singular, 1 );
					return $found_posts - $offset_start;
				};
				
				add_filter('found_posts', $singular, 1 );

			} else {
				$config['offset'] = $config['offset'];
			}
			
		}

		// Tax query
		if (! empty( $config['tax_query'] )) {
			$config['tax_query'] = array_map(function ($tax_query) {
				if (isset($tax_query['terms']) && is_string($tax_query['terms'])) {
					$tax_query['terms'] = explode(',', $tax_query['terms']);
				}

				return $tax_query;
			}, $config['tax_query']);
		}

		// Check for taxonomy relation
		if (isset($config['relation']) && count($config['tax_query']) > 1) {
			$config['tax_query']['relation'] = $config['relation'];
			unset($config['relation']);
		}

		// Meta query
		if (! empty( $config['meta_query'] )) {
			$config['meta_query'] = array_map(function ($meta_query) {
				if (isset($meta_query['value']) && is_string($meta_query['value'])) {
					$meta_query['value'] = explode(',', $meta_query['value']);
				}

				return $meta_query;
			}, $config['meta_query']);
		}

		// Check for meta query relation
		if (isset($config['meta_query_relation']) && count($config['meta_query']) > 1) {
			$config['meta_query']['relation'] = $config['meta_query_relation'];
			unset($config['meta_query_relation']);
		}

		// Show only current users posts
		if (isset($config['filter_by_author']) && $config['filter_by_author']) {
			switch ($config['filter_by_author']) {
				case 'current_user':
					$config['author__in'] = [get_current_user_id()];
					
					break;
				case 'custom_author':
					if (isset( $config['custom_author'] )) {
						$config['author__in'] = explode(',', $config['custom_author']);
					}
					
					break;
				default:
					# code...
					break;
			}

			unset($config['current_user']);
		}

		// $posts_query = new \WP_Query( $config );
		query_posts( $config );
		$items       = is_array( $wp_query->posts ) ? $wp_query->posts : [];

		if (null !== $singular) {
			remove_filter('found_posts', $singular, 1 );
		}

		return [
			'query' => $wp_query,
			'items' => $items,
		];

	}

	/**
	 * Recalculates the number of found posts
	 * 
	 * This is needed when a custom offset is used and the pagination is enabled
	 *
	 * @param number $found_posts
	 * @param \WP_Query $query
	 * @return void
	 */
	static function recalculate_found_posts($found_posts, $query) {

	}

	public function reset_query() {
		wp_reset_query();
		wp_reset_postdata();
	}

	/**
	 * Get class name.
	 *
	 * Return the name of the current class.
	 * Used to instantiate elements with data.
	 *
	 * @return string The current class name
	 */
	final public function get_class_name() {
		return get_called_class();
	}

	public function get_schema() {
		return [];
	}


	public function start_loop( $loop_config = [] ) {
		$start = isset( $loop_config['start'] ) && $loop_config['start'] !== null ? $loop_config['start'] : 0;
		$end   = isset( $loop_config['end'] ) && $loop_config['end'] !== null ? $loop_config['end'] : count( $this->query['items'] );
		$length = $end - $start;

		$items       = array_slice( $this->query['items'], $start, $length );
		$active_loop = [
			'items' => $items,
			'index' => 0,
			'count' => count( $items ),
			'start' => $start,
			'end'   => $end,
		];

		// Save the last loop if we started a new ones
		$this->active_loops[] = $active_loop;
	}

	public function stop_loop() {
		array_pop( $this->active_loops );
	}

	public function is_looping() {
		return ! empty( $this->active_loops );
	}

	public function reset_item() {
	}

	/**
	 * Returns the active loop config
	 *
	 * @return bool|array
	 */
	public function &get_active_loop() {
		if ( ! empty( $this->active_loops ) ) {
			end($this->active_loops);

			return $this->active_loops[key($this->active_loops)];
		}

		return false;
	}

	public function get_loop_items() {
		$active_loop = $this->get_active_loop();

		if ( $active_loop ) {
			return $active_loop['items'];
		}

		return [];
	}

	public function have_items() {
		$active_loop = $this->get_active_loop();

		if ( $active_loop ) {
			if ($active_loop['index'] + 1 <= $active_loop['count']) {
				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	public function next() {
		$active_loop = &$this->get_active_loop();

		if ( $active_loop ) {
			$active_loop['index'] += 1;
		}
	}

	public function get_loop_index() {
		$active_loop = $this->get_active_loop();

		if ( $active_loop ) {
			return $active_loop['index'];
		}

		return false;
	}

	public function get_real_index() {
		$active_loop = $this->get_active_loop();

		if ( $active_loop ) {
			$current_index = $active_loop['index'];
			$start_index   = $active_loop['start'];
			return $start_index + $current_index;
		}

		return false;
	}

	public function get_active_item() {
		$active_loop = $this->get_active_loop();

		if ( $active_loop ) {
			$active_index = $active_loop['index'];
			if ( isset( $active_loop['items'][$active_index] ) ) {
				return $active_loop['items'][$active_index];
			}
		}

		return false;
	}

	public function get_item_by_index( $index = null ) {
		if ( null === $index ) {
			return $this->get_active_item();
		}

		$active_loop = $this->get_active_loop();

		if ( $active_loop ) {
			if ( isset( $active_loop['items'][$index] ) ) {
				return $active_loop['items'][$index];
			}
		}

		return false;
	}

}
