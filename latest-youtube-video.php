<?php
/**
* Plugin Name: Latest YouTube Video
* Plugin URI: https://www.derekcodes.io
* Description: Get the latest YouTube Video for any Channel using a WordPress shortcode.
* Version: 1.1
* Author: Derek Codes
* Author URI: https://www.derekcodes.io/about
**/

/**
 * JSON request - return video data
 */
add_action( 'wp_ajax_nopriv_dc_ajax_action', 'dc_ajaxAction' );
add_action( 'wp_ajax_dc_ajax_action', 'dc_ajaxAction' );
function dc_ajaxAction() {
    $atts = shortcode_atts(
        [
            'channel'   => 'UCUE50FtRT_m6tEiAxpqVUkg',
        ],
        $atts,
        'latest_youtube_video'
    );

    $url = 'https://www.youtube.com/feeds/videos.xml?channel_id='.$atts['channel'];
    if ($response = wp_remote_get($url)) {
        if ($xml = simplexml_load_string($response['body'])) {
            if ($xml->entry) {
                foreach ($xml->entry as $entry) {
                    $video_url  = (string)$entry->link['href'];
                    $video_code = substr($video_url, strpos($video_url, '=')+1);

                    wp_send_json_success([
                        'video_code' => esc_attr($video_code)
                    ]);exit;

                    break;
                }
            }
        }
    }

    wp_send_json_error();
}


/**
 * @param $attr = [
 *      'channel',
 *      'width',
 *      'height'
 * ]
 */
function dc_getLatestVideo($atts) {
    $atts = shortcode_atts(
        [
            'channel'   => 'UCUE50FtRT_m6tEiAxpqVUkg',
            'width'     => 560,
            'height'    => 315,
            'ajax'      => 0,
        ],
        $atts,
        'latest_youtube_video'
    );

    if ($atts['ajax'] == 0) {
        $url = 'https://www.youtube.com/feeds/videos.xml?channel_id='.$atts['channel'];
        if ($response = wp_remote_get($url)) {
            if ($xml = simplexml_load_string($response['body'])) {
                if ($xml->entry) {
                    foreach ($xml->entry as $entry) {
                        $video_url  = (string)$entry->link['href'];
                        $video_code = substr($video_url, strpos($video_url, '=')+1);
                        return '<div id="dc_video_container" style="width: '.esc_attr($atts['width']).'px; height: '.esc_attr($atts['height']).'px;"><iframe title="Youtube video" class="widget__iframe" width="'.esc_attr($atts['width']).'" height="'.esc_attr($atts['height']).'" src="https://www.youtube.com/embed/'.esc_attr($video_code).'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';

                        break;
                    }
                }
            }
        }
    } else {
        return "
        <div id=\"dc_video_container\" style=\"width: ".esc_attr($atts['width'])."px; height: ".esc_attr($atts['height'])."px;\">
        </div>
        <script type=\"text/javascript\">
            document.addEventListener('DOMContentLoaded', () => {
                var data = 'action=dc_ajax_action';
                var request = new XMLHttpRequest();
                request.open('POST', 'wp-admin/admin-ajax.php', true);
                request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                request.send(data);
                request.onload = function() {
                    var json = JSON.parse(this.responseText);
                    if (json.success) {
                        let container = document.getElementById('dc_video_container');
                        const iframe = document.createElement('iframe');
                        iframe.src = 'https://www.youtube.com/embed/' + json.data.video_code;
                        iframe.title = 'YouTube Video';
                        iframe.width = ".esc_attr($atts['width']).";
                        iframe.height = ".esc_attr($atts['height']).";
                        iframe.style = 'border: 0;';
                        container.appendChild(iframe);
                    }
                };
            });
        </script>";
    }
}
add_shortcode('latest_youtube_video', 'dc_getLatestVideo');