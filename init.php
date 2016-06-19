<?php
namespace mospans;

require_once 'Site.php';

function dateByLanguage($format, $bitrixDate)
{
	$timestamp = MakeTimeStamp($bitrixDate, \CSite::GetDateFormat());
	switch (\mospans\Site::getLanguageCode()) {
		case 'ru':
			return ToLower(FormatDate($format, $timestamp));
			break;
		case 'en':
			return date($format, $timestamp);
			break;
	}
}

\Bitrix\Main\Localization\Loc::setCurrentLang(\mospans\Site::getLanguageCode());
?>