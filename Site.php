<?php
namespace mospans;

require_once 'SiteException.php';

class Site
{
	private static $cacheElements = array();
	
	private static $iblocks = array(
		'languages' => 1
	);
	
	private static $languageCode;
	
	public static function getIblockId($code)
	{
		if (array_key_exists($code, self::$iblocks)) {
			return self::$iblocks[$code];
		}
		return -1;
	}
	
	private static function sortArrayByKeys($array)
	{
		if (is_array($array)) {
			ksort($array);
			foreach ($array as $key => $value) {
				$array[$key] = self::sortArrayByKeys($value);
			}
		} else {
			return $array;
		}
		return $array;
	}
	
	private static function serializeArray($array)
	{
		$sortedArray = self::sortArrayByKeys($array);
		$serializedArray = json_encode($sortedArray);
		return $serializedArray;
	}
	
	public static function getElement($parameters)
	{
		if (!(isset($parameters['FILTER']) && is_array($parameters['FILTER']) && !empty($parameters['FILTER']))) {
			throw new \mospans\SiteException('Filter not set in method \mospans\Site::getElement()');
		}
		
		$arSort = array('SORT' => 'ASC');
		$arFilter = $parameters['FILTER'];
		$arSelect = array('ID', 'NAME', 'CODE');
		
		if (isset($parameters['SORT']) && is_array($parameters['SORT']) && !empty($parameters['SORT'])) {
			$arSort = $parameters['SORT'];
		} else {
			$parameters['SORT'] = $arSort;
		}
		
		if (isset($parameters['SELECT']) && is_array($parameters['SELECT']) && !empty($parameters['SELECT'])) {
			$arSelect = $parameters['SELECT'];
		} else {
			$parameters['SELECT'] = $arSelect;
		}
		
		$cacheKey = self::serializeArray($parameters);
		
		if (!array_key_exists($cacheKey, self::$cacheElements)) {
			\CModule::IncludeModule('iblock');
			$res = \CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
			if ($arRes = $res->GetNext()) {
				self::$cacheElements[$cacheKey] = $arRes;
			} else {
				self::$cacheElements[$cacheKey] = null;
			}
		}
		
		return self::$cacheElements[$cacheKey];
	}
	
	public static function getLanguageCode()
	{
		if (is_null(self::$languageCode)) {
			$arLanguage = self::getElement(array('FILTER' => array('IBLOCK_ID' => self::getIblockId('languages'), 'ACTIVE' => 'Y', 'CODE' => explode('/', $_SERVER['REQUEST_URI'])[1])));
			if (is_null($arLanguage)) {
				$arLanguage = self::getElement(array('FILTER' => array('IBLOCK_ID' => self::getIblockId('languages'), 'ACTIVE' => 'Y')));
				if (is_null($arLanguage)) {
					throw new \mospans\SiteException('Active language not set');
				}
			}
			self::$languageCode = $arLanguage['CODE'];
		}
		return self::$languageCode;
	}
	
	public static function getURLPrefix()
	{
		return '/' . self::getLanguageCode();
	}
}