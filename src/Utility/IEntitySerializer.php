<?php 

/**
 * Entity serialize contract.
 */
class IEntitySerializer {

	/**
	 * Serializes the specified entity.
	 * @param object entity
	 * @return object Returns the serialize entity in string format.
	 */
	public function Serialize($entity)
	{
	}

	/**
	 * DeSerializes the message to Type T.
	 * @param T The type to be  serailse to (unused; auto-detected)
	 * @param string The message
	 * @return string Returns the deserialized message.
	 */
	 public function Deserialize($message)
	 {
	 }
}

?>