<?php
/**
 * Plugin Name: WP Kandoo Mobile
 * Description: Professional mobile experience builder with live device preview, mobile overrides, custom header and bottom navigation.
 * Version: 1.0.0
 * Author: WP Kandoo
 * Text Domain: wp-kandoo-mobile
 */

if (!defined('ABSPATH')) exit;

final class WP_Kandoo_Mobile {
    const VERSION = '1.0.0';
    const OPTION = 'wpkm_settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_head', [$this, 'frontend_css'], 999);
        add_action('wp_footer', [$this, 'frontend_bottom_nav'], 999);
        add_action('wp_ajax_wpkm_save', [$this, 'ajax_save']);
        add_action('wp_ajax_wpkm_reset', [$this, 'ajax_reset']);
    }

    public static function activate() {
        if (get_option(self::OPTION) === false) {
            add_option(self::OPTION, self::defaults());
        }
    }

    public static function defaults() {
        return [
            'enabled' => 1,
            'breakpoint' => 767,
            'mobile_css' => '',
            'hide_selectors' => '',
            'header' => [
                'enabled' => 0,
                'height' => 56,
                'sticky' => 0,
                'logo_url' => '',
                'menu_label' => 'منو',
            ],
            'bottom_nav' => [
                'enabled' => 0,
                'items' => [
                    ['label' => 'خانه', 'url' => home_url('/'), 'icon' => '⌂'],
                    ['label' => 'جستجو', 'url' => '#', 'icon' => '⌕'],
                    ['label' => 'دسته‌بندی', 'url' => '#', 'icon' => '☷'],
                    ['label' => 'سبد خرید', 'url' => '#', 'icon' => '🛒'],
                    ['label' => 'حساب', 'url' => '#', 'icon' => '◉'],
                ],
            ],
        ];
    }

    private function settings() {
        return wp_parse_args(get_option(self::OPTION, []), self::defaults());
    }

    public function admin_menu() {
        add_menu_page(
            'Kandoo Mobile',
            'Kandoo Mobile',
            'manage_options',
            'wpkm-mobile',
            [$this, 'render_admin'],
            'dashicons-smartphone',
            58
        );
    }

    public function admin_assets($hook) {
        if ($hook !== 'toplevel_page_wpkm-mobile') return;
        wp_enqueue_style('dashicons');
        wp_add_inline_style('dashicons', '');
        wp_enqueue_script('jquery');
    }

    private function admin_css() {
        return <<<'CSS'
.wpkm-wrap{margin:20px 20px 0 0;direction:rtl;font-family:inherit}
.wpkm-shell{display:grid;grid-template-columns:340px minmax(420px,1fr);gap:18px;min-height:calc(100vh - 90px)}
.wpkm-panel,.wpkm-preview{background:#fff;border:1px solid #e7e9ee;border-radius:18px;box-shadow:0 8px 30px rgba(15,23,42,.06)}
.wpkm-panel{padding:18px;overflow:auto;max-height:calc(100vh - 110px)}
.wpkm-brand{display:flex;align-items:center;gap:12px;padding:6px 2px 18px;border-bottom:1px solid #eef0f4;margin-bottom:16px}
.wpkm-logo{width:42px;height:42px;border-radius:13px;background:#00C896;color:#fff;display:grid;place-items:center;font-weight:800;font-size:19px}
.wpkm-brand strong{font-size:17px}.wpkm-brand small{display:block;color:#7b8494;margin-top:2px}
.wpkm-tabs{display:flex;gap:6px;overflow:auto;margin-bottom:14px}.wpkm-tab{border:0;background:#f4f6f8;padding:9px 12px;border-radius:10px;cursor:pointer;white-space:nowrap}.wpkm-tab.active{background:#00C896;color:#fff}
.wpkm-section{border:1px solid #edf0f4;border-radius:14px;padding:14px;margin-bottom:12px}.wpkm-section h3{margin:0 0 12px;font-size:14px}.wpkm-field{margin:10px 0}.wpkm-field label{display:block;font-size:12px;font-weight:600;margin-bottom:6px}.wpkm-field input,.wpkm-field textarea,.wpkm-field select{width:100%;box-sizing:border-box;border:1px solid #dfe3e8;border-radius:9px;padding:8px 10px;background:#fff}.wpkm-field textarea{min-height:90px;font-family:monospace;direction:ltr;text-align:left}
.wpkm-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}.wpkm-actions{display:flex;gap:8px;position:sticky;bottom:-18px;background:#fff;padding:14px 0 2px}.wpkm-btn{border:0;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:700}.wpkm-primary{background:#00C896;color:#fff}.wpkm-secondary{background:#eef1f4;color:#243044}.wpkm-danger{background:#fff0f0;color:#c33}
.wpkm-preview{display:flex;flex-direction:column;overflow:hidden;background:#f3f5f7}.wpkm-toolbar{display:flex;align-items:center;gap:8px;padding:12px 14px;background:#fff;border-bottom:1px solid #e7e9ee}.wpkm-toolbar select,.wpkm-toolbar input{border:1px solid #dfe3e8;border-radius:9px;padding:7px}.wpkm-spacer{flex:1}.wpkm-stage{flex:1;display:grid;place-items:center;overflow:auto;padding:24px}.wpkm-device{background:#111827;border:8px solid #111827;border-radius:34px;box-shadow:0 20px 55px rgba(15,23,42,.28);transition:.2s;width:390px;height:780px;max-width:90%;position:relative}.wpkm-device iframe{width:100%;height:100%;border:0;border-radius:25px;background:#fff}.wpkm-device.landscape{width:780px;height:390px}.wpkm-status{font-size:12px;color:#7b8494}.wpkm-item{display:grid;grid-template-columns:54px 1fr 70px;gap:6px;align-items:center;margin-bottom:7px}.wpkm-item input{width:100%;box-sizing:border-box}.wpkm-item button{border:0;background:#fff0f0;color:#c33;border-radius:8px;padding:7px;cursor:pointer}
@media(max-width:1000px){.wpkm-shell{grid-template-columns:1fr}.wpkm-panel{max-height:none}.wpkm-preview{min-height:720px}}
CSS;
    }

    private function admin_js() {
        return <<<'JS'
(function($){
'use strict';
const data=window.WPKM||{};
let settings=data.settings||{};
const $iframe=()=>$('#wpkm-frame');
function applyPreview(){
 const f=$iframe()[0]; if(!f||!f.contentDocument) return;
 const d=f.contentDocument;
 let s=d.getElementById('wpkm-live-preview-style');
 if(!s){s=d.createElement('style');s.id='wpkm-live-preview-style';d.head.appendChild(s);}
 let css='@media(max-width:'+settings.breakpoint+'px){'+(settings.mobile_css||'');
 (settings.hide_selectors||'').split('\n').map(x=>x.trim()).filter(Boolean).forEach(x=>css+=x+'{display:none!important;}');
 if(settings.header&&settings.header.enabled) css+='body{padding-top:'+parseInt(settings.header.height||56,10)+'px!important;}';
 if(settings.bottom_nav&&settings.bottom_nav.enabled) css+='body{padding-bottom:72px!important;}';
 css+='}';
 s.textContent=css;
 let old=d.getElementById('wpkm-live-header'); if(old) old.remove();
 if(settings.header&&settings.header.enabled){
   const h=d.createElement('div');h.id='wpkm-live-header';h.style.cssText='position:fixed;z-index:2147483000;top:0;left:0;right:0;height:'+parseInt(settings.header.height||56,10)+'px;background:#fff;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;padding:0 14px;box-sizing:border-box;direction:rtl;';
   if(settings.header.sticky) h.style.position='fixed';
   h.innerHTML=(settings.header.logo_url?'<img src="'+settings.header.logo_url+'" style="max-height:36px;max-width:130px;object-fit:contain">':'<strong style="font-size:17px">Kandoo</strong>')+'<span style="font-size:22px">☰</span>';
   d.body.appendChild(h);
 }
 let oldNav=d.getElementById('wpkm-live-bottom'); if(oldNav) oldNav.remove();
 if(settings.bottom_nav&&settings.bottom_nav.enabled){
   const n=d.createElement('nav');n.id='wpkm-live-bottom';n.style.cssText='position:fixed;z-index:2147483001;bottom:0;left:0;right:0;height:68px;background:#fff;border-top:1px solid #e5e7eb;display:grid;grid-template-columns:repeat('+settings.bottom_nav.items.length+',1fr);direction:rtl;';
   settings.bottom_nav.items.forEach(it=>{const a=d.createElement('a');a.href=it.url||'#';a.style.cssText='display:flex;flex-direction:column;align-items:center;justify-content:center;text-decoration:none;color:#475569;font-size:11px;gap:2px';a.innerHTML='<span style="font-size:20px;line-height:1">'+(it.icon||'●')+'</span><span>'+escapeHtml(it.label||'')+'</span>';n.appendChild(a);});
   d.body.appendChild(n);
 }
}
function escapeHtml(v){return String(v).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
function collect(){
 settings.enabled=$('#wpkm-enabled').is(':checked')?1:0;
 settings.breakpoint=parseInt($('#wpkm-breakpoint').val(),10)||767;
 settings.mobile_css=$('#wpkm-css').val();
 settings.hide_selectors=$('#wpkm-hide').val();
 settings.header={enabled:$('#wpkm-header-enabled').is(':checked')?1:0,height:parseInt($('#wpkm-header-height').val(),10)||56,sticky:$('#wpkm-header-sticky').is(':checked')?1:0,logo_url:$('#wpkm-logo').val(),menu_label:'منو'};
 settings.bottom_nav=settings.bottom_nav||{enabled:0,items:[]};
 settings.bottom_nav.enabled=$('#wpkm-nav-enabled').is(':checked')?1:0;
 settings.bottom_nav.items=[];
 $('#wpkm-nav-items .wpkm-item').each(function(){settings.bottom_nav.items.push({icon:$(this).find('.nav-icon').val(),label:$(this).find('.nav-label').val(),url:$(this).find('.nav-url').val()});});
}
function renderNav(){
 const box=$('#wpkm-nav-items').empty();
 (settings.bottom_nav.items||[]).forEach((it,i)=>box.append('<div class="wpkm-item"><input class="nav-icon" value="'+escapeHtml(it.icon||'')+'"><input class="nav-label" value="'+escapeHtml(it.label||'')+'"><input class="nav-url" value="'+escapeHtml(it.url||'')+'"></div>'));
}
$(document).on('input change','input,textarea,select',function(){collect();applyPreview();});
$(document).on('click','#wpkm-add-nav',function(){settings.bottom_nav.items.push({icon:'●',label:'جدید',url:'#'});renderNav();applyPreview();});
$(document).on('click','#wpkm-save',function(){
 collect(); const b=$(this).prop('disabled',true).text('در حال ذخیره...');
 $.post(ajaxurl,{action:'wpkm_save',nonce:data.nonce,settings:JSON.stringify(settings)},function(r){b.prop('disabled',false).text('ذخیره تغییرات');$('#wpkm-status').text(r.success?'ذخیره شد':'خطا در ذخیره');},'json');
});
$(document).on('click','#wpkm-reset',function(){if(!confirm('همه تنظیمات Kandoo Mobile به حالت پیش‌فرض برگردد؟'))return;$.post(ajaxurl,{action:'wpkm_reset',nonce:data.nonce},function(r){if(r.success)location.reload();},'json');});
$(document).on('change','#wpkm-device',function(){const v=$(this).val().split('x');$('.wpkm-device').css({width:v[0]+'px',height:v[1]+'px'});});
$(document).on('click','#wpkm-orient',function(){$('.wpkm-device').toggleClass('landscape');});
$(document).on('change','#wpkm-page',function(){$iframe().attr('src',$(this).val());});
$('#wpkm-frame').on('load',function(){applyPreview();$('#wpkm-status').text('پیش‌نمایش زنده');});
renderNav();applyPreview();
})(jQuery);
JS;
    }

    public function render_admin() {
        if (!current_user_can('manage_options')) return;
        $s=$this->settings();
        $pages=get_pages(['post_status'=>['publish','private']]);
        $home=home_url('/');
        echo '<style>'.$this->admin_css().'</style>';
        ?>
        <div class="wrap wpkm-wrap">
          <div class="wpkm-shell">
            <aside class="wpkm-panel">
              <div class="wpkm-brand"><div class="wpkm-logo">K</div><div><strong>WP Kandoo Mobile</strong><small>Mobile Experience Builder</small></div></div>
              <div class="wpkm-section">
                <h3>وضعیت موبایل</h3>
                <label><input type="checkbox" id="wpkm-enabled" <?php checked($s['enabled'],1); ?>> فعال‌سازی تجربه موبایل</label>
                <div class="wpkm-field"><label>Breakpoint</label><input id="wpkm-breakpoint" type="number" min="320" max="1200" value="<?php echo esc_attr($s['breakpoint']); ?>"><small>پیکسل</small></div>
              </div>
              <div class="wpkm-section">
                <h3>Header موبایل</h3>
                <label><input type="checkbox" id="wpkm-header-enabled" <?php checked($s['header']['enabled'],1); ?>> فعال</label>
                <div class="wpkm-row"><div class="wpkm-field"><label>ارتفاع</label><input id="wpkm-header-height" type="number" value="<?php echo esc_attr($s['header']['height']); ?>"></div><div class="wpkm-field"><label>Logo URL</label><input id="wpkm-logo" value="<?php echo esc_attr($s['header']['logo_url']); ?>"></div></div>
                <label><input type="checkbox" id="wpkm-header-sticky" <?php checked($s['header']['sticky'],1); ?>> Sticky</label>
              </div>
              <div class="wpkm-section">
                <h3>Bottom Navigation</h3>
                <label><input type="checkbox" id="wpkm-nav-enabled" <?php checked($s['bottom_nav']['enabled'],1); ?>> نمایش منوی پایین گوشی</label>
                <div id="wpkm-nav-items"></div>
                <button type="button" class="wpkm-btn wpkm-secondary" id="wpkm-add-nav">+ افزودن آیتم</button>
              </div>
              <div class="wpkm-section">
                <h3>مخفی‌سازی عناصر</h3>
                <div class="wpkm-field"><label>CSS Selector — هر خط یک selector</label><textarea id="wpkm-hide"><?php echo esc_textarea($s['hide_selectors']); ?></textarea></div>
              </div>
              <div class="wpkm-section">
                <h3>Mobile CSS</h3>
                <div class="wpkm-field"><label>CSS اختصاصی</label><textarea id="wpkm-css"><?php echo esc_textarea($s['mobile_css']); ?></textarea></div>
              </div>
              <div class="wpkm-actions"><button id="wpkm-save" class="wpkm-btn wpkm-primary">ذخیره تغییرات</button><button id="wpkm-reset" class="wpkm-btn wpkm-danger">Reset</button></div>
            </aside>
            <main class="wpkm-preview">
              <div class="wpkm-toolbar">
                <select id="wpkm-page"><option value="<?php echo esc_url($home); ?>">صفحه اصلی</option><?php foreach($pages as $p): ?><option value="<?php echo esc_url(get_permalink($p)); ?>"><?php echo esc_html($p->post_title); ?></option><?php endforeach; ?></select>
                <select id="wpkm-device"><option value="390x780">Phone 390 × 780</option><option value="375x812">Phone 375 × 812</option><option value="360x800">Phone 360 × 800</option><option value="430x860">Phone 430 × 860</option></select>
                <button id="wpkm-orient" class="wpkm-btn wpkm-secondary">↔ چرخش</button>
                <span class="wpkm-spacer"></span><span id="wpkm-status" class="wpkm-status">در حال آماده‌سازی Preview…</span>
              </div>
              <div class="wpkm-stage"><div class="wpkm-device"><iframe id="wpkm-frame" src="<?php echo esc_url($home); ?>" title="Kandoo Mobile Live Preview"></iframe></div></div>
            </main>
          </div>
        </div>
        <script>window.WPKM=<?php echo wp_json_encode(['settings'=>$s,'nonce'=>wp_create_nonce('wpkm')]); ?>;</script>
        <script><?php echo $this->admin_js(); ?></script>
        <?php
    }

    public function ajax_save() {
        check_ajax_referer('wpkm','nonce');
        if(!current_user_can('manage_options')) wp_send_json_error(['message'=>'forbidden'],403);
        $raw=isset($_POST['settings'])?json_decode(wp_unslash($_POST['settings']),true):[];
        $base=self::defaults();
        $s=[
            'enabled'=>!empty($raw['enabled'])?1:0,
            'breakpoint'=>max(320,min(1200,absint($raw['breakpoint']??767))),
            'mobile_css'=>sanitize_textarea_field($raw['mobile_css']??''),
            'hide_selectors'=>sanitize_textarea_field($raw['hide_selectors']??''),
            'header'=>[
                'enabled'=>!empty($raw['header']['enabled'])?1:0,
                'height'=>max(40,min(160,absint($raw['header']['height']??56))),
                'sticky'=>!empty($raw['header']['sticky'])?1:0,
                'logo_url'=>esc_url_raw($raw['header']['logo_url']??''),
                'menu_label'=>sanitize_text_field($raw['header']['menu_label']??'منو'),
            ],
            'bottom_nav'=>['enabled'=>!empty($raw['bottom_nav']['enabled'])?1:0,'items'=>[]],
        ];
        foreach(($raw['bottom_nav']['items']??[]) as $it){
            if(count($s['bottom_nav']['items'])>=8) break;
            $s['bottom_nav']['items'][]=[
                'label'=>sanitize_text_field($it['label']??''),
                'url'=>esc_url_raw($it['url']??'#'),
                'icon'=>sanitize_text_field($it['icon']??'●'),
            ];
        }
        update_option(self::OPTION,$s,false);
        wp_send_json_success(['settings'=>$s]);
    }

    public function ajax_reset() {
        check_ajax_referer('wpkm','nonce');
        if(!current_user_can('manage_options')) wp_send_json_error([],403);
        update_option(self::OPTION,self::defaults(),false);
        wp_send_json_success();
    }

    public function frontend_css() {
        $s=$this->settings();
        if(empty($s['enabled'])) return;
        $bp=(int)$s['breakpoint'];
        $css='';
        $selectors=preg_split('/\r\n|\r|\n/',(string)$s['hide_selectors']);
        foreach($selectors as $sel){$sel=trim($sel);if($sel && preg_match('/^[a-zA-Z0-9_\-\.\#\[\]=:"\' >+~*(),]+$/',$sel))$css.=$sel.'{display:none!important;}';}
        if($s['mobile_css'])$css.=$s['mobile_css'];
        echo '<style id="wpkm-frontend">@media(max-width:'.$bp.'px){'.$css.'}</style>';
    }

    public function frontend_bottom_nav() {
        $s=$this->settings();
        if(empty($s['enabled']) || empty($s['bottom_nav']['enabled']) || empty($s['bottom_nav']['items'])) return;
        $bp=(int)$s['breakpoint'];
        echo '<nav class="wpkm-bottom-nav" aria-label="منوی موبایل">';
        foreach($s['bottom_nav']['items'] as $it){
            echo '<a href="'.esc_url($it['url']).'"><span class="wpkm-nav-icon">'.esc_html($it['icon']).'</span><span>'.esc_html($it['label']).'</span></a>';
        }
        echo '</nav><style>@media(min-width:'.($bp+1).'px){.wpkm-bottom-nav{display:none!important}}.wpkm-bottom-nav{position:fixed;z-index:99999;bottom:0;left:0;right:0;height:68px;background:#fff;border-top:1px solid #e5e7eb;display:grid;grid-template-columns:repeat('.count($s['bottom_nav']['items']).',1fr);direction:rtl;padding-bottom:env(safe-area-inset-bottom)}.wpkm-bottom-nav a{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;text-decoration:none;color:#475569;font-size:11px}.wpkm-nav-icon{font-size:20px;line-height:1}</style>';
    }
}
register_activation_hook(__FILE__, ['WP_Kandoo_Mobile','activate']);
new WP_Kandoo_Mobile();
