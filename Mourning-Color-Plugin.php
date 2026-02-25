<?php
/*
Plugin Name: Grayscale Control (悼念色控制)
Description: 后台可控黑白模式 + 纪念日自动黑白（可开关）
Version: 1.1
Author: HowellAce
*/

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 添加后台设置页
 */
add_action('admin_menu', function() {
    add_options_page(
        '悼念色设置',
        '悼念色',
        'manage_options',
        'grayscale-control',
        'grayscale_control_settings_page'
    );
});

function grayscale_control_settings_page() {
    $mode = get_option('grayscale_control', '0');
    $memorial = get_option('grayscale_memorial_enable', '1');
    ?>
    <div class="wrap">
        <h1>悼念色设置</h1>
        <form method="post" action="options.php">
            <?php settings_fields('grayscale_control_group'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">黑白模式</th>
                    <td>
                        <select name="grayscale_control">
                            <option value="0" <?php selected($mode, '0'); ?>>彩色（默认）</option>
                            <option value="1" <?php selected($mode, '1'); ?>>黑白</option>
                        </select>
                        <p class="description">
                            1 = 全站黑白，0 = 正常彩色
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">悼念日自动黑白</th>
                    <td>
                        <select name="grayscale_memorial_enable">
                            <option value="1" <?php selected($memorial, '1'); ?>>开启（推荐）</option>
                            <option value="0" <?php selected($memorial, '0'); ?>>关闭</option>
                        </select>
                        <p class="description">
                            开启后：9月3日、12月13日自动黑白<br>
                            关闭后：只受手动开关控制
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * 注册设置字段
 */
add_action('admin_init', function() {
    register_setting('grayscale_control_group', 'grayscale_control');
    register_setting('grayscale_control_group', 'grayscale_memorial_enable');
});

/**
 * 黑白核心逻辑
 */
add_action('wp_head', function() {

    // 手动开关：0/1
    $control = get_option('grayscale_control', '0');

    // 悼念日开关：0/1（默认开启）
    $memorial_enable = get_option('grayscale_memorial_enable', '1');

    // 防无意义字符
    if ($control !== '0' && $control !== '1') {
        $control = '0';
    }
    if ($memorial_enable !== '0' && $memorial_enable !== '1') {
        $memorial_enable = '1';
    }

    // 当前日期
    $month = date('n');
    $day   = date('j');

    // 悼念/纪念日
    $is_memorial_day = (
        ($month == 9 && $day == 3) ||   // 抗战胜利纪念日
        ($month == 12 && $day == 13)    // 国家公祭日
    );

    // 最终逻辑：
    // 1. 手动=1 → 强制黑白（最高优先级）
    // 2. 悼念日开关=1 且今天是悼念日 → 黑白
    $enable_grayscale = (
        $control === '1' ||
        ($memorial_enable === '1' && $is_memorial_day)
    );

    if ($enable_grayscale) {
        echo '<style id="grayscale-control">
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