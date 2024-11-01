<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if (!class_exists('toc_widget')) :
    class toc_widget extends WP_Widget
    {

        function __construct()
        {
            $widget_options = array(
                'classname' => 'toc_widget',
                'description' => __('Display the table of contents in the sidebar with this widget', 'table-of-contents-reloaded')
            );
            $control_options = array(
                'width' => 250,
                'height' => 350,
                'id_base' => 'toc-widget'
            );
            parent::__construct('toc-widget', 'TOC+', $widget_options, $control_options);
        }


        /**
         * Widget output to the public
         */
        function widget($args, $instance)
        {
            global $tic, $wp_query;
            $items = $custom_toc_position = '';
            $find = $replace = array();

            $toc_options = $tic->get_options();
            $post = get_post($wp_query->post->ID);
            $custom_toc_position = strpos($post->post_content, '[toc]');    // at this point, shortcodes haven't run yet so we can't search for <!--TOC-->

            if ($tic->is_eligible($custom_toc_position)) {

                extract($args);

                $items = $tic->extract_headings($find, $replace, wptexturize($post->post_content));
                $title = (array_key_exists('title', $instance)) ? apply_filters('widget_title', $instance['title']) : '';
                if (strpos($title, '%PAGE_TITLE%') !== false) $title = str_replace('%PAGE_TITLE%', get_the_title(), $title);
                if (strpos($title, '%PAGE_NAME%') !== false) $title = str_replace('%PAGE_NAME%', get_the_title(), $title);
                $hide_inline = $toc_options['show_toc_in_widget_only'];

                $css_classes = '';
                // bullets?
                if ($toc_options['bullet_spacing'])
                    $css_classes .= ' have_bullets';
                else
                    $css_classes .= ' no_bullets';

                if ($items) {
                    // before widget (defined by themes)
                    echo $before_widget;

                    // display the widget title if one was input (before and after titles defined by themes)
                    if ($title) echo $before_title . $title . $after_title;

                    // display the list
                    echo '<ul class="toc_widget_list' . $css_classes . '">' . $items . '</ul>';

                    // after widget (defined by themes)
                    echo $after_widget;
                }
            }
        }


        /**
         * Update the widget settings
         */
        function update($new_instance, $old_instance)
        {
            global $tic;

            $instance = $old_instance;

            // strip tags for title to remove HTML (important for text inputs)
            $instance['title'] = strip_tags(trim($new_instance['title']));

            // no need to strip tags for the following
            //$instance['hide_inline'] = $new_instance['hide_inline'];
            $tic->set_show_toc_in_widget_only($new_instance['hide_inline']);
            $tic->set_show_toc_in_widget_only_post_types((array)$new_instance['show_toc_in_widget_only_post_types']);

            return $instance;
        }


        /**
         * Displays the widget settings on the widget panel.
         */
        function form($instance)
        {
            global $tic;
            $toc_options = $tic->get_options();

            $defaults = array(
                'title' => $toc_options['heading_text']
            );
            $instance = wp_parse_args((array)$instance, $defaults);

            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'table-of-contents-reloaded'); ?>
                    :</label>
                <input type="text" id="<?php echo $this->get_field_id('title'); ?>"
                       name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>"
                       style="width:100%;"/>
            </p>

            <p>
                <input class="checkbox" type="checkbox" <?php checked($toc_options['show_toc_in_widget_only'], 1); ?>
                       id="<?php echo $this->get_field_id('hide_inline'); ?>"
                       name="<?php echo $this->get_field_name('hide_inline'); ?>" value="1"/>
                <label for="<?php echo $this->get_field_id('hide_inline'); ?>"> <?php _e('Show the table of contents only in the sidebar', 'table-of-contents-reloaded'); ?></label>
            </p>

            <div class="show_toc_in_widget_only_post_types"
                 style="margin: 0 0 25px 25px; display: <?php echo ($toc_options['show_toc_in_widget_only'] == 1) ? 'block' : 'none'; ?>;">
                <p><?php _e('For the following content types:', 'table-of-contents-reloaded'); ?></p>

                <?php
                foreach (get_post_types() as $post_type) {
                    // make sure the post type isn't on the exclusion list
                    if (!in_array($post_type, $tic->get_exclude_post_types())) {
                        echo '<input type="checkbox" value="' . $post_type . '" id="' . $this->get_field_id('show_toc_in_widget_only_post_types_' . $post_type) . '" name="' . $this->get_field_name("show_toc_in_widget_only_post_types") . '[]"';
                        if (in_array($post_type, $toc_options['show_toc_in_widget_only_post_types'])) echo ' checked="checked"';
                        echo ' /><label for="' . $this->get_field_id('show_toc_in_widget_only_post_types_' . $post_type) . '"> ' . $post_type . '</label><br />';
                    }
                }

                ?></div>

            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#<?php echo $this->get_field_id('hide_inline'); ?>').click(function () {
                        $(this).parent().siblings('div.show_toc_in_widget_only_post_types').toggle('fast');
                    });
                });
            </script>
            <?php
        }

    } // end class
endif;