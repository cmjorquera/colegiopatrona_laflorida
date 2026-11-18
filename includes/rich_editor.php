<?php
declare(strict_types=1);
/**
 * Sanitizador HTML compartido para campos descripcion del CMS.
 * Lista permitida estricta: sin estilos, scripts, imagenes ni atributos de eventos.
 */
function patrona_rich_clean(string $html): string {
    $html=trim($html);
    if($html==='')return '';
    if(!class_exists('DOMDocument')){
        return htmlspecialchars(strip_tags($html),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
    }
    $dom=new DOMDocument('1.0','UTF-8');
    $previous=libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8"><div id="rich-root">'.$html.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD|LIBXML_NONET);
    libxml_clear_errors();libxml_use_internal_errors($previous);
    $root=$dom->getElementById('rich-root');
    if(!$root)return htmlspecialchars(strip_tags($html),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
    $allowed=['p','br','strong','b','em','i','u','s','strike','ul','ol','li','blockquote','a','div','span'];
    $clean=function(DOMNode $parent)use(&$clean,$allowed,$dom):void{
        for($node=$parent->firstChild;$node;){
            $next=$node->nextSibling;
            if($node instanceof DOMComment){$parent->removeChild($node);$node=$next;continue;}
            if($node instanceof DOMElement){
                $tag=strtolower($node->tagName);
                if(!in_array($tag,$allowed,true)){
                    if(in_array($tag,['script','style','iframe','svg','math','object','embed','form','input','button','textarea','select','img'],true)){
                        $parent->removeChild($node);$node=$next;continue;
                    }
                    $clean($node);
                    while($node->firstChild)$parent->insertBefore($node->firstChild,$node);
                    $parent->removeChild($node);
                    $node=$next;continue;
                }
                $href=$tag==='a'?$node->getAttribute('href'):'';
                $style=$tag==='span'?$node->getAttribute('style'):'';
                while($node->attributes->length)$node->removeAttributeNode($node->attributes->item(0));
                if($tag==='span' && preg_match('/(?:^|;)\\s*color\\s*:\\s*(#[0-9a-fA-F]{6})\\s*(?:;|$)/',$style,$match))$node->setAttribute('style','color:'.$match[1]);
                if($tag==='a' && preg_match('~^(https?://|mailto:|/|#)~i',$href)){
                    $node->setAttribute('href',$href);
                    $node->setAttribute('rel','noopener noreferrer');
                }
                $clean($node);
            }
            $node=$next;
        }
    };
    $clean($root);
    $output='';
    foreach($root->childNodes as $node)$output.=$dom->saveHTML($node);
    return trim($output);
}
function patrona_rich_assets(): void {
    echo '<link rel="stylesheet" href="assets/admin/css/rich-editor.css"><script defer src="assets/admin/js/rich-editor.js"></script>';
}
