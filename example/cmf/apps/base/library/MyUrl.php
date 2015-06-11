<?php
namespace CMF\Base\Library;

use Phalcon\Mvc\Url as UrlProvider;
/**
* 重写Url，修复动态网址中关于问号的bug
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/6/9
*/

class MyUrl extends UrlProvider{
	static public $hasDynamicUrl=null;
	public function get($uri=null, $args=null, $local=null){
		if(self::$hasDynamicUrl && strpos($uri,'?')!==false){
			$uri=str_replace('?','&',$uri);
		}
		return parent::get($uri, $args, $local);
	}
}