<?php
/*
* SiteSEO
* https://siteseo.io/
* (c) SiteSEO Team <support@siteseo.io>
*/

/*
Copyright 2016 - 2024 - Benjamin Denis  (email : contact@seopress.org)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

///////////////////////////////////////////////////////////////////////////////////////////////////
//SEO Framework migration
///////////////////////////////////////////////////////////////////////////////////////////////////
function siteseo_seo_framework_migration() {
	siteseo_check_ajax_referer('siteseo_seo_framework_migrate_nonce');

	if (current_user_can(siteseo_capability('manage_options', 'migration')) && is_admin()) {
		if (isset($_POST['offset']) && isset($_POST['offset'])) {
			$offset = absint(siteseo_opt_post('offset'));
		}

		global $wpdb;
		$total_count_posts = (int) $wpdb->get_var("SELECT count(*) FROM {$wpdb->posts}");
		$total_count_terms = (int) $wpdb->get_var("SELECT count(*) FROM {$wpdb->terms}");

		$increment = 200;
		global $post;

		if ($offset > $total_count_posts) {
			wp_reset_query();
			$count_items = $total_count_posts;

			$args = [
				//'number' => $increment,
				'hide_empty' => false,
				//'offset' => $offset,
				'fields' => 'ids',
			];
			$seo_framework_query_terms = get_terms($args);

			if ($seo_framework_query_terms) {
				foreach ($seo_framework_query_terms as $term_id) {
					if ('' != get_term_meta($term_id, 'autodescription-term-settings', true)) {
						$term_settings = get_term_meta($term_id, 'autodescription-term-settings', true);

						if ( ! empty($term_settings['doctitle'])) { //Import title tag
							update_term_meta($term_id, '_siteseo_titles_title', $term_settings['doctitle']);
						}
						if ( ! empty($term_settings['description'])) { //Import meta desc
							update_term_meta($term_id, '_siteseo_titles_desc', $term_settings['description']);
						}
						if ( ! empty($term_settings['noindex'])) { //Import Robots NoIndex
							update_term_meta($term_id, '_siteseo_robots_index', 'yes');
						}
						if ( ! empty($term_settings['nofollow'])) { //Import Robots NoFollow
							update_term_meta($term_id, '_siteseo_robots_follow', 'yes');
						}
						if ( ! empty($term_settings['noarchive'])) { //Import Robots NoArchive
							update_term_meta($term_id, '_siteseo_robots_archive', 'yes');
						}
					}
				}
			}
			$offset = 'done';
			wp_reset_query();
		} else {
			$args = [
				'posts_per_page' => $increment,
				'post_type'	  => 'any',
				'post_status'	=> 'any',
				'offset'		 => $offset,
			];

			$seo_framework_query = get_posts($args);

			if ($seo_framework_query) {
				foreach ($seo_framework_query as $post) {
					if ('' != get_post_meta($post->ID, '_genesis_title', true)) { //Import title tag
						update_post_meta($post->ID, '_siteseo_titles_title', get_post_meta($post->ID, '_genesis_title', true));
					}
					if ('' != get_post_meta($post->ID, '_genesis_description', true)) { //Import meta desc
						update_post_meta($post->ID, '_siteseo_titles_desc', get_post_meta($post->ID, '_genesis_description', true));
					}
					if ('' != get_post_meta($post->ID, '_open_graph_title', true)) { //Import Facebook Title
						update_post_meta($post->ID, '_siteseo_social_fb_title', get_post_meta($post->ID, '_open_graph_title', true));
					}
					if ('' != get_post_meta($post->ID, '_open_graph_description', true)) { //Import Facebook Desc
						update_post_meta($post->ID, '_siteseo_social_fb_desc', get_post_meta($post->ID, '_open_graph_description', true));
					}
					if ('' != get_post_meta($post->ID, '_social_image_url', true)) { //Import Facebook Image
						update_post_meta($post->ID, '_siteseo_social_fb_img', get_post_meta($post->ID, '_social_image_url', true));
					}
					if ('' != get_post_meta($post->ID, '_twitter_title', true)) { //Import Twitter Title
						update_post_meta($post->ID, '_siteseo_social_twitter_title', get_post_meta($post->ID, '_twitter_title', true));
					}
					if ('' != get_post_meta($post->ID, '_twitter_description', true)) { //Import Twitter Desc
						update_post_meta($post->ID, '_siteseo_social_twitter_desc', get_post_meta($post->ID, '_twitter_description', true));
					}
					if ('' != get_post_meta($post->ID, '_social_image_url', true)) { //Import Twitter Image
						update_post_meta($post->ID, '_siteseo_social_twitter_img', get_post_meta($post->ID, '_social_image_url', true));
					}
					if ('1' == get_post_meta($post->ID, '_genesis_noindex', true)) { //Import Robots NoIndex
						update_post_meta($post->ID, '_siteseo_robots_index', 'yes');
					}
					if ('1' == get_post_meta($post->ID, '_genesis_nofollow', true)) { //Import Robots NoFollow
						update_post_meta($post->ID, '_siteseo_robots_follow', 'yes');
					}
					if ('1' == get_post_meta($post->ID, '_genesis_noarchive', true)) { //Import Robots NoArchive
						update_post_meta($post->ID, '_siteseo_robots_archive', 'yes');
					}
					if ('' != get_post_meta($post->ID, '_genesis_canonical_uri', true)) { //Import Canonical URL
						update_post_meta($post->ID, '_siteseo_robots_canonical', get_post_meta($post->ID, '_genesis_canonical_uri', true));
					}
					if ('' != get_post_meta($post->ID, 'redirect', true)) { //Import Redirect URL
						update_post_meta($post->ID, '_siteseo_redirections_enabled', 'yes');
						update_post_meta($post->ID, '_siteseo_redirections_type', '301');
						update_post_meta($post->ID, '_siteseo_redirections_value', get_post_meta($post->ID, 'redirect', true));
					}

					//Primary category
					if ('post' == get_post_type($post->ID)) {
						$tax = 'category';
					} elseif ('product' == get_post_type($post->ID)) {
						$tax = 'product_cat';
					}
					if (isset($tax)) {
						$primary_term = get_post_meta($post->ID, '_primary_term_'.$tax, true);

						if ('' != $primary_term) {
							update_post_meta($post->ID, '_siteseo_robots_primary_cat', $primary_term);
						}
					}
				}
			}
			$offset += $increment;

			if ($offset >= $total_count_posts) {
				$count_items = $total_count_posts;
			} else {
				$count_items = $offset;
			}
		}
		$data		   = [];

		$data['count']		  = $count_items;
		$data['total']		  = $total_count_posts + $total_count_terms;

		$data['offset'] = $offset;
		wp_send_json_success($data);
		exit();
	}
}
add_action('wp_ajax_siteseo_seo_framework_migration', 'siteseo_seo_framework_migration');
