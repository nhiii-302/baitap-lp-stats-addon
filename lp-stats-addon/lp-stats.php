<?php
/**
 * Plugin Name: LearnPress Stats Dashboard
 * Description: Plugin thống kê dữ liệu từ LearnPress (Khóa học, Học viên, Hoàn thành).
 * Version: 1.0
 * Author: Phạm Ngọc Nhi
 */
// Chặn truy cập trực tiếp vào file (Bảo mật cơ bản)
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Hàm tính toán và hiển thị Thống kê (Dùng chung cho cả Admin Dashboard và Shortcode)
function lp_stats_get_data_html() {
    // 1. Kiểm tra xem LearnPress đã được cài đặt và kích hoạt chưa
    if ( ! class_exists( 'LearnPress' ) ) {
        return '<p style="color:red;">Vui lòng cài đặt và kích hoạt LearnPress trước!</p>';
    }
    // Khởi tạo biến toàn cục $wpdb để truy vấn trực tiếp vào Database của WordPress
    global $wpdb;
    // --- TRUY VẤN 1: TỔNG SỐ KHÓA HỌC (Post type là 'lp_course' và đã 'publish') ---
    $total_courses = $wpdb->get_var( "
        SELECT COUNT(ID) 
        FROM {$wpdb->posts} 
        WHERE post_type = 'lp_course' AND post_status = 'publish'
    " );
    // --- TRUY VẤN 2: TỔNG SỐ HỌC VIÊN ĐÃ ĐĂNG KÝ (Đếm tổng số user_id không trùng lặp trong bảng learnpress_user_items có loại là 'lp_course') ---
    $table_user_items = $wpdb->prefix . 'learnpress_user_items';
    $total_students = $wpdb->get_var( "
        SELECT COUNT(DISTINCT user_id) 
        FROM {$table_user_items} 
        WHERE item_type = 'lp_course'
    " );
    // --- TRUY VẤN 3: SỐ KHÓA HỌC ĐÃ HOÀN THÀNH (Trạng thái 'completed' trong bảng learnpress_user_items) ---
    $completed_courses = $wpdb->get_var( "
        SELECT COUNT(user_item_id) 
        FROM {$table_user_items} 
        WHERE item_type = 'lp_course' AND status = 'completed'
    " );
    // Xử lý trường hợp không có dữ liệu (trả về NULL thì ép về 0)
    $total_courses     = $total_courses ? $total_courses : 0;
    $total_students    = $total_students ? $total_students : 0;
    $completed_courses = $completed_courses ? $completed_courses : 0;
    // Tạo giao diện HTML hiển thị
    $html = '
    <div style="background:#f9f9f9; padding:15px; border:1px solid #ccc; border-radius:5px; font-family:sans-serif;">
        <h3 style="margin-top:0;">Thống kê LearnPress</h3>
        <ul style="list-style-type:none; padding-left:0; font-size:16px;">
            <li style="margin-bottom:10px;">📚 <strong>Tổng số khóa học:</strong> ' . esc_html($total_courses) . '</li>
            <li style="margin-bottom:10px;">👨‍🎓 <strong>Tổng số học viên:</strong> ' . esc_html($total_students) . '</li>
            <li>✅ <strong>Khóa học hoàn thành:</strong> ' . esc_html($completed_courses) . '</li>
        </ul>
    </div>
    ';
    return $html;
}
// Đăng ký Shortcode [lp_total_stats]
add_shortcode( 'lp_total_stats', 'lp_stats_get_data_html' );

// --- TẠO DASHBOARD WIDGET CHO ADMIN ---

// Hàm in nội dung ra Widget (tái sử dụng lại hàm HTML đã viết ở trên)
function lp_stats_dashboard_widget_content() {
    echo lp_stats_get_data_html();
}

// Hàm đăng ký Widget vào trang Bảng tin (Dashboard)
function lp_stats_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'lp_stats_widget_id',                 // ID của widget
        'Thống kê LearnPress (Báo cáo)',      // Tiêu đề của widget hiện trên màn hình
        'lp_stats_dashboard_widget_content'   // Hàm xuất nội dung
    );
}
// Móc (Hook) chức năng này vào lúc WordPress tải trang Bảng tin
add_action( 'wp_dashboard_setup', 'lp_stats_add_dashboard_widget' );