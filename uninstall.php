<?php

// アンインストールでない場合は処理終了
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// オプションを削除する。オプションが複数なら複数記述する
$user_data = wp_get_current_user();
$user_login = $user_data->user_login;
$option_name = $user_login . '_dashboard_reminder';
delete_option($option_name);

$option_name = $user_login . '_dashboard_timer';
delete_option($option_name);