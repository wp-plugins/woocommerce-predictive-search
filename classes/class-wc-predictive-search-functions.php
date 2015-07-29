<?php
/**
 * WooCommerce Predictive Search Functions
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * woops_limit_words()
 * create_page()
 * create_page_wpml()
 * auto_create_page_for_wpml()
 * strip_shortcodes()
 */
class WC_Predictive_Search_Functions
{

	public static function symbol_entities() {
		$symbol_entities = array(
			"_" => "_",
			"(" => "&lpar;",
			")" => "&rpar;",
			"{" => "&lcub;",
			"}" => "&rcub;",
			"<" => "&lt;",
			">" => "&gt;",
			"«" => "&laquo;",
			"»" => "&raquo;",
			"‘" => "&lsquo;",
			"’" => "&rsquo;",
			"“" => "&ldquo;",
			"”" => "&rdquo;",
			"‐" => "&dash;",
			"–" => "&ndash;",
			"—" => "&mdash;",
			"←" => "&larr;",
			"→" => "&rarr;",
			"↑" => "&uarr;",
			"↓" => "&darr;",
			"©" => "&copy;",
			"®" => "&reg;",
			"™" => "&trade;",
			"€" => "&euro;",
			"£" => "&pound;",
			"¥" => "&yen;",
			"¢" => "&cent;",
			"§" => "&sect;",
			"∑" => "&sum;",
			"µ" => "&micro;",
			"¶" => "&para;",
			"¿" => "&iquest;",
			"¡" => "&iexcl;",

		);

		return apply_filters( 'wc_ps_symbol_entities', $symbol_entities );
	}

	public static function get_argument_vars() {
		$argument_vars = array( 'keyword' , 'search-in', 'cat-in', 'search-other' );
		return $argument_vars;
	}

	public static function special_characters_list() {
		$special_characters = array();
		foreach ( self::symbol_entities() as $symbol => $entity ) {
			$special_characters[$symbol] = $symbol;
		}

		return apply_filters( 'wc_ps_special_characters', $special_characters );
	}

	public static function remove_special_characters_in_mysql( $field_name ) {
		if ( trim( $field_name ) == '' ) return '';

		$woocommerce_search_remove_special_character = get_option( 'woocommerce_search_remove_special_character', 'no' );
		if ( 'no' == $woocommerce_search_remove_special_character ) {
			return $field_name;
		}

		$woocommerce_search_special_characters = get_option( 'woocommerce_search_special_characters', array() );
		if ( !is_array( $woocommerce_search_special_characters ) || count( $woocommerce_search_special_characters ) < 1 ) {
			return $field_name;
		}

		foreach ( $woocommerce_search_special_characters as $special_symbol ) {
			$field_name = 'REPLACE( '.$field_name.', "'.$special_symbol.'", "")';
		}

		return $field_name;
	}

	public static function remove_s_letter_at_end_word( $search_keyword ) {
		$search_keyword_new = '';
		$search_keyword_new_a = array();
		$search_keyword_split = explode( " ", trim( $search_keyword ) );
		if ( is_array( $search_keyword_split ) && count( $search_keyword_split ) > 0 ) {
			foreach ( $search_keyword_split as $search_keyword_element ) {
				if ( strlen( $search_keyword_element ) > 2 ) {
					$search_keyword_new_a[] = rtrim( $search_keyword_element, 's' );
				} else {
					$search_keyword_new_a[] = $search_keyword_element;
				}
			}
			$search_keyword_new = implode(" ", $search_keyword_new_a);
		}

		if ( '' != $search_keyword && $search_keyword_new != $search_keyword ) {
			return $search_keyword_new;
		} else {
			return false;
		}
	}

	public static function woops_limit_words($str='',$len=100,$more) {
		if (trim($len) == '' || $len < 0) $len = 100;
	   if ( $str=="" || $str==NULL ) return $str;
	   if ( is_array($str) ) return $str;
	   $str = trim($str);
	   $str = strip_tags(str_replace("\r\n", "", $str));
	   if ( strlen($str) <= $len ) return $str;
	   $str = substr($str,0,$len);
	   if ( $str != "" ) {
			if ( !substr_count($str," ") ) {
					  if ( $more ) $str .= " ...";
					return $str;
			}
			while( strlen($str) && ($str[strlen($str)-1] != " ") ) {
					$str = substr($str,0,-1);
			}
			$str = substr($str,0,-1);
			if ( $more ) $str .= " ...";
			}
			return $str;
	}

	public static function create_page( $slug, $option, $page_title = '', $page_content = '', $post_parent = 0 ) {
		global $wpdb;

		$option_value = get_option($option);

		if ( $option_value > 0 && get_post( $option_value ) )
			return $option_value;

		$page_id = $wpdb->get_var( "SELECT ID FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%$page_content%'  AND `post_type` = 'page' AND post_status = 'publish' ORDER BY ID ASC LIMIT 1" );

		if ( $page_id != NULL ) :
			if ( ! $option_value )
				update_option( $option, $page_id );
			return $page_id;
		endif;

		$page_data = array(
			'post_status' 		=> 'publish',
			'post_type' 		=> 'page',
			'post_author' 		=> 1,
			'post_name' 		=> $slug,
			'post_title' 		=> $page_title,
			'post_content' 		=> $page_content,
			'post_parent' 		=> $post_parent,
			'comment_status' 	=> 'closed'
		);
		$page_id = wp_insert_post( $page_data );

		if ( class_exists('SitePress') ) {
			global $sitepress;
			$source_lang_code = $sitepress->get_default_language();
			$trid = $sitepress->get_element_trid( $page_id, 'post_page' );
			if ( ! $trid ) {
				$wpdb->query( "UPDATE ".$wpdb->prefix . "icl_translations SET trid=".$page_id." WHERE element_id=".$page_id." AND language_code='".$source_lang_code."' AND element_type='post_page' " );
			}
		}

		update_option( $option, $page_id );

		return $page_id;
	}

	public static function create_page_wpml( $trid, $lang_code, $source_lang_code, $slug, $page_title = '', $page_content = '' ) {
		global $wpdb;

		$element_id = $wpdb->get_var( "SELECT ID FROM " . $wpdb->posts . " AS p INNER JOIN " . $wpdb->prefix . "icl_translations AS ic ON p.ID = ic.element_id WHERE p.post_content LIKE '%$page_content%' AND p.post_type = 'page' AND p.post_status = 'publish' AND ic.trid=".$trid." AND ic.language_code = '".$lang_code."' AND ic.element_type = 'post_page' ORDER BY p.ID ASC LIMIT 1" );

		if ( $element_id != NULL ) :
			return $element_id;
		endif;

		$page_data = array(
			'post_date'			=> gmdate( 'Y-m-d H:i:s' ),
			'post_modified'		=> gmdate( 'Y-m-d H:i:s' ),
			'post_status' 		=> 'publish',
			'post_type' 		=> 'page',
			'post_author' 		=> 1,
			'post_name' 		=> $slug,
			'post_title' 		=> $page_title,
			'post_content' 		=> $page_content,
			'comment_status' 	=> 'closed'
		);
		$wpdb->insert( $wpdb->posts , $page_data);
		$element_id = $wpdb->insert_id;

		//$element_id = wp_insert_post( $page_data );

		$wpdb->insert( $wpdb->prefix . "icl_translations", array(
				'element_type'			=> 'post_page',
				'element_id'			=> $element_id,
				'trid'					=> $trid,
				'language_code'			=> $lang_code,
				'source_language_code'	=> $source_lang_code,
			) );

		return $element_id;
	}

	public static function auto_create_page_for_wpml(  $original_id, $slug, $page_title = '', $page_content = '' ) {
		if ( class_exists('SitePress') ) {
			global $sitepress;
			$active_languages = $sitepress->get_active_languages();
			if ( is_array($active_languages)  && count($active_languages) > 0 ) {
				$source_lang_code = $sitepress->get_default_language();
				$trid = $sitepress->get_element_trid( $original_id, 'post_page' );
				foreach ( $active_languages as $language ) {
					if ( $language['code'] == $source_lang_code ) continue;
					WC_Predictive_Search_Functions::create_page_wpml( $trid, $language['code'], $source_lang_code, $slug.'-'.$language['code'], $page_title.' '.$language['display_name'], $page_content );
				}
			}
		}
	}

	public static function get_page_id_from_option( $shortcode, $option ) {
		global $wpdb;
		global $wp_version;
		$page_id = get_option($option);

		if ( version_compare( $wp_version, '4.0', '<' ) ) {
			$shortcode = esc_sql( like_escape( $shortcode ) );
		} else {
			$shortcode = esc_sql( $wpdb->esc_like( $shortcode ) );
		}

		$page_data = null;
		if ( $page_id ) {
			$page_data = $wpdb->get_row( "SELECT ID FROM " . $wpdb->posts . " WHERE post_content LIKE '%[{$shortcode}]%' AND ID = '".$page_id."' AND post_type = 'page' LIMIT 1" );
		}
		if ( $page_data == null ) {
			$page_data = $wpdb->get_row( "SELECT ID FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%[{$shortcode}]%' AND `post_type` = 'page' ORDER BY post_date DESC LIMIT 1" );
		}

		$page_id = $page_data->ID;

		return $page_id;
	}

	public static function get_page_id_from_shortcode( $shortcode, $option ) {
		global $wpdb;

		$page_id = self::get_page_id_from_option( $shortcode, $option );

		// For WPML
		if ( class_exists('SitePress') ) {
			global $sitepress;
			$translation_page_data = null;
			$trid = $sitepress->get_element_trid( $page_id, 'post_page' );
			if ( $trid ) {
				$translation_page_data = $wpdb->get_row( $wpdb->prepare( "SELECT element_id FROM " . $wpdb->prefix . "icl_translations WHERE trid = %d AND element_type='post_page' AND language_code = %s LIMIT 1", $trid , $sitepress->get_current_language() ) );
				if ( $translation_page_data != null )
					$page_id = $translation_page_data->element_id;
			}
		}

		return $page_id;
	}

	public static function add_query_vars( $aVars ) {
		$argument_vars = self::get_argument_vars();
		foreach ( $argument_vars as $avar ) {
			$aVars[] = $avar;
		}

		return $aVars;
	}

	public static function add_page_rewrite_rules( $aRules, $page_id ) {
		$search_page = get_page( $page_id );

		if ( ! empty( $search_page ) ) {

			$search_page_slug = $search_page->post_name;
			$argument_vars    = self::get_argument_vars();

			$rewrite_rule   = '';
			$original_url   = '';
			$number_matches = 0;
			foreach ( $argument_vars as $avar ) {
				$number_matches++;
				$rewrite_rule .= $avar.'/([^/]*)/';
				$original_url .= '&'.$avar.'=$matches['.$number_matches.']';
			}

			$aNewRules = array($search_page_slug.'/'.$rewrite_rule.'?$' => 'index.php?pagename='.$search_page_slug.$original_url);
			$aRules = $aNewRules + $aRules;

		}

		return $aRules;
	}

	public static function add_rewrite_rules( $aRules ) {
		global $wpdb;

		$shortcode   = 'woocommerce_search';
		$option_name = 'woocommerce_search_page_id';
		$page_id     = self::get_page_id_from_option( $shortcode, $option_name );

		$aRules      = self::add_page_rewrite_rules( $aRules, $page_id );

		// For WPML
		if ( class_exists('SitePress') ) {
			global $sitepress;
			$translation_page_data = null;
			$trid = $sitepress->get_element_trid( $page_id, 'post_page' );
			if ( $trid ) {
				$translation_page_data = $wpdb->get_results( $wpdb->prepare( "SELECT element_id FROM " . $wpdb->prefix . "icl_translations WHERE trid = %d AND element_type='post_page' AND element_id != %d", $trid , $page_id ) );
				if ( is_array( $translation_page_data ) && count( $translation_page_data ) > 0 ) {
					foreach( $translation_page_data as $translation_page ) {
						$aRules = self::add_page_rewrite_rules( $aRules, $translation_page->element_id );
					}
				}
			}
		}

		return $aRules;
	}

	public static function strip_shortcodes ($content='') {
		$content = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $content);

		return $content;
	}

	/**
	 * Get product price
	 */
	public static function get_product_price( $product_id ) {
		$product_price_output = '';
		$current_db_version = get_option( 'woocommerce_db_version', null );
		if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
			$current_product = new WC_Product($product_id);
		} elseif ( version_compare( WC()->version, '2.2.0', '<' ) ) {
			$current_product = get_product( $product_id );
		} else {
			$current_product = wc_get_product( $product_id );
		}

		$product_price_output = $current_product->get_price_html();

		return $product_price_output;
	}

	/**
	 * Get product add to cart
	 */
	public static function get_product_addtocart( $product_id ) {
		$product_addtocart_output = '';
		global $product;
		global $post;
		$current_db_version = get_option( 'woocommerce_db_version', null );
		if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
			$current_product = new WC_Product($product_id);
		} elseif ( version_compare( WC()->version, '2.2.0', '<' ) ) {
			$current_product = get_product( $product_id );
		} else {
			$current_product = wc_get_product( $product_id );
		}
		$product = $current_product;
		$post = get_post( $product_id );
		ob_start();
		if (function_exists('woocommerce_template_loop_add_to_cart') )
			woocommerce_template_loop_add_to_cart();
		$product_addtocart_output = ob_get_clean();

		return $product_addtocart_output;
	}

	/**
	 * Get product add to cart
	 */
	public static function get_terms_object( $object_id, $taxonomy = 'product_cat' ) {
		$terms_list = array();

		$terms = get_the_terms( $object_id, $taxonomy );

		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $terms ) {
				$terms_list[] = array(
					'name'	=> $terms->name,
					'url'	=> get_term_link($terms->slug, $taxonomy )
				);
			}
		}

		return $terms_list;
	}

	/**
	 * Get product thumbnail url
	 */
	public static function get_product_thumbnail_url( $post_id, $size = 'shop_catalog', $placeholder_width = 0, $placeholder_height = 0  ) {
		global $woocommerce;
		$woocommerce_db_version = get_option( 'woocommerce_db_version', null );
		$shop_catalog = ( ( version_compare( $woocommerce_db_version, '2.1', '<' ) ) ? $woocommerce->get_image_size( 'shop_catalog' ) : wc_get_image_size( 'shop_catalog' ) );
		if ( is_array( $shop_catalog ) && isset( $shop_catalog['width'] ) && $placeholder_width == 0 ) {
			$placeholder_width = $shop_catalog['width'];
		}
		if ( is_array( $shop_catalog ) && isset( $shop_catalog['height'] ) && $placeholder_height == 0 ) {
			$placeholder_height = $shop_catalog['height'];
		}

		$mediumSRC = '';

		// Return Feature Image URL
		if ( has_post_thumbnail( $post_id ) ) {
			$thumbid = get_post_thumbnail_id( $post_id );
			$attachmentArray = wp_get_attachment_image_src( $thumbid, $size, false );
			if ( $attachmentArray ) {
				$mediumSRC = $attachmentArray[0];
				if ( trim( $mediumSRC != '' ) ) {
					return $mediumSRC;
				}
			}
		}

		// Return First Image URL in gallery of this product
		if ( trim( $mediumSRC == '' ) ) {
			$args = array( 'post_parent' => $post_id , 'numberposts' => 1, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'DESC', 'orderby' => 'ID', 'post_status' => null );
			$attachments = get_posts( $args );
			if ( $attachments ) {
				foreach ( $attachments as $attachment ) {
					$attachmentArray = wp_get_attachment_image_src( $attachment->ID, $size, false );
					if ( $attachmentArray ) {
						$mediumSRC = $attachmentArray[0];
						if ( trim( $mediumSRC != '' ) ) {
							return $mediumSRC;
						}
					}
				}
			}
		}

		// Ger Image URL of parent product
		if ( trim( $mediumSRC == '' ) ) {
			// Load the product
			$product = get_post( $post_id );

			// Get ID of parent product if one exists
			if ( !empty( $product->post_parent ) )
				$post_id = $product->post_parent;

			if ( has_post_thumbnail( $post_id ) ) {
				$thumbid = get_post_thumbnail_id( $post_id );
				$attachmentArray = wp_get_attachment_image_src( $thumbid, $size, false );
				if ( $attachmentArray ) {
					$mediumSRC = $attachmentArray[0];
					if ( trim( $mediumSRC != '' ) ) {
						return $mediumSRC;
					}
				}
			}

			if ( trim( $mediumSRC == '' ) ) {
				$args = array( 'post_parent' => $post_id , 'numberposts' => 1, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'DESC', 'orderby' => 'ID', 'post_status' => null );
				$attachments = get_posts( $args );
				if ( $attachments ) {
					foreach ( $attachments as $attachment ) {
						$attachmentArray = wp_get_attachment_image_src( $attachment->ID, $size, false );
						if ( $attachmentArray ) {
							$mediumSRC = $attachmentArray[0];
							if ( trim( $mediumSRC != '' ) ) {
								return $mediumSRC;
							}
						}
					}
				}
			}
		}

		// Use place holder image of Woo
		if ( trim( $mediumSRC == '' ) ) {
			$mediumSRC = ( ( version_compare( $woocommerce_db_version, '2.1', '<' ) && null !== $woocommerce_db_version ) ? woocommerce_placeholder_img_src() : wc_placeholder_img_src() );
		}

		return $mediumSRC;
	}
}
?>
