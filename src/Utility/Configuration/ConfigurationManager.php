<?php

require_once(PATH_SDK_ROOT . 'Core/CoreConstants.php');

/**
 * This file is unique to the PHP SDK, it is designed to
 * handle configuration requests that are handled in a more
 * native manner in the .NET version of the SDK
 */
class ConfigurationManager
{
	/**
	 * App specific settings
	 * @param string targetSetting
	 */
	public static function AppSettings($targetSetting)
	{
		$fileName = getcwd() . CoreConstants::SLASH_CHAR . "App.Config";
		$xmlObj = simplexml_load_file($fileName);
		
		$oneXPathTest = '//appSettings/add[@key="'.$targetSetting.'"]';
		$result = $xmlObj->xpath($oneXPathTest);

		$returnVal = NULL;
		if ($result && $result[0])
		{
			foreach($result[0]->attributes() as $attrName => $attrVal)
			{
				if ('value'==$attrName)
				{
					$returnVal = (string)$attrVal;
					break;
				}
			}
		}
		
		return $returnVal;
	}
}

?>
