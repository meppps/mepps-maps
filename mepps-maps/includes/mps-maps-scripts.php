<?php
    // Add scripts
    function mpsmaps_add_scripts(){
        // Add Main CSS
        wp_enqueue_style('yts-main-style', plugins_url(). '/mepps-maps/css/style.css');
        // Add Main JS

    }

    // Load scripts
    add_action('wp_enqueue_scripts', 'mpsmaps_add_scripts');

