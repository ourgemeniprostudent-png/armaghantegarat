<?php
if (!defined('ABSPATH')) exit;
function vatan_inquiry_form(){
    $s=armaghan_section_data('inquiry')['form']['values'];$errors=[];$data=[];
    $token=isset($_GET['form_error'])&&!is_array($_GET['form_error'])?sanitize_text_field(wp_unslash($_GET['form_error'])):'';
    if(preg_match('/^[a-zA-Z0-9]{40}$/D',$token)){$stored=get_transient('vatan_error_'.$token);if(is_array($stored)){$errors=$stored['errors'];$data=$stored['data'];}}
    $cat=isset($_GET['category'])&&!is_array($_GET['category'])?sanitize_key($_GET['category']):'';
    if(!isset(vatan_core_categories()[$cat]))$cat='';if(!isset($data['category']))$data['category']=$cat;
    if(empty($data['product'])&&isset($_GET['product'])&&!is_array($_GET['product']))$data['product']=mb_substr(sanitize_text_field(wp_unslash($_GET['product'])),0,150);
    $hint=function($key)use($s){if(!empty($s[$key.'_hint']))echo '<p class="ag-field-hint" id="hint-'.esc_attr($key).'">'.esc_html($s[$key.'_hint']).'</p>';};
    $error=function($key)use($errors){if(isset($errors[$key]))echo '<p class="field-error" id="error-'.esc_attr($key).'">'.esc_html($errors[$key]).'</p>';};
    $describe=function($key)use($errors,$s){$ids=[];if(!empty($s[$key.'_hint']))$ids[]='hint-'.$key;if(isset($errors[$key]))$ids[]='error-'.$key;return $ids?' aria-describedby="'.esc_attr(implode(' ',$ids)).'"':'';};
    $field=function($key,$type='text',$required=false,$attrs='')use($data,$errors,$s,$hint,$error,$describe){
        echo '<div class="field"><label for="vatan-'.esc_attr($key).'">'.esc_html($s[$key.'_label']).($required?' <span class="required">*</span>':'').'</label><input id="vatan-'.esc_attr($key).'" name="'.esc_attr($key).'" type="'.esc_attr($type).'" value="'.esc_attr($data[$key]??'').'" '.($required?'required ':'').$attrs.$describe($key).(isset($errors[$key])?' aria-invalid="true"':'').'>';$hint($key);$error($key);echo '</div>';
    };
    $select=function($key,$options,$required=false)use($data,$errors,$s,$error,$describe){
        echo '<div class="field"><label for="vatan-'.esc_attr($key).'">'.esc_html($s[$key.'_label']).($required?' <span class="required">*</span>':'').'</label><select id="vatan-'.esc_attr($key).'" name="'.esc_attr($key).'" '.($required?'required ':'').$describe($key).(isset($errors[$key])?' aria-invalid="true"':'').'><option value="">'.esc_html($s['select_label']).'</option>';
        foreach($options as $value=>$label)echo '<option value="'.esc_attr($value).'" '.selected($data[$key]??'',$value,false).'>'.esc_html($label).'</option>';echo '</select>';$error($key);echo '</div>';
    };
    ob_start();
    if($errors){echo '<div class="form-status" role="alert" tabindex="-1"><strong>درخواست هنوز ثبت نشده است.</strong><p>اطلاعات واردشده حفظ شده؛ موارد زیر را اصلاح کنید.</p><ul>';foreach($errors as $key=>$message)echo '<li><a href="#'.esc_attr($key==='general'?'inquiry-form':'vatan-'.$key).'">'.esc_html($message).'</a></li>';echo '</ul></div>';}
    $request_key=isset($data['request_key'])&&preg_match('/^[a-zA-Z0-9]{32,64}$/D',$data['request_key'])?$data['request_key']:wp_generate_password(40,false,false);
    ?>
    <form id="inquiry-form" data-inquiry-form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="vatan_inquiry"><?php wp_nonce_field('vatan_inquiry','vatan_nonce'); ?>
    <input type="hidden" name="request_key" value="<?php echo esc_attr($request_key); ?>"><input type="hidden" name="source_url" value="<?php echo esc_url($data['source_url']??(wp_get_referer()?:home_url('/inquiry/'))); ?>">
    <div class="honeypot" aria-hidden="true"><label>وب‌سایت<input name="website" tabindex="-1" autocomplete="off"></label></div>
    <nav class="ag-form-progress" aria-label="مراحل درخواست"><?php for($i=0;$i<3;$i++)echo '<button type="button" data-form-go="'.$i.'"><span>'.esc_html(strtr((string)($i+1),['1'=>'۰۱','2'=>'۰۲','3'=>'۰۳'])).'</span>'.esc_html($s['step_'.($i+1)]).'</button>'; ?></nav>
    <fieldset class="ag-form-step" data-form-step="0"><legend tabindex="-1"><?php echo esc_html($s['step_1']); ?></legend><div class="form-grid">
    <?php $select('category',vatan_core_categories(),true);$field('product','text',false,'maxlength="150"');$field('quantity','text',false,'maxlength="150"'); ?>
    <div class="field full"><label for="vatan-notes"><?php echo esc_html($s['notes_label']); ?></label><textarea name="notes" id="vatan-notes" maxlength="1000" <?php echo $describe('notes'); ?> <?php if(isset($errors['notes']))echo 'aria-invalid="true"'; ?>><?php echo esc_textarea($data['notes']??''); ?></textarea><?php $hint('notes');$error('notes'); ?></div>
    </div></fieldset>
    <fieldset class="ag-form-step" data-form-step="1"><legend tabindex="-1"><?php echo esc_html($s['step_2']); ?></legend><div class="form-grid">
    <?php $field('name','text',true,'autocomplete="name" minlength="2" maxlength="80"');$field('mobile','tel',true,'autocomplete="tel" inputmode="tel" dir="ltr" maxlength="20"');$field('company','text',false,'autocomplete="organization" maxlength="150"');$field('city','text',true,'autocomplete="address-level2" minlength="2" maxlength="80"');$select('customer_type',vatan_customer_types(),true);$select('preferred_time',['morning'=>$s['morning_label'],'noon'=>$s['noon_label'],'afternoon'=>$s['afternoon_label']]); ?>
    </div></fieldset>
    <fieldset class="ag-form-step" data-form-step="2"><legend tabindex="-1"><?php echo esc_html($s['step_3']); ?></legend>
    <div class="ag-review"><h3><?php echo esc_html($s['review_title']); ?></h3><p><?php echo esc_html($s['review_body']); ?></p><dl data-form-review></dl></div>
    <div class="field full"><label class="consent" for="vatan-consent"><input type="checkbox" id="vatan-consent" name="consent" value="1" required <?php checked($data['consent']??'','1'); ?> <?php echo $describe('consent'); ?> <?php if(isset($errors['consent']))echo 'aria-invalid="true"'; ?>><span><?php echo esc_html($s['consent_label']); ?> <a class="text-link" href="<?php echo esc_url(home_url('/privacy/')); ?>"><?php echo esc_html($s['privacy_label']); ?></a> <span class="required">*</span></span></label><?php $error('consent'); ?></div>
    </fieldset>
    <div class="ag-form-controls"><button class="button ghost" type="button" data-form-back><?php echo esc_html($s['back_label']); ?></button><button class="button" type="button" data-form-next><?php echo esc_html($s['next_label']); ?> <span aria-hidden="true">←</span></button><button class="button" type="submit"><?php echo esc_html($s['submit_label']); ?></button></div>
    <p class="ag-form-note"><?php echo esc_html($s['note']); ?></p>
    </form>
    <?php return ob_get_clean();
}
