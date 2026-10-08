<?php get_header();$identity=ag_section('identity','global'); ?>
<div class="ag-interior ag-art-page"><section class="ag-error-art">
<?php ag_art_motif(); ?>
<div class="wrap"><div class="error-number" aria-hidden="true">۴۰۴</div><h1><?php ag_text($identity,'error_title'); ?></h1><p><?php ag_text($identity,'error_body'); ?></p><div class="actions"><a class="button" href="<?php echo esc_url(home_url('/')); ?>"><?php ag_text($identity,'error_home'); ?></a><a class="home-route-link" href="<?php echo esc_url(vatan_url('products/')); ?>"><?php ag_text($identity,'error_products'); ?> <span class="ag-symbol" aria-hidden="true"><svg class="ag-arrow-icon" viewBox="0 0 24 24" fill="none" width="1em" height="1em" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.3"/></svg></span></a></div></div>
</section></div>
<?php get_footer(); ?>
