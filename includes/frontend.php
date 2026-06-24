<?php

/**
 * @package         GDPR_Google_Maps_Embed_SF
 * @license         GPLv2 or later
 * @license URI     https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_shortcode('dsgvo_map', function ($atts) {
    $atts = shortcode_atts(
        array('id' => '', 'class' => ''),
        $atts,
        'dsgvo_map'
    );
    $id = intval($atts['id']);


    $btn_text      = get_post_meta($id, '_dsgvo_gm_button_text', true) ?: __('Load Map', 'gdpr-dsgvo-compliant-embeds-for-google-maps');
    $btn_shape      = get_post_meta($id, '_dsgvo_gm_button_shape', true);
    $overlay_bg    = get_post_meta($id, '_dsgvo_gm_overlay_bg',  true);
    $button_bg     = get_post_meta($id, '_dsgvo_gm_button_bg',   true);
    $btn_color     = get_post_meta($id, '_dsgvo_gm_button_color', true);
    $btn_font_size = dsgvo_gm_sanitize_font_size(get_post_meta($id, '_dsgvo_gm_button_font_size', true), '16px');
    $privacy_color = get_post_meta($id, '_dsgvo_gm_privacy_color', true);

    $iframe          = get_post_meta($id, '_dsgvo_gm_iframe', true);
    $template        = get_post_meta($id, '_dsgvo_gm_template', true);
    $privacy_enabled = get_post_meta($id, '_dsgvo_gm_privacy_enabled', true);
    $privacy_link    = get_post_meta($id, '_dsgvo_gm_privacy_link', true);
    $load_all_enabled = get_post_meta($id, '_dsgvo_gm_load_all_enabled', true) ? 1 : 0;
    $remember_enabled = get_post_meta($id, '_dsgvo_gm_remember_enabled', true) ? 1 : 0;
    $remember_color   = get_post_meta($id, '_dsgvo_gm_remember_color', true);


    $width_input  = get_post_meta($id, '_dsgvo_gm_width', true);
    $height_input = get_post_meta($id, '_dsgvo_gm_height', true);

    
    $width_val  = $width_input  ? trim($width_input)  : '100%';
    $height_val = $height_input ? trim($height_input) : '100%';

    $privacy_text = get_post_meta($id, '_dsgvo_gm_privacy_text', true) ?: __('Please see our', 'gdpr-dsgvo-compliant-embeds-for-google-maps');
    $privacy_link_text = get_post_meta($id, '_dsgvo_gm_privacy_link_text', true) ?: __('Privacy Policy', 'gdpr-dsgvo-compliant-embeds-for-google-maps');
    $privacy_font_size = dsgvo_gm_sanitize_font_size(get_post_meta($id, '_dsgvo_gm_privacy_font_size', true), '0.8em');
    $privacy_link_font_size = dsgvo_gm_sanitize_font_size(get_post_meta($id, '_dsgvo_gm_privacy_link_font_size', true), '0.8em');
    $message_text = get_post_meta($id, '_dsgvo_gm_message_text', true);
    $message_font_size = dsgvo_gm_sanitize_font_size(get_post_meta($id, '_dsgvo_gm_message_font_size', true), '0.9em');
    $remember_text = get_post_meta($id, '_dsgvo_gm_remember_text', true) ?: __('Remember selection', 'gdpr-dsgvo-compliant-embeds-for-google-maps');
    $remember_font_size = dsgvo_gm_sanitize_font_size(get_post_meta($id, '_dsgvo_gm_remember_font_size', true), '0.85em');

    // Append 'px' if numeric
    if (preg_match('/^\d+$/', $width_val)) {
        $width_val .= 'px';
    }
    if (preg_match('/^\d+$/', $height_val)) {
        $height_val .= 'px';
    }

    if (! $iframe) {
        return '';
    }

    // Build inline style
    if (substr($height_val, -1) === '%') {
        // Height is percentage - use padding-bottom for aspect ratio
        $style_attr = sprintf(
            'width:%s;position:relative;height:0;padding-bottom:%s;overflow:hidden;',
            esc_attr($width_val),
            esc_attr($height_val)
        );
    } else {
        // Fixed height in px or other unit
        $style_attr = sprintf(
            'width:%s;height:%s;position:relative;overflow:hidden;',
            esc_attr($width_val),
            esc_attr($height_val)
        );
    }

    // Classes & inline styles
    $class = 'dsgvo-gm-' . (in_array($template, ['light', 'dark']) ? $template : 'custom');
    $overlay_style = $template === 'custom'
        ? 'background-color:' . esc_attr($overlay_bg) . ';'
        : '';
    $btn_style = $template === 'custom'
        ? 'background-color:' . esc_attr($button_bg) . ';color:' . esc_attr($btn_color) . ';'
        : '';
    $btn_font_size_style = 'font-size:' . esc_attr($btn_font_size) . ';';
    $privacy_style = $template === 'custom'
        ? 'color:' . esc_attr($privacy_color) . ';'
        : '';
    $privacy_text_style = $privacy_style . 'font-size:' . esc_attr($privacy_font_size) . ';';
    $privacy_link_style = $privacy_style . 'font-size:' . esc_attr($privacy_link_font_size) . ';';
    $message_style = $privacy_style . 'font-size:' . esc_attr($message_font_size) . ';';
    $remember_style = $remember_color
        ? 'color:' . esc_attr($remember_color) . ';font-size:' . esc_attr($remember_font_size) . ';'
        : $privacy_style . 'font-size:' . esc_attr($remember_font_size) . ';';


    $btn_shape_style = $btn_shape === 'rounded'
        ? 'border-radius:15px;'
        : 'border-radius:0;';

    $b64 = base64_encode($iframe);

    // Build output
    $html  = '<div class="dsgvo-gm-container ' . esc_attr($class) . '" style="' . $style_attr . '">';
    $html .= '<div class="dsgvo-gm-overlay ' . esc_attr($class) . '" style="' . $overlay_style . '" data-iframe="' . esc_attr($b64) . '" data-load-all="' . esc_attr($load_all_enabled) . '" data-remember-enabled="' . esc_attr($remember_enabled) . '" data-map-id="' . esc_attr($id) . '">';
    $html .= '<button class="dsgvo-gm-load-btn ' . esc_attr($class) . '" style="' . $btn_shape_style . $btn_style . $btn_font_size_style . '">'
        . esc_html($btn_text) .
        '</button>';
    if ($message_text) {
        $html .= '<div class="dsgvo-gm-message ' . esc_attr($class) . '" style="' . $message_style . '">'
            . esc_html($message_text)
            . '</div>';
    }
    if ($privacy_enabled && $privacy_link) {
        $html .= '<div class="dsgvo-gm-privacy-info ' . esc_attr($class) . '" style="' . $privacy_style . '">'
            . '<span style="' . $privacy_text_style . '">' . esc_html( $privacy_text ) . '</span>'
            . ' <a href="' . esc_url($privacy_link) . '" target="_blank" style="' . $privacy_link_style . '">'
            . esc_html( $privacy_link_text )
            . '</a></div>';
    }
    if ($remember_enabled) {
        $html .= '<label class="dsgvo-gm-remember-choice ' . esc_attr($class) . '" style="' . $remember_style . '">'
            . '<input type="checkbox" class="dsgvo-gm-remember-checkbox" value="1"> '
            . '<span>' . esc_html($remember_text) . '</span>'
            . '</label>';
    }
    $html .= '</div></div>';

    return $html;
});
