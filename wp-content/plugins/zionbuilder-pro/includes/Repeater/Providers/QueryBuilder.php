<?php

namespace ZionBuilderPro\Repeater\Providers;

use ZionBuilderPro\Repeater\RepeaterProvider;
use ZionBuilder\Options\Options;

class QueryBuilder extends RepeaterProvider {
	public static function get_id() {
		return 'query_builder';
	}

	public static function get_name() {
		return esc_html__( 'Query builder', 'zionbuilder-pro' );
	}

	public function reset_item() {
		wp_reset_postdata();
	}

	public function the_item( $index = null ) {
		global $post;

		$current_item = $this->get_item_by_index( $index );

		if ( $current_item ) {
			$post = get_post( $current_item->ID );
			setup_postdata( $post );
		}
	}

	public function perform_query() {
		$config = isset( $this->config['config'] ) ? $this->config['config'] : [];
		$this->query = self::perform_custom_query( $config );
	}

	public function get_schema() {
		$options_schema = new Options( 'zionbuilderpro/repeater_provider/query_builder' );

		$post_types = get_post_types( [ 'public' => true ], 'objects' );

		$post_types_list = [];

		foreach ( $post_types as $name => $post_type ) {
			$post_types_list[] = [
				'id'   => $name,
				'name' => $post_type->label,
			];
		}

		$options_schema->add_option(
			'post_type',
			[
				'type' => 'select',
				'title' => __( 'Post type', 'zionbuilder-pro' ),
				'options' => $post_types_list,
				'multiple' => true,
				'placeholder' => __('Select post types', 'zionbuilder-pro' ),
				'filterable' => true
			]
		);

		$options_schema->add_option(
			'posts_per_page',
			[
				'type' => 'number',
				'title' => __('Posts per page', 'zionbuilder-pro' ),
				'default' => 10
			]
		);

		$options_schema->add_option(
			'offset',
			[
				'type' => 'number',
				'title' => __('Offset', 'zionbuilder-pro' ),
				'default' => 0
			]
		);

		$options_schema->add_option(
			'ignore_sticky_posts',
			[
				'type' => 'custom_selector',
				'title' => __('Ignore sticky posts', 'zionbuilder-pro' ),
				'description' => __('Set to yes to prevent moving the sticky posts to the top of the results.', 'zionbuilder-pro' ),
				'default' => false,
				'options' => [
					[
						'name' => __( 'yes', 'zionbuilder-pro' ),
						'id'   => true,
					],
					[
						'name' => __( 'no', 'zionbuilder-pro' ),
						'id'   => false,
					],
				],
			]
		);

		

		$options_schema->add_option(
			'orderby',
			[
				'title' => __('Order by', 'zionbuilder-pro' ),
				'type' => 'select',
				'default' => 'date',
				'options' => [
					[
						'name' => __('none', 'zionbuilder-pro' ),
						'id' => 'none'
					],
					[
						'name' => __('ID', 'zionbuilder-pro' ),
						'id' => 'ID'
					],
					[
						'name' => __('author', 'zionbuilder-pro' ),
						'id' => 'author'
					],
					[
						'name' => __('title', 'zionbuilder-pro' ),
						'id' => 'title'
					],
					[
						'name' => __('name', 'zionbuilder-pro' ),
						'id' => 'name'
					],
					[
						'name' => __('type', 'zionbuilder-pro' ),
						'id' => 'type'
					],
					[
						'name' => __('date', 'zionbuilder-pro' ),
						'id' => 'date'
					],
					[
						'name' => __('Modified date', 'zionbuilder-pro' ),
						'id' => 'modified'
					],
					[
						'name' => __('parent', 'zionbuilder-pro' ),
						'id' => 'parent'
					],
					[
						'name' => __('Random', 'zionbuilder-pro' ),
						'id' => 'rand'
					],
					[
						'name' => __('Comment count', 'zionbuilder-pro' ),
						'id' => 'comment_count'
					],
				]
			]
		);

		$options_schema->add_option(
			'order',
			[
				'title' => esc_html__( 'Order', 'zionbuilder-pro' ),
				'type' => 'custom_selector',
				'default' => 'DESC',
				'options' => [
					[
						'name' => esc_html__( 'Ascending', 'zionbuilder-pro' ),
						'id' => 'ASC'
					],
					[
						'name' => esc_html__( 'Descending', 'zionbuilder-pro' ),
						'id' => 'DESC'
					],
				]
			]
		);

		$options_schema->add_option(
			'exclude_current_post',
			[
				'type'    => 'checkbox_switch',
				'default' => false,
				'layout'  => 'inline',
				'title'   => esc_html__( 'Exclude current post?', 'zionbuilder-pro' ),
			]
		);


		$tax_query = $options_schema->add_option(
			'tax_query',
			[
				'type'               => 'repeater',
				'title'              => __( 'Taxonomy query', 'zionbuilder-pro' ),
				'default_item_title' =>  __( 'Taxonomy query (%s)', 'zionbuilder-pro' ),
			]
		);


		$tax_query->add_option(
			'taxonomy',
			[
				'type'               => 'select',
				'title'              => __( 'Taxonomy', 'zionbuilder-pro' ),
				'data_source'            => 'taxonomies',
			]
		);

		$tax_query->add_option(
			'field',
			[
				'type'               => 'select',
				'title'              => __( 'Taxonomy', 'zionbuilder-pro' ),
				'options'     => [
					[
						'name' => esc_html__( 'Term id', 'zionbuilder-pro' ),
						'id'   => 'term_id',
					],
					[
						'name' => esc_html__( 'Name', 'zionbuilder-pro' ),
						'id'   => 'name',
					],
					[
						'name' => esc_html__( 'Slug', 'zionbuilder-pro' ),
						'id'   => 'slug',
					],
					[
						'name' => esc_html__( 'Term taxonomy id', 'zionbuilder-pro' ),
						'id'   => 'term_taxonomy_id',
					],
				],
			]
		);

		$tax_query->add_option(
			'field',
			[
				'type'               => 'select',
				'title'              => __( 'Field', 'zionbuilder-pro' ),
				'default' => 'term_id',
				'options'     => [
					[
						'name' => esc_html__( 'Term id', 'zionbuilder-pro' ),
						'id'   => 'term_id',
					],
					[
						'name' => esc_html__( 'Name', 'zionbuilder-pro' ),
						'id'   => 'name',
					],
					[
						'name' => esc_html__( 'Slug', 'zionbuilder-pro' ),
						'id'   => 'slug',
					],
					[
						'name' => esc_html__( 'Term taxonomy id', 'zionbuilder-pro' ),
						'id'   => 'term_taxonomy_id',
					],
				],
			]
		);

		$tax_query->add_option(
			'terms',
			[
				'type'               => 'text',
				'title'              => __( 'Terms', 'zionbuilder-pro' ),
				'description'        => __( 'Set the desired terms separated by comma', 'zionbuilder-pro' ),
				'placeholder'        => __( 'term_1, term_2', 'zionbuilder-pro' ),
			]
		);

		$tax_query->add_option(
			'operator',
			[
				'type'               => 'select',
				'title'              => __( 'Operator', 'zionbuilder-pro' ),
				'default' => 'IN',
				'options'     => [
					[
						'name' => esc_html__( 'IN', 'zionbuilder-pro' ),
						'id'   => 'IN',
					],
					[
						'name' => esc_html__( 'NOT IN', 'zionbuilder-pro' ),
						'id'   => 'NOT IN',
					],
					[
						'name' => esc_html__( 'AND', 'zionbuilder-pro' ),
						'id'   => 'AND',
					],
					[
						'name' => esc_html__( 'EXISTS', 'zionbuilder-pro' ),
						'id'   => 'EXISTS',
					],
					[
						'name' => esc_html__( 'NOT EXISTS', 'zionbuilder-pro' ),
						'id'   => 'NOT EXISTS',
					],
				],
			]
		);

		$tax_query->add_option(
			'include_children',
			[
				'type'    => 'checkbox_switch',
				'columns' => 2,
				'title'   => esc_html__( 'Include children', 'zionbuilder-pro' ),
				'default' => true,
				'layout'  => 'inline',
			]
		);

		
		$options_schema->add_option(
			'relation',
			[
				'type'               => 'select',
				'title'              => __( 'Taxonomy query relation', 'zionbuilder-pro' ),
				'default' => 'AND',
				'options'     => [
					[
						'name' => esc_html__( 'AND', 'zionbuilder-pro' ),
						'id'   => 'AND',
					],
					[
						'name' => esc_html__( 'OR', 'zionbuilder-pro' ),
						'id'   => 'OR',
					],
				],
			]
		);



		$meta_query = $options_schema->add_option(
			'meta_query',
			[
				'type'               => 'repeater',
				'title'              => __( 'Meta query', 'zionbuilder-pro' ),
				'default_item_title' =>  __( 'Meta query (%s)', 'zionbuilder-pro' ),
			]
		);


		$meta_query->add_option(
			'key',
			[
				'type'               => 'text',
				'title'              => __( 'Meta key', 'zionbuilder-pro' ),
			]
		);

		$meta_query->add_option(
			'value',
			[
				'type'               => 'text',
				'title'              => __( 'Meta value', 'zionbuilder-pro' ),
			]
		);

		$meta_query->add_option(
			'compare',
			[
				'type' => 'select',
				'title' => __( 'Compare', 'zionbuilder-pro' ),
				'default' => '=',
				'options' => [
					[
						'name' => esc_html__( 'Equals', 'zionbuilder-pro' ),
						'id'   => '=',
					],
					[
						'name' => esc_html__( 'Not equal', 'zionbuilder-pro' ),
						'id'   => '!=',
					],
					[
						'name' => esc_html__( 'Greater', 'zionbuilder-pro' ),
						'id'   => '>',
					],
					[
						'name' => esc_html__( 'Greater or equal', 'zionbuilder-pro' ),
						'id'   => '>=',
					],
					[
						'name' => esc_html__( 'Lower', 'zionbuilder-pro' ),
						'id'   => '<',
					],
					[
						'name' => esc_html__( 'Lower or equal', 'zionbuilder-pro' ),
						'id'   => '<=',
					],
					[
						'name' => esc_html__( 'Like', 'zionbuilder-pro' ),
						'id'   => 'LIKE',
					],
					[
						'name' => esc_html__( 'NOT like', 'zionbuilder-pro' ),
						'id'   => 'NOT LIKE',
					],
					[
						'name' => esc_html__( 'IN', 'zionbuilder-pro' ),
						'id'   => 'IN',
					],
					[
						'name' => esc_html__( 'NOT IN', 'zionbuilder-pro' ),
						'id'   => 'NOT IN',
					],
					[
						'name' => esc_html__( 'Between', 'zionbuilder-pro' ),
						'id'   => 'BETWEEN',
					],
					[
						'name' => esc_html__( 'NOT between', 'zionbuilder-pro' ),
						'id'   => 'NOT BETWEEN',
					],
					[
						'name' => esc_html__( 'NOT EXISTS', 'zionbuilder-pro' ),
						'id'   => 'EXISTS',
					],
					[
						'name' => esc_html__( 'NOT EXISTS', 'zionbuilder-pro' ),
						'id'   => 'NOT EXISTS',
					],
				]
			]
		);


		$meta_query->add_option(
			'type',
			[
				'type'               => 'select',
				'title'              => __( 'Operator', 'zionbuilder-pro' ),
				'default' => 'CHAR',
				'options'     => [
					[
						'name' => esc_html__( 'NUMERIC', 'zionbuilder-pro' ),
						'id'   => 'NUMERIC',
					],
					[
						'name' => esc_html__( 'BINARY', 'zionbuilder-pro' ),
						'id'   => 'BINARY',
					],
					[
						'name' => esc_html__( 'CHAR', 'zionbuilder-pro' ),
						'id'   => 'CHAR',
					],
					[
						'name' => esc_html__( 'DATE', 'zionbuilder-pro' ),
						'id'   => 'DATE',
					],
					[
						'name' => esc_html__( 'DATETIME', 'zionbuilder-pro' ),
						'id'   => 'DATETIME',
					],
					[
						'name' => esc_html__( 'DECIMAL', 'zionbuilder-pro' ),
						'id'   => 'DECIMAL',
					],
					[
						'name' => esc_html__( 'SIGNED', 'zionbuilder-pro' ),
						'id'   => 'SIGNED',
					],
					[
						'name' => esc_html__( 'TIME', 'zionbuilder-pro' ),
						'id'   => 'TIME',
					],
					[
						'name' => esc_html__( 'UNSIGNED', 'zionbuilder-pro' ),
						'id'   => 'UNSIGNED',
					],
				],
			]
		);

		
		$options_schema->add_option(
			'meta_query_relation',
			[
				'type'               => 'select',
				'title'              => __( 'Meta query relation', 'zionbuilder-pro' ),
				'default' => 'AND',
				'options'     => [
					[
						'name' => esc_html__( 'AND', 'zionbuilder-pro' ),
						'id'   => 'AND',
					],
					[
						'name' => esc_html__( 'OR', 'zionbuilder-pro' ),
						'id'   => 'OR',
					],
				],
			]
		);

		$options_schema->add_option(
			'filter_by_author',
			[
				'type'    => 'custom_selector',
				'title'   => esc_html__( 'Post author', 'zionbuilder-pro' ),
				'default' => '',
				'options' => [
					[
						'name' => __( 'any author', 'zionbuilder-pro' ),
						'id'   => '',
					],
					[
						'name' => __( 'current logged-in user', 'zionbuilder-pro' ),
						'id'   => 'current_user',
					],
					[
						'name' => __( 'custom', 'zionbuilder-pro' ),
						'id'   => 'custom_author',
					],
				],
			]
		);

		$options_schema->add_option(
			'custom_author',
			[
				'type'    => 'text',
				'title'   => esc_html__( 'Custom author id', 'zionbuilder-pro' ),
				'description'   => esc_html__( 'Set the custom author id separated by comma.', 'zionbuilder-pro' ),
				'default' => '',
				'options' => [
					[
						'name' => __( 'any', 'zionbuilder-pro' ),
						'id'   => '',
					],
					[
						'name' => __( 'current user', 'zionbuilder-pro' ),
						'id'   => 'current_user',
					],
					[
						'name' => __( 'custom', 'zionbuilder-pro' ),
						'id'   => 'custom_user',
					],
				],
				'dependency'  => [
					[
						'option' => 'filter_by_author',
						'value'  => [ 'custom_author' ],
					],
				],
			]
		);

		return $options_schema->get_schema();
	}
}