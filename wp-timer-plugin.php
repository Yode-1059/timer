<?php
/**
 * Plugin Name:リマインダー
 * Description: Adds a timer to the admin toolbar and the post editor.
 * version:1.111
 */

function timer_enqueue_scripts()
{
    $user_data = wp_get_current_user();
    $user_login = $user_data->user_login;
    wp_enqueue_style('timer-css', plugin_dir_url(__FILE__) . 'timer.css');
    wp_enqueue_script('timer-js', plugin_dir_url(__FILE__) . 'timer.js');

    wp_localize_script(
        'timer-js',
        'userData',
        array(
            'user_login' => $user_login
        )
    );
}
add_action('admin_enqueue_scripts', 'timer_enqueue_scripts');
add_action('wp_enqueue_scripts', 'timer_enqueue_scripts');

function timer_admin_bar($wp_admin_bar)
{
    $user_data = wp_get_current_user();
    $user_login = $user_data->user_login;
    // リマインド情報を取得
    $reminder = get_option($user_login . '_dashboard_reminder', '');
    $timer = get_option($user_login . '_dashboard_timer', '');
    $response = wp_remote_get("https://api.capy.lol/v1/fact"); // 指定したURLのデータを取得
    $json = wp_remote_retrieve_body($response);
    $data = json_decode($json);

    $wp_admin_bar->add_node(
        array(
            'id' => 'custom_timer_menu',
            'title' => '<a target="_new" href="https://capy.lol/"><img id="remind_img" src="https://api.capy.lol/v1/capybara"></a>' . $data->data->fact . '<p id="remind_info">現在の時刻<span id="custom_timer">00:00</span> - ' . $reminder . 'が<span id="remind_time">' . $timer . "</span>にあります</p>",
            'meta' => array('class' => 'timer_menu')
        )
    );
}

add_action('admin_bar_menu', 'timer_admin_bar', 100);

add_action('wp_ajax_update_timer_options', 'update_timer_options_callback');

function update_timer_options_callback()
{
    $user_data = wp_get_current_user();
    $user_login = $user_data->user_login;
    check_ajax_referer($user_login . '_dashboard_reminder', 'nonce');

    $reminder = sanitize_text_field($_POST['reminder']);
    $time = sanitize_time($_POST['time']);

    $user_data = wp_get_current_user();
    $user_login = $user_data->user_login;
    update_option($user_login . '_dashboard_reminder', $reminder);
    update_option($user_login . '_dashboard_timer', $time);

    wp_send_json_success();
}

function timer_editor()
{
    echo '<div id="editor_timer">Time spent: <span id="custom_timer">00:00</span></div>';
}
add_action('edit_form_top', 'timer_editor');

function add_my_timer_dashboard_widget()
{
    wp_add_dashboard_widget(
        'widget_timer',
        // Widget ID
        'タイマー',
        // Widget Title
        'my_dashboard_widget_callback_timer' // Callback function
    );
}
add_action('wp_dashboard_setup', 'add_my_timer_dashboard_widget');
function sanitize_time($time)
{
    // HH:MM または HH:MM:SS の形式に合致するかチェック
    if (preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9]))?$/', $time)) {
        return $time;
    }
    return false; // もしくはデフォルトの時間を返すなどの処理を行う
}
function my_dashboard_widget_callback_timer()
{
    $user_data = wp_get_current_user();
    $user_login = $user_data->user_login;
    // リマインド情報を取得
    $reminder = get_option($user_login . '_dashboard_reminder');
    $time = get_option($user_login . '_dashboard_timer');

    // リマインド情報の更新
    if (isset($_POST[$user_login . '_dashboard_reminder_nonce']) && wp_verify_nonce($_POST[$user_login . '_dashboard_reminder_nonce'], $user_login . '_dashboard_reminder')) {
        $reminder = esc_textarea(sanitize_text_field($_POST[$user_login . '_dashboard_reminder']));
        $time = sanitize_text_field($_POST[$user_login . '_dashboard_timer']); // タイムのサニタイズを追加
        if (update_option($user_login . '_dashboard_reminder', $reminder) && update_option($user_login . '_dashboard_timer', $time)) {
            echo 'Options updated successfully';
        } else {
            echo 'Options update failed';
        }
    }

    // フォームの表示
    echo '<form method="post" action="" id="dashbord_reminder">';
    wp_nonce_field($user_login . '_dashboard_reminder', $user_login . '_dashboard_reminder_nonce');
    echo '<p><label for="' . $user_login . '_dashboard_reminder">予定書き込み</label></p>';
    echo '<p><textarea name="' . $user_login . '_dashboard_reminder" id="' . $user_login . '_dashboard_reminder" rows="5" cols="50">' . esc_textarea($reminder) . '</textarea></p>';
    echo '<p><label for="' . $user_login . '_dashboard_timer">時間</label></p>'; // タイムのラベルを追加
    echo '<p><input type="time" name="' . $user_login . '_dashboard_timer" id="' . $user_login . '_dashboard_timer" value="' . $time . '"></p>'; // タイムの属性を追加
    echo '<p><input type="submit" value="確定" class="button-primary"></p>';
    echo '</form>';
}