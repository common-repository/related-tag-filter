<?php
/*
Plugin Name: Related Tag Filter
Plugin URI:
Description: This plugin takes all tags within your blog and compiles a visually related list for sorting through posts quickly with single-click-filtering.
Version: 1.0.0
Author: Karo Group Inc. 
Author URI: http://www.karo.com

	Copyright 2009 Joel Pittet, Jamie Totten and Milan Mitranic  (email : joel at pittet dot com, jamie at karo dot com, milan at karo dot com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Related_Tag_Filter_Widget extends WP_Widget {
		
	function Related_Tag_Filter_Widget() {
		$widget_ops = array('classname' => 'related_tag_filter', 'description' => __( "This plugin takes all tags within your blog and compiles a visually related list for sorting through posts quickly with single-click-filtering.") );
		$control_ops = array('width' => 300, 'height' => 300);
		$this->WP_Widget('related_tag_filter', __('Related_Tag_Filter'), $widget_ops, $control_ops);
	}
 
	function widget($args, $instance) {
		
		extract($args);
		
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$max_number_of_tags = empty($instance['number_of_tags']) ? 10 : $instance['number_of_tags'];
		
		# Before the widget
		echo $before_widget;
		
		#Begins related_tag_filter
		echo '<div id="related_tag_filter">'; 
		
			# The title
			if ( $title )
			echo $before_title . $title . $after_title;
		
			# Widget code goes here
			echo '<div id="rtf">';
		
				global $wp_query;
			
				// Parse query string tags into an array
				$selected_tags_string = strtolower($wp_query->query_vars['tag']);

				//print($selected_tags_string);
				$selected_tag_slugs = explode(' ', $selected_tags_string);
				$selected_tag_slugs = array_filter($selected_tag_slugs);

				// Get all tags
				$tags = get_tags();

				// Removable Tags
				$removable_tags = array();
			
				// Loop through tags looking for selected_tag_slugs and adding them to removable_tags
				foreach($tags as $tag) {
					foreach($selected_tag_slugs as $selected_tag) {
						if($selected_tag == $tag->slug) {
							$removable_tags[] = $tag;
						}
					}
				}
			
				$removable_tags_count = count($removable_tags);
			
				if($removable_tags_count > 0) {
					echo '<ul class="rtf_tags rtf_removable">';
				}
			
				// If only 1 removable tag, link goes back the home page, Else remove current tag from query string
				if($removable_tags_count == 1) {
					echo '<li><a href="/">' . $removable_tags[0]->name . '<span /></a></li>';
				} else if($removable_tags_count > 1) {
				
					foreach($removable_tags as $removable_tag) {
						$other_tags = array_diff($removable_tags, array($removable_tag));
					
						$other_tags_slugs = array();
					
						foreach($other_tags as $other_tag) {
							$other_tags_slugs[] = $other_tag->slug;
						}
						// Using the other tags that remain as the new query string
						$tag_slug = implode('+', $other_tags_slugs);
						$taglink = $this->generic_link_rewrite($tag_slug);
						echo '<li><a href="' . $taglink . '">' . $removable_tag->name . '<span /></a></li>';
					}
				}  
			
				if($removable_tags_count > 0) {
					echo '</ul>';
				}
			
				// Addable Tags
				$addable_tags = array_values(array_diff($tags, $removable_tags));
				$addable_tags_count = count($addable_tags);
			
				$addable_tags_sort_array = array();
				$related_posts_count_array = array();
			
				for($i = 0; $i < $addable_tags_count; $i++) {
					$addable_tag = $addable_tags[$i];
					$tags_array = array($addable_tag->slug);
					$tags_array = array_merge($tags_array, $selected_tag_slugs);
					
					$related_post_count = $this->get_post_count_by_tags($tags_array);
						// echo "<pre>related_post_count";
						// 	print($related_post_count);
						// 	echo "</pre>";
					if($related_post_count > 0) {
						$addable_tags_with_related_posts[$i] = $addable_tag;
						$addable_tags_sort_array[$i] = (99999 - $related_post_count) . strtolower($addable_tag->name);
						$related_posts_count_array[$i] = $related_post_count;
					}
				}
			
				if(count($addable_tags_sort_array) > 0) {
					asort($addable_tags_sort_array);
				}
						
				$addable_tags_with_related_posts_count = count($addable_tags_with_related_posts);
			
				$addable_tags_to_display = ($addable_tags_with_related_posts_count > $max_number_of_tags) ? $max_number_of_tags : $addable_tags_with_related_posts_count;
			
				if($addable_tags_to_display > 0) {
					echo '<ul class="rtf_tags rtf_addable">';
				}
			
				$tag_index = 0;
				
				foreach($addable_tags_sort_array as $key => $addable_tag) {
					
					// echo "<pre>related_posts_count_array";
					// print_r($related_posts_count_array[$key]);
					// echo "</pre>";
					// die();
					
					$addable_tag = $addable_tags_with_related_posts[$key];
					
					// if there are no selected tags, don't put a plus sign
					if(count($selected_tag_slugs) == 0) {
						$tag_slugs = $addable_tag->slug;
					} else {
						$tag_slugs = implode('+', $selected_tag_slugs) . '+' .$addable_tag->slug;
					}
				
					$taglink = $this->generic_link_rewrite($tag_slugs);
					echo '<li><a href="' . $taglink .  '">' . $addable_tag->name . '<span>(' . $related_posts_count_array[$key] . ')</span>' . '</a></li>';
				
					if($tag_index < $addable_tags_to_display - 1) {
						$tag_index++;
					} else {
						break;	
					}
				}
				if($addable_tags_to_display > 0) {
					echo '</ul>';
				}
				// LINK EXAMPLE
				echo '<p><a href="#" id="rtf_modal" class="rtf_show-all-link">Show all tags</a></p>';
				if($removable_tags_count > 1) {
					echo '<p><a href="/" class="rtf_clear-link">Reset tags</a></p>';
				}
				$tags_count_sorted = get_tags('orderby=count&order=DESC');
				
				echo '<div id="rtf_dialog">';
				echo '		<ul id="rtf_tags_by_name" class="rtf_tags addable">';
				foreach ($tags as $tag) {
					$taglink = $this->generic_link_rewrite($tag->slug);
					echo '		<li><a href="' . $taglink . '">' . $tag->name . '<span>' . $tag->count . '</span>' . '</a></li>';
				}			
				echo '		</ul>';
				echo '		<ul id="rtf_tags_by_count" class="rtf_tags addable" style="display: none">';
				foreach ($tags_count_sorted as $tag) {
					$taglink = $this->generic_link_rewrite($tag->slug);
					echo '		<li><a href="' . $taglink . '">' . $tag->name . '<span>' . $tag->count . '</span>' . '</a></li>';
				}
				echo '		</ul>';
				echo '</div>';
			echo '</div>'; // this ends #rtf
		echo '</div>'; // this ends #related_tag_filter
		
		# After the widget
		echo $after_widget;

	}
 
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['number_of_tags'] = strip_tags(stripslashes($new_instance['number_of_tags']));
		
		return $instance;
	}
 
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array('title'=>'Related Tag Filter', 'number_of_tags'=>10) );
		
		$title = htmlspecialchars($instance['title']);
		$number_of_tags = htmlspecialchars($instance['number_of_tags']);
		
		# Output the options
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('title') . '">' . __('Title:') . ' <input style="width: 250px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
		# Text line 1
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('number_of_tags') . '">' . __('Number of tags:') . ' <input style="width: 50px;" id="' . $this->get_field_id('number_of_tags') . '" name="' . $this->get_field_name('number_of_tags') . '" type="text" value="' . $number_of_tags . '" /></label></p>';
	}
	
	function get_post_count_by_tags($tags_array = array()) {
		
		$posts_array = query_posts(array('posts_per_page' => -1, 'tag_slug__and' => $tags_array));
		return count($posts_array);
	}
	
	function generic_link_rewrite($tag_slugs) {
		// Stolen from get_tag_link in category-template.php
		global $wp_rewrite;
		$taglink_structure = $wp_rewrite->get_tag_permastruct();
		
		$taglink = '';
		// Stolen from get_tag_link in category-template.php
		if ( empty( $taglink_structure ) ) {
			$file = get_option( 'home' ) . '/';
			$taglink = $file . '?tag=' . $tag_slugs;
		} else {
			$taglink = str_replace( '%tag%', $tag_slugs, $taglink_structure );
			$taglink = get_option( 'home' ) . user_trailingslashit( $taglink, 'category' );
		}
		return $taglink;
	}

}

function init_rtf_header() {
	$stylesheet_url = '/related-tag-filter/css/style.css';
	$stylesheet_wp_url = WP_PLUGIN_URL . $stylesheet_url;
	$stylesheet_file = WP_PLUGIN_DIR . $stylesheet_url;
	
	$jquerystyle_url = '/related-tag-filter/css/jquery-ui-1.7.2.custom.css';
	$jquerystyle_wp_url = WP_PLUGIN_URL . $jquerystyle_url;
	$jquerystyle_file = WP_PLUGIN_DIR . $jquerystyle_url;
	
	$customjs_url = '/related-tag-filter/js/custom.js';
	$customjs_wp_url = WP_PLUGIN_URL . $customjs_url;
	$customjs_file = WP_PLUGIN_DIR .  $customjs_url;
	
	if ( file_exists($stylesheet_file) ) {
		wp_register_style('rtfstyle', $stylesheet_wp_url);
		wp_enqueue_style('rtfstyle');
	}
	if ( file_exists($jquerystyle_file) ) {
		wp_register_style('jquerystyle', $jquerystyle_wp_url);
		wp_enqueue_style('jquerystyle');
	}
	if ( file_exists($customjs_file) ) {
		wp_register_script('rtfjs', $customjs_wp_url, array('jquery'));
		wp_enqueue_script('rtfjs');
	}
	wp_enqueue_script('jquery-ui-dialog');
	
}

function init_rtf_widget() {
	register_widget("Related_Tag_Filter_Widget");
}

add_action("wp_print_styles", "init_rtf_header");
add_action("widgets_init", "init_rtf_widget");

?>

