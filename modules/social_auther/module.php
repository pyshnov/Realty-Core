<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function social_auther_template_pre_process(&$variables) {
    $variables['social_auther'] = social_html();
}

function social_html() {

    $html = '<ul class="social-network social-circle text-center">';

    if(Pyshnov::config()->get('social_auther.vk_enable')) {
        $html .= '<li><a href="/socialauth/?provider=vk" class="icoVk" title="Vk"><i class="fa fa-vk"></i></a></li>';
    }
    if(Pyshnov::config()->get('social_auther.ok_enable')) {
        $html .= '<li><a href="/socialauth/?provider=ok" class="icoOk" title="Odnoklassniki"><i class="fa fa-odnoklassniki"></i></a></li>';
    }
    if(Pyshnov::config()->get('social_auther.facebook_enable')) {
        $html .= '<li><a href="/socialauth/?provider=facebook" class="icoFacebook" title="Facebook"><i class="fa fa-facebook"></i></a></li>';
    }
    if(Pyshnov::config()->get('social_auther.twitter_enable')) {
        $html .= '<li><a href="/socialauth/?provider=twitter" class="icoTwitter" title="Twitter"><i class="fa fa-twitter"></i></a></li>';
    }
    if(Pyshnov::config()->get('social_auther.google_enable')) {
        $html .= '<li><a href="/socialauth/?provider=google" class="icoGoogle" title="Google +"><i class="fa fa-google-plus"></i></a></li>';
    }
    $html .= '</ul>';

    $html .= '
        <script>
            $(document).ready(function(){
                $(\'.social-network a\').click(function(e){
                    var expires = new Date();
                    expires.setTime(expires.getTime() + (1000 * 86400));
                    document.cookie = "back_url=" + window.location.href + "; expires=" + expires.toGMTString() +  "; path=/";
                });
            });
        </script>';

 return $html;
}


