<?php

function gt_get_post_view()
{
    $count = get_post_meta( get_the_ID(), 'post_views_count', true );
    
    if ($count <= 0) {
        return '1 views';
    }
    
    if ($count < 1000) {
        return "$count views";
    }
    
    if ($count < 1000000) {
        // Anything less than a million
        $count = number_format($count / 1000, 1).'K';
    } else if ($count < 1000000000) {
        // Anything less than a billion
        $count = number_format($count / 1000000, 1).'M';
    } else {
        // At least a billion
        $count = number_format($count / 1000000000, 1).'B';
    }
    
    return "$count views";
}

function gt_set_post_view()
{
    $key = 'post_views_count';
    
    $post_id = get_the_ID();
    
    $count = (int) get_post_meta( $post_id, $key, true );
    
    $count++;
    
    update_post_meta( $post_id, $key, $count );
}

function gt_posts_column_views( $columns )
{
    $columns['post_views'] = 'Views';
    
    return $columns;
}


function gt_posts_custom_column_views( $column )
{
    if ( $column === 'post_views') {
        echo gt_get_post_view();
    }
}

function dailyporns_child_scripts()
{
    wp_enqueue_style( 'dailyporns-child-mainstyle', get_stylesheet_directory_uri().'/style.css');
    
    wp_enqueue_script( 'dailyporns-child-mainjs', get_stylesheet_directory_uri().'/assets/js/app.js', [], true);
    
    wp_enqueue_script( 'dailyporns-child-jquery-mobile-customize-js', get_stylesheet_directory_uri().'/assets/js/jquery.mobile.custom.min.js', [], true);
}

function setAWSCookie()
{
    $cmdGettingCookieOfAWS = PHP_BIN_PATH.' '.SERVICE_ROOT_PATH.DIRECTORY_SEPARATOR.'artisan make:sign';
    
    $signCookie = exec($cmdGettingCookieOfAWS);
    
    $signCookie = json_decode($signCookie, true);
    
    foreach ($signCookie as $signCookieKey => $signCookieVal) {
        $urlparts = parse_url(site_url());
        $domain = $urlparts['host'];
        
        if (!isset($_COOKIE[$signCookieKey]) || !$_COOKIE[$signCookieKey]) {
            setcookie($signCookieKey, $signCookieVal, time() + 3600*3*24, '/');
        }
    }
}

add_action( 'wp_enqueue_scripts', 'dailyporns_child_scripts', 11);
add_filter( 'manage_posts_columns', 'gt_posts_column_views' );
add_action( 'manage_posts_custom_column', 'gt_posts_custom_column_views' );
add_action('init', 'setAWSCookie');
?>