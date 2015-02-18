<?php

require_once(PATH_SDK_ROOT . 'Core/Configuration/IppConfiguration.php');
require_once(PATH_SDK_ROOT . 'Core/Configuration/Message.php');
require_once(PATH_SDK_ROOT . 'Core/Configuration/CompressionFormat.php');
require_once(PATH_SDK_ROOT . 'Core/Configuration/SerializationFormat.php');
require_once(PATH_SDK_ROOT . 'Core/Configuration/BaseUrl.php');
require_once(PATH_SDK_ROOT . 'Core/Configuration/Logger.php');
require_once(PATH_SDK_ROOT . 'Security/OAuthRequestValidator.php');
require_once(PATH_SDK_ROOT . 'Utility/Configuration/CompressionFormat.php');
require_once(PATH_SDK_ROOT . 'Utility/Configuration/SerializationFormat.php');



/**
 * Specifies the Default Configuration Reader implmentation used by the SDK.
 */
class LocalConfigReader
{
	/**
	 * Reads the configuration from the config file and converts it to custom 
	 * config objects which the end developer will use to get or set the properties.
	 * 
	 * @return IppConfiguration The custom config object.
	 */
	public static function ReadConfiguration()
	{
		/*
		// Example:
		
		<?xml version="1.0" encoding="utf-8" ?>
		<configuration>
		  <intuit>
		    <ipp>
		      <security mode="OAuth">
		        <oauth consumerKey="" consumerToken="" accessKey="" accessToken="" />
		      </security>
		      <message>
		        <request serializationFormat="Xml" compressionFormat="None"/>
		        <response serializationFormat="Xml" compressionFormat="None"/>
		      </message>
		      <service>
		        <baseUrl qbd="https://quickbooks.api.intuit.com/" qbo="https://quickbooks.api.intuit.com/" ipp="https://appcenter.intuit.com" />
		      </service>
		      <logger>       
		        <!-- To enable/disable Request and Response log-->
		        <requestLog enableRequestResponseLogging="true" requestResponseLoggingDirectory="/IdsLogs" />    
		      </logger>
		    </ipp>
		  </intuit>
		  <appSettings>
		  </appSettings>
		</configuration>
		*/
		
		$ippConfig = new IppConfiguration();
		
		$xmlObj = simplexml_load_file(PATH_SDK_ROOT . 'sdk.config');

		// Initialize OAuthRequestValidator Configuration Object
		if ($xmlObj &&
		    $xmlObj->intuit &&
		    $xmlObj->intuit->ipp &&
		    $xmlObj->intuit->ipp->security &&
		    $xmlObj->intuit->ipp->security->oauth && 
		    $xmlObj->intuit->ipp->security->oauth->attributes())
		{
			$ippConfig->Security = new OAuthRequestValidator($xmlObj->intuit->ipp->security->oauth->attributes()->accessToken,
			                                                 $xmlObj->intuit->ipp->security->oauth->attributes()->accessKey,
			                                                 $xmlObj->intuit->ipp->security->oauth->attributes()->consumerToken,
			                                                 $xmlObj->intuit->ipp->security->oauth->attributes()->consumerKey
			                                                 );
		}
		
		// Initialize Request Configuration Object
		$ippConfig->Message = new Message();
		$ippConfig->Message->Request = new Request();
        $ippConfig->Message->Response = new Response();

		$requestSerializationFormat = NULL;
		$requestCompressionFormat = NULL;
		$responseSerializationFormat = NULL;
		$responseCompressionFormat = NULL;
		
		if ($xmlObj &&
		    $xmlObj->intuit &&
		    $xmlObj->intuit->ipp &&
		    $xmlObj->intuit->ipp->message &&
		    $xmlObj->intuit->ipp->message->request &&
		    $xmlObj->intuit->ipp->message->request->attributes())
		{
			$requestAttr = $xmlObj->intuit->ipp->message->request->attributes();
			$requestSerializationFormat = (string)$requestAttr->serializationFormat;
			$requestCompressionFormat = (string)$requestAttr->compressionFormat;
		}

		// Initialize Response Configuration Object
		if ($xmlObj &&
		    $xmlObj->intuit &&
		    $xmlObj->intuit->ipp &&
		    $xmlObj->intuit->ipp->message &&
		    $xmlObj->intuit->ipp->message->response->attributes())
		{
			$responseAttr = $xmlObj->intuit->ipp->message->response->attributes();
			$responseSerializationFormat = (string)$responseAttr->serializationFormat;
			$responseCompressionFormat = (string)$responseAttr->compressionFormat;
		}

        switch ($requestCompressionFormat)
        {
            case Intuit\Ipp\Utility\CompressionFormat::None:
                $ippConfig->Message->Request->CompressionFormat = CompressionFormat::None;
                break;
            case Intuit\Ipp\Utility\CompressionFormat::GZip:
                $ippConfig->Message->Request->CompressionFormat = CompressionFormat::GZip;
                break;
            case Intuit\Ipp\Utility\CompressionFormat::Deflate:
                $ippConfig->Message->Request->CompressionFormat = CompressionFormat::Deflate;
                break;
            default:
                break;
        }
        
        switch ($responseCompressionFormat)
        {
            case Intuit\Ipp\Utility\CompressionFormat::None:
                $ippConfig->Message->Response->CompressionFormat = CompressionFormat::None;
                break;
            case Intuit\Ipp\Utility\CompressionFormat::GZip:
                $ippConfig->Message->Response->CompressionFormat = CompressionFormat::GZip;
                break;
            case Intuit\Ipp\Utility\CompressionFormat::Deflate:
                $ippConfig->Message->Response->CompressionFormat = CompressionFormat::Deflate;
                break;
        }

        switch ($requestSerializationFormat)
        {
            //case Intuit\Ipp\Utility\SerializationFormat::DEFAULT:
            case Intuit\Ipp\Utility\SerializationFormat::Xml:
                $ippConfig->Message->Request->SerializationFormat = SerializationFormat::Xml;
                break;
            case Intuit\Ipp\Utility\SerializationFormat::Json:
                $ippConfig->Message->Request->SerializationFormat = SerializationFormat::Json;
                break;
            case Intuit\Ipp\Utility\SerializationFormat::Custom:
                $ippConfig->Message->Request->SerializationFormat = SerializationFormat::Custom;
                break;
        }

        switch ($responseSerializationFormat)
        {
            case Intuit\Ipp\Utility\SerializationFormat::Xml:
                $ippConfig->Message->Response->SerializationFormat = SerializationFormat::Xml;
                break;
            //case Intuit\Ipp\Utility\SerializationFormat::DEFAULT:
            case Intuit\Ipp\Utility\SerializationFormat::Json:
                $ippConfig->Message->Response->SerializationFormat = SerializationFormat::Json;
                break;
            case Intuit\Ipp\Utility\SerializationFormat::Custom:
                $ippConfig->Message->Response->SerializationFormat = SerializationFormat::Custom;
                break;
        }

		// Initialize BaseUrl Configuration Object
		$ippConfig->BaseUrl = new BaseUrl();
		if ($xmlObj &&
		    $xmlObj->intuit &&
		    $xmlObj->intuit->ipp &&
		    $xmlObj->intuit->ipp->service &&
		    $xmlObj->intuit->ipp->service->baseUrl && 
		    $xmlObj->intuit->ipp->service->baseUrl->attributes())
		{
			$responseAttr = $xmlObj->intuit->ipp->service->baseUrl->attributes();
			$ippConfig->BaseUrl->Qbd = (string)$responseAttr->qbd;
			$ippConfig->BaseUrl->Qbo = (string)$responseAttr->qbo;
			$ippConfig->BaseUrl->Ipp = (string)$responseAttr->ipp;
		}

		// Initialize Logger
		$ippConfig->Logger = new LoggerMech();
		if ($xmlObj &&
		    $xmlObj->intuit &&
		    $xmlObj->intuit->ipp &&
		    $xmlObj->intuit->ipp->logger &&
		    $xmlObj->intuit->ipp->logger->requestLog && 
		    $xmlObj->intuit->ipp->logger->requestLog->attributes())
		{
			$requestLogAttr = $xmlObj->intuit->ipp->logger->requestLog->attributes();
			$ippConfig->Logger->RequestLog->ServiceRequestLoggingLocation = (string)$requestLogAttr->requestResponseLoggingDirectory;
			$ippConfig->Logger->RequestLog->EnableRequestResponseLogging = (string)$requestLogAttr->enableRequestResponseLogging;
		}
		
		return $ippConfig;
	}

}

?>
