<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$msg = '';

if (isset($_GET['update'])) {
    if ($this->save_admin_options())
        $msg = '<div id="message" class="updated fade"><p>' . __('Options saved.', 'table-of-contents-reloaded') . '</p></div>';
    else
        $msg = '<div id="message" class="error fade"><p>' . __('Save failed.', 'table-of-contents-reloaded') . '</p></div>';
}

?>
<div id='toc' class='wrap'>
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2>Table of Contents Reloaded</h2>
    <?php echo $msg; ?>
    <form method="post" action="<?php echo htmlentities('?page=' . $_GET['page'] . '&update'); ?>">
        <?php wp_nonce_field('toc-plus', 'toc-admin-options'); ?>

        <ul id="tabbed-nav">
            <li><a href="#tab1"><?php _e('Main Options', 'table-of-contents-reloaded'); ?></a></li>
            <li><a href="#tab2"><?php _e('Sitemap', 'table-of-contents-reloaded'); ?></a></li>
            <li class="url"><a
                        href="https://wpplugins.net/table-of-contents-reloaded/"><?php _e('Help', 'table-of-contents-reloaded'); ?></a>
            </li>
        </ul>
        <div class="tab_container">
            <div id="tab1" class="tab_content">

                <table class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="position"><?php _e('Position', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td>
                            <select name="position" id="position">
                                <option value="<?php echo TOC_POSITION_BEFORE_FIRST_HEADING; ?>"<?php if (TOC_POSITION_BEFORE_FIRST_HEADING == $this->options['position']) echo ' selected="selected"'; ?>><?php _e('Before first heading (default)', 'table-of-contents-reloaded'); ?></option>
                                <option value="<?php echo TOC_POSITION_AFTER_FIRST_HEADING; ?>"<?php if (TOC_POSITION_AFTER_FIRST_HEADING == $this->options['position']) echo ' selected="selected"'; ?>><?php _e('After first heading', 'table-of-contents-reloaded'); ?></option>
                                <option value="<?php echo TOC_POSITION_TOP; ?>"<?php if (TOC_POSITION_TOP == $this->options['position']) echo ' selected="selected"'; ?>><?php _e('Top', 'table-of-contents-reloaded'); ?></option>
                                <option value="<?php echo TOC_POSITION_BOTTOM; ?>"<?php if (TOC_POSITION_BOTTOM == $this->options['position']) echo ' selected="selected"'; ?>><?php _e('Bottom', 'table-of-contents-reloaded'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="start"><?php _e('Show when', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td>
                            <select name="start" id="start">
                                <?php
                                for ($i = TOC_MIN_START; $i <= TOC_MAX_START; $i++) {
                                    echo '<option value="' . $i . '"';
                                    if ($i == $this->options['start']) echo ' selected="selected"';
                                    echo '>' . $i . '</option>' . "\n";
                                }
                                ?>
                            </select> <?php
                            /* translators: text follows drop down list of numbers */
                            _e('or more headings are present', 'table-of-contents-reloaded'); ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Auto insert for the following content types', 'table-of-contents-reloaded'); ?></th>
                        <td><?php
                            foreach (get_post_types() as $post_type) {
                                // make sure the post type isn't on the exclusion list
                                if (!in_array($post_type, $this->exclude_post_types)) {
                                    echo '<input type="checkbox" value="' . $post_type . '" id="auto_insert_post_types_' . $post_type . '" name="auto_insert_post_types[]"';
                                    if (in_array($post_type, $this->options['auto_insert_post_types'])) echo ' checked="checked"';
                                    echo ' /><label for="auto_insert_post_types_' . $post_type . '"> ' . $post_type . '</label><br />';
                                }
                            }
                            ?>
                    </tr>
                    <tr>
                        <th><label for="show_heading_text"><?php
                                /* translators: this is the title of the table of contents */
                                _e('Heading text', 'table-of-contents-reloaded'); ?></label></th>
                        <td>
                            <input type="checkbox" value="1" id="show_heading_text"
                                   name="show_heading_text"<?php if ($this->options['show_heading_text']) echo ' checked="checked"'; ?> /><label
                                    for="show_heading_text"> <?php _e('Show title on top of the table of contents', 'table-of-contents-reloaded'); ?></label><br/>
                            <div class="more_toc_options<?php if (!$this->options['show_heading_text']) echo ' disabled'; ?>">
                                <input type="text" class="regular-text"
                                       value="<?php echo htmlentities($this->options['heading_text'], ENT_COMPAT, 'UTF-8'); ?>"
                                       id="heading_text" name="heading_text"/>
                                <span class="description"><label
                                            for="heading_text"><?php _e('Eg: Contents, Table of Contents, Page Contents', 'table-of-contents-reloaded'); ?></label></span><br/><br/>

                                <input type="checkbox" value="1" id="visibility"
                                       name="visibility"<?php if ($this->options['visibility']) echo ' checked="checked"'; ?> /><label
                                        for="visibility"> <?php _e('Allow the user to toggle the visibility of the table of contents', 'table-of-contents-reloaded'); ?></label><br/>
                                <div class="more_toc_options<?php if (!$this->options['visibility']) echo ' disabled'; ?>">
                                    <table class="more_toc_options_table">
                                        <tbody>
                                        <tr>
                                            <th>
                                                <label for="visibility_show"><?php _e('Show text', 'table-of-contents-reloaded'); ?></label>
                                            </th>
                                            <td><input type="text" class=""
                                                       value="<?php echo htmlentities($this->options['visibility_show'], ENT_COMPAT, 'UTF-8'); ?>"
                                                       id="visibility_show" name="visibility_show"/>
                                                <span class="description"><label for="visibility_show"><?php
                                                        /* translators: example text to display when you want to expand the table of contents */
                                                        _e('Eg: show', 'table-of-contents-reloaded'); ?></label></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                <label for="visibility_hide"><?php _e('Hide text', 'table-of-contents-reloaded'); ?></label>
                                            </th>
                                            <td><input type="text" class=""
                                                       value="<?php echo htmlentities($this->options['visibility_hide'], ENT_COMPAT, 'UTF-8'); ?>"
                                                       id="visibility_hide" name="visibility_hide"/>
                                                <span class="description"><label for="visibility_hide"><?php
                                                        /* translators: example text to display when you want to collapse the table of contents */
                                                        _e('Eg: hide', 'table-of-contents-reloaded'); ?></label></span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <input type="checkbox" value="1" id="visibility_hide_by_default"
                                           name="visibility_hide_by_default"<?php if ($this->options['visibility_hide_by_default']) echo ' checked="checked"'; ?> /><label
                                            for="visibility_hide_by_default"> <?php _e('Hide the table of contents initially', 'table-of-contents-reloaded'); ?></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="show_heirarchy"><?php _e('Show hierarchy', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td><input type="checkbox" value="1" id="show_heirarchy"
                                   name="show_heirarchy"<?php if ($this->options['show_heirarchy']) echo ' checked="checked"'; ?> />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="ordered_list"><?php _e('Number list items', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td><input type="checkbox" value="1" id="ordered_list"
                                   name="ordered_list"<?php if ($this->options['ordered_list']) echo ' checked="checked"'; ?> />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="smooth_scroll"><?php _e('Enable smooth scroll effect', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td><input type="checkbox" value="1" id="smooth_scroll"
                                   name="smooth_scroll"<?php if ($this->options['smooth_scroll']) echo ' checked="checked"'; ?> /><label
                                    for="smooth_scroll"> <?php _e('Scroll rather than jump to the anchor link', 'table-of-contents-reloaded'); ?></label>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <h3><?php _e('Appearance', 'table-of-contents-reloaded'); ?></h3>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="width"><?php _e('Width', 'table-of-contents-reloaded'); ?></label>
                        </td>
                        <td>
                            <select name="width" id="width">
                                <optgroup label="<?php _e('Fixed width', 'table-of-contents-reloaded'); ?>">
                                    <option value="200px"<?php if ('200px' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        200px
                                    </option>
                                    <option value="225px"<?php if ('225px' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        225px
                                    </option>
                                    <option value="250px"<?php if ('250px' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        250px
                                    </option>
                                    <option value="275px"<?php if ('275px' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        275px
                                    </option>
                                    <option value="300px"<?php if ('300px' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        300px
                                    </option>
                                    <option value="325px"<?php if ('325px' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        325px
                                    </option>
                                    <option value="350px"<?php if ('350px' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        350px
                                    </option>
                                    <option value="375px"<?php if ('375px' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        375px
                                    </option>
                                    <option value="400px"<?php if ('400px' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        400px
                                    </option>
                                </optgroup>
                                <optgroup label="<?php _e('Relative', 'table-of-contents-reloaded'); ?>">
                                    <option value="Auto"<?php if ('Auto' == $this->options['width']) echo ' selected="selected"'; ?>><?php _e('Auto (default)', 'table-of-contents-reloaded'); ?></option>
                                    <option value="25%"<?php if ('25%' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        25%
                                    </option>
                                    <option value="33%"<?php if ('33%' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        33%
                                    </option>
                                    <option value="50%"<?php if ('50%' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        50%
                                    </option>
                                    <option value="66%"<?php if ('66%' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        66%
                                    </option>
                                    <option value="75%"<?php if ('75%' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        75%
                                    </option>
                                    <option value="100%"<?php if ('100%' == $this->options['width']) echo ' selected="selected"'; ?>>
                                        100%
                                    </option>
                                </optgroup>
                                <optgroup label="<?php
                                /* translators: other width */
                                _e('Other', 'table-of-contents-reloaded'); ?>">
                                    <option value="User defined"<?php if ('User defined' == $this->options['width']) echo ' selected="selected"'; ?>><?php _e('User defined', 'table-of-contents-reloaded'); ?></option>
                                </optgroup>
                            </select>
                            <div class="more_toc_options<?php if ('User defined' != $this->options['width']) echo ' disabled'; ?>">
                                <label for="width_custom"><?php
                                    /* translators: ignore %s as it's some HTML label tags */
                                    printf(__('Please enter a number and %s select its units, eg: 100px, 10em', 'table-of-contents-reloaded'), '</label><label for="width_custom_units">'); ?></label><br/>
                                <input type="text" class="regular-text"
                                       value="<?php echo floatval($this->options['width_custom']); ?>"
                                       id="width_custom" name="width_custom"/>
                                <select name="width_custom_units" id="width_custom_units">
                                    <option value="px"<?php if ('px' == $this->options['width_custom_units']) echo ' selected="selected"'; ?>>
                                        px
                                    </option>
                                    <option value="%"<?php if ('%' == $this->options['width_custom_units']) echo ' selected="selected"'; ?>>
                                        %
                                    </option>
                                    <option value="em"<?php if ('em' == $this->options['width_custom_units']) echo ' selected="selected"'; ?>>
                                        em
                                    </option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wrapping"><?php _e('Wrapping', 'table-of-contents-reloaded'); ?></label>
                        </td>
                        <td>
                            <select name="wrapping" id="wrapping">
                                <option value="<?php echo TOC_WRAPPING_NONE; ?>"<?php if (TOC_WRAPPING_NONE == $this->options['wrapping']) echo ' selected="selected"'; ?>><?php _e('None (default)', 'table-of-contents-reloaded'); ?></option>
                                <option value="<?php echo TOC_WRAPPING_LEFT; ?>"<?php if (TOC_WRAPPING_LEFT == $this->options['wrapping']) echo ' selected="selected"'; ?>><?php _e('Left', 'table-of-contents-reloaded'); ?></option>
                                <option value="<?php echo TOC_WRAPPING_RIGHT; ?>"<?php if (TOC_WRAPPING_RIGHT == $this->options['wrapping']) echo ' selected="selected"'; ?>><?php _e('Right', 'table-of-contents-reloaded'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="font_size"><?php _e('Font size', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td>
                            <input type="text" class="regular-text"
                                   value="<?php echo floatval($this->options['font_size']); ?>"
                                   id="font_size" name="font_size"/>
                            <select name="font_size_units" id="font_size_units">
                                <option value="px"<?php if ('pt' == $this->options['font_size_units']) echo ' selected="selected"'; ?>>
                                    pt
                                </option>
                                <option value="%"<?php if ('%' == $this->options['font_size_units']) echo ' selected="selected"'; ?>>
                                    %
                                </option>
                                <option value="em"<?php if ('em' == $this->options['font_size_units']) echo ' selected="selected"'; ?>>
                                    em
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php
                            /* translators: appearance / colour / look and feel options */
                            _e('Presentation', 'table-of-contents-reloaded'); ?></th>
                        <td>
                            <div class="toc_theme_option">
                                <input type="radio" name="theme" id="theme_<?php echo TOC_THEME_GREY; ?>"
                                       value="<?php echo TOC_THEME_GREY; ?>"<?php if ($this->options['theme'] == TOC_THEME_GREY) echo ' checked="checked"'; ?> /><label
                                        for="theme_<?php echo TOC_THEME_GREY; ?>"> <?php _e('Grey (default)', 'table-of-contents-reloaded'); ?>
                                    <br/>
                                    <img src="<?php echo $this->path; ?>/images/grey.png" alt=""/>
                                </label>
                            </div>
                            <div class="toc_theme_option">
                                <input type="radio" name="theme"
                                       id="theme_<?php echo TOC_THEME_LIGHT_BLUE; ?>"
                                       value="<?php echo TOC_THEME_LIGHT_BLUE; ?>"<?php if ($this->options['theme'] == TOC_THEME_LIGHT_BLUE) echo ' checked="checked"'; ?> /><label
                                        for="theme_<?php echo TOC_THEME_LIGHT_BLUE; ?>"> <?php _e('Light blue', 'table-of-contents-reloaded'); ?>
                                    <br/>
                                    <img src="<?php echo $this->path; ?>/images/blue.png" alt=""/>
                                </label>
                            </div>
                            <div class="toc_theme_option">
                                <input type="radio" name="theme" id="theme_<?php echo TOC_THEME_WHITE; ?>"
                                       value="<?php echo TOC_THEME_WHITE; ?>"<?php if ($this->options['theme'] == TOC_THEME_WHITE) echo ' checked="checked"'; ?> /><label
                                        for="theme_<?php echo TOC_THEME_WHITE; ?>"> <?php _e('White', 'table-of-contents-reloaded'); ?>
                                    <br/>
                                    <img src="<?php echo $this->path; ?>/images/white.png" alt=""/>
                                </label>
                            </div>
                            <div class="toc_theme_option">
                                <input type="radio" name="theme" id="theme_<?php echo TOC_THEME_BLACK; ?>"
                                       value="<?php echo TOC_THEME_BLACK; ?>"<?php if ($this->options['theme'] == TOC_THEME_BLACK) echo ' checked="checked"'; ?> /><label
                                        for="theme_<?php echo TOC_THEME_BLACK; ?>"> <?php _e('Black', 'table-of-contents-reloaded'); ?>
                                    <br/>
                                    <img src="<?php echo $this->path; ?>/images/black.png" alt=""/>
                                </label>
                            </div>
                            <div class="toc_theme_option">
                                <input type="radio" name="theme"
                                       id="theme_<?php echo TOC_THEME_TRANSPARENT; ?>"
                                       value="<?php echo TOC_THEME_TRANSPARENT; ?>"<?php if ($this->options['theme'] == TOC_THEME_TRANSPARENT) echo ' checked="checked"'; ?> /><label
                                        for="theme_<?php echo TOC_THEME_TRANSPARENT; ?>"> <?php _e('Transparent', 'table-of-contents-reloaded'); ?>
                                    <br/>
                                    <img src="<?php echo $this->path; ?>/images/transparent.png" alt=""/>
                                </label>
                            </div>
                            <div class="toc_theme_option">
                                <input type="radio" name="theme" id="theme_<?php echo TOC_THEME_CUSTOM; ?>"
                                       value="<?php echo TOC_THEME_CUSTOM; ?>"<?php if ($this->options['theme'] == TOC_THEME_CUSTOM) echo ' checked="checked"'; ?> /><label
                                        for="theme_<?php echo TOC_THEME_CUSTOM; ?>"> <?php _e('Custom', 'table-of-contents-reloaded'); ?>
                                    <br/>
                                    <img src="<?php echo $this->path; ?>/images/custom.png" alt=""/>
                                </label>
                            </div>
                            <div class="clear"></div>

                            <div class="more_toc_options<?php if (TOC_THEME_CUSTOM != $this->options['theme']) echo ' disabled'; ?>">
                                <table id="theme_custom" class="more_toc_options_table">
                                    <tbody>
                                    <tr>
                                        <th>
                                            <label for="custom_background_colour"><?php _e('Background', 'table-of-contents-reloaded'); ?></label>
                                        </th>
                                        <td><input type="text" class="custom_colour_option"
                                                   value="<?php echo htmlentities($this->options['custom_background_colour']); ?>"
                                                   id="custom_background_colour"
                                                   name="custom_background_colour"/> <img
                                                    src="<?php echo $this->path; ?>/images/colour-wheel.png"
                                                    alt=""/></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="custom_border_colour"><?php _e('Border', 'table-of-contents-reloaded'); ?></label>
                                        </th>
                                        <td><input type="text" class="custom_colour_option"
                                                   value="<?php echo htmlentities($this->options['custom_border_colour']); ?>"
                                                   id="custom_border_colour" name="custom_border_colour"/>
                                            <img src="<?php echo $this->path; ?>/images/colour-wheel.png"
                                                 alt=""/></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="custom_title_colour"><?php _e('Title', 'table-of-contents-reloaded'); ?></label>
                                        </th>
                                        <td><input type="text" class="custom_colour_option"
                                                   value="<?php echo htmlentities($this->options['custom_title_colour']); ?>"
                                                   id="custom_title_colour" name="custom_title_colour"/>
                                            <img src="<?php echo $this->path; ?>/images/colour-wheel.png"
                                                 alt=""/></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="custom_links_colour"><?php _e('Links', 'table-of-contents-reloaded'); ?></label>
                                        </th>
                                        <td><input type="text" class="custom_colour_option"
                                                   value="<?php echo htmlentities($this->options['custom_links_colour']); ?>"
                                                   id="custom_links_colour" name="custom_links_colour"/>
                                            <img src="<?php echo $this->path; ?>/images/colour-wheel.png"
                                                 alt=""/></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="custom_links_hover_colour"><?php _e('Links (hover)', 'table-of-contents-reloaded'); ?></label>
                                        </th>
                                        <td><input type="text" class="custom_colour_option"
                                                   value="<?php echo htmlentities($this->options['custom_links_hover_colour']); ?>"
                                                   id="custom_links_hover_colour"
                                                   name="custom_links_hover_colour"/> <img
                                                    src="<?php echo $this->path; ?>/images/colour-wheel.png"
                                                    alt=""/></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="custom_links_visited_colour"><?php _e('Links (visited)', 'table-of-contents-reloaded'); ?></label>
                                        </th>
                                        <td><input type="text" class="custom_colour_option"
                                                   value="<?php echo htmlentities($this->options['custom_links_visited_colour']); ?>"
                                                   id="custom_links_visited_colour"
                                                   name="custom_links_visited_colour"/> <img
                                                    src="<?php echo $this->path; ?>/images/colour-wheel.png"
                                                    alt=""/></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div id="farbtastic_colour_wheel"></div>
                                <div class="clear"></div>
                                <p><?php printf(__("Leaving the value as %s will inherit your theme's styles", 'table-of-contents-reloaded'), '<code>#</code>'); ?></p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <h3><?php _e('Advanced', 'table-of-contents-reloaded'); ?> <span class="show_hide">(<a
                                href="#toc_advanced_usage"><?php _e('show', 'table-of-contents-reloaded'); ?></a>)</span>
                </h3>
                <div id="toc_advanced_usage">
                    <h4><?php _e('Power options', 'table-of-contents-reloaded'); ?></h4>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th>
                                <label for="lowercase"><?php _e('Lowercase', 'table-of-contents-reloaded'); ?></label>
                            </th>
                            <td><input type="checkbox" value="1" id="lowercase"
                                       name="lowercase"<?php if ($this->options['lowercase']) echo ' checked="checked"'; ?> /><label
                                        for="lowercase"> <?php _e('Ensure anchors are in lowercase', 'table-of-contents-reloaded'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="hyphenate"><?php _e('Hyphenate', 'table-of-contents-reloaded'); ?></label>
                            </th>
                            <td><input type="checkbox" value="1" id="hyphenate"
                                       name="hyphenate"<?php if ($this->options['hyphenate']) echo ' checked="checked"'; ?> /><label
                                        for="hyphenate"> <?php _e('Use - rather than _ in anchors', 'table-of-contents-reloaded'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="include_homepage"><?php _e('Include homepage', 'table-of-contents-reloaded'); ?></label>
                            </th>
                            <td><input type="checkbox" value="1" id="include_homepage"
                                       name="include_homepage"<?php if ($this->options['include_homepage']) echo ' checked="checked"'; ?> /><label
                                        for="include_homepage"> <?php _e('Show the table of contents for qualifying items on the homepage', 'table-of-contents-reloaded'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="exclude_css"><?php _e('Exclude CSS file', 'table-of-contents-reloaded'); ?></label>
                            </th>
                            <td><input type="checkbox" value="1" id="exclude_css"
                                       name="exclude_css"<?php if ($this->options['exclude_css']) echo ' checked="checked"'; ?> /><label
                                        for="exclude_css"> <?php _e("Prevent the loading of this plugin's CSS styles. When selected, the appearance options from above will also be ignored.", 'table-of-contents-reloaded'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="bullet_spacing"><?php _e('Preserve theme bullets', 'table-of-contents-reloaded'); ?></label>
                            </th>
                            <td><input type="checkbox" value="1" id="bullet_spacing"
                                       name="bullet_spacing"<?php if ($this->options['bullet_spacing']) echo ' checked="checked"'; ?> /><label
                                        for="bullet_spacing"> <?php _e('If your theme includes background images for unordered list elements, enable this to support them', 'table-of-contents-reloaded'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Heading levels', 'table-of-contents-reloaded'); ?></th>
                            <td>
                                <p><?php _e('Include the following heading levels. Deselecting a heading will exclude it.', 'table-of-contents-reloaded'); ?></p>
                                <?php
                                // show heading 1 to 6 options
                                for ($i = 1; $i <= 6; $i++) {
                                    echo '<input type="checkbox" value="' . $i . '" id="heading_levels' . $i . '" name="heading_levels[]"';
                                    if (in_array($i, $this->options['heading_levels'])) echo ' checked="checked"';
                                    echo ' /><label for="heading_levels' . $i . '"> ' . __('heading ') . $i . ' - h' . $i . '</label><br />';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="exclude"><?php _e('Exclude headings', 'table-of-contents-reloaded'); ?></label>
                            </th>
                            <td>
                                <input type="text" class="regular-text"
                                       value="<?php echo htmlentities($this->options['exclude'], ENT_COMPAT, 'UTF-8'); ?>"
                                       id="exclude" name="exclude" style="width: 100%;"/><br/>
                                <label for="exclude"><?php _e('Specify headings to be excluded from appearing in the table of contents.  Separate multiple headings with a pipe <code>|</code>.  Use an asterisk <code>*</code> as a wildcard to match other text.  Note that this is not case sensitive. Some examples:', 'table-of-contents-reloaded'); ?></label><br/>
                                <ul>
                                    <li><?php _e('<code>Fruit*</code> ignore headings starting with "Fruit"', 'table-of-contents-reloaded'); ?></li>
                                    <li><?php _e('<code>*Fruit Diet*</code> ignore headings with "Fruit Diet" somewhere in the heading', 'table-of-contents-reloaded'); ?></li>
                                    <li><?php _e('<code>Apple Tree|Oranges|Yellow Bananas</code> ignore headings that are exactly "Apple Tree", "Oranges" or "Yellow Bananas"', 'table-of-contents-reloaded'); ?></li>
                                </ul>
                            </td>
                        </tr>
                        <tr id="smooth_scroll_offset_tr"
                            class="<?php if (!$this->options['smooth_scroll']) echo 'disabled'; ?>">
                            <th>
                                <label for="smooth_scroll_offset"><?php _e('Smooth scroll top offset', 'table-of-contents-reloaded'); ?></label>
                            </th>
                            <td>
                                <input type="text" class="regular-text"
                                       value="<?php echo intval($this->options['smooth_scroll_offset']); ?>"
                                       id="smooth_scroll_offset" name="smooth_scroll_offset"/> px<br/>
                                <label for="smooth_scroll_offset"><?php _e('If you have a consistent menu across the top of your site, you can adjust the top offset to stop the headings from appearing underneath the top menu. A setting of 30 accommodates the WordPress admin bar. This setting appears after you have enabled smooth scrolling from above.', 'table-of-contents-reloaded'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="restrict_path"><?php _e('Restrict path', 'table-of-contents-reloaded'); ?></label>
                            </th>
                            <td>
                                <input type="text" class="regular-text"
                                       value="<?php echo htmlentities($this->options['restrict_path'], ENT_COMPAT, 'UTF-8'); ?>"
                                       id="restrict_path" name="restrict_path"/><br/>
                                <label for="restrict_path"><?php _e('Restrict generation of the table of contents to pages that match the required path. This path is from the root of your site and always begins with a forward slash.', 'table-of-contents-reloaded'); ?>
                                    <br/>
                                    <span class="description"><?php
                                        /* translators: example URL path restriction */
                                        _e('Eg: /wiki/, /corporate/annual-reports/', 'table-of-contents-reloaded'); ?></span></label>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="fragment_prefix"><?php _e('Default anchor prefix', 'table-of-contents-reloaded'); ?></label>
                            </th>
                            <td>
                                <input type="text" class="regular-text"
                                       value="<?php echo htmlentities($this->options['fragment_prefix'], ENT_COMPAT, 'UTF-8'); ?>"
                                       id="fragment_prefix" name="fragment_prefix"/><br/>
                                <label for="fragment_prefix"><?php _e('Anchor targets are restricted to alphanumeric characters as per HTML specification (see readme for more detail). The default anchor prefix will be used when no characters qualify. When left blank, a number will be used instead.', 'table-of-contents-reloaded'); ?>
                                    <br/>
                                    <?php _e('This option normally applies to content written in character sets other than ASCII.', 'table-of-contents-reloaded'); ?>
                                    <br/>
                                    <span class="description"><?php
                                        /* translators: example anchor prefixes when no ascii characters match */
                                        _e('Eg: i, toc_index, index, _', 'table-of-contents-reloaded'); ?></span></label>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <h4><?php
                        /* translators: advanced usage */
                        _e('Usage', 'table-of-contents-reloaded'); ?></h4>
                    <p><?php printf(__('If you would like to fully customise the position of the table of contents, you can use the %s shortcode by placing it at the desired position of your post, page or custom post type. This method allows you to generate the table of contents despite having auto insertion disabled for its content type. Please visit the help tab for further information about this shortcode.', 'table-of-contents-reloaded'), '<code>[toc]</code>'); ?></p>
                </div>


            </div>
            <div id="tab2" class="tab_content">


                <p><?php printf(__('At its simplest, placing %s into a page will automatically create a sitemap of all pages and categories. This also works in a text widget.', 'table-of-contents-reloaded'), '<code>[sitemap]</code>'); ?></p>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th>
                            <label for="sitemap_show_page_listing"><?php _e('Show page listing', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td><input type="checkbox" value="1" id="sitemap_show_page_listing"
                                   name="sitemap_show_page_listing"<?php if ($this->options['sitemap_show_page_listing']) echo ' checked="checked"'; ?> />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="sitemap_show_category_listing"><?php _e('Show category listing', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td><input type="checkbox" value="1" id="sitemap_show_category_listing"
                                   name="sitemap_show_category_listing"<?php if ($this->options['sitemap_show_category_listing']) echo ' checked="checked"'; ?> />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="sitemap_heading_type"><?php _e('Heading type', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td><label for="sitemap_heading_type"><?php
                                /* translators: the full line is supposed to read - Use [1-6 drop down list] to print out the titles */
                                _e('Use', 'table-of-contents-reloaded'); ?> h</label><select
                                    name="sitemap_heading_type" id="sitemap_heading_type">
                                <?php
                                // h1 to h6
                                for ($i = 1; $i <= 6; $i++) {
                                    echo '<option value="' . $i . '"';
                                    if ($i == $this->options['sitemap_heading_type']) echo ' selected="selected"';
                                    echo '>' . $i . '</option>' . "\n";
                                }
                                ?>
                            </select> <?php
                            /* translators: the full line is supposed to read - Use [h1-h6 drop down list] to print out the titles */
                            _e('to print out the titles', 'table-of-contents-reloaded'); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="sitemap_pages"><?php _e('Pages label', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td><input type="text" class="regular-text"
                                   value="<?php echo htmlentities($this->options['sitemap_pages'], ENT_COMPAT, 'UTF-8'); ?>"
                                   id="sitemap_pages" name="sitemap_pages"/>
                            <span class="description"><?php _e('Eg: Pages, Page List', 'table-of-contents-reloaded'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="sitemap_categories"><?php _e('Categories label', 'table-of-contents-reloaded'); ?></label>
                        </th>
                        <td><input type="text" class="regular-text"
                                   value="<?php echo htmlentities($this->options['sitemap_categories'], ENT_COMPAT, 'UTF-8'); ?>"
                                   id="sitemap_categories" name="sitemap_categories"/>
                            <span class="description"><?php _e('Eg: Categories, Category List', 'table-of-contents-reloaded'); ?></span>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <h3><?php _e('Advanced usage', 'table-of-contents-reloaded'); ?> <span class="show_hide">(<a
                                href="#sitemap_advanced_usage"><?php _e('show', 'table-of-contents-reloaded'); ?></a>)</span>
                </h3>
                <div id="sitemap_advanced_usage">
                    <p>
                        <code>[sitemap_pages]</code> <?php printf(__('lets you print out a listing of only pages. Similarly %s can be used to print out a category listing. They both can accept a number of attributes so visit the help tab for more information.', 'table-of-contents-reloaded'), '<code>[sitemap_categories]</code>'); ?>
                    </p>
                    <p><?php _e('Examples', 'table-of-contents-reloaded'); ?></p>
                    <ol>
                        <li><code>[sitemap_categories
                                no_label="true"]</code> <?php _e('hides the heading from a category listing', 'table-of-contents-reloaded'); ?>
                        </li>
                        <li><code>[sitemap_pages heading="6" label="This is an awesome listing"
                                exclude="1,15"]</code> <?php printf(__('Uses h6 to display %s on a page listing excluding pages with IDs 1 and 15', 'table-of-contents-reloaded'), '<em>This is an awesome listing</em>'); ?>
                        </li>
                    </ol>
                </div>


            </div>
        </div>


        <p class="submit"><input type="submit" name="submit" class="button-primary"
                                 value="<?php _e('Update Options', 'table-of-contents-reloaded'); ?>"/></p>
    </form>
</div>
