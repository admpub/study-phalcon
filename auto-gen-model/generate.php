<?php
//echo '<pre>';
//generate_comments(ROOT_PATH.'library/global.func.php');

/**
 * 为函数自动生成PHPDoc格式的注释
 *
 * @param string $file 文件路径
 * @param bool   $save 是否保存
 * @author AdamShen (swh@admpub.com)
 */
function generate_comments($file,$save=false){
	$content=file_get_contents($file);
	$_SERVER['content']=$content;
	$content=preg_replace_callback('/([\s]+)function[\s]+([a-z_][a-z0-9_]*)[\s]*\(([^{]*)\)/i',function($p)use($content){
		$paramComment='';
		if(isset($p[3]) && ($p[3]=trim($p[3]))){
			$params=explode(',',$p[3]);
			foreach($params as $param){
				$param=trim($param);
				$pv=explode('=',$param);
				$paramComment.=' * @param';
				$pv[0]=trim($pv[0]);
				if(isset($pv[1])){
					$pv[1]=trim($pv[1]);
					if($pv[1]{0}=='"'||$pv[1]{0}=='\''){
						$pv[1]='string';
					}elseif(in_array(strtoupper($pv[1]),array('TRUE','FALSE'))){
						$pv[1]='bool';
					}elseif(is_numeric($pv[1])){
						$pv[1]='number';
					}elseif(preg_match('/^array[\s]*\\(/i',$pv[1])){
						$pv[1]='array';
					}else{
						$pv[1]='unknown';
					}
				}else{
					$pv[1]='unknown';
				}
				if($pv[1]=='unknown'){
					if(preg_match('/^\\$(string|name|str|filename|path|title|url|content)$/i',$pv[0])||preg_match('/_(string|name|str|file|path|title|url|content)$/i',$pv[0])){
						$pv[1]='string';
					}elseif(preg_match('/^\\$(arr|array)$/i',$pv[0])){
						$pv[1]='array';
					}elseif(preg_match('/^\\$(id|num|length|width|height|min|max|offset|total|amount|balance)$/i',$pv[0])||preg_match('/_(id|num)$/i',$pv[0])){
						$pv[1]='number';
					}
				}
				$paramComment.=' '.$pv[1].' '.$pv[0].' '.PHP_EOL;
			}

		}
		$space='';
		$replace='';
		if(strpos($p[0],"\n")===false){
			$pos=strpos($content,$p[0]);
			$str=substr($content,0,$pos);
			$pos=strrpos($str,"\n");
			$str=substr($str,$pos+1);
			$replace="\n".$str.$p[0];
			$matches=array();
			if(preg_match('/^([\s]*)/',$str,$matches)){
				$space=str_repeat(' ',strlen($matches[1]));
			}
		}else{
			$str=ltrim($p[1],"\r\n");
			$space=str_repeat(' ',strlen($str));
			$replace=$p[0];
		}
		/*
		if(preg_match('|/\\*\\*\n\\@\n \\*\/$|',trim(substr($content,0,strpos($content,$replace))))){
			echo '______________',PHP_EOL;
		}
		*/
		$comment = PHP_EOL.PHP_EOL;
		$comment .= $space.'/**'.PHP_EOL;
		$comment .= $space.' * description'.PHP_EOL;
		$comment .= $space.' * '.PHP_EOL;
		$comment .= $space.str_replace(PHP_EOL.' * ',PHP_EOL.$space.' * ',$paramComment);
		$comment .= $space.' * @return unknown'.PHP_EOL;
		$comment .= $space.' */'.PHP_EOL;
		/*
		echo $comment;
		echo '['.ltrim($replace,"\n").']'.PHP_EOL;
		*/
		$_SERVER['content']=str_replace($replace,$comment.ltrim($replace,"\r\n"),$_SERVER['content']);
	},$content);
	if($save){
		file_put_contents($file,$_SERVER['content']);
	}else{
		echo htmlspecialchars($_SERVER['content']);
	}
	unset($_SERVER['content'],$content);
}