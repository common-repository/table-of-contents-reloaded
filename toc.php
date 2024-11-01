<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
/*
Plugin Name:	Table of Contents Reloaded
Plugin URI: 	https://wpplugins.net/table-of-contents-reloaded/
Description: 	A powerful yet user friendly plugin that automatically creates a table of contents. Can also output a sitemap listing all pages and categories.
Author: 		Yehuda Hassine
Author URI: 	https://wpplugins.net/
Text Domain:	table-of-contents-reloaded
Domain Path:	/languages
Version: 		1.0
License:		GPL2
*/

/*
Table Of Contents Reloaded is a fork of Table Of Contents Plus developed by Michael Tran.
GPL licenced Oxygen icon used for the colour wheel - http://www.iconfinder.com/search/?q=iconset%3Aoxygen
*/

/**
 * FOR CONSIDERATION:
 * - back to top links
 * - sitemap
 * - easier exclude pages/categories
 * - support other taxonomies
 * - advanced options
 * - highlight target css
 */

define('TOC_VERSION', '1.0');
define('TOC_POSITION_BEFORE_FIRST_HEADING', 1);
define('TOC_POSITION_TOP', 2);
define('TOC_POSITION_BOTTOM', 3);
define('TOC_POSITION_AFTER_FIRST_HEADING', 4);
define('TOC_MIN_START', 2);
define('TOC_MAX_START', 10);
define('TOC_SMOOTH_SCROLL_OFFSET', 30);
define('TOC_WRAPPING_NONE', 0);
define('TOC_WRAPPING_LEFT', 1);
define('TOC_WRAPPING_RIGHT', 2);
define('TOC_THEME_GREY', 1);
define('TOC_THEME_LIGHT_BLUE', 2);
define('TOC_THEME_WHITE', 3);
define('TOC_THEME_BLACK', 4);
define('TOC_THEME_TRANSPARENT', 99);
define('TOC_THEME_CUSTOM', 100);
define('TOC_DEFAULT_BACKGROUND_COLOUR', '#f9f9f9');
define('TOC_DEFAULT_BORDER_COLOUR', '#aaaaaa');
define('TOC_DEFAULT_TITLE_COLOUR', '#');
define('TOC_DEFAULT_LINKS_COLOUR', '#');
define('TOC_DEFAULT_LINKS_HOVER_COLOUR', '#');
define('TOC_DEFAULT_LINKS_VISITED_COLOUR', '#');


if (!class_exists('toc')) :
    class toc
    {

        private $path;        // eg /wp-content/plugins/toc
        private $options;
        private $show_toc;    // allows to override the display (eg through [no_toc] shortcode)
        private $exclude_post_types;
        private $collision_collector;    // keeps a track of used anchors for collision detecting

        function __construct()
        {

            $this->includes();

            // get options
            $defaults = array(        // default options
                'fragment_prefix' => 'i',
                'position' => TOC_POSITION_BEFORE_FIRST_HEADING,
                'start' => 4,
                'show_heading_text' => true,
                'heading_text' => 'Contents',
                'auto_insert_post_types' => array('page'),
                'show_heirarchy' => true,
                'ordered_list' => true,
                'smooth_scroll' => false,
                'smooth_scroll_offset' => TOC_SMOOTH_SCROLL_OFFSET,
                'visibility' => true,
                'visibility_show' => 'show',
                'visibility_hide' => 'hide',
                'visibility_hide_by_default' => false,
                'width' => 'Auto',
                'width_custom' => '275',
                'width_custom_units' => 'px',
                'wrapping' => TOC_WRAPPING_NONE,
                'font_size' => '95',
                'font_size_units' => '%',
                'theme' => TOC_THEME_GREY,
                'custom_background_colour' => TOC_DEFAULT_BACKGROUND_COLOUR,
                'custom_border_colour' => TOC_DEFAULT_BORDER_COLOUR,
                'custom_title_colour' => TOC_DEFAULT_TITLE_COLOUR,
                'custom_links_colour' => TOC_DEFAULT_LINKS_COLOUR,
                'custom_links_hover_colour' => TOC_DEFAULT_LINKS_HOVER_COLOUR,
                'custom_links_visited_colour' => TOC_DEFAULT_LINKS_VISITED_COLOUR,
                'lowercase' => false,
                'hyphenate' => false,
                'bullet_spacing' => false,
                'include_homepage' => false,
                'exclude_css' => false,
                'exclude' => '',
                'heading_levels' => array('1', '2', '3', '4', '5', '6'),
                'restrict_path' => '',
                'css_container_class' => '',
                'sitemap_show_page_listing' => true,
                'sitemap_show_category_listing' => true,
                'sitemap_heading_type' => 3,
                'sitemap_pages' => 'Pages',
                'sitemap_categories' => 'Categories',
                'show_toc_in_widget_only' => false,
                'show_toc_in_widget_only_post_types' => array('page')
            );
            $options = get_option('toc-options', $defaults);
            $this->options = wp_parse_args($options, $defaults);

            // Init Shortcodes
            new toc_shortcodes($this->options);

            $this->path = plugins_url('', __FILE__);
            $this->show_toc = apply_filters('toc_show_toc', true);
            $this->exclude_post_types = array('attachment', 'revision', 'nav_menu_item', 'safecss');
            $this->collision_collector = array();

            add_action('plugins_loaded', array($this, 'plugins_loaded'));
            add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
            add_action('wp_head', array($this, 'wp_head'));
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('widgets_init', array($this, 'widgets_init'));
            // Test this
            //add_action('sidebar_admin_setup', array($this, 'sidebar_admin_setup'));
            add_action( 'delete_widget', array( $this, 'sidebar_admin_setup'),10 ,3 );

            add_filter('the_content', array($this, 'the_content'), 100);    // run after shortcodes are interpretted (level 10)
            add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
            add_filter('widget_text', 'do_shortcode');


        }


        function __destruct()
        {
        }

        private function includes() {
            require_once 'inc/api/functions.php';
            require_once 'inc/shortcodes.php';
            require_once 'inc/toc-widget.php';
        }


        public function get_options()
        {
            return $this->options;
        }


        public function set_option($array)
        {
            $this->options = array_merge($this->options, $array);
        }


        /**
         * Allows the developer to disable TOC execution
         */
        public function disable()
        {
            $this->show_toc = false;
        }


        /**
         * Allows the developer to enable TOC execution
         */
        public function enable()
        {
            $this->show_toc = true;
        }


        public function set_show_toc_in_widget_only($value = false)
        {
            if ($value)
                $this->options['show_toc_in_widget_only'] = true;
            else
                $this->options['show_toc_in_widget_only'] = false;

            update_option('toc-options', $this->options);
        }


        public function set_show_toc_in_widget_only_post_types($value = false)
        {
            if ($value)
                $this->options['show_toc_in_widget_only_post_types'] = $value;
            else
                $this->options['show_toc_in_widget_only_post_types'] = array();

            update_option('toc-options', $this->options);
        }


        public function get_exclude_post_types()
        {
            return $this->exclude_post_types;
        }


        function plugin_action_links($links, $file)
        {
            if ($file == 'table-of-contents-reloaded/' . basename(__FILE__)) {
                $settings_link = '<a href="options-general.php?page=toc">' . __('Settings', 'table-of-contents-reloaded') . '</a>';
                $links = array_merge(array($settings_link), $links);
            }
            return $links;
        }


        /**
         * Register and load CSS and javascript files for frontend.
         */
        function wp_enqueue_scripts()
        {
            $js_vars = array();

            // register our CSS and scripts
            wp_register_style('toc-screen', $this->path . '/screen.min.css', array(), TOC_VERSION);
            wp_register_script('toc-front', $this->path . '/front.min.js', array('jquery'), TOC_VERSION, true);

            // enqueue them!
            if (!$this->options['exclude_css']) wp_enqueue_style("toc-screen");

            if ($this->options['smooth_scroll']) $js_vars['smooth_scroll'] = true;
            wp_enqueue_script('toc-front');
            if ($this->options['show_heading_text'] && $this->options['visibility']) {
                $width = ($this->options['width'] != 'User defined') ? $this->options['width'] : $this->options['width_custom'] . $this->options['width_custom_units'];
                $js_vars['visibility_show'] = esc_js($this->options['visibility_show']);
                $js_vars['visibility_hide'] = esc_js($this->options['visibility_hide']);
                if ($this->options['visibility_hide_by_default']) $js_vars['visibility_hide_by_default'] = true;
                $js_vars['width'] = esc_js($width);
            }
            if ($this->options['smooth_scroll_offset'] != TOC_SMOOTH_SCROLL_OFFSET)
                $js_vars['smooth_scroll_offset'] = esc_js($this->options['smooth_scroll_offset']);

            if (count($js_vars) > 0) {
                wp_localize_script(
                    'toc-front',
                    'tocplus',
                    $js_vars
                );
            }
        }


        function plugins_loaded()
        {
            load_plugin_textdomain('table-of-contents-reloaded', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }


        function admin_init()
        {
            wp_register_script('toc_admin_script', $this->path . '/admin.js');
            wp_register_style('toc_admin_style', $this->path . '/admin.css');
        }


        function admin_menu()
        {
            $page = add_submenu_page(
                'options-general.php',
                __('TOC', 'table-of-contents-reloaded') . '+',
                __('TOC', 'table-of-contents-reloaded') . '+',
                'manage_options',
                'toc',
                array($this, 'admin_options')
            );

            add_action('admin_print_styles-' . $page, array($this, 'admin_options_head'));
        }


        function widgets_init()
        {
            register_widget('toc_widget');
        }


        /**
         * Remove widget options on widget deletion
         */
        function sidebar_admin_setup($widget_id, $sidebar_id, $id_base)
        {
            if ( $id_base == 'toc-widget') {
                $this->set_show_toc_in_widget_only(false);
                $this->set_show_toc_in_widget_only_post_types(array('page'));
            }
        }


        /**
         * Load needed scripts and styles only on the toc administration interface.
         */
        function admin_options_head()
        {
            wp_enqueue_style('farbtastic');
            wp_enqueue_script('farbtastic');
            wp_enqueue_script('jquery');
            wp_enqueue_script('toc_admin_script');
            wp_enqueue_style('toc_admin_style');
        }


        /**
         * Tries to convert $string into a valid hex colour.
         * Returns $default if $string is not a hex value, otherwise returns verified hex.
         */
        private function hex_value($string = '', $default = '#')
        {
            $return = $default;

            if ($string) {
                $value = sanitize_text_field($string);
                // strip out non hex chars
                $return = preg_replace('/[^a-fA-F0-9]*/', '', $value);

                switch (strlen($return)) {
                    case 3:    // do next
                    case 6:
                        $return = '#' . $return;
                        break;

                    default:
                        if (strlen($return) > 6)
                            $return = '#' . substr($return, 0, 6);    // if > 6 chars, then take the first 6
                        elseif (strlen($return) > 3 && strlen($return) < 6)
                            $return = '#' . substr($return, 0, 3);    // if between 3 and 6, then take first 3
                        else
                            $return = $default;                        // not valid, return $default
                }
            }

            return $return;
        }

        /**
         * get called from admin/options.php
         *
         * @return bool
         */
        private function save_admin_options()
        {

            // security check
            if (!wp_verify_nonce(@$_POST['toc-admin-options'], 'toc-plus' ) )
                return false;

            // require an administrator level to save
            if (!current_user_can('manage_options'))
                return false;

            // use stripslashes on free text fields that can have ' " \
            // WordPress automatically slashes these characters as part of
            // wp-includes/load.php::wp_magic_quotes()

            $custom_background_colour = $this->hex_value(trim($_POST['custom_background_colour']), TOC_DEFAULT_BACKGROUND_COLOUR);
            $custom_border_colour = $this->hex_value(trim($_POST['custom_border_colour']), TOC_DEFAULT_BORDER_COLOUR);
            $custom_title_colour = $this->hex_value(trim($_POST['custom_title_colour']), TOC_DEFAULT_TITLE_COLOUR);
            $custom_links_colour = $this->hex_value(trim($_POST['custom_links_colour']), TOC_DEFAULT_LINKS_COLOUR);
            $custom_links_hover_colour = $this->hex_value(trim($_POST['custom_links_hover_colour']), TOC_DEFAULT_LINKS_HOVER_COLOUR);
            $custom_links_visited_colour = $this->hex_value(trim($_POST['custom_links_visited_colour']), TOC_DEFAULT_LINKS_VISITED_COLOUR);

            if ($restrict_path = sanitize_text_field($_POST['restrict_path'])) {
                if (strpos($restrict_path, '/') !== 0) {
                    // restrict path did not start with a / so unset it
                    $restrict_path = '';
                }
            }

            $this->options = array_merge(
                $this->options,
                array(
                    'fragment_prefix' => sanitize_text_field(trim($_POST['fragment_prefix']) ),
                    'position' => absint($_POST['position']),
                    'start' => absint($_POST['start']),
                    'show_heading_text' => (isset($_POST['show_heading_text']) && $_POST['show_heading_text']) ? true : false,
                    'heading_text' => sanitize_text_field(trim($_POST['heading_text'])),
                    'auto_insert_post_types' => array_map('sanitize_text_field', $_POST['auto_insert_post_types']),
                    'show_heirarchy' => (isset($_POST['show_heirarchy']) && $_POST['show_heirarchy']) ? true : false,
                    'ordered_list' => (isset($_POST['ordered_list']) && $_POST['ordered_list']) ? true : false,
                    'smooth_scroll' => (isset($_POST['smooth_scroll']) && $_POST['smooth_scroll']) ? true : false,
                    'smooth_scroll_offset' => absint($_POST['smooth_scroll_offset']),
                    'visibility' => (isset($_POST['visibility']) && $_POST['visibility']) ? true : false,
                    'visibility_show' => sanitize_text_field(trim($_POST['visibility_show'])),
                    'visibility_hide' => sanitize_text_field(trim($_POST['visibility_hide'])),
                    'visibility_hide_by_default' => (isset($_POST['visibility_hide_by_default']) && $_POST['visibility_hide_by_default']) ? true : false,
                    'width' => sanitize_text_field(trim($_POST['width'])),
                    'width_custom' => floatval($_POST['width_custom']),
                    'width_custom_units' => sanitize_text_field(trim($_POST['width_custom_units'])),
                    'wrapping' => absint($_POST['wrapping']),
                    'font_size' => floatval($_POST['font_size']),
                    'font_size_units' => sanitize_text_field(trim($_POST['font_size_units'])),
                    'theme' => absint($_POST['theme']),
                    'custom_background_colour' => $custom_background_colour,
                    'custom_border_colour' => $custom_border_colour,
                    'custom_title_colour' => $custom_title_colour,
                    'custom_links_colour' => $custom_links_colour,
                    'custom_links_hover_colour' => $custom_links_hover_colour,
                    'custom_links_visited_colour' => $custom_links_visited_colour,
                    'lowercase' => (isset($_POST['lowercase']) && $_POST['lowercase']) ? true : false,
                    'hyphenate' => (isset($_POST['hyphenate']) && $_POST['hyphenate']) ? true : false,
                    'bullet_spacing' => (isset($_POST['bullet_spacing']) && $_POST['bullet_spacing']) ? true : false,
                    'include_homepage' => (isset($_POST['include_homepage']) && $_POST['include_homepage']) ? true : false,
                    'exclude_css' => (isset($_POST['exclude_css']) && $_POST['exclude_css']) ? true : false,
                    'heading_levels' => @(array)$_POST['heading_levels'],
                    'exclude' => sanitize_text_field(trim($_POST['exclude'])),
                    'restrict_path' => $restrict_path,
                    'sitemap_show_page_listing' => (isset($_POST['sitemap_show_page_listing']) && $_POST['sitemap_show_page_listing']) ? true : false,
                    'sitemap_show_category_listing' => (isset($_POST['sitemap_show_category_listing']) && $_POST['sitemap_show_category_listing']) ? true : false,
                    'sitemap_heading_type' => absint($_POST['sitemap_heading_type']),
                    'sitemap_pages' => sanitize_text_field(trim($_POST['sitemap_pages'])),
                    'sitemap_categories' => sanitize_text_field(trim($_POST['sitemap_categories']))
                )
            );

            // update_option will return false if no changes were made
            update_option('toc-options', $this->options);

            return true;
        }


        function admin_options() {
            include_once 'admin/options.php';
        }


        function wp_head()
        {
            $css = '';

            if (!$this->options['exclude_css']) {
                if ($this->options['theme'] == TOC_THEME_CUSTOM || $this->options['width'] != 'Auto') {
                    $css .= 'div#toc_container {';
                    if ($this->options['theme'] == TOC_THEME_CUSTOM)
                        $css .= 'background: ' . $this->options['custom_background_colour'] . ';border: 1px solid ' . $this->options['custom_border_colour'] . ';';
                    if ($this->options['width'] != 'Auto') {
                        $css .= 'width: ';
                        if ($this->options['width'] != 'User defined')
                            $css .= $this->options['width'];
                        else
                            $css .= $this->options['width_custom'] . $this->options['width_custom_units'];
                        $css .= ';';
                    }
                    $css .= '}';
                }

                if ('95%' != $this->options['font_size'] . $this->options['font_size_units'])
                    $css .= 'div#toc_container ul li {font-size: ' . $this->options['font_size'] . $this->options['font_size_units'] . ';}';

                if ($this->options['theme'] == TOC_THEME_CUSTOM) {
                    if ($this->options['custom_title_colour'] != TOC_DEFAULT_TITLE_COLOUR)
                        $css .= 'div#toc_container p.toc_title {color: ' . $this->options['custom_title_colour'] . ';}';
                    if ($this->options['custom_links_colour'] != TOC_DEFAULT_LINKS_COLOUR)
                        $css .= 'div#toc_container p.toc_title a,div#toc_container ul.toc_list a {color: ' . $this->options['custom_links_colour'] . ';}';
                    if ($this->options['custom_links_hover_colour'] != TOC_DEFAULT_LINKS_HOVER_COLOUR)
                        $css .= 'div#toc_container p.toc_title a:hover,div#toc_container ul.toc_list a:hover {color: ' . $this->options['custom_links_hover_colour'] . ';}';
                    if ($this->options['custom_links_hover_colour'] != TOC_DEFAULT_LINKS_HOVER_COLOUR)
                        $css .= 'div#toc_container p.toc_title a:hover,div#toc_container ul.toc_list a:hover {color: ' . $this->options['custom_links_hover_colour'] . ';}';
                    if ($this->options['custom_links_visited_colour'] != TOC_DEFAULT_LINKS_VISITED_COLOUR)
                        $css .= 'div#toc_container p.toc_title a:visited,div#toc_container ul.toc_list a:visited {color: ' . $this->options['custom_links_visited_colour'] . ';}';
                }
            }

            if ($css)
                echo '<style type="text/css">' . $css . '</style>';
        }


        /**
         * Returns a clean url to be used as the destination anchor target
         */
        private function url_anchor_target($title)
        {
            $return = false;

            if ($title) {
                $return = trim(strip_tags($title));

                // convert accented characters to ASCII
                $return = remove_accents($return);

                // replace newlines with spaces (eg when headings are split over multiple lines)
                $return = str_replace(array("\r", "\n", "\n\r", "\r\n"), ' ', $return);

                // remove &amp;
                $return = str_replace('&amp;', '', $return);

                // remove non alphanumeric chars
                $return = preg_replace('/[^a-zA-Z0-9 \-_]*/', '', $return);

                // convert spaces to _
                $return = str_replace(
                    array('  ', ' '),
                    '_',
                    $return
                );

                // remove trailing - and _
                $return = rtrim($return, '-_');

                // lowercase everything?
                if ($this->options['lowercase']) $return = strtolower($return);

                // if blank, then prepend with the fragment prefix
                // blank anchors normally appear on sites that don't use the latin charset
                if (!$return) {
                    $return = ($this->options['fragment_prefix']) ? $this->options['fragment_prefix'] : '_';
                }

                // hyphenate?
                if ($this->options['hyphenate']) {
                    $return = str_replace('_', '-', $return);
                    $return = str_replace('--', '-', $return);
                }
            }

            if (array_key_exists($return, $this->collision_collector)) {
                $this->collision_collector[$return]++;
                $return .= '-' . $this->collision_collector[$return];
            } else
                $this->collision_collector[$return] = 1;

            return apply_filters('toc_url_anchor_target', $return);
        }


        private function build_hierarchy(&$matches)
        {
            $current_depth = 100;    // headings can't be larger than h6 but 100 as a default to be sure
            $html = '';
            $numbered_items = array();
            $numbered_items_min = null;

            // reset the internal collision collection
            $this->collision_collector = array();

            // find the minimum heading to establish our baseline
            for ($i = 0; $i < count($matches); $i++) {
                if ($current_depth > $matches[$i][2])
                    $current_depth = (int)$matches[$i][2];
            }

            $numbered_items[$current_depth] = 0;
            $numbered_items_min = $current_depth;

            for ($i = 0; $i < count($matches); $i++) {

                if ($current_depth == (int)$matches[$i][2])
                    $html .= '<li>';

                // start lists
                if ($current_depth != (int)$matches[$i][2]) {
                    for ($current_depth; $current_depth < (int)$matches[$i][2]; $current_depth++) {
                        $numbered_items[$current_depth + 1] = 0;
                        $html .= '<ul><li>';
                    }
                }

                // list item
                if (in_array($matches[$i][2], $this->options['heading_levels'])) {
                    $html .= '<a href="#' . $this->url_anchor_target($matches[$i][0]) . '">';
                    if ($this->options['ordered_list']) {
                        // attach leading numbers when lower in hierarchy
                        $html .= '<span class="toc_number toc_depth_' . ($current_depth - $numbered_items_min + 1) . '">';
                        for ($j = $numbered_items_min; $j < $current_depth; $j++) {
                            $number = ($numbered_items[$j]) ? $numbered_items[$j] : 0;
                            $html .= $number . '.';
                        }

                        $html .= ($numbered_items[$current_depth] + 1) . '</span> ';
                        $numbered_items[$current_depth]++;
                    }
                    $html .= strip_tags($matches[$i][0]) . '</a>';
                }


                // end lists
                if ($i != count($matches) - 1) {
                    if ($current_depth > (int)$matches[$i + 1][2]) {
                        for ($current_depth; $current_depth > (int)$matches[$i + 1][2]; $current_depth--) {
                            $html .= '</li></ul>';
                            $numbered_items[$current_depth] = 0;
                        }
                    }

                    if ($current_depth == (int)@$matches[$i + 1][2])
                        $html .= '</li>';
                } else {
                    // this is the last item, make sure we close off all tags
                    for ($current_depth; $current_depth >= $numbered_items_min; $current_depth--) {
                        $html .= '</li>';
                        if ($current_depth != $numbered_items_min) $html .= '</ul>';
                    }
                }
            }

            return $html;
        }


        /**
         * Returns a string with all items from the $find array replaced with their matching
         * items in the $replace array.  This does a one to one replacement (rather than
         * globally).
         *
         * This function is multibyte safe.
         *
         * $find and $replace are arrays, $string is the haystack.  All variables are
         * passed by reference.
         */
        private function mb_find_replace(&$find = false, &$replace = false, &$string = '')
        {
            if (is_array($find) && is_array($replace) && $string) {
                // check if multibyte strings are supported
                if (function_exists('mb_strpos')) {
                    for ($i = 0; $i < count($find); $i++) {
                        $string =
                            mb_substr($string, 0, mb_strpos($string, $find[$i])) .    // everything befor $find
                            $replace[$i] .                                                // its replacement
                            mb_substr($string, mb_strpos($string, $find[$i]) + mb_strlen($find[$i]))    // everything after $find
                        ;
                    }
                } else {
                    for ($i = 0; $i < count($find); $i++) {
                        $string = substr_replace(
                            $string,
                            $replace[$i],
                            strpos($string, $find[$i]),
                            strlen($find[$i])
                        );
                    }
                }
            }

            return $string;
        }


        /**
         * This function extracts headings from the html formatted $content.  It will pull out
         * only the required headings as specified in the options.  For all qualifying headings,
         * this function populates the $find and $replace arrays (both passed by reference)
         * with what to search and replace with.
         *
         * Returns a html formatted string of list items for each qualifying heading.  This
         * is everything between and NOT including <ul> and </ul>
         */
        public function extract_headings(&$find, &$replace, $content = '')
        {
            $matches = array();
            $anchor = '';
            $items = false;

            // reset the internal collision collection as the_content may have been triggered elsewhere
            // eg by themes or other plugins that need to read in content such as metadata fields in
            // the head html tag, or to provide descriptions to twitter/facebook
            $this->collision_collector = array();

            if (is_array($find) && is_array($replace) && $content) {
                // get all headings
                // the html spec allows for a maximum of 6 heading depths
                if (preg_match_all('/(<h([1-6]{1})[^>]*>).*<\/h\2>/msuU', $content, $matches, PREG_SET_ORDER)) {

                    // remove undesired headings (if any) as defined by heading_levels
                    if (count($this->options['heading_levels']) != 6) {
                        $new_matches = array();
                        for ($i = 0; $i < count($matches); $i++) {
                            if (in_array($matches[$i][2], $this->options['heading_levels']))
                                $new_matches[] = $matches[$i];
                        }
                        $matches = $new_matches;
                    }

                    // remove specific headings if provided via the 'exclude' property
                    if ($this->options['exclude']) {
                        $excluded_headings = explode('|', $this->options['exclude']);
                        if (count($excluded_headings) > 0) {
                            for ($j = 0; $j < count($excluded_headings); $j++) {
                                // escape some regular expression characters
                                // others: http://www.php.net/manual/en/regexp.reference.meta.php
                                $excluded_headings[$j] = str_replace(
                                    array('*'),
                                    array('.*'),
                                    trim($excluded_headings[$j])
                                );
                            }

                            $new_matches = array();
                            for ($i = 0; $i < count($matches); $i++) {
                                $found = false;
                                for ($j = 0; $j < count($excluded_headings); $j++) {
                                    if (@preg_match('/^' . $excluded_headings[$j] . '$/imU', strip_tags($matches[$i][0]))) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) $new_matches[] = $matches[$i];
                            }
                            if (count($matches) != count($new_matches))
                                $matches = $new_matches;
                        }
                    }

                    // remove empty headings
                    $new_matches = array();
                    for ($i = 0; $i < count($matches); $i++) {
                        if (trim(strip_tags($matches[$i][0])) != false)
                            $new_matches[] = $matches[$i];
                    }
                    if (count($matches) != count($new_matches))
                        $matches = $new_matches;

                    // check minimum number of headings
                    if (count($matches) >= $this->options['start']) {

                        for ($i = 0; $i < count($matches); $i++) {
                            // get anchor and add to find and replace arrays
                            $anchor = $this->url_anchor_target($matches[$i][0]);
                            $find[] = $matches[$i][0];
                            $replace[] = str_replace(
                                array(
                                    $matches[$i][1],                // start of heading
                                    '</h' . $matches[$i][2] . '>'    // end of heading
                                ),
                                array(
                                    $matches[$i][1] . '<span id="' . $anchor . '">',
                                    '</span></h' . $matches[$i][2] . '>'
                                ),
                                $matches[$i][0]
                            );

                            // assemble flat list
                            if (!$this->options['show_heirarchy']) {
                                $items .= '<li><a href="#' . $anchor . '">';
                                if ($this->options['ordered_list']) $items .= count($replace) . ' ';
                                $items .= strip_tags($matches[$i][0]) . '</a></li>';
                            }
                        }

                        // build a hierarchical toc?
                        // we could have tested for $items but that var can be quite large in some cases
                        if ($this->options['show_heirarchy']) $items = $this->build_hierarchy($matches);

                    }
                }
            }

            return $items;
        }


        /**
         * Returns true if the table of contents is eligible to be printed, false otherwise.
         */
        public function is_eligible($shortcode_used = false)
        {
            global $post;

            // do not trigger the TOC when displaying an XML/RSS feed
            if (is_feed()) return false;

            // if the shortcode was used, this bypasses many of the global options
            if ($shortcode_used !== false) {
                // shortcode is used, make sure it adheres to the exclude from
                // homepage option if we're on the homepage
                if (!$this->options['include_homepage'] && is_front_page())
                    return false;
                else
                    return true;
            } else {
                if (
                    (in_array(get_post_type($post), $this->options['auto_insert_post_types']) && $this->show_toc && !is_search() && !is_archive() && !is_front_page()) ||
                    ($this->options['include_homepage'] && is_front_page())
                ) {
                    if ($this->options['restrict_path']) {
                        if (strpos($_SERVER['REQUEST_URI'], $this->options['restrict_path']) === 0)
                            return true;
                        else
                            return false;
                    } else
                        return true;
                } else
                    return false;
            }
        }


        function the_content($content)
        {
            global $post;
            $items = $css_classes = $anchor = '';
            $custom_toc_position = strpos($content, '<!--TOC-->');
            $find = $replace = array();

            if ($this->is_eligible($custom_toc_position)) {

                $items = $this->extract_headings($find, $replace, $content);

                if ($items) {
                    // do we display the toc within the content or has the user opted
                    // to only show it in the widget?  if so, then we still need to
                    // make the find/replace call to insert the anchors
                    if ($this->options['show_toc_in_widget_only'] && (in_array(get_post_type(), $this->options['show_toc_in_widget_only_post_types']))) {
                        $content = $this->mb_find_replace($find, $replace, $content);
                    } else {

                        // wrapping css classes
                        switch ($this->options['wrapping']) {
                            case TOC_WRAPPING_LEFT:
                                $css_classes .= ' toc_wrap_left';
                                break;

                            case TOC_WRAPPING_RIGHT:
                                $css_classes .= ' toc_wrap_right';
                                break;

                            case TOC_WRAPPING_NONE:
                            default:
                                // do nothing
                        }

                        // colour themes
                        switch ($this->options['theme']) {
                            case TOC_THEME_LIGHT_BLUE:
                                $css_classes .= ' toc_light_blue';
                                break;

                            case TOC_THEME_WHITE:
                                $css_classes .= ' toc_white';
                                break;

                            case TOC_THEME_BLACK:
                                $css_classes .= ' toc_black';
                                break;

                            case TOC_THEME_TRANSPARENT:
                                $css_classes .= ' toc_transparent';
                                break;

                            case TOC_THEME_GREY:
                            default:
                                // do nothing
                        }

                        // bullets?
                        if ($this->options['bullet_spacing'])
                            $css_classes .= ' have_bullets';
                        else
                            $css_classes .= ' no_bullets';

                        if ($this->options['css_container_class']) $css_classes .= ' ' . $this->options['css_container_class'];

                        $css_classes = trim($css_classes);

                        // an empty class="" is invalid markup!
                        if (!$css_classes) $css_classes = ' ';

                        // add container, toc title and list items
                        $html = '<div id="toc_container" class="' . $css_classes . '">';
                        if ($this->options['show_heading_text']) {
                            $toc_title = $this->options['heading_text'];
                            if (strpos($toc_title, '%PAGE_TITLE%') !== false) $toc_title = str_replace('%PAGE_TITLE%', get_the_title(), $toc_title);
                            if (strpos($toc_title, '%PAGE_NAME%') !== false) $toc_title = str_replace('%PAGE_NAME%', get_the_title(), $toc_title);
                            $html .= '<p class="toc_title">' . htmlentities($toc_title, ENT_COMPAT, 'UTF-8') . '</p>';
                        }
                        $html .= '<ul class="toc_list">' . $items . '</ul></div>' . "\n";

                        if ($custom_toc_position !== false) {
                            $find[] = '<!--TOC-->';
                            $replace[] = $html;
                            $content = $this->mb_find_replace($find, $replace, $content);
                        } else {
                            if (count($find) > 0) {
                                switch ($this->options['position']) {
                                    case TOC_POSITION_TOP:
                                        $content = $html . $this->mb_find_replace($find, $replace, $content);
                                        break;

                                    case TOC_POSITION_BOTTOM:
                                        $content = $this->mb_find_replace($find, $replace, $content) . $html;
                                        break;

                                    case TOC_POSITION_AFTER_FIRST_HEADING:
                                        $replace[0] = $replace[0] . $html;
                                        $content = $this->mb_find_replace($find, $replace, $content);
                                        break;

                                    case TOC_POSITION_BEFORE_FIRST_HEADING:
                                    default:
                                        $replace[0] = $html . $replace[0];
                                        $content = $this->mb_find_replace($find, $replace, $content);
                                }
                            }
                        }
                    }
                }
            } else {
                // remove <!--TOC--> (inserted from shortcode) from content
                $content = str_replace('<!--TOC-->', '', $content);
            }

            return $content;
        }

    } // end class
endif;


// do the magic
$tic = new toc();
