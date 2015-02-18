<?php

require_once(PATH_SDK_ROOT . 'Utility/Serialization/IEntitySerializer.php');
require_once(PATH_SDK_ROOT . 'Diagnostics/TraceLogger.php');

/**
 * Json Serialize(r) to serialize and de serialize.
 */
class JsonObjectSerializer extends IEntitySerializer {

	/**
	 * The ids logger.
	 * @var ILogger IDSLogger
	 */
	 private $IDSLogger;

	/**
	 * Initializes a new instance of the JsonObjectSerializer class.
	 * @param IDSLogger idsLogger The ids logger.
	 */
	public function __construct($idsLogger=NULL) {
		if ($idsLogger)
			$this->IDSLogger = $idsLogger;
		else		
			$this->IDSLogger = new TraceLogger();
	}

	/**
	 * Serializes the specified entity.
	 * @param object entity The entity
	 * @return string Returns the serialize entity in string format.
	 */
	public function Serialize($entity)
	{
		return NULL;
	}
	
	/**
	 * DeSerializes the specified action entity type.
	 * @param string message The message.
	 * @return Returns the de serialized object.
	 */
	public function Deserialize($message)
	{
		return NULL;
	} 
}

?>
