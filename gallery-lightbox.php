<?php
/**
 * Plugin Name: Creative Gallery
 * Description: Suite Portfolio v11.3 - Base v10.2 + Video (Auto Frame) + Single Image Width.
 * Version: 11.4
 * Author: Creative Metrics
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* * INIZIO AGGIORNAMENTI AUTOMATICI GITHUB 
 */
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/CreativeMetrics/creative-gallery/', // URL del tuo repository
    __FILE__, // Percorso completo del file
    'creative-gallery' // Slug del plugin
);
$myUpdateChecker->setBranch('main');
// Se il repository è PRIVATO, togli il commento alla riga sotto e inserisci il token
// $myUpdateChecker->setAuthentication('IL-TUO-TOKEN-GITHUB');

/* FINE AGGIORNAMENTI AUTOMATICI */



// --- 1. IMPOSTAZIONI ---
add_action('admin_menu', 'cg_create_menu');
function cg_create_menu() {
    add_menu_page( 'Creative Gallery', 'Creative Gallery', 'manage_options', 'creative-gallery-settings', 'cg_settings_page', 'dashicons-images-alt2', 25 );
}

add_action('admin_init', 'cg_register_settings');
function cg_register_settings() {
    register_setting('cg_opt_group', 'cg_post_types');
    register_setting('cg_opt_group', 'cg_columns', ['default' => 4]);
    register_setting('cg_opt_group', 'cg_thumb_size', ['default' => 'large']);
    register_setting('cg_opt_group', 'cg_masonry'); 
    register_setting('cg_opt_group', 'cg_gap', ['default' => 15]);
    register_setting('cg_opt_group', 'cg_radius', ['default' => 6]);
    register_setting('cg_opt_group', 'cg_shadow', ['default' => 1]);
    register_setting('cg_opt_group', 'cg_shape', ['default' => 'original']);
    register_setting('cg_opt_group', 'cg_hover_caption');
    register_setting('cg_opt_group', 'cg_bg_color', ['default' => '#000000']);
    register_setting('cg_opt_group', 'cg_fullscreen', ['default' => 1]);
    register_setting('cg_opt_group', 'cg_zoom', ['default' => 1]);
    register_setting('cg_opt_group', 'cg_protect');
    register_setting('cg_opt_group', 'cg_per_page', ['default' => 0]);
    register_setting('cg_opt_group', 'cg_hover_effect', ['default' => 'zoom']);
    register_setting('cg_opt_group', 'cg_filters', ['default' => 0]);
    register_setting('cg_opt_group', 'cg_deeplink', ['default' => 1]);
    register_setting('cg_opt_group', 'cg_filmstrip', ['default' => 1]); 
    register_setting('cg_opt_group', 'cg_social', ['default' => 1]); 
    register_setting('cg_opt_group', 'cg_debug_mode', ['default' => 0]); 
    register_setting('cg_opt_group', 'cg_single_width', ['default' => 95]);
}

function cg_settings_page() {
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';
    ?>
    <div class="wrap">
        <h1>Creative Gallery <span style="font-size:12px; font-weight:normal; background:#2271b1; color:white; padding:3px 10px; border-radius:12px;">v11.3</span></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=creative-gallery-settings&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Configurazione</a>
            <a href="?page=creative-gallery-settings&tab=guide" class="nav-tab <?php echo $active_tab == 'guide' ? 'nav-tab-active' : ''; ?>">Guida & Definizioni</a>
        </h2>

        <?php if( $active_tab == 'settings' ): ?>
        <form method="post" action="options.php">
            <?php settings_fields('cg_opt_group'); do_settings_sections('cg_opt_group'); ?>
            <div style="display:flex; gap:20px; flex-wrap:wrap; margin-top:20px;">
                <div style="flex:1; min-width:300px; background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                    <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:12px;">Generale</h2>
                    <table class="form-table">
                        <tr valign="top"><th scope="row">Attiva su Post Type:</th><td>
                        <?php 
                        $saved = get_option('cg_post_types') ?: (get_option('al_gal_post_types') ?: []);
                        $types = get_post_types(['public'=>true], 'objects');
                        foreach ($types as $pt) {
                            if($pt->name=='attachment') continue;
                            $chk = in_array($pt->name, $saved) ? 'checked' : '';
                            echo '<label style="margin-right:10px; display:inline-block; margin-bottom:5px;"><input type="checkbox" name="cg_post_types[]" value="'.$pt->name.'" '.$chk.'> <strong>'.$pt->label.'</strong></label><br>';
                        }
                        ?>
                        </td></tr>
                        <tr valign="top"><th scope="row">Colonne:</th><td>
                            <select name="cg_columns" style="width:100%; max-width:200px;"><?php for($i=2; $i<=6; $i++) echo "<option value='$i' ".selected(get_option('cg_columns', 4), $i, false).">$i Colonne</option>"; ?></select>
                        </td></tr>
                        <tr valign="top"><th scope="row">Larghezza Foto Singola:</th><td>
                            <input type="number" min="10" max="100" name="cg_single_width" value="<?php echo esc_attr(get_option('cg_single_width', 95)); ?>" style="width:80px"> %
                        </td></tr>
                        <tr valign="top"><th scope="row">Qualità Foto:</th><td>
                            <select name="cg_thumb_size" style="width:100%; max-width:200px;">
                                <?php 
                                $sizes = ['medium' => 'Media', 'large' => 'Grande', 'full' => 'Originale'];
                                foreach($sizes as $k => $v) echo "<option value='$k' ".selected(get_option('cg_thumb_size', 'large'), $k, false).">$v</option>"; 
                                ?>
                            </select>
                        </td></tr>
                        <tr valign="top"><th scope="row">Funzioni Avanzate:</th><td>
                            <label style="display:block; margin-bottom:5px;"><input type="checkbox" name="cg_filters" value="1" <?php checked(1, get_option('cg_filters', 0), true); ?>> <strong>Filtri Categorie</strong> (Usa campo "Descrizione" media)</label>
                            <label style="display:block; margin-bottom:5px;"><input type="checkbox" name="cg_deeplink" value="1" <?php checked(1, get_option('cg_deeplink', 1), true); ?>> <strong>Deep Linking</strong> (URL diretto foto)</label>
                            <label style="display:block;"><input type="number" min="0" name="cg_per_page" value="<?php echo esc_attr(get_option('cg_per_page', 0)); ?>" style="width:80px"> foto per pagina (0 = tutte)</label>
                        </td></tr>
                    </table>
                </div>

                <div style="flex:1; min-width:300px; background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                    <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:12px;">Design & Lightbox</h2>
                    <table class="form-table">
                        <tr valign="top"><th scope="row">Layout:</th><td>
                            <label><input type="checkbox" name="cg_masonry" value="1" <?php checked(1, get_option('cg_masonry'), true); ?>> Masonry</label> &nbsp;
                            <select name="cg_shape" style="vertical-align:middle;">
                                <option value="original" <?php selected(get_option('cg_shape'), 'original'); ?>>Originale</option>
                                <option value="square" <?php selected(get_option('cg_shape'), 'square'); ?>>Quadrato</option>
                                <option value="circle" <?php selected(get_option('cg_shape'), 'circle'); ?>>Cerchio</option>
                                <option value="landscape" <?php selected(get_option('cg_shape'), 'landscape'); ?>>Orizzontale</option>
                                <option value="portrait" <?php selected(get_option('cg_shape'), 'portrait'); ?>>Verticale</option>
                            </select>
                        </td></tr>
                        <tr valign="top"><th scope="row">Stile:</th><td>
                            Gap: <input type="number" min="0" name="cg_gap" value="<?php echo esc_attr(get_option('cg_gap', 15)); ?>" style="width:80px">px &nbsp;
                            Radius: <input type="number" min="0" name="cg_radius" value="<?php echo esc_attr(get_option('cg_radius', 6)); ?>" style="width:80px">px<br><br>
                            
                            <label style="display:block; margin-bottom:10px;">
                                <input type="checkbox" name="cg_hover_caption" value="1" <?php checked(1, get_option('cg_hover_caption'), true); ?>> 
                                <strong>Mostra Titolo su Hover</strong>
                            </label>

                            <select name="cg_hover_effect">
                                <option value="zoom" <?php selected(get_option('cg_hover_effect'), 'zoom'); ?>>Zoom Hover</option>
                                <option value="bw" <?php selected(get_option('cg_hover_effect'), 'bw'); ?>>B&N -> Colore</option>
                                <option value="blur" <?php selected(get_option('cg_hover_effect'), 'blur'); ?>>Blur -> Focus</option>
                                <option value="sepia" <?php selected(get_option('cg_hover_effect'), 'sepia'); ?>>Seppia -> Colore</option>
                            </select>
                        </td></tr>
                        
                        <tr valign="top"><th scope="row">Sfondo Lightbox:</th><td>
                            <input type="color" name="cg_bg_color" value="<?php echo esc_attr(get_option('cg_bg_color', '#000000')); ?>" style="vertical-align:middle; cursor:pointer;">
                        </td></tr>

                        <tr valign="top"><th scope="row">Strumenti:</th><td>
                            <label style="margin-right:10px;"><input type="checkbox" name="cg_fullscreen" value="1" <?php checked(1, get_option('cg_fullscreen', 1), true); ?>> Fullscreen</label>
                            <label style="margin-right:10px;"><input type="checkbox" name="cg_zoom" value="1" <?php checked(1, get_option('cg_zoom', 1), true); ?>> Zoom</label><br>
                            <label style="margin-right:10px;"><input type="checkbox" name="cg_filmstrip" value="1" <?php checked(1, get_option('cg_filmstrip', 1), true); ?>> <strong>Filmstrip</strong></label>
                            <label><input type="checkbox" name="cg_social" value="1" <?php checked(1, get_option('cg_social', 1), true); ?>> <strong>Social Share</strong></label>
                        </td></tr>
                        <tr valign="top"><th scope="row" style="color:#d63638;">Sicurezza:</th><td>
                            <label><input type="checkbox" name="cg_protect" value="1" <?php checked(1, get_option('cg_protect'), true); ?>> Protezione (No Right Click)</label>
                        </td></tr>
                    </table>
                </div>
            </div>

            <div style="margin-top:30px; background:#fffbfb; border:1px solid #d63638; padding:15px; border-radius:4px;">
                <h3 style="color:#d63638; margin-top:0;">🔧 Strumenti di Diagnostica</h3>
                <label>
                    <input type="checkbox" name="cg_debug_mode" value="1" <?php checked(1, get_option('cg_debug_mode', 0), true); ?>> 
                    <strong>Attiva Modalità Debug</strong>
                </label>
                <p class="description">Attiva questa opzione se non vedi le foto sul sito. Appariranno dei messaggi rossi di errore che spiegano il motivo (solo per amministratori).</p>
            </div>

            <p class="submit" style="margin-top:20px;"><input type="submit" class="button button-primary button-large" value="Salva Modifiche"></p>
        </form>

        <?php else: ?>
        <div style="margin-top:20px; background:#fff; padding:40px; border:1px solid #ccd0d4; max-width:1000px; line-height:1.6; font-size:14px;">
            <h2 style="border-bottom:1px solid #eee; padding-bottom:15px; margin-top:0; color:#23282d;">📖 Manuale e Definizioni</h2>
            <div style="display:flex; gap:40px; flex-wrap:wrap;">
                <div style="flex:1; min-width:300px;">
                    <h3 style="color:#2271b1; border-bottom:2px solid #2271b1; padding-bottom:5px; display:inline-block;">1. Come Inserire la Galleria</h3>
                    <p><strong>Automatico:</strong><br>Se non scrivi nulla, la galleria appare da sola alla fine della pagina.</p>
                    <p><strong>Manuale (Shortcode):</strong><br>Copia e incolla: <code>[creative_gallery]</code></p>
                    <p><strong>Galleria Spezzata (Storytelling):</strong><br>
                    1. Scrivi testo...<br>2. Inserisci <code>[creative_gallery count="3"]</code><br>3. Scrivi altro testo...<br>4. Inserisci <code>[creative_gallery]</code></p>
                    <br>
                    <h3 style="color:#2271b1; border-bottom:2px solid #2271b1; padding-bottom:5px; display:inline-block;">2. Funzioni Speciali</h3>
                    <ul style="list-style:disc; margin-left:15px;">
                        <li><strong>Filtri Categorie:</strong> Usa il campo "Descrizione" media.</li>
                        <li><strong>Deep Linking:</strong> Modifica l'URL (#img-5).</li>
                        <li><strong>Paginazione:</strong> Carica altro con pulsante.</li>
                    </ul>
                </div>
                <div style="flex:1; min-width:300px; background:#f9f9f9; padding:20px; border-radius:8px;">
                    <h3 style="color:#333; margin-top:0;">📚 Opzioni</h3>
                    <p><strong>Masonry:</strong> Disposizione "a mattoni" (stile Pinterest).</p>
                    <p><strong>Gap & Radius:</strong> Spazio e arrotondamento.</p>
                    <p><strong>Maschera (Shape):</strong> Forza forma (Quadrato, Cerchio, ecc).</p>
                    <p><strong>Filmstrip:</strong> Striscia miniature nel lightbox.</p>
                    <p><strong>Social Share:</strong> Icone condivisione nel lightbox.</p>
                    <p><strong>Protezione:</strong> No tasto destro.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// --- 2. BACKEND (MODIFICATO PER THUMB VIDEO AUTO) ---
add_action('admin_enqueue_scripts', 'cg_admin_scripts');
function cg_admin_scripts($hook) {
    global $post;
    $types = get_option('cg_post_types') ?: (get_option('al_gal_post_types') ?: []);
    if ( ($hook == 'post-new.php' || $hook == 'post.php') && isset($post->post_type) && in_array($post->post_type, $types) ) {
        wp_enqueue_media(); wp_enqueue_script('jquery-ui-sortable');
    }
}
add_action('add_meta_boxes', 'cg_add_box');
function cg_add_box() {
    $types = get_option('cg_post_types') ?: (get_option('al_gal_post_types') ?: []);
    foreach ($types as $type) add_meta_box('cg_gallery_box', 'Creative Gallery', 'cg_render_box', $type, 'normal', 'high');
}
function cg_render_box($post) {
    $img_ids = get_post_meta($post->ID, '_cg_gallery_ids', true);
    if(empty($img_ids)) $img_ids = get_post_meta($post->ID, '_al_gallery_ids', true);
    if(empty($img_ids)) { $v1 = get_post_meta($post->ID, 'gallery_installazione', false); if(!empty($v1)) $img_ids = implode(',', $v1); }
    $count = empty($img_ids) ? 0 : count(explode(',', $img_ids));
    wp_nonce_field('cg_save_action', 'cg_nonce');
    ?>
    <div id="cg-wrapper">
        <div class="cg-toolbar">
            <button type="button" class="button button-primary" id="cg-add-btn">Gestisci Foto/Video</button>
            <span class="cg-count">Totale: <strong id="cg-count-num"><?php echo $count; ?></strong></span>
            <button type="button" class="button" id="cg-clear-btn" style="float:right;">Svuota</button>
        </div>
        <ul id="cg-list">
            <?php if(!empty($img_ids)): $ids = explode(',', $img_ids); foreach($ids as $id): 
                $url = wp_get_attachment_image_url($id, 'thumbnail');
                $is_vid_attr = '';
                if(!$url) {
                    // È un video (o file senza thumb). Metto l'icona E l'attributo per il JS
                    $url = wp_mime_type_icon($id);
                    $full_src = wp_get_attachment_url($id);
                    if(preg_match('/\.(mp4|webm|ogv)$/i', $full_src)) {
                        $is_vid_attr = ' data-video-src="'.esc_url($full_src).'" ';
                    }
                }
            ?>
                <li class="cg-item" data-id="<?php echo $id; ?>"><span class="cg-remove">×</span><img src="<?php echo esc_url($url); ?>" <?php echo $is_vid_attr; ?>></li>
            <?php endforeach; endif; ?>
        </ul>
        
        <div class="cg-info">
            <strong>Promemoria Shortcode:</strong>
            <ul>
                <li><code>[creative_gallery]</code> Mostra tutto qui.</li>
                <li><code>[creative_gallery count="3"]</code> Mostra 3 foto e ricorda il punto.</li>
            </ul>
            <p style="margin:5px 0 0; font-size:11px; color:#666;">Il plugin continua automaticamente dalla foto successiva.</p>
        </div>

        <input type="hidden" name="cg_gallery_ids" id="cg_gallery_ids" value="<?php echo esc_attr($img_ids); ?>">
    </div>
    <style>
        #cg-list{list-style:none;margin:15px 0;padding:10px;display:flex;flex-wrap:wrap;gap:10px;min-height:40px;border:2px dashed #ddd;background:#fafafa;border-radius:4px}
        .cg-item{width:90px;height:90px;position:relative;border:1px solid #ccc;cursor:move;}.cg-item img{width:100%;height:100%;object-fit:cover}
        .cg-remove{position:absolute;top:0;right:0;background:red;color:#fff;width:20px;height:20px;text-align:center;cursor:pointer;display:none;z-index:10;}
        .cg-item:hover .cg-remove{display:block}
        .cg-info { margin-top:10px; padding:10px; background:#f0f6fc; border-left:3px solid #2271b1; font-size:12px; }
        .cg-info ul { margin:5px 0 0 15px; padding:0; list-style:disc; }
        .cg-info code { background:#fff; padding:2px 5px; border:1px solid #ddd; }
    </style>
    <script>jQuery(document).ready(function($){
        var frame, list=$('#cg-list'), input=$('#cg_gallery_ids'), cnt=$('#cg-count-num');
        
        // Funzione per generare thumb video
        function checkVids(){
            $('#cg-list img[data-video-src]').each(function(){
                var img = $(this); var src = img.attr('data-video-src');
                if(!src || img.hasClass('v-done')) return;
                var v = document.createElement('video'); v.src = src; v.muted=true; v.preload='metadata'; v.currentTime=0.5;
                v.onloadeddata = function(){
                    if(v.readyState>=2){
                        var c=document.createElement('canvas');c.width=v.videoWidth;c.height=v.videoHeight;
                        c.getContext('2d').drawImage(v,0,0); img.attr('src',c.toDataURL()); img.addClass('v-done');
                    }
                };
            });
        }
        // Avvio controllo su esistenti
        setTimeout(checkVids, 500);

        list.sortable({update:function(){upd()}});
        $('#cg-add-btn').on('click',function(e){
            e.preventDefault(); if(frame){frame.open();return}
            frame=wp.media({title:'Gestisci',multiple:true});
            frame.on('select',function(){
                frame.state().get('selection').map(function(att){
                    att=att.toJSON();
                    var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.icon;
                    var vAttr = '';
                    // Se non ha thumb ed è video
                    if(!att.sizes || !att.sizes.thumbnail) {
                        if(att.url.match(/\.(mp4|webm|ogv)$/i)) vAttr = ' data-video-src="'+att.url+'" ';
                    }
                    list.append('<li class="cg-item" data-id="'+att.id+'"><span class="cg-remove">×</span><img src="'+url+'" '+vAttr+'></li>')
                });
                upd(); checkVids();
            });
            frame.open()
        });
        $('#cg-clear-btn').on('click',function(){if(confirm('Svuotare?')){list.empty();upd()}});
        $(document).on('click','.cg-remove',function(){$(this).parent().remove();upd()});
        function upd(){var ids=[];list.find('li').each(function(){ids.push($(this).data('id'))});input.val(ids.join(','));cnt.text(ids.length)}
    });</script>
    <?php
}
add_action('save_post', 'cg_save_data');
function cg_save_data($post_id) {
    if (!isset($_POST['cg_nonce']) || !wp_verify_nonce($_POST['cg_nonce'], 'cg_save_action')) return;
    if (isset($_POST['cg_gallery_ids'])) update_post_meta($post_id, '_cg_gallery_ids', sanitize_text_field($_POST['cg_gallery_ids']));
    else delete_post_meta($post_id, '_cg_gallery_ids');
}

// --- 3. HELPER & DEBUG ---
function cg_debug_print($message) {
    if(get_option('cg_debug_mode')) {
        echo '<div style="background:#ffcccc; border:2px solid red; color:black; padding:15px; margin:20px 0; z-index:99999; position:relative; font-family:monospace; font-size:14px;"><strong>[CG DEBUG]</strong> ' . $message . '</div>';
    }
}

function cg_get_gallery_ids($post_id) {
    $ids = get_post_meta($post_id, '_cg_gallery_ids', true);
    if(!$ids) $ids = get_post_meta($post_id, '_al_gallery_ids', true);
    if(!$ids) { $old = get_post_meta($post_id, 'gallery_installazione', false); if(!empty($old)) $ids = implode(',', $old); }
    return $ids;
}

// --- 4. RENDER HTML (MODIFICATO PER DATA-VIDEO-SRC) ---
function cg_render_html($ids, $start_index_global = 0) {
    if(empty($ids)) return '';
    
    // Vars
    $masonry = get_option('cg_masonry');
    $hover_cap = get_option('cg_hover_caption');
    $thumb_size = get_option('cg_thumb_size', 'large');
    $per_page = intval(get_option('cg_per_page', 0));
    $hover_effect = get_option('cg_hover_effect', 'zoom');
    $filters_active = get_option('cg_filters', 0);

    // LOGICA CLASSE SINGOLA
    $is_single_class = (count($ids) === 1) ? ' cg-is-single' : '';

    $cls = $masonry ? 'cg-masonry' : 'cg-grid';
    if($hover_cap) $cls .= ' cg-has-captions';
    $cls .= ' cg-effect-' . $hover_effect;
    $cls .= $is_single_class;

    $categories = [];
    $items_html = '';
    $i = 0;

    foreach($ids as $id) {
        $real_global_index = $start_index_global + $i;
        $full = wp_get_attachment_url($id); // URL completo per i video
        // Se è immagine usa image_url, se video e no thumb usa icona
        $thumb = wp_get_attachment_image_url($id, $thumb_size);
        $video_attr = '';
        
        if(!$thumb) {
            $thumb = wp_mime_type_icon($id); // Icona fallback
            // Controllo se è video per thumb automatica JS
            if(preg_match('/\.(mp4|webm|ogv)$/i', $full)) {
                $video_attr = ' data-video-src="'.esc_url($full).'" ';
            }
        }

        $meta = get_post($id);
        if (!$full) { continue; } 

        $title = $meta->post_title;
        $desc = trim($meta->post_content); 
        $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
        
        $cat_slug = '';
        if($filters_active && !empty($desc)) {
            $cat_slug = sanitize_title($desc);
            if(!isset($categories[$cat_slug])) $categories[$cat_slug] = $desc;
        }

        $hidden_class = ($per_page > 0 && $i >= $per_page) ? 'cg-hidden-page' : '';

        $items_html .= '<div class="cg-box cg-anim '.$hidden_class.' cg-cat-all '.$cat_slug.'" data-cat="'.$cat_slug.'">';
        $items_html .= '<a href="'.esc_url($full).'" class="cg-trigger" data-title="'.esc_attr($title).'" data-id="'.$real_global_index.'" data-thumb="'.esc_url($thumb).'">';
        // QUI AGGIUNTO $video_attr per JS generazione thumb
        $items_html .= '<img src="'.esc_url($thumb).'" alt="'.esc_attr($alt).'" loading="lazy" '.$video_attr.'>';
        
        if($hover_cap && $title) {
            $items_html .= '<div class="cg-overlay"><span>'.esc_html($title).'</span></div>';
        }
        
        $items_html .= '</a></div>';
        $i++;
    }

    $output = '';
    if($filters_active && count($categories) > 0) {
        $output .= '<div class="cg-filter-bar">';
        $output .= '<button class="cg-filter-btn active" data-filter="cg-cat-all">Tutti</button>';
        foreach($categories as $slug => $name) $output .= '<button class="cg-filter-btn" data-filter="'.$slug.'">'.esc_html($name).'</button>';
        $output .= '</div>';
    }

    $output .= '<div class="cg-wrapper '.$cls.'" data-per-page="'.$per_page.'">' . $items_html . '</div>';

    if ($per_page > 0 && count($ids) > $per_page) {
        $output .= '<div class="cg-load-more-wrap"><button id="cg-load-more">Mostra altre foto</button></div>';
    }

    return $output;
}

add_shortcode('creative_gallery', 'cg_short');
add_shortcode('al_gallery', 'cg_short'); 
function cg_short($atts) {
    global $post;
    if(!$post) return '';

    static $gallery_offsets = [];
    $pid = $post->ID;
    if (!isset($gallery_offsets[$pid])) { $gallery_offsets[$pid] = 0; }

    $ids_str = cg_get_gallery_ids($pid);
    if(!$ids_str) { cg_debug_print("Nessuna foto trovata."); return ''; }
    
    $arr = explode(',', $ids_str);

    if (isset($atts['start']) && $atts['start'] !== '') { $start_index = intval($atts['start']); } else { $start_index = $gallery_offsets[$pid]; }
    $count = (isset($atts['count']) && $atts['count'] != -1) ? intval($atts['count']) : null;
    $subset = array_slice($arr, $start_index, $count);

    if (!isset($atts['start'])) { $gallery_offsets[$pid] += count($subset); }
    
    if (empty($subset) && $start_index > 0) {
        cg_debug_print("FIX: Reset indice per doppio rendering.");
        $start_index = 0;
        $subset = array_slice($arr, 0, $count);
        $gallery_offsets[$pid] = count($subset);
    }

    if (empty($subset)) return '';

    return cg_render_html($subset, $start_index);
}

// 5. AUTO-APPEND LOGIC
add_filter('the_content', 'cg_append');
function cg_append($c) {
    global $post;
    
    $types = get_option('cg_post_types') ?: (get_option('al_gal_post_types') ?: []);
    if(!in_array(get_post_type(), $types)) return $c;

    if(has_shortcode($c, 'creative_gallery') || has_shortcode($c, 'al_gallery')) {
        cg_debug_print("Auto-Append saltato: shortcode trovato.");
        return $c;
    }

    $ids = cg_get_gallery_ids(get_the_ID());
    if(!$ids) return $c;

    return $c . do_shortcode('[creative_gallery]');
}

// STYLES & JS
add_action('wp_head', 'cg_styles');
add_action('wp_footer', 'cg_scripts');

function cg_styles() {
    $cols = get_option('cg_columns', 4);
    $bg = get_option('cg_bg_color', '#000000');
    list($r, $g, $b) = sscanf($bg, "#%02x%02x%02x");
    $gap = get_option('cg_gap', 15);
    $rad = get_option('cg_radius', 6);
    $shadow = get_option('cg_shadow', 1) ? '0 4px 10px rgba(0,0,0,0.1)' : 'none';
    $protect = get_option('cg_protect');
    
    // OPZIONE LARGHEZZA SINGOLA
    $single_w = get_option('cg_single_width', 95);

    $shape = get_option('cg_shape', 'original');
    $aspect = 'auto'; $override_rad = '';
    switch($shape) {
        case 'square': $aspect = '1 / 1'; break;
        case 'circle': $aspect = '1 / 1'; $override_rad = 'border-radius: 50% !important;'; break;
        case 'landscape': $aspect = '4 / 3'; break;
        case 'portrait': $aspect = '3 / 4'; break;
    }
    ?>
    <style>
    .cg-wrapper { margin-top:30px; }
    @keyframes cg-fadeUp { 0% { opacity: 0; transform: translateY(20px); } 100% { opacity: 1; transform: translateY(0); } }
    @keyframes cg-fadeInSimple { 0% { opacity: 0; } 100% { opacity: 1; } }

    .cg-grid { display: grid; gap: <?php echo $gap; ?>px; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
    .cg-box { 
        position:relative; background:#eee; 
        border-radius: <?php echo $rad; ?>px; 
        <?php echo $override_rad; ?>
        overflow: hidden; 
        box-shadow: <?php echo $shadow; ?>; 
        transition:transform 0.3s, filter 0.3s; 
        aspect-ratio: <?php echo $aspect; ?>;
        opacity: 0; animation: cg-fadeUp 0.6s ease forwards;
    }
    
    /* CSS SINGOLA + MAX HEIGHT + FADEIN */
    .cg-wrapper.cg-is-single { display: block !important; text-align: center; }
    .cg-wrapper.cg-is-single .cg-box { 
        width: <?php echo $single_w; ?>%; 
        max-width: <?php echo $single_w; ?>%; 
        margin: 0 auto; 
        aspect-ratio: auto; 
        height: auto; 
        max-height: 80vh;
        animation: cg-fadeInSimple 1s ease forwards; 
    }
    .cg-wrapper.cg-is-single img, .cg-wrapper.cg-is-single video { width: 100%; height: auto; max-height: 80vh; object-fit: contain; }

    .cg-box:nth-child(1) { animation-delay: 0.05s; }
    .cg-box:nth-child(2) { animation-delay: 0.1s; }
    .cg-box:nth-child(3) { animation-delay: 0.15s; }
    .cg-box:nth-child(4) { animation-delay: 0.2s; }
    .cg-box:nth-child(n+5) { animation-delay: 0.25s; }

    .cg-grid img, .cg-masonry img { width:100%; height:100%; object-fit:cover; display:block; transition:transform 0.5s ease, filter 0.5s ease; <?php if($protect) echo '-webkit-user-drag: none; user-select: none;'; ?> }
    .cg-masonry { column-count: 2; column-gap: <?php echo $gap; ?>px; }
    .cg-masonry .cg-box { break-inside: avoid; margin-bottom: <?php echo $gap; ?>px; }
    
    .cg-effect-zoom .cg-box:hover img { transform: scale(1.05); }
    .cg-effect-bw .cg-box img { filter: grayscale(100%); } .cg-effect-bw .cg-box:hover img { filter: grayscale(0%); transform: scale(1.03); }
    .cg-effect-blur .cg-box img { filter: blur(3px); transform: scale(1.03); } .cg-effect-blur .cg-box:hover img { filter: blur(0px); transform: scale(1); }
    .cg-effect-sepia .cg-box img { filter: sepia(100%); } .cg-effect-sepia .cg-box:hover img { filter: sepia(0%); transform: scale(1.03); }

    .cg-overlay { position:absolute; bottom:0; left:0; width:100%; background:linear-gradient(to top, rgba(0,0,0,0.8), transparent); padding:30px 10px 10px; opacity:0; transition:opacity 0.3s; pointer-events:none; }
    .cg-overlay span { color:#fff; font-size:14px; font-weight:500; text-shadow:0 1px 2px rgba(0,0,0,0.5); }
    <?php if($shape === 'circle'): ?>
    .cg-box .cg-overlay { top: 0; bottom: 0; display: flex; align-items: center; justify-content: center; text-align: center; background: rgba(0,0,0,0.4); border-radius: 50%; padding: 20px; }
    .cg-box .cg-overlay span { white-space: normal; line-height: 1.4; }
    <?php endif; ?>
    .cg-has-captions .cg-box:hover .cg-overlay { opacity:1; }

    @media (min-width: 1025px) { .cg-grid { grid-template-columns: repeat(<?php echo $cols; ?>, 1fr); } .cg-masonry { column-count: <?php echo $cols; ?>; } }
    @media (max-width: 767px) { .cg-grid { grid-template-columns: 1fr 1fr; gap: 10px; } .cg-masonry { column-count: 2; column-gap: 10px; } .cg-wrapper.cg-is-single .cg-box { width: 100%; max-width: 100%; } }

    #cg-lb { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(<?php echo "$r,$g,$b"; ?>,0.95); display:flex; z-index:999999; opacity:0; visibility:hidden; transition:opacity 0.3s; flex-direction: column; }
    #cg-lb.on { opacity:1; visibility:visible; }
    .cg-in { position:relative; width:100%; flex: 1; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .cg-img, .cg-video-lb { max-width:85%; max-height:80vh; border-radius:4px; box-shadow:0 0 40px rgba(0,0,0,0.5); transition: opacity 0.3s; opacity: 0; }
    .cg-img { cursor: zoom-in; }
    .cg-img.loaded, .cg-video-lb.loaded { opacity: 1; }
    .cg-img.zoomed { transform: scale(2.5); cursor: zoom-out; max-width: 85%; max-height: 85vh; }

    .cg-filmstrip { width: 100%; height: 70px; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; gap: 10px; overflow-x: auto; padding: 0 10px; white-space: nowrap; scroll-behavior: smooth; }
    .cg-thumb-item { width: 50px; height: 50px; object-fit: cover; opacity: 0.5; cursor: pointer; border-radius: 4px; border: 2px solid transparent; transition: all 0.2s; flex-shrink: 0; }
    .cg-thumb-item:hover { opacity: 1; }
    .cg-thumb-item.active { opacity: 1; border-color: #fff; transform: scale(1.1); }

    .cg-social { position: absolute; top: 20px; left: 20px; display: flex; gap: 10px; z-index: 1003; }
    .cg-social-btn { width: 35px; height: 35px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; color: #fff; transition: background 0.2s; }
    .cg-social-btn:hover { background: rgba(255,255,255,0.3); }
    .cg-social-btn svg { width: 18px; height: 18px; }

    .cg-loader { position:absolute; width:40px; height:40px; border:4px solid rgba(255,255,255,0.2); border-top:4px solid #fff; border-radius:50%; animation: cg-spin 1s linear infinite; z-index: -1; }
    @keyframes cg-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    .cg-btn { position:absolute; color:#fff; cursor:pointer; z-index:1002; user-select:none; width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:rgba(255,255,255,0.1); transition:background 0.2s; }
    .cg-btn:hover { background:rgba(255,255,255,0.25); }
    .cg-btn svg { width: 28px; height: 28px; stroke-width: 2px; }

    .cg-prev{top:50%; left:20px; transform:translateY(-50%);} .cg-next{top:50%; right:20px; transform:translateY(-50%);} .cg-close{top:20px; right:20px;} 
    .cg-full{top:20px; right:80px;} .cg-zoom-btn{top:20px; right:140px;}
    
    .cg-meta { position:absolute; bottom:20px; width:100%; text-align:center; color:#fff; pointer-events:none; }
    .cg-cap { font-size:16px; text-shadow:0 1px 3px rgba(0,0,0,0.8); display:block; margin-bottom:5px; }
    .cg-num { font-size:12px; opacity:0.7; font-family:sans-serif; background:rgba(0,0,0,0.3); padding:2px 8px; border-radius:10px; }

    @media (max-width: 767px) { 
        .cg-img, .cg-video-lb { max-width:95%; } 
        .cg-btn { width:40px; height:40px; background:rgba(0,0,0,0.3); } .cg-btn svg { width:22px; height:22px; }
        .cg-prev{left:10px} .cg-next{right:10px} .cg-close{right:10px; top:10px;} 
        .cg-full{display:none;} .cg-zoom-btn{right:60px; top:10px;}
        .cg-filmstrip { height: 60px; } .cg-thumb-item { width: 40px; height: 40px; }
    }
    </style>
    <?php
}

function cg_scripts() {
    $types = get_option('cg_post_types') ?: (get_option('al_gal_post_types') ?: []);
    if(!in_array(get_post_type(), $types)) return;
    
    $fs = get_option('cg_fullscreen', 1);
    $zoom = get_option('cg_zoom', 1);
    $protect = get_option('cg_protect');
    $deep = get_option('cg_deeplink', 1);
    $filmstrip = get_option('cg_filmstrip', 1);
    $social = get_option('cg_social', 1);
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const wrapper = document.querySelector('.cg-wrapper');
        const loadBtn = document.getElementById('cg-load-more');
        const filterBtns = document.querySelectorAll('.cg-filter-btn');

        if(filterBtns.length > 0) {
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('active')); this.classList.add('active');
                    const filter = this.getAttribute('data-filter');
                    const items = document.querySelectorAll('.cg-box'); 
                    items.forEach(item => {
                        if(filter === 'cg-cat-all' || item.classList.contains(filter)) item.classList.remove('cg-hidden-filter');
                        else item.classList.add('cg-hidden-filter');
                    });
                    if(loadBtn) loadBtn.style.display = (filter === 'cg-cat-all') ? 'inline-block' : 'none';
                });
            });
        }

        if(loadBtn) {
            loadBtn.addEventListener('click', function() {
                const perPage = parseInt(wrapper.getAttribute('data-per-page'));
                const hiddenItems = wrapper.querySelectorAll('.cg-hidden-page');
                let count = 0;
                hiddenItems.forEach(item => {
                    if(count < perPage) {
                        item.classList.remove('cg-hidden-page');
                        item.style.animation = 'none'; item.offsetHeight; item.style.animation = 'cg-fadeUp 0.6s ease forwards';
                        count++;
                    }
                });
                if(wrapper.querySelectorAll('.cg-hidden-page').length === 0) this.parentElement.style.display = 'none';
            });
        }

        const isProtected = <?php echo $protect ? 'true' : 'false'; ?>;
        if(isProtected) { document.querySelectorAll('.cg-wrapper img').forEach(img => { img.addEventListener('contextmenu', e=>e.preventDefault()); img.addEventListener('dragstart', e=>e.preventDefault()); }); }

        let allTriggers = Array.from(document.querySelectorAll('.cg-trigger')).sort((a,b) => parseInt(a.getAttribute('data-id')) - parseInt(b.getAttribute('data-id')));

        function getVisibleTriggers() {
            return allTriggers.filter(t => {
                const box = t.closest('.cg-box');
                return !box.classList.contains('cg-hidden-filter') && !box.classList.contains('cg-hidden-page');
            });
        }

        // GENERATORE THUMBNAIL VIDEO FRONTEND
        function checkVidsFront(){
            const vImgs = document.querySelectorAll('.cg-wrapper img[data-video-src]');
            vImgs.forEach(img => {
                if(img.classList.contains('v-done')) return;
                const src = img.getAttribute('data-video-src');
                const v = document.createElement('video');
                v.src = src; v.muted=true; v.preload='metadata'; v.currentTime=0.5;
                v.onloadeddata = function(){
                    if(v.readyState>=2){
                        const c=document.createElement('canvas');c.width=v.videoWidth;c.height=v.videoHeight;
                        c.getContext('2d').drawImage(v,0,0); 
                        img.src=c.toDataURL(); img.classList.add('v-done');
                    }
                };
            });
        }
        setTimeout(checkVidsFront, 500);

        let currIndex = 0;
        
        const lb = document.createElement('div');
        lb.id = 'cg-lb';
        
        const iClose = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`;
        const iPrev  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>`;
        const iNext  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>`;
        const iFull  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>`;
        const iZoom  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>`;
        const iWa    = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>`;
        const iFb    = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>`;
        const iCopy  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>`;

        let html = `<div class="cg-in"><div class="cg-loader"></div>
            <div class="cg-btn cg-close" title="Chiudi">${iClose}</div>
            <div class="cg-btn cg-prev">${iPrev}</div>
            <div class="cg-btn cg-next">${iNext}</div>`;
        
        if(<?php echo $fs ? 'true' : 'false'; ?>) html += `<div class="cg-btn cg-full" title="Fullscreen">${iFull}</div>`;
        if(<?php echo $zoom ? 'true' : 'false'; ?>) html += `<div class="cg-btn cg-zoom-btn" title="Zoom">${iZoom}</div>`;
        
        if(<?php echo $social ? 'true' : 'false'; ?>) {
            html += `<div class="cg-social">
                <div class="cg-social-btn" id="cg-s-wa" title="WhatsApp">${iWa}</div>
                <div class="cg-social-btn" id="cg-s-fb" title="Facebook">${iFb}</div>
                <div class="cg-social-btn" id="cg-s-cp" title="Copia Link">${iCopy}</div>
            </div>`;
        }

        html += `<img src="" class="cg-img"><div class="cg-meta"><span class="cg-cap"></span><span class="cg-num"></span></div></div>`;
        
        if(<?php echo $filmstrip ? 'true' : 'false'; ?>) html += `<div class="cg-filmstrip"></div>`;

        lb.innerHTML = html;
        document.body.appendChild(lb);
        
        const img = lb.querySelector('.cg-img');
        const cap = lb.querySelector('.cg-cap');
        const num = lb.querySelector('.cg-num');
        const cont = lb.querySelector('.cg-in');
        const strip = lb.querySelector('.cg-filmstrip');

        if(isProtected) { img.addEventListener('contextmenu', e=>e.preventDefault()); img.addEventListener('dragstart', e=>e.preventDefault()); }
        img.addEventListener('load', function() { this.classList.add('loaded'); });

        function buildFilmstrip(visible) {
            if(!strip) return;
            strip.innerHTML = '';
            visible.forEach((t, i) => {
                const thumbUrl = t.getAttribute('data-thumb');
                const tImg = document.createElement('img');
                tImg.src = thumbUrl;
                tImg.className = 'cg-thumb-item';
                tImg.dataset.idx = i;
                tImg.addEventListener('click', (e) => {
                    e.stopPropagation();
                    upd(i);
                });
                strip.appendChild(tImg);
            });
        }

        function upd(index) {
            const visible = getVisibleTriggers();
            if(index >= visible.length) index = 0;
            if(index < 0) index = visible.length - 1;
            currIndex = index;
            const target = visible[currIndex];
            const url = target.getAttribute('href');
            
            // CHECK VIDEO E PULIZIA
            const isVideo = url.match(/\.(mp4|webm|ogv)$/i);
            const oldMedia = cont.querySelector('.cg-img, .cg-video-lb');
            if(oldMedia) oldMedia.remove();
            resetZoom();

            if(isVideo) {
                const v = document.createElement('video');
                v.src = url;
                v.className = 'cg-video-lb';
                v.controls = true;
                v.autoplay = true;
                cont.appendChild(v);
                setTimeout(() => v.classList.add('loaded'), 50);
                if(lb.querySelector('.cg-zoom-btn')) lb.querySelector('.cg-zoom-btn').style.display = 'none';
            } else {
                const newImg = document.createElement('img');
                newImg.src = url;
                newImg.className = 'cg-img';
                cont.appendChild(newImg);
                newImg.addEventListener('load', function() { this.classList.add('loaded'); });
                newImg.addEventListener('click', e => { e.stopPropagation(); toggleZoom(); });
                if(lb.querySelector('.cg-zoom-btn')) lb.querySelector('.cg-zoom-btn').style.display = 'flex';
                if(isProtected) { newImg.addEventListener('contextmenu', e=>e.preventDefault()); newImg.addEventListener('dragstart', e=>e.preventDefault()); }
            }
            
            setTimeout(() => {
                cap.textContent = target.getAttribute('data-title') || '';
                num.textContent = (currIndex + 1) + ' / ' + visible.length;
                
                if(strip) {
                    strip.querySelectorAll('.cg-thumb-item').forEach(ti => ti.classList.remove('active'));
                    const activeThumb = strip.children[currIndex];
                    if(activeThumb) {
                        activeThumb.classList.add('active');
                        activeThumb.scrollIntoView({behavior: "smooth", inline: "center", block: "nearest"});
                    }
                }

                const newHash = '#img-' + (parseInt(target.getAttribute('data-id'))+1);
                if(<?php echo $deep ? 'true' : 'false'; ?>) history.replaceState(null, null, newHash);
                
                const currentUrl = window.location.href.split('#')[0] + newHash;
                if(document.getElementById('cg-s-wa')) document.getElementById('cg-s-wa').onclick = () => window.open('https://wa.me/?text=' + encodeURIComponent(currentUrl));
                if(document.getElementById('cg-s-fb')) document.getElementById('cg-s-fb').onclick = () => window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(currentUrl));
                if(document.getElementById('cg-s-cp')) document.getElementById('cg-s-cp').onclick = () => { navigator.clipboard.writeText(currentUrl); alert('Link copiato!'); };

            }, 10);
        }

        function resetZoom() { 
            const media = cont.querySelector('.cg-img');
            if(media) { media.classList.remove('zoomed'); media.style.transformOrigin = 'center center'; }
        }
        function toggleZoom() { 
            const media = cont.querySelector('.cg-img');
            if(media) media.classList.toggle('zoomed'); 
        }
        
        cont.addEventListener('mousemove', function(e) { 
            const media = cont.querySelector('.cg-img');
            if(!media || !media.classList.contains('zoomed')) return; 
            const x=(e.clientX/window.innerWidth)*100; const y=(e.clientY/window.innerHeight)*100; media.style.transformOrigin = `${x}% ${y}%`; 
        });
        
        const zBtn = lb.querySelector('.cg-zoom-btn'); 
        if(zBtn) zBtn.addEventListener('click', e => { e.stopPropagation(); toggleZoom(); });

        document.body.addEventListener('click', function(e) {
            const t = e.target.closest('.cg-trigger');
            if (t) {
                e.preventDefault();
                allTriggers = Array.from(document.querySelectorAll('.cg-trigger')).sort((a,b) => parseInt(a.getAttribute('data-id')) - parseInt(b.getAttribute('data-id')));
                const visible = getVisibleTriggers();
                if(strip) buildFilmstrip(visible); 
                const idx = visible.indexOf(t);
                if(idx !== -1) {
                    upd(idx);
                    lb.classList.add('on');
                    document.body.style.overflow='hidden';
                }
            }
        });

        lb.querySelector('.cg-next').addEventListener('click', e=>{
            e.stopPropagation(); 
            const media = cont.querySelector('.cg-img');
            if(!media || !media.classList.contains('zoomed')) upd(currIndex+1);
        });
        lb.querySelector('.cg-prev').addEventListener('click', e=>{
            e.stopPropagation(); 
            const media = cont.querySelector('.cg-img');
            if(!media || !media.classList.contains('zoomed')) upd(currIndex-1);
        });
        
        const close = ()=>{ 
            lb.classList.remove('on'); document.body.style.overflow=''; 
            const m = cont.querySelector('.cg-img, .cg-video-lb'); if(m) m.remove();
            if(document.fullscreenElement) document.exitFullscreen(); 
            if(<?php echo $deep ? 'true' : 'false'; ?>) history.replaceState(null, null, ' '); 
        };
        lb.querySelector('.cg-close').addEventListener('click', close);
        lb.addEventListener('click', e=>{if(e.target===lb||e.target.classList.contains('cg-in')) close()});
        const fsBtn = lb.querySelector('.cg-full'); if(fsBtn) fsBtn.addEventListener('click', e => { e.stopPropagation(); if(!document.fullscreenElement) lb.requestFullscreen().catch(e=>{}); else document.exitFullscreen(); });

        document.addEventListener('keydown', e=>{
            if(!lb.classList.contains('on')) return;
            const media = cont.querySelector('.cg-img');
            if(e.key==='ArrowRight') { if(!media || !media.classList.contains('zoomed')) upd(currIndex+1); }
            if(e.key==='ArrowLeft') { if(!media || !media.classList.contains('zoomed')) upd(currIndex-1); }
            if(e.key==='Escape') close();
        });

        let sX=0;
        lb.addEventListener('touchstart', e=>{sX=e.changedTouches[0].screenX}, {passive:true});
        lb.addEventListener('touchend', e=>{
            const media = cont.querySelector('.cg-img');
            if(media && media.classList.contains('zoomed')) return;
            if(sX - e.changedTouches[0].screenX > 50) upd(currIndex+1);
            if(e.changedTouches[0].screenX - sX > 50) upd(currIndex-1);
        }, {passive:true});

        if(<?php echo $deep ? 'true' : 'false'; ?>) {
            const hash = window.location.hash;
            if(hash && hash.startsWith('#img-')) {
                const id = parseInt(hash.replace('#img-', '')) - 1;
                const target = document.querySelector('.cg-trigger[data-id="'+id+'"]');
                if(target) target.click();
            }
        }
    });
    </script>
    <?php
}
