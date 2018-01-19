<?php
namespace UseDesk\SyncEngineIntegration\helpers;
class EmailBodyHelper
{
    public static function syncEngineCommentBodyHelper($body, $removeQuotes=true){
        $body = preg_replace('/T_I_C_K_E_T_I_D_([0-9]+)/', '', $body);
        return static::prepareDisplayHtml($body, $removeQuotes);
    }
    protected static function prepareDisplayHtml($html, $removeQuotes = true)
    {
        if ($removeQuotes) {
            $html = static::removeQuotes($html);
        }
        $purifierConfig = HTMLPurifier_Config::createDefault();
        $purifierConfig->set('HTML.TargetBlank', true);
//        if ($removeQuotes) {
//            $purifierConfig->set('HTML.Allowed', 'p,ul[style],ol,li[style],img[src],img[style],img[width],table[style],tbody[style],a[href],br,div[style],span[style],td[style],tr[style]');
//        } else {
//            $purifierConfig->set('HTML.Allowed', 'p,ul[style],ol,li,img[src],img[style],img[width],table[style],tbody[style],a[href],br,blockquote,div[style],span[style],td[style],tr[style]');
//        }
//        $purifierConfig->set('AutoFormat.RemoveEmpty', true);
//        $def = $purifierConfig->getHTMLDefinition();
//        $def->info_tag_transform['div'] = new HTMLPurifier_TagTransform_Simple('p');
        $purifier = new HTMLPurifier($purifierConfig);
        $html = $purifier->purify($html);
        $html = trim($html);
        return $html;
    }

}
