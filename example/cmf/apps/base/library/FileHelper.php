<?php
namespace CMF\Base\Library;
use Phalcon\Http\Request\FileInterface;

/**
 * 文件助手类
 */
class FileHelper {
	private static $_extensions = array();
	/**
	 * 验证真实的MIME类型
	 * @param  string|object $mimeType MIME类型或Phalcon\Http\Request\FileInterface对象。例如：image/gif
	 * @param  string|array $mimeTypes MIME类型列表。例如：image/gif,image/png,image/jpg,image/jpeg
	 * @return bool
	 */
	public static function checkRealType($mimeType, $mimeTypes) {
		if (is_object($mimeType) && $mimeType instanceof FileInterface) {
			$mimeType = $mimeType->getRealType();
		}
		if (is_string($mimeTypes)) {
			$mimeTypes = preg_split('|[^\\w/-]+|i', $mimeTypes);
		}
		return in_array($mimeType, $mimeTypes);
	}

	/**
	 * 验证扩展名类型
	 * @param  string|object $fileName
	 * @param  string|array $extTypes 类型列表。例如：gif,png,jpg,jpeg
	 * @return bool
	 */
	public static function checkExtType($fileName, $extTypes) {
		$extType = self::getExt($fileName);
		if (is_string($extTypes)) {
			$extTypes = preg_split('|[^\\w/-]+|i', $extTypes);
		}
		return in_array($extType, $mimeTypes);
	}

	/**
	 * 获取扩展名
	 * @param  string|object $fileName
	 * @return string
	 */
	public static function getExt($fileName) {
		if (is_object($fileName) && $fileName instanceof FileInterface) {
			$fileName = $fileName->getName();
		}

		if (!isset(self::$_extensions[$fileName])) {
			self::$_extensions[$fileName] = (false === ($pos = strrpos($fileName, '.'))) ? '' : substr($fileName, $pos + 1);
		}

		return self::$_extensions[$fileName];
	}

	/**
	 * 检查文件大小是否超出限制
	 * @param  object $file    [description]
	 * @param  number $maxSize 允许的最大字节数
	 * @return bool
	 */
	public static function checkSize($file, $maxSize) {
		return $file->getSize() <= $maxSize;
	}

	/**
	 * 生成随机文件名
	 * @param  string|object $fileName 文件名
	 * @return string
	 */
	public static function randFileName($fileName) {
		srand((double) microtime() * 1000000);
		$rand = rand(100, 999);
		$name = date('U') + $rand;
		$name = $name . '.' . self::getExt($fileName);
		return $name;
	}

	/**
	 * 验证所有项目
	 * @param  object  $file            Phalcon\Http\Request\File对象
	 * @param  string|array  $mimeTypes MIME类型
	 * @param  string|array  $extTypes  扩展名，不含“.”
	 * @param  integer $maxSize         允许的最大字节
	 * @param  boolean $genRandFileName 是否生成随机文件名
	 * @return array
	 */
	public static function verifyAll($file, $mimeTypes, $extTypes = null, $maxSize = -1, $genRandFileName = true) {
		if ($mimeTypes && !self::checkRealType($file, $mimeTypes)) {
			return array(-1, '');
		}
		if ($extTypes && !self::checkExtType($file, $extTypes)) {
			return array(-2, '');
		}
		if ($maxSize >= 0 && !self::checkSize($file, $maxSize)) {
			return array(-3, '');
		}
		return array(true, $genRandFileName ? self::randFileName($file) : '');
	}
}