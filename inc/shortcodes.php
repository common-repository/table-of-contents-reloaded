<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class toc_shortcodes {

    private $options;

    /**
     * toc_shortcodes constructor.
     * @param toc $toc
     */
    public function __construct($options)
    {

        $this->options = $options;

        add_shortcode('toc', array($this, 'shortcode_toc'));
        add_shortcode('no_toc', array($this, 'shortcode_no_toc'));
        add_shortcode('sitemap', array($this, 'shortcode_sitemap'));
        add_shortcode('sitemap_pages', array($this, 'shortcode_sitemap_pages'));
        add_shortcode('sitemap_categories', array($this, 'shortcode_sitemap_categories'));
        add_shortcode('sitemap_posts', array($this, 'shortcode_sitemap_posts'));
    }

    function shortcode_toc($atts)
    {
        /**
         * @var $label
         * @var $label_show
         * @var $label_hide
         * @var $no_label
         * @var $class
         * @var $wrapping
         * @var $heading_levels
         * @var $exclude
         * @var $collapse
         */
        extract(shortcode_atts(array(
                'label' => $this->options['heading_text'],
                'label_show' => $this->options['visibility_show'],
                'label_hide' => $this->options['visibility_hide'],
                'no_label' => false,
                'class' => false,
                'wrapping' => $this->options['wrapping'],
                'heading_levels' => $this->options['heading_levels'],
                'exclude' => $this->options['exclude'],
                'collapse' => false
            ), $atts)
        );

        $re_enqueue_scripts = false;

        if ($no_label) $this->options['show_heading_text'] = false;
        if ($label) $this->options['heading_text'] = html_entity_decode($label);
        if ($label_show) {
            $this->options['visibility_show'] = html_entity_decode($label_show);
            $re_enqueue_scripts = true;
        }
        if ($label_hide) {
            $this->options['visibility_hide'] = html_entity_decode($label_hide);
            $re_enqueue_scripts = true;
        }
        if ($class) $this->options['css_container_class'] = $class;
        if ($wrapping) {
            switch (strtolower(trim($wrapping))) {
                case 'left':
                    $this->options['wrapping'] = TOC_WRAPPING_LEFT;
                    break;

                case 'right':
                    $this->options['wrapping'] = TOC_WRAPPING_RIGHT;
                    break;

                default:
                    // do nothing
            }
        }

        if ($exclude) $this->options['exclude'] = $exclude;
        if ($collapse) {
            $this->options['visibility_hide_by_default'] = true;
            $re_enqueue_scripts = true;
        }

        if ($re_enqueue_scripts) do_action('wp_enqueue_scripts');

        // if $heading_levels is an array, then it came from the global options
        // and wasn't provided by per instance
        if ($heading_levels && !is_array($heading_levels)) {
            // make sure they are numbers between 1 and 6 and put into
            // the $clean_heading_levels array if not already
            $clean_heading_levels = array();
            foreach (explode(',', $heading_levels) as $heading_level) {
                if (is_numeric($heading_level)) {
                    if (1 <= $heading_level && $heading_level <= 6) {
                        if (!in_array($heading_level, $clean_heading_levels)) {
                            $clean_heading_levels[] = $heading_level;
                        }
                    }
                }
            }

            if (count($clean_heading_levels) > 0)
                $this->options['heading_levels'] = $clean_heading_levels;
        }

        if (!is_search() && !is_archive() && !is_feed())
            return '<!--TOC-->';
        else
            return;
    }


    function shortcode_no_toc($atts)
    {
        add_filter('toc_show_toc', '__return_false' );
    }


    function shortcode_sitemap($atts)
    {
        $html = '';

        // only do the following if enabled
        if ($this->options['sitemap_show_page_listing'] || $this->options['sitemap_show_category_listing']) {
            $html = '<div class="toc_sitemap">';
            if ($this->options['sitemap_show_page_listing'])
                $html .=
                    '<h' . $this->options['sitemap_heading_type'] . ' class="toc_sitemap_pages">' . htmlentities($this->options['sitemap_pages'], ENT_COMPAT, 'UTF-8') . '</h' . $this->options['sitemap_heading_type'] . '>' .
                    '<ul class="toc_sitemap_pages_list">' .
                    wp_list_pages(array('title_li' => '', 'echo' => false)) .
                    '</ul>';
            if ($this->options['sitemap_show_category_listing'])
                $html .=
                    '<h' . $this->options['sitemap_heading_type'] . ' class="toc_sitemap_categories">' . htmlentities($this->options['sitemap_categories'], ENT_COMPAT, 'UTF-8') . '</h' . $this->options['sitemap_heading_type'] . '>' .
                    '<ul class="toc_sitemap_categories_list">' .
                    wp_list_categories(array('title_li' => '', 'echo' => false)) .
                    '</ul>';
            $html .= '</div>';
        }

        return $html;
    }


    function shortcode_sitemap_pages($atts)
    {
        /**
         * @var $heading
         * @var $label
         * @var $no_label
         * @var $exclude
         * @var $exclude_tree
         */
        extract(shortcode_atts(array(
                'heading' => $this->options['sitemap_heading_type'],
                'label' => htmlentities($this->options['sitemap_pages'], ENT_COMPAT, 'UTF-8'),
                'no_label' => false,
                'exclude' => '',
                'exclude_tree' => ''
            ), $atts)
        );

        if ($heading < 1 || $heading > 6)        // h1 to h6 are valid
            $heading = $this->options['sitemap_heading_type'];

        $html = '<div class="toc_sitemap">';
        if (!$no_label) $html .= '<h' . $heading . ' class="toc_sitemap_pages">' . $label . '</h' . $heading . '>';
        $html .=
            '<ul class="toc_sitemap_pages_list">' .
            wp_list_pages(array('title_li' => '', 'echo' => false, 'exclude' => $exclude, 'exclude_tree' => $exclude_tree)) .
            '</ul>' .
            '</div>';

        return $html;
    }


    function shortcode_sitemap_categories($atts)
    {
        /**
         * @var $heading
         * @var $label
         * @var $no_label
         * @var $exclude
         * @var $exclude_tree
         */
        extract(shortcode_atts(array(
                'heading' => $this->options['sitemap_heading_type'],
                'label' => htmlentities($this->options['sitemap_categories'], ENT_COMPAT, 'UTF-8'),
                'no_label' => false,
                'exclude' => '',
                'exclude_tree' => ''
            ), $atts)
        );

        if ($heading < 1 || $heading > 6)        // h1 to h6 are valid
            $heading = $this->options['sitemap_heading_type'];

        $html = '<div class="toc_sitemap">';
        if (!$no_label) $html .= '<h' . $heading . ' class="toc_sitemap_categories">' . $label . '</h' . $heading . '>';
        $html .=
            '<ul class="toc_sitemap_categories_list">' .
            wp_list_categories(array('title_li' => '', 'echo' => false, 'exclude' => $exclude, 'exclude_tree' => $exclude_tree)) .
            '</ul>' .
            '</div>';

        return $html;
    }


    function shortcode_sitemap_posts($atts)
    {
        /**
         * @var $order
         * @var $orderby
         * @var $separate
         */
        extract(shortcode_atts(array(
                'order' => 'ASC',
                'orderby' => 'title',
                'separate' => true
            ), $atts)
        );

        $articles = new WP_Query(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'order' => $order,
            'orderby' => $orderby,
            'posts_per_page' => -1
        ));

        $html = $letter = '';

        $separate = strtolower($separate);
        if ($separate == 'false' || $separate == 'no') $separate = false;

        while ($articles->have_posts()) {
            $articles->the_post();
            $title = strip_tags(get_the_title());

            if ($separate) {
                if ($letter != strtolower($title[0])) {
                    if ($letter) $html .= '</ul></div>';

                    $html .= '<div class="toc_sitemap_posts_section"><p class="toc_sitemap_posts_letter">' . strtolower($title[0]) . '</p><ul class="toc_sitemap_posts_list">';
                    $letter = strtolower($title[0]);
                }
            }

            $html .= '<li><a href="' . get_permalink($articles->post->ID) . '">' . $title . '</a></li>';
        }

        if ($html) {
            if ($separate)
                $html .= '</div>';
            else
                $html = '<div class="toc_sitemap_posts_section"><ul class="toc_sitemap_posts_list">' . $html . '</ul></div>';
        }

        wp_reset_postdata();

        return $html;
    }

}
