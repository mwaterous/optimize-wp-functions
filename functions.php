<?php
if (isset($_GET['hide_admin_bar'])) {
    add_filter('show_admin_bar', '__return_false');
}


// Remove the <div> surrounding the dynamic navigation to cleanup markup
function _pfx_wp_nav_menu_args($args = '')
{
    $args['container'] = false;

    return $args;
}

// Remove Injected classes, ID's and Page ID's from Navigation <li> items
function _pfx_css_attributes_filter($var)
{
    return is_array($var) ? [] : '';
}

// Remove invalid rel attribute values in the categorylist
function _pfx_remove_category_rel_from_category_list($thelist)
{
    return str_replace('rel="category tag"', 'rel="tag"', $thelist);
}

// Add page slug to body class, love this - Credit: Starkers Wordpress Theme
function _pfx_add_slug_to_body_class($classes)
{
    global $post;

    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[ $key ]);
        }
    } else if (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } else if (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }

    return $classes;
}

// Remove wp_head() injected Recent Comment styles
function _pfx_remove_recent_comments_style()
{
    global $wp_widget_factory;

    remove_action('wp_head', [
        $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
        'recent_comments_style',
    ]);
}

// Remove 'text/css' from enqueued stylesheet
function _pfx_remove_style_from_header_tags($tag)
{
    return preg_replace('~\s+type=["\'][^"\']++["\']~', '', $tag);
}

// Remove thumbnail width and height dimensions that prevent fluid images in the_thumbnail
function _pfx_remove_thumbnail_dimensions($html)
{
    $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);

    return $html;
}


/*------------------------------------*\
	Actions + Filters + ShortCodes
\*------------------------------------*/

// Add Actions
add_action('widgets_init', '_pfx_remove_recent_comments_style'); // Remove inline Recent Comment Styles from wp_head()

// Remove Actions
remove_action('wp_head', 'feed_links_extra', 3); // Display the links to the extra feeds such as category feeds
remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
remove_action('wp_head', 'rsd_link'); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action('wp_head', 'wlwmanifest_link'); // Display the link to the Windows Live Writer manifest file.
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); // Display relational links for the posts adjacent to the current post.
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

// Add Filters
add_filter('body_class', '_pfx_add_slug_to_body_class'); // Add slug to body class (Starkers build)
add_filter('wp_nav_menu_args', '_pfx_wp_nav_menu_args'); // Remove surrounding <div> from WP Navigation
add_filter('nav_menu_css_class', '_pfx_css_attributes_filter', 100, 1); // Remove Navigation <li> injected classes (Commented out by default)
add_filter('nav_menu_item_id', '_pfx_css_attributes_filter', 100, 1); // Remove Navigation <li> injected ID (Commented out by default)
add_filter('page_css_class', '_pfx_css_attributes_filter', 100, 1); // Remove Navigation <li> Page ID's (Commented out by default)
add_filter('the_category', 'remove_category_rel_from_category_list'); // Remove invalid rel attribute
add_filter('style_loader_tag', '_pfx_remove_style_from_header_tags'); // Remove 'text/css' from enqueued stylesheet
add_filter('post_thumbnail_html', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to thumbnails
add_filter('image_send_to_editor', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to post images

// Remove Filters
remove_filter('the_excerpt', 'wpautop'); // Remove <p> tags from Excerpt altogether
remove_filter('excerpt_more', 'wp_embed_excerpt_more', 20);



/* Alternative scripts/style cleanup */

// Remove the ver string
function onboard_remove_script_version($src)
{
    return remove_query_arg('ver', $src);
}

add_filter('script_loader_src', 'onboard_remove_script_version');
add_filter('style_loader_src', 'onboard_remove_script_version');

// This is more militant than the earlier version, and also removes the script handle
add_filter('style_loader_tag', function ($tag, $handle, $href, $media) {
    return str_replace("id='{$handle}-css'  href='{$href}' type='text/css' media='{$media}' />", "href='{$href}' media='{$media}'>", $tag);
}, 10, 4);

remove_filter('the_title', 'add_breadcrumb_to_the_title');