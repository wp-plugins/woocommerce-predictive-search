<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

class WC_PS_Posts_Data
{
	public function install_database() {
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if( ! empty($wpdb->charset ) ) $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			if( ! empty($wpdb->collate ) ) $collate .= " COLLATE $wpdb->collate";
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_ps_posts = $wpdb->prefix. "ps_posts";

		if ($wpdb->get_var("SHOW TABLES LIKE '$table_ps_posts'") != $table_ps_posts) {
			$sql = "CREATE TABLE IF NOT EXISTS `{$table_ps_posts}` (
					post_id bigint(20) NOT NULL,
					post_title mediumtext NOT NULL,
					post_type VARCHAR(20) NOT NULL DEFAULT 'post',
					PRIMARY KEY  (post_id)
				) $collate; ";

			$wpdb->query($sql);
		}

	}

	/**
	 * Predictive Search Post Table - set table name
	 *
	 * @return void
	 */
	public function set_table_wpdbfix() {
		global $wpdb;
		$meta_name = 'ps_posts';

		$wpdb->ps_posts = $wpdb->prefix . $meta_name;

		$wpdb->tables[] = 'ps_posts';
	}

	/**
	 * Predictive Search Post Table - return sql
	 *
	 * @return void
	 */
	public function get_sql( $search_keyword = '', $search_keyword_nospecial = '', $post_type = 'product', $number_row, $start = 0, $check_existed = false ) {
		if ( '' == $search_keyword && '' == $search_keyword_nospecial ) {
			return false;
		}

		global $wpdb;
		global $wc_ps_exclude_data;

		$sql     = array();
		$join    = array();
		$where   = array();
		$groupby = array();
		$orderby = array();

		$items_excluded = $wc_ps_exclude_data->get_array_items( $post_type );
		if ( 'page' == $post_type ) {
			global $woocommerce_search_page_id;
			$items_excluded = array_merge( array( (int) $woocommerce_search_page_id ), $items_excluded );
		}
		$id_excluded    = implode( ',', $items_excluded );

		$sql['select']   = array();
		if ( $check_existed ) {
			$sql['select'][] = " 1 ";
		} else {
			$sql['select'][] = " pp.* ";
		}

		$sql['from']   = array();
		$sql['from'][] = " {$wpdb->ps_posts} AS pp ";

		$sql['join']   = $join;

		$where[] = $wpdb->prepare( " pp.post_type = %s", $post_type );

		if ( '' != trim( $id_excluded ) ) {
			$where[] = " AND pp.post_id NOT IN ({$id_excluded}) ";
		}

		$where_title = ' ( ';
		$where_title .= $wpdb->prepare( WC_Predictive_Search_Functions::remove_special_characters_in_mysql( 'pp.post_title' ) . " LIKE '%s' OR " . WC_Predictive_Search_Functions::remove_special_characters_in_mysql( 'pp.post_title' ) . " LIKE '%s' ", $search_keyword.'%', '% '.$search_keyword.'%' );
		if ( '' != $search_keyword_nospecial ) {
			$where_title .= " OR ". $wpdb->prepare( WC_Predictive_Search_Functions::remove_special_characters_in_mysql( 'pp.post_title' ) . " LIKE '%s' OR " . WC_Predictive_Search_Functions::remove_special_characters_in_mysql( 'pp.post_title' ) . " LIKE '%s' ", $search_keyword_nospecial.'%', '% '.$search_keyword_nospecial.'%' );
		}
		$search_keyword_no_s_letter = WC_Predictive_Search_Functions::remove_s_letter_at_end_word( $search_keyword );
		if ( $search_keyword_no_s_letter != false ) {
			$where_title .= " OR ". $wpdb->prepare( WC_Predictive_Search_Functions::remove_special_characters_in_mysql( 'pp.post_title' ) . " LIKE '%s' OR " . WC_Predictive_Search_Functions::remove_special_characters_in_mysql( 'pp.post_title' ) . " LIKE '%s' ", $search_keyword_no_s_letter.'%', '% '.$search_keyword_no_s_letter.'%' );
		}
		$where_title .= ' ) ';

		$where['search']   = array();
		$where['search'][] = ' ( ' . $where_title . ' ) ';

		$sql['where']      = $where;

		$sql['groupby']    = array();
		$sql['groupby'][]  = ' pp.post_id ';

		$sql['orderby']    = array();
		if ( $check_existed ) {
			$sql['limit']      = " 0 , 1 ";
		} else {
			$sql['orderby'][]  = $wpdb->prepare( " pp.post_title NOT LIKE '%s' ASC, pp.post_title ASC ", $search_keyword.'%' );

			$sql['limit']      = " {$start} , {$number_row} ";
		}

		return $sql;
	}

	/**
	 * Insert Predictive Search Post
	 */
	public function insert_item( $post_id, $post_title = '', $post_type = 'post' ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->ps_posts} VALUES(%d, %s, %s)", $post_id, stripslashes( $post_title ), stripslashes( $post_type ) ) );
	}

	/**
	 * Update Predictive Search Post
	 */
	public function update_item( $post_id, $post_title = '', $post_type = 'post' ) {
		global $wpdb;

		$value = $this->get_item( $post_id );
		if ( NULL == $value ) {
			return $this->insert_item( $post_id, $post_title, $post_type );
		} else {
			return $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ps_posts} SET post_title = %s, post_type = %s WHERE post_id = %d ", stripslashes( $post_title ), stripslashes( $post_type ), $post_id ) );
		}
	}

	/**
	 * Get Predictive Search Post
	 */
	public function get_item( $post_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT post_title FROM {$wpdb->ps_posts} WHERE post_id = %d LIMIT 0,1", $post_id ) );
	}

	/**
	 * Delete Predictive Search Post
	 */
	public function delete_item( $post_id ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->ps_posts} WHERE post_id = %d ", $post_id ) );
	}

	/**
	 * Empty Predictive Search Posts
	 */
	public function empty_table() {
		global $wpdb;
		return $wpdb->query( "TRUNCATE {$wpdb->ps_posts}" );
	}
}

global $wc_ps_posts_data;
$wc_ps_posts_data = new WC_PS_Posts_Data();
?>
