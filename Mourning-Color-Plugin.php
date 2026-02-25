<?php
/*
Plugin Name: Mourning-Color-Plugin
Description: 悼念色控制，可自行添加自动悼念日期
Version: 1.2
Author: Howell_Ace
*/

if (!defined('ABSPATH')) exit;

/* 默认日期 */
register_activation_hook(__FILE__, function() {
    if (get_option('mourning_dates') === false) {
        update_option('mourning_dates', "09-03\n12-13");
    }
});

/* 悼念色控制 */
add_action('admin_menu', function() {
    add_options_page(
        '悼念色控制',
        '悼念色控制',
        'manage_options',
        'mourning-color-plugin',
        'mourning_color_settings_page'
    );
});

/* 注册设置项 */
add_action('admin_init', function() {
    register_setting('mourning_color_group', 'mourning_manual');
    register_setting('mourning_color_group', 'mourning_auto_enable');
    register_setting('mourning_color_group', 'mourning_dates');
});

function mourning_color_settings_page() {
    $manual = get_option('mourning_manual', '0');
    $auto   = get_option('mourning_auto_enable', '1');
    $dates  = get_option('mourning_dates', "09-03\n12-13");
    ?>
    <div class="wrap">
        <h1>悼念色控制</h1>
        <form method="post" action="options.php">
            <?php settings_fields('mourning_color_group'); ?>

            <table class="form-table">
                <tr>
                    <th>手动悼念</th>
                    <td>
                        <select name="mourning_manual">
                            <option value="0" <?php selected($manual,'0'); ?>>关闭</option>
                            <option value="1" <?php selected($manual,'1'); ?>>开启</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>自动进行悼念</th>
                    <td>
                        <select name="mourning_auto_enable">
                            <option value="1" <?php selected($auto,'1'); ?>>开启</option>
                            <option value="0" <?php selected($auto,'0'); ?>>关闭</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>自动悼念日期</th>
                    <td>
                        <textarea name="mourning_dates" rows="6" cols="30"><?php echo esc_textarea($dates); ?></textarea>
                        <p class="description">
                            每行一个日期，格式：MM-DD<br>
                            示例：09-03 或 12-13<br>
                            可删除默认日期或自行添加
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action('wp_head', function() {

    $manual = get_option('mourning_manual', '0');
    $auto   = get_option('mourning_auto_enable', '1');
    $dates  = get_option('mourning_dates', '');

    if ($manual !== '1') $manual = '0';
    if ($auto !== '1') $auto = '0';

    $today = date('m-d');

    $date_list = array_filter(array_map('trim', explode("\n", $dates)));

    $is_mourning_day = in_array($today, $date_list, true);

    /* 逻辑：手动优先，其次自动悼念日 */
    $enable = ($manual === '1') || ($auto === '1' && $is_mourning_day);

    if ($enable) {
        echo '<style id="mourning-color-style">
        html {
            transition: filter 0.4s ease;
            filter: grayscale(100%) !important;
            -webkit-filter: grayscale(100%) !important;
            -moz-filter: grayscale(100%) !important;
            -ms-filter: grayscale(100%) !important;
            -o-filter: grayscale(100%) !important;
            filter: progid:DXImageTransform.Microsoft.BasicImage(grayscale=1);
        }
        </style>';
    }
});