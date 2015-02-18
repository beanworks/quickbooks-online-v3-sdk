<?php

require_once(PATH_SDK_ROOT . 'Core/CoreHelper.php');
require_once(PATH_SDK_ROOT . 'DataService/Batch.php');
require_once(PATH_SDK_ROOT . 'DataService/IntuitCDCResponse.php');
require_once(PATH_SDK_ROOT . 'Data/IntuitRestServiceDef/IPPAttachableResponse.php');
require_once(PATH_SDK_ROOT . 'Data/IntuitRestServiceDef/IPPFault.php');
require_once(PATH_SDK_ROOT . 'Data/IntuitRestServiceDef/IPPError.php');


/**
* This file contains DataService performs CRUD operations on IPP REST APIs.
*/
class DataService
{
	/**
	 * The Service context object.
	 * @var ServiceContext 
	 */
	private $serviceContext;
	
	/**
	 * Rest Request Handler.
	 * @var IRestHandler 
	 */
	private $restHandler;

	/**
	 * Serializer needs to be used.
	 * @var IEntitySerializer 
	 */
	private $responseSerializer;
	
	/**
	 * If true, indicates a desire to echo verbose output
	 * @var bool 
	 */
	private $verbose;

	/**
	 * Initializes a new instance of the DataService class.
	 *
	 * @param ServiceContext $serviceContext IPP Service Context
	 */
	public function __construct($serviceContext)
	{
		if (NULL == $serviceContext)
		{
			throw new ArgumentNullException('Resources.ServiceContextCannotBeNull');
		}
		
		if (!is_object($serviceContext))
		{
			throw new InvalidParameterException('Wrong arg type passed - is not an object.');
		}
	
		//ServiceContextValidation(serviceContext);
		$this->serviceContext = $serviceContext;
		
		$this->responseSerializer = CoreHelper::GetSerializer($this->serviceContext, false);
		$this->restHandler = new SyncRestHandler($serviceContext);
		
		// Set the Service type to either QBO or QBD by calling a method.
		$this->serviceContext->UseDataServices();
	
	}
	
	/**
	 * Marshall a POPO object to XML, presumably for inclusion on an IPP v3 API call
	 *
	 * @param POPOObject $phpObj inbound POPO object
	 * @return string XML output derived from POPO object 
	 */
	private function getXmlFromObj($phpObj)
	{
		if (!$phpObj)
		{
			echo "getXmlFromObj NULL arg\n";
			var_dump(debug_backtrace());
			return FALSE;
		}
			
		$php2xml = new com\mikebevz\xsd2php\Php2Xml(PHP_CLASS_PREFIX);
		$php2xml->overrideAsSingleNamespace='http://schema.intuit.com/finance/v3';
		
		try {
			return $php2xml->getXml($phpObj);
		}
		catch (Exception $e) {
			echo "getXmlFromObj EXCEPTION: " . $e->getMessage() . "\n";
			var_dump($phpObj);
			var_dump(debug_backtrace());
			return FALSE;
		}
	}

	
	/**
	 * Decorate an IPP v3 Entity name (like 'Class') to be a POPO class name (like 'IPPClass')
	 *
	 * @param string Intuit Entity name
	 * @return POPO class name
	 */
	private static function decorateIntuitEntityToPhpClassName($intuitEntityName)
	{
		return PHP_CLASS_PREFIX . $intuitEntityName;
	}
	
	/**
	 * Clean a POPO class name (like 'IPPClass') to be an IPP v3 Entity name (like 'Class')
	 *
	 * @param string $phpClassName POPO class name
	 * @return string Intuit Entity name 
	 */
	private static function cleanPhpClassNameToIntuitEntityName($phpClassName)
	{
		if (0==strpos($phpClassName, PHP_CLASS_PREFIX))
			return substr($phpClassName, strlen(PHP_CLASS_PREFIX));
			
		return NULL;
	}

	/**
	 * Updates an entity under the specified realm. The realm must be set in the context.
	 *
	 * @param IPPIntuitEntity $entity Entity to Update.
	 * @return IPPIntuitEntity Returns an updated version of the entity with updated identifier and sync token. 
	 */
	public function Update($entity)
	{
		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Called Method Update.");
	
        // Validate parameter
		if (!$entity)
		{
			$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Error, "Argument Null Exception");
			throw new IdsException('Argument Null Exception');
		}

		$httpsPostBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($entity, $urlResource);
		
		// Builds resource Uri
		// Handle some special cases
		if ((strtolower('preferences')==strtolower($urlResource)) &&
		    (IntuitServicesType::QBO==$this->serviceContext->serviceType))
		{		    
			// URL format for *QBO* prefs request is different than URL format for *QBD* prefs request
			$uri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, $urlResource));
		}
		else if ((strtolower('company')==strtolower($urlResource)) &&
		         (IntuitServicesType::QBD==$this->serviceContext->serviceType))
		{		    
			// URL format for *QBD* companyinfo request is different than URL format for *QBO* companyinfo request
			$urlResource='companyInfo';
			$uri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, $urlResource.'?operation=update'));
		}
		else
		{
			// Normal case
			$uri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, $urlResource.'?operation=update'));
		}

        // Creates request parameters
		if ($this->serviceContext->IppConfiguration->Message->Request->SerializationFormat == SerializationFormat::Json)
		{
			$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONJSON, NULL);
		}
		else
		{
			$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONXML, NULL);
		}

		$restRequestHandler = new SyncRestHandler($this->serviceContext);
		try
		{
		    // gets response
			list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, $httpsPostBody, NULL);
		}
		catch (Exception $e)
		{
		}		
		
        CoreHelper::CheckNullResponseAndThrowException($responseBody);

		try {
			$parsedResponseBody = $this->responseSerializer->Deserialize($responseBody, TRUE);		                                                            
		}
		catch (Exception $e) {
			return NULL;
		}		

		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Finished Executing Method Update.");
		return $parsedResponseBody;
    }

	/**
	 * Returns an entity under the specified realm. The realm must be set in the context.
	 *
	 * @param object $entity Entity to Find
	 * @return IPPIntuitEntity Returns an entity of specified Id. 
	 */	
	public function FindById($entity)
	{
		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Called Method FindById.");

		$httpsPostBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($entity, $urlResource);

	    // Validate parameter
		if ($entity && (strtolower('preferences')==strtolower($urlResource)))
		{
			// Exempt from general-purpose bad parameter check.  This is a special, allowable case.
		}
		else if (!$entity || !$entity->Id)
		{
			$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Error, "Argument Null Exception");
			throw new IdsException('Argument Null Exception');
		}

		$entityId = $entity->Id;

		// Handle some special cases
		if (strtolower('preferences')==strtolower($urlResource))
		{
			// FindById semantics for CompanyInfo are unusual.  Handle via special case. 
			$allEntities = $this->FindAll('Preferences');

			foreach($allEntities as $singletonPreferences)
			{
				return $singletonPreferences;
			}
			return NULL;
		}		    
		else if ((strtolower('company')==strtolower($urlResource)) ||
		         (strtolower('companyinfo')==strtolower($urlResource)))
		{		   
			// FindById semantics for CompanyInfo are unusual.  Handle via special case. 
			$allEntities = $this->FindAll('Company');
			foreach($allEntities as $oneCompany)
			{
				if (0 == strcmp($oneCompany->Id,$entity->Id))
				{
					return $oneCompany;
				}
			}
			return NULL;
		}
		else
		{
			// Normal case
			$uri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, $urlResource,$entityId));
		}


        // Creates request parameters
		if ($this->serviceContext->IppConfiguration->Message->Request->SerializationFormat == SerializationFormat::Json)
		{
			$requestParameters = new RequestParameters($uri, 'GET', CoreConstants::CONTENTTYPE_APPLICATIONJSON, NULL);
		}
		else
		{
			$requestParameters = new RequestParameters($uri, 'GET', CoreConstants::CONTENTTYPE_APPLICATIONXML, NULL);
		}

		$restRequestHandler = new SyncRestHandler($this->serviceContext);
		
		try
		{
			list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, NULL, NULL);
		}
		catch (Exception $e) {
			return NULL;
		}		
		
	    CoreHelper::CheckNullResponseAndThrowException($responseBody);
	    // De serialize object
		try
		{
			$parsedResponseBody = $this->responseSerializer->Deserialize($responseBody, TRUE);		                                                            
		}
		catch (Exception $e) {
			return NULL;
		}		

		return $parsedResponseBody;
	}
	
	/**
	 * Returns an entity under the specified realm. The realm must be set in the context.
	 *
	 * @param object $entity Entity to Find
	 * @return IPPIntuitEntity Returns an entity of specified Id. 
	 */	
	public function Retrieve($entity)
	{
		return $this->FindById($entity);
	}
	
	/**
	 * Creates an entity under the specified realm. The realm must be set in the context.
	 *
	 * @param IPPIntuitEntity $entity Entity to Create.
	 * @return IPPIntuitEntity Returns the created version of the entity. 
	 */
	public function Add($entity)
	{
		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Called Method Add.");
	
        // Validate parameter
		if (!$entity)
		{
			$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Error, "Argument Null Exception");
			throw new IdsException('Argument Null Exception');
		}

		$httpsPostBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($entity, $urlResource);

        // Builds resource Uri
		$uri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, $urlResource));

        // Creates request parameters
		if ($this->serviceContext->IppConfiguration->Message->Request->SerializationFormat == SerializationFormat::Json)
		{
			$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONJSON, NULL);
		}
		else
		{
			$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONXML, NULL);
		}

		$restRequestHandler = new SyncRestHandler($this->serviceContext);
		try
		{
		    // gets response
			list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, $httpsPostBody, NULL);
		}
		catch (Exception $e)
		{
		}		
		
        CoreHelper::CheckNullResponseAndThrowException($responseBody);
		
		try {
			$parsedResponseBody = $this->responseSerializer->Deserialize($responseBody, TRUE);		                                                            
		}
		catch (Exception $e) {
			return NULL;
		}		

		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Finished Executing Method Add.");
		return $parsedResponseBody;
	}    

	
	/**
	 * Deletes an entity under the specified realm. The realm must be set in the context.
	 *
	 * @param IPPIntuitEntity $entity Entity to Delete.
	 */
	public function Delete($entity)
	{
		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Called Method Delete.");
	
        // Validate parameter
		if (!$entity)
		{
			$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Error, "Argument Null Exception");
			throw new IdsException('Argument Null Exception');
		}
		
		// Builds resource Uri
		$httpsPostBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($entity, $urlResource);
		$uri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, $urlResource.'?operation=delete'));

        // Creates request parameters
		if ($this->serviceContext->IppConfiguration->Message->Request->SerializationFormat == SerializationFormat::Json)
		{
			$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONJSON, NULL);
		}
		else
		{
			$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONXML, NULL);
		}

		$restRequestHandler = new SyncRestHandler($this->serviceContext);

		try
		{
		    // gets response
			list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, $httpsPostBody, NULL);
		}
		catch (Exception $e)
		{
		}		
		
        CoreHelper::CheckNullResponseAndThrowException($responseBody);

		$restRequestHandler = new SyncRestHandler($this->serviceContext);
		try {
			$parsedResponseBody = $this->responseSerializer->Deserialize($responseBody, TRUE);	
		}
		catch (Exception $e) {
			return NULL;
		}		

		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Finished Executing Method Delete.");
		return $parsedResponseBody;
	}



	/**
	 * Voids an entity under the specified realm. The realm must be set in the context.
	 *
	 * @param IPPIntuitEntity $entity Entity to Void.
	 */
	public function Void($entity)
	{
		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Called Method Void.");
	
        // Validate parameter
		if (!$entity)
		{
			$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Error, "Argument Null Exception");
			throw new IdsException('Argument Null Exception');
		}
		
		// Builds resource Uri
		$httpsPostBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($entity, $urlResource);
		$uri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, $urlResource.'?operation=void'));

        // Creates request parameters
		if ($this->serviceContext->IppConfiguration->Message->Request->SerializationFormat == SerializationFormat::Json)
		{
			$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONJSON, NULL);
		}
		else
		{
			$requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONXML, NULL);
		}

		$restRequestHandler = new SyncRestHandler($this->serviceContext);

		try
		{
		    // gets response
			list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, $httpsPostBody, NULL);
		}
		catch (Exception $e)
		{
		}		
		
        CoreHelper::CheckNullResponseAndThrowException($responseBody);

		$restRequestHandler = new SyncRestHandler($this->serviceContext);
		try {
			$parsedResponseBody = $this->responseSerializer->Deserialize($responseBody, TRUE);	
		}
		catch (Exception $e) {
			return NULL;
		}		

		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Finished Executing Method Void.");
		return $parsedResponseBody;
	}
	
	/**
	 * Uploads an image
	 *
	 * @param string $imgBits image bytes
	 * @param string $fileName Filename to use for this file
	 * @param string $mimeType MIME type to send in the HTTP Headers
	 * @param IPPAttachable $objAttachable entity describing the attachement
	 * @return array Returns an array of entities fulfilling the query. 
	 */	
	public function Upload($imgBits, $fileName, $mimeType, $objAttachable)
	{
		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Called Method Upload.");
	
        // Validate parameter
		if (!$imgBits || !$mimeType || !$fileName)
		{
			$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Error, "Argument Null Exception");
			throw new IdsException('Argument Null Exception');
		}
		
		// Builds resource Uri
		$urlResource = "upload";
		$uri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, $urlResource));

		$boundaryString=md5(time());

        // Creates request parameters
		$requestParameters = new RequestParameters($uri, 'POST', "multipart/form-data; boundary={$boundaryString}", NULL);

		$MetaData = XmlObjectSerializer::getPostXmlFromArbitraryEntity($objAttachable, $urlResource);
		
		$desiredIdentifier = '0';
		$newline = "\r\n";
		$dataMultipart = '';
		$dataMultipart .= '--' . $boundaryString . $newline;
		$dataMultipart .= "Content-Disposition: form-data; name=\"file_metadata_{$desiredIdentifier}\"" . $newline;
		$dataMultipart .= "Content-Type: " . CoreConstants::CONTENTTYPE_APPLICATIONXML . '; charset=UTF-8' .$newline;
		$dataMultipart .= 'Content-Transfer-Encoding: 8bit' . $newline . $newline;
		$dataMultipart .= $MetaData;
		$dataMultipart .= '--' . $boundaryString . $newline;
		$dataMultipart .= "Content-Disposition: form-data; name=\"file_content_{$desiredIdentifier}\"; filename=\"{$fileName}\"" . $newline;
		$dataMultipart .= "Content-Type: {$mimeType}" . $newline;
		$dataMultipart .= 'Content-Transfer-Encoding: base64' . $newline . $newline;
		$dataMultipart .= chunk_split(base64_encode($imgBits)) . $newline;
		$dataMultipart .= "--" . $boundaryString . "--" . $newline . $newline; // finish with two eol's!!

		$restRequestHandler = new SyncRestHandler($this->serviceContext);

		try
		{
		    // gets response
			list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, $dataMultipart, NULL);
		}
		catch (Exception $e)
		{
		}		

        CoreHelper::CheckNullResponseAndThrowException($responseBody);

		$restRequestHandler = new SyncRestHandler($this->serviceContext);
		try {
			$parsedResponseBody = $this->responseSerializer->Deserialize($responseBody, TRUE);	
		}
		catch (Exception $e) {
			return NULL;
		}		

		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Finished Executing Method Upload.");
		return $parsedResponseBody;
	}
	

	/**
	 * Returns an downloaded entity under the specified realm. The realm must be set in the context.
	 *
	 * @param object $entity Entity to Find
	 * @return IPPIntuitEntity Returns an entity of specified Id. 
	 */	
	public function Download($entity)
	{
		return $this->FindById($entity);
	}
	
	
	/**
	 * Retrieves specified entities based passed page number and page size and query
	 *
	 * @param string $query Query to issue
	 * @param string $pageNumber Starting page number
	 * @param string $pageSize Page size
	 * @return array Returns an array of entities fulfilling the query. 
	 */	
	public function Query($query, $pageNumber=0, $pageSize=500)
	{
		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Called Method Query.");
	
		if ('QBO'==$this->serviceContext->serviceType)
			$httpsContentType = CoreConstants::CONTENTTYPE_APPLICATIONTEXT;
		else
			$httpsContentType = CoreConstants::CONTENTTYPE_TEXTPLAIN;
		
		$httpsUri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, 'query'));
		$httpsPostBody = $query;

		$requestParameters = new RequestParameters($httpsUri, 'POST', $httpsContentType, NULL);
		$restRequestHandler = new SyncRestHandler($this->serviceContext);
		list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, $httpsPostBody, NULL);
		
		$parsedResponseBody = NULL;
		try {
			$responseXmlObj = simplexml_load_string($responseBody);
			if ($responseXmlObj && $responseXmlObj->QueryResponse)	                                                            
				$parsedResponseBody = $this->responseSerializer->Deserialize($responseXmlObj->QueryResponse->asXML(), FALSE);		                                                            
		}
		catch (Exception $e) {
			return NULL;
		}		
		return $parsedResponseBody;
	}

	/**
	 * Retrieves specified entity based passed page number and page size
	 *
	 * @param string $urlResource Entity type to Find
	 * @return array Returns an array of entities of specified type. 
	 */	
	public function FindAll($entityName, $pageNumber=0, $pageSize=500)
	{
		$this->serviceContext->IppConfiguration->Logger->RequestLog->Log(TraceLevel::Info, "Called Method FindAll.");

		$phpClassName = DataService::decorateIntuitEntityToPhpClassName($entityName);
		
		// Handle some special cases
		if (strtolower('company')==strtolower($entityName))
			$entityName='CompanyInfo';
		
		if ('QBO'==$this->serviceContext->serviceType)
			$httpsContentType = CoreConstants::CONTENTTYPE_APPLICATIONTEXT;
		else
			$httpsContentType = CoreConstants::CONTENTTYPE_TEXTPLAIN;

		$httpsUri = implode(CoreConstants::SLASH_CHAR,array('company', $this->serviceContext->realmId, 'query'));
		$httpsPostBody = "select * from $entityName startPosition $pageNumber maxResults $pageSize";

		$requestParameters = new RequestParameters($httpsUri, 'POST', $httpsContentType, NULL);
		$restRequestHandler = new SyncRestHandler($this->serviceContext);
		list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, $httpsPostBody, NULL);
		
		$parsedResponseBody = NULL;
		try {
			$responseXmlObj = simplexml_load_string($responseBody);
			if ($responseXmlObj && $responseXmlObj->QueryResponse)
			{
				$parsedResponseBody = $this->responseSerializer->Deserialize($responseXmlObj->QueryResponse->asXML(), FALSE);		                                                            
			}
		}
		catch (Exception $e) {
            IdsExceptionManager::HandleException($e);
		}		

		return $parsedResponseBody;
	}

	/**
	 * Creates new batch
	 *
	 * @return Batch returns the batch object
	 */	
	public function CreateNewBatch()
    {
        $batch = new Batch($this->serviceContext, $this->restHandler);
        return $batch;
    }
    
    
    /** 
     * Returns List of entities changed after certain time.
     * @param array entityList List of entity.
     * @param long changedSince DateTime of timespan after which entities were changed.
     * @return IntuitCDCResponse Returns an IntuitCDCResponse.
     */
    public function CDC($entityList, $changedSince)
    {
        $this->serviceContext->IppConfiguration->Logger->CustomLogger->Log(TraceLevel::Info, "Called Method CDC.");
        
        // Validate parameter
        if (count($entityList) <= 0)
        {
            $exception = new IdsException('ParameterNotNullMessage');
	        $this->serviceContext->IppConfiguration->Logger->CustomLogger->Log(TraceLevel::Error, "ParameterNotNullMessage");
            IdsExceptionManager::HandleException($exception);
        }

        $entityString = implode(",",$entityList);
        
        $query = NULL;
        $uri = NULL;

		$formattedChangedSince = date("Y-m-d\TH:m:sP", $changedSince);

		$query = "entities=" . $entityString . "&changedSince=" . $formattedChangedSince;
		$uri = "company/{1}/cdc?{2}";
		//$uri = str_replace("{0}", CoreConstants::VERSION, $uri);
		$uri = str_replace("{1}", $this->serviceContext->realmId, $uri);
		$uri = str_replace("{2}", $query, $uri);

        // Creates request parameters
		$requestParameters = new RequestParameters($uri, 'GET', CoreConstants::CONTENTTYPE_APPLICATIONXML, NULL);
		$restRequestHandler = new SyncRestHandler($this->serviceContext);
		try
		{
		    // gets response
			list($responseCode,$responseBody) = $restRequestHandler->GetResponse($requestParameters, NULL, NULL);
		}
		catch (Exception $e)
		{
		}		
		
        CoreHelper::CheckNullResponseAndThrowException($responseBody);
        $returnValue = new IntuitCDCResponse();
		try {
			$xmlObj = simplexml_load_string($responseBody);
			foreach($xmlObj->CDCResponse->QueryResponse as $oneObj)
			{
				$entities = $this->responseSerializer->Deserialize($oneObj->asXML(), FALSE);	

				$entityName = NULL;
				foreach($oneObj->children() as $oneObjChild)
				{
					$entityName = (string)$oneObjChild->getName();
					break;
				}

                $returnValue->entities[$entityName] = $entities;
			}
		}
		catch (Exception $e) {
            IdsExceptionManager::HandleException($e);
		}		

        $this->serviceContext->IppConfiguration->Logger->CustomLogger->Log(TraceLevel::Info, "Finished Executing Method CDC.");
        return $returnValue;
    }

}
?>
