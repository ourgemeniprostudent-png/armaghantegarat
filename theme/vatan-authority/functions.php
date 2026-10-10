<?php
if (!defined('ABSPATH')) exit;
require_once __DIR__.'/inc/sections.php';
require_once __DIR__.'/inc/art-direction.php';
require_once __DIR__.'/inc/public-search.php';
require_once __DIR__.'/inc/search-page.php';
require_once __DIR__.'/inc/capabilities.php';
require_once __DIR__.'/inc/seo.php';
require_once __DIR__.'/inc/breadcrumbs.php';
require_once __DIR__.'/inc/contact-art.php';
require_once __DIR__.'/inc/authority-edition.php';
require_once __DIR__.'/inc/about-editorial.php';
require_once __DIR__.'/inc/approved-interiors.php';
require_once __DIR__.'/inc/media-library.php';
require_once __DIR__.'/inc/media-studio.php';
require_once __DIR__.'/inc/media-hub.php';
require_once __DIR__.'/inc/responsive-images.php';
require_once __DIR__.'/inc/brand-accent.php';
require_once __DIR__.'/inc/appearance.php';
add_filter('body_class',function($classes){if(!vatan_feature('animations'))$classes[]='ag-static-motion';return $classes;});
add_action('after_setup_theme', function () {
    load_theme_textdomain('vatan-authority', get_template_directory() . '/languages');
    add_theme_support('title-tag'); add_theme_support('post-thumbnails');
    add_theme_support('custom-logo',['height'=>160,'width'=>160,'flex-height'=>true,'flex-width'=>true]);
    add_theme_support('html5', ['search-form','gallery','caption','style','script']);
    register_nav_menus(['primary'=>'منوی اصلی','footer'=>'فوتر: دسترسی سریع','footer_products'=>'فوتر: حوزه‌های محصول']);
});
add_action('wp_enqueue_scripts', function () {
    $bundle=is_front_page()?'site-home.css':(is_page(['products','solutions'])?'site-gallery.css':'site-interior.css');
    wp_enqueue_style('armaghan-site',vatan_asset($bundle),[],filemtime(get_template_directory().'/assets/'.$bundle));

    wp_enqueue_script('armaghan-interior', vatan_asset('interior.js'), ['vatan-site'], filemtime(get_template_directory().'/assets/interior.js'), true);
    if(!is_front_page()) {

        wp_enqueue_script('armaghan-art', vatan_asset('art-direction.js'), ['armaghan-interior'], filemtime(get_template_directory().'/assets/art-direction.js'), true);
    }

    wp_enqueue_script('armaghan-capabilities', vatan_asset('capabilities.js'), ['armaghan-interior'], filemtime(get_template_directory().'/assets/capabilities.js'), true);
    wp_enqueue_script('armaghan-media',vatan_asset('media-library.js'),['armaghan-capabilities'],filemtime(get_template_directory().'/assets/media-library.js'),true);


    wp_enqueue_script('vatan-site', get_template_directory_uri().'/assets/site.js', [], filemtime(get_template_directory().'/assets/site.js'), true);
    wp_enqueue_script('armaghan-video-hero', vatan_asset('video-hero.js'), ['vatan-site'], filemtime(get_template_directory().'/assets/video-hero.js'), true);
    if (is_front_page() || is_page(['products','solutions'])) {

        wp_enqueue_script('armaghan-product-revolver', vatan_asset('product-revolver.js'), ['vatan-site'], filemtime(get_template_directory().'/assets/product-revolver.js'), true);

        wp_enqueue_script('armaghan-cooperation-gallery', vatan_asset('cooperation-gallery.js'), ['armaghan-product-revolver'], filemtime(get_template_directory().'/assets/cooperation-gallery.js'), true);
    }

    if(is_home()||is_page(['contact','inquiry'])||is_singular('post')){wp_enqueue_script('armaghan-approved-validation',vatan_asset('approved-validation.js'),['armaghan-capabilities'],filemtime(get_template_directory().'/assets/approved-validation.js'),true);wp_enqueue_script('armaghan-approved',vatan_asset('approved-interiors.js'),['armaghan-approved-validation'],filemtime(get_template_directory().'/assets/approved-interiors.js'),true);}
    if(is_front_page())wp_enqueue_script('armaghan-home-story', vatan_asset('home-story.js'), ['vatan-site'], filemtime(get_template_directory().'/assets/home-story.js'), true);
});
function vatan_url($path='') { return home_url('/'.ltrim($path,'/')); }
function vatan_asset($file) { return get_template_directory_uri().'/assets/'.$file; }
function armaghan_video_asset($file) { return add_query_arg('v', filemtime(get_template_directory().'/assets/media/'.$file), vatan_asset('media/'.$file)); }
function vatan_option($name,$default='') { return get_option('vatan_'.$name,$default); }
function vatan_categories() { return ['coffee'=>['قهوه','COFFEE','انتخاب قهوه برای کافه و کسب‌وکار شما'], 'rice'=>['برنج','RICE','برای نیاز روزانه و سفارش عمده'], 'dried-fruits'=>['خشکبار','NUTS & DRIED FRUITS','آجیل و خشکبار برای کسب‌وکارها'], 'spices'=>['ادویه','SPICES','طعم و عطر، متناسب با نیاز شما'], 'legumes'=>['حبوبات','LEGUMES','استعلام تامین و سفارش عمده']]; }
function vatan_logo() {
 $identity=ag_section('identity','global');$saved=get_post_meta((int)get_option('page_on_front'),'_armaghan_identity',true);
 if(isset($saved['identity']['logo'])&&$saved['identity']['logo']!=='asset:brand-original.png')echo '<span class="custom-brand-mark"><img src="'.esc_url(ag_media_url($identity['logo'])).'" alt="'.esc_attr($identity['title']).'"></span>';
 elseif(has_custom_logo())echo '<span class="custom-brand-mark">'.wp_get_attachment_image(get_theme_mod('custom_logo'),'thumbnail',false,['alt'=>$identity['title']]).'</span>';
 else echo '<span class="brand-mark"><img '.ag_image_attrs(vatan_asset('brand-original.png'),'135px',240).' alt="نشان طلایی ارمغان تجارت وطن"></span>';
 echo '<span class="brand-name">'.esc_html($identity['title']).'</span>';
}
function vatan_intro($title,$description='',$eyebrow='ارمغان تجارت وطن') { ?><section class="page-intro"><div class="wrap"><?php ag_breadcrumb($title); ?><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><h1><?php echo esc_html($title); ?></h1><?php if($description) echo '<p>'.esc_html($description).'</p>'; ?></div></section><?php }
function vatan_empty($title,$text,$category='') { ?><div class="empty"><h2><?php echo esc_html($title); ?></h2><p><?php echo esc_html($text); ?></p><a class="button" href="<?php echo esc_url(add_query_arg('category',$category,vatan_url('inquiry/'))); ?>">درخواست مشاوره</a></div><?php }
function vatan_product_card($post) { $terms=get_the_terms($post->ID,'vatan_category'); ?><article class="product-card"><?php if(has_post_thumbnail($post)) echo get_the_post_thumbnail($post,'large',['class'=>'product-image']); ?><span class="eyebrow"><?php echo esc_html($terms&&!is_wp_error($terms)?$terms[0]->name:'محصول'); ?></span><h2><a href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html(get_the_title($post)); ?></a></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt($post),24)); ?></p><a class="text-link" href="<?php echo esc_url(get_permalink($post)); ?>">مشخصات و استعلام</a></article><?php }
add_action('wp_head',function(){foreach(['Regular','Bold'] as $weight)echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="'.esc_url(vatan_asset('fonts/PeydaWebFaNum-'.$weight.'.woff2')).'">';},1);
add_action('wp_head',function(){
    $icon='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="3" fill="#080909"/><path d="M7 8l9 17L25 8h-4l-5 10-5-10z" fill="#c7a065"/></svg>';
    echo '<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,'.esc_attr(rawurlencode($icon)).'">';

});
add_filter('wp_robots',function($robots){if(is_page(['inquiry','thank-you','search'])){$robots['noindex']=true;unset($robots['index']);}return $robots;});
// Keep Persian display copy free of hamza while leaving links and stored inquiries intact.
function armaghan_copy_without_hamza($text) {
    if (!is_string($text)) return $text;
    static $letters = [
        "\u{0623}"=>'ا', "\u{0625}"=>'ا', "\u{0624}"=>'و', "\u{0626}"=>'ی',
        "\u{0621}"=>'', "\u{0654}"=>'', "\u{0655}"=>'', "\u{0674}"=>'',
        "\u{06C0}"=>'ه', "\u{06C2}"=>'ه', "\u{0672}"=>'ا', "\u{0673}"=>'ا',
        "\u{0675}"=>'ا', "\u{0676}"=>'و', "\u{0678}"=>'ی'
    ];
    $chunks = wp_html_split($text);
    foreach ($chunks as $i => $chunk) {
        if ($i % 2) continue;
        $chunk = strtr($chunk, $letters);
        $chunks[$i] = preg_replace_callback('/&#(?:x([0-9a-f]+)|([0-9]+));/i', function($match) use($letters) {
            $char = html_entity_decode($match[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return array_key_exists($char, $letters) ? $letters[$char] : $match[0];
        }, $chunk);
    }
    return implode('', $chunks);
}
foreach (['the_title','the_content','get_the_excerpt','gettext','gettext_with_context','ngettext','ngettext_with_context','esc_html','single_term_title','term_description'] as $copy_filter) {
    add_filter($copy_filter, 'armaghan_copy_without_hamza', 99);
}
add_filter('document_title_parts', function($parts) { return array_map('armaghan_copy_without_hamza', $parts); }, 99);
add_filter('bloginfo', function($value, $show) { return in_array($show, ['name','description'], true) ? armaghan_copy_without_hamza($value) : $value; }, 99, 2);
add_filter('wp_get_attachment_image_attributes', function($attrs) {
    foreach (['alt','title'] as $key) if(isset($attrs[$key])) $attrs[$key] = armaghan_copy_without_hamza($attrs[$key]);
    return $attrs;
}, 99);
