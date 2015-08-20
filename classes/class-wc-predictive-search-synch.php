<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
class WC_Predictive_Search_Synch
{
	public function __construct() {

		// Synch for post
		add_action( 'save_post', array( $this, 'synch_save_post' ), 102, 2 );
		add_action( 'delete_post', array( $this, 'synch_delete_post' ) );

		/*
		 *
		 * Synch for custom mysql query from 3rd party plugin
		 * Call below code on 3rd party plugin when create post by mysql query
		 * do_action( 'mysql_inserted_post', $post_id );
		 */
		add_action( 'mysql_inserted_post', array( $this, 'synch_mysql_inserted_post' ) );
	}

	public function migrate_posts() {
		global $wpdb;
		global $wc_ps_posts_data;
		global $wc_ps_product_sku_data;

		// Check if synch data is stopped at latest run then continue synch without empty all the tables
		$synched_data = get_option( 'wc_predictive_search_synched_data', 0 );

		if ( 0 == $synched_data ) {
			// continue synch data from stopped post ID
			$stopped_ID = $wc_ps_posts_data->get_latest_post_id();
			if ( empty( $stopped_ID ) || is_null( $stopped_ID ) ) {
				$stopped_ID = 0;
			}
		} else {
			// Empty all tables
			$wc_ps_posts_data->empty_table();
			$wc_ps_product_sku_data->empty_table();

			update_option( 'wc_predictive_search_synched_data', 0 );

			$stopped_ID = 0;
		}

		$post_types = apply_filters( 'predictive_search_post_types_support', array( 'post', 'page', 'product' ) );

		$all_posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_type FROM {$wpdb->posts} WHERE ID > %d AND post_status = %s AND post_type IN ('". implode("','", $post_types ) ."') ORDER BY ID ASC" , $stopped_ID, 'publish'
			)
		);

		if ( $all_posts ) {
			foreach ( $all_posts as $item ) {
				$post_id       = $item->ID;

				$wc_ps_posts_data->insert_item( $post_id, $item->post_title, $item->post_type );

				if ( 'product' == $item->post_type ) {
					$sku = get_post_meta( $post_id, '_sku', true );
					if ( ! empty( $sku ) && '' != trim( $sku ) ) {
						$wc_ps_product_sku_data->insert_item( $post_id, $sku );
					}
				}
			}
		}

		update_option( 'wc_predictive_search_synched_data', 1 );
	}

	public function synch_full_database() {
		$this->migrate_posts();
	}

	public function delete_post_data( $post_id ) {
		global $wc_ps_posts_data;
		global $wc_ps_product_sku_data;

		$wc_ps_posts_data->delete_item( $post_id );
		$wc_ps_product_sku_data->delete_item( $post_id );
	}

	public function synch_save_post( $post_id, $post ) {
		global $wpdb;
		global $wc_ps_posts_data;
		global $wc_ps_product_sku_data;

		$this->delete_post_data( $post_id );

		if ( 'publish' == $post->post_status ) {

			$wc_ps_posts_data->update_item( $post_id, $post->post_title, $post->post_type );

			if ( 'page' == $post->post_type ) {
				global $woocommerce_search_page_id;

				// flush rewrite rules if page is editing is WooCommerce Search Result page
				if ( $post_id == $woocommerce_search_page_id ) {
					flush_rewrite_rules();
				}
			}

		}
	}

	public function synch_delete_post( $post_id ) {
		global $wc_ps_exclude_data;

		$this->delete_post_data( $post_id );

		$post_type = get_post_type( $post_id );

		$wc_ps_exclude_data->delete_item( $post_id, $post_type );
	}

	public function synch_mysql_inserted_post( $post_id = 0 ) {
		if ( $post_id < 1 ) return;

		global $wpdb;
		$post_types = apply_filters( 'predictive_search_post_types_support', array( 'post', 'page', 'product' ) );

		$item = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_status = %s AND post_type IN ('". implode("','", $post_types ) ."')" , $post_id, 'publish'
			)
		);

		if ( $item ) {
			global $wc_ps_posts_data;
			global $wc_ps_product_sku_data;

			$wc_ps_posts_data->insert_item( $post_id, $item->post_title, $item->post_type );

			if ( 'product' == $item->post_type ) {
				$sku = get_post_meta( $post_id, '_sku', true );
				if ( ! empty( $sku ) && '' != trim( $sku ) ) {
					$wc_ps_product_sku_data->insert_item( $post_id, $sku );
				}
			}
		}
	}
}

global $wc_ps_synch;
$wc_ps_synch = new WC_Predictive_Search_Synch();
?>
