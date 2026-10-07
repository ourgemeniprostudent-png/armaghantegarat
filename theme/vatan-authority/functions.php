<?php
if (!defined('ABSPATH')) exit;
add_action('after_setup_theme', function () {
    load_theme_textdomain('vatan-authority', get_template_directory() . '/languages');
    add_theme_support('title-tag'); add_theme_support('post-thumbnails');
    add_theme_support('custom-logo',['height'=>160,'width'=>160,'flex-height'=>true,'flex-width'=>true]);
    add_theme_support('html5', ['search-form','gallery','caption','style','script']);
    register_nav_menus(['primary'=>'منوی اصلی','footer'=>'منوی پایین سایت']);
});
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('vatan-theme', get_stylesheet_uri(), [], filemtime(get_template_directory().'/style.css'));
    wp_enqueue_style('armaghan-video-hero', vatan_asset('video-hero.css'), ['vatan-theme'], filemtime(get_template_directory().'/assets/video-hero.css'));
    wp_enqueue_script('vatan-site', get_template_directory_uri().'/assets/site.js', [], filemtime(get_template_directory().'/assets/site.js'), true);
    wp_enqueue_script('armaghan-video-hero', vatan_asset('video-hero.js'), ['vatan-site'], filemtime(get_template_directory().'/assets/video-hero.js'), true);
    if (is_front_page()) {
        wp_enqueue_style('armaghan-product-revolver', vatan_asset('product-revolver.css'), ['armaghan-video-hero'], filemtime(get_template_directory().'/assets/product-revolver.css'));
        wp_enqueue_script('armaghan-product-revolver', vatan_asset('product-revolver.js'), ['vatan-site'], filemtime(get_template_directory().'/assets/product-revolver.js'), true);
        wp_enqueue_style('armaghan-cooperation-gallery', vatan_asset('cooperation-gallery.css'), ['armaghan-product-revolver'], filemtime(get_template_directory().'/assets/cooperation-gallery.css'));
        wp_enqueue_script('armaghan-cooperation-gallery', vatan_asset('cooperation-gallery.js'), ['armaghan-product-revolver'], filemtime(get_template_directory().'/assets/cooperation-gallery.js'), true);
        wp_enqueue_style('armaghan-home-story', vatan_asset('home-story.css'), ['armaghan-cooperation-gallery'], filemtime(get_template_directory().'/assets/home-story.css'));
        wp_enqueue_script('armaghan-home-story', vatan_asset('home-story.js'), ['vatan-site'], filemtime(get_template_directory().'/assets/home-story.js'), true);
    }
});
function vatan_url($path='') { return home_url('/'.ltrim($path,'/')); }
function vatan_asset($file) { return get_template_directory_uri().'/assets/'.$file; }
function armaghan_video_asset($file) { return add_query_arg('v', filemtime(get_template_directory().'/assets/media/'.$file), vatan_asset('media/'.$file)); }
function vatan_option($name,$default='') { return get_option('vatan_'.$name,$default); }
function vatan_categories() { return ['coffee'=>['قهوه','COFFEE','انتخاب قهوه برای کافه و کسب‌وکار شما'], 'rice'=>['برنج','RICE','برای نیاز روزانه و سفارش عمده'], 'dried-fruits'=>['خشکبار','NUTS & DRIED FRUITS','آجیل و خشکبار برای کسب‌وکارها'], 'spices'=>['ادویه','SPICES','طعم و عطر، متناسب با نیاز شما'], 'legumes'=>['حبوبات','LEGUMES','استعلام تامین و سفارش عمده']]; }
function vatan_logo() { if(has_custom_logo()){echo '<span class="custom-brand-mark">'.wp_get_attachment_image(get_theme_mod('custom_logo'),'thumbnail',false,['alt'=>'نشان ارمغان تجارت وطن']).'</span>';}else{ ?><span class="brand-mark"><img src="<?php echo esc_url(vatan_asset('brand-original.png')); ?>" alt="نشان طلایی ارمغان تجارت وطن" width="499" height="1080"></span><?php } ?><span class="brand-name">ارمغان تجارت وطن</span><?php }
function vatan_intro($title,$description='',$eyebrow='ارمغان تجارت وطن') { ?><section class="page-intro"><div class="wrap"><div class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">خانه</a> / <?php echo esc_html($title); ?></div><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><h1><?php echo esc_html($title); ?></h1><?php if($description) echo '<p>'.esc_html($description).'</p>'; ?></div></section><?php }
function vatan_empty($title,$text,$category='') { ?><div class="empty"><h2><?php echo esc_html($title); ?></h2><p><?php echo esc_html($text); ?></p><a class="button" href="<?php echo esc_url(add_query_arg('category',$category,vatan_url('inquiry/'))); ?>">درخواست مشاوره</a></div><?php }
function vatan_product_card($post) { $terms=get_the_terms($post->ID,'vatan_category'); ?><article class="product-card"><?php if(has_post_thumbnail($post)) echo get_the_post_thumbnail($post,'large',['class'=>'product-image']); ?><span class="eyebrow"><?php echo esc_html($terms&&!is_wp_error($terms)?$terms[0]->name:'محصول'); ?></span><h2><a href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html(get_the_title($post)); ?></a></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt($post),24)); ?></p><a class="text-link" href="<?php echo esc_url(get_permalink($post)); ?>">مشخصات و استعلام</a></article><?php }
add_action('wp_head',function(){
    $icon='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="3" fill="#080909"/><path d="M7 8l9 17L25 8h-4l-5 10-5-10z" fill="#c7a065"/></svg>';
    echo '<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,'.esc_attr(rawurlencode($icon)).'">';
    $desc=is_front_page()?'ارمغان تجارت وطن؛ قهوه، برنج، خشکبار، ادویه و حبوبات. معرفی محصولات و درخواست مشاوره و استعلام.':wp_strip_all_tags(get_the_excerpt());
    if(is_tax('vatan_category')){$term=get_queried_object();$desc='معرفی گروه '.$term->name.' در وطن؛ درخواست مشاوره و استعلام شرایط تامین.';}
    if(!$desc&&is_singular())$desc=get_the_title().' | ارمغان تجارت وطن؛ معرفی محصولات، گفتگو و درخواست همکاری.';
    if($desc) echo '<meta name="description" content="'.esc_attr(wp_trim_words($desc,40)).'">';
    echo '<meta property="og:title" content="'.esc_attr(wp_get_document_title()).'"><meta property="og:type" content="'.(is_singular('post')?'article':'website').'">';
    if($desc)echo '<meta property="og:description" content="'.esc_attr(wp_trim_words($desc,40)).'">';
    if(is_front_page()) echo '<script type="application/ld+json">'.wp_json_encode(['@context'=>'https://schema.org','@type'=>'Organization','name'=>'شرکت ارمغان تجارت وطن','url'=>home_url('/'),'telephone'=>vatan_option('phone','02191028166'),'address'=>['@type'=>'PostalAddress','streetAddress'=>vatan_option('address','تهران، سهروردی شمالی، کوچه زمانی، پلاک ۱۱، ساختمان ایلیا، طبقه ۳، واحد ۹'),'addressLocality'=>'تهران','addressCountry'=>'IR']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
    if(is_singular('vatan_product')){$product=['@context'=>'https://schema.org','@type'=>'Product','name'=>get_the_title(),'description'=>wp_strip_all_tags(get_the_excerpt()),'url'=>get_permalink()];if(has_post_thumbnail())$product['image']=get_the_post_thumbnail_url(null,'large');echo '<script type="application/ld+json">'.wp_json_encode($product,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';}
});
add_filter('wp_robots',function($robots){if(is_page(['inquiry','thank-you','search'])){$robots['noindex']=true;unset($robots['index']);}return $robots;});
add_action('customize_register',function($customizer){
 $customizer->add_section('vatan_home',['title'=>'صفحه اصلی ارمغان تجارت وطن','priority'=>30]);
 foreach(['trade_line_one'=>['تیتر اصلی، خط اول','از مبدا،'],'trade_line_two'=>['تیتر اصلی، خط دوم','تا بازار ایران.'],'trade_description'=>['توضیح اصلی','واردات مستقیم و تامین عمده قهوه، برنج، خشکبار، ادویه و حبوبات؛ برای بازار و کسب‌وکار ایران.']] as $key=>$args){$customizer->add_setting('vatan_'.$key,['default'=>$args[1],'sanitize_callback'=>'sanitize_text_field']);$customizer->add_control('vatan_'.$key,['label'=>$args[0],'section'=>'vatan_home','type'=>'text']);}
});

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
