<?php

/**
 * @xmlNamespace http://schema.intuit.com/finance/v3
 * @xmlType string
 * @xmlName IPPOLBTxnStatusEnum
 * @var IPPOLBTxnStatusEnum
 * @xmlDefinition  Product: All
				Description: Enumeration of  OLBTransactions Status 
 */
class IPPOLBTxnStatusEnum
	{

		/**                                                                       
		* Initializes this object, optionally with pre-defined property values    
		*                                                                         
		* Initializes this object and it's property members, using the dictionary
		* of key/value pairs passed as an optional argument.                      
		*                                                                         
		* @param dictionary $keyValInitializers key/value pairs to be populated into object's properties 
		* @param boolean $verbose specifies whether object should echo warnings   
		*/                                                                        
		public function __construct($keyValInitializers=array(), $verbose=FALSE)
		{
			foreach($keyValInitializers as $initPropName => $initPropVal)
			{
				if (property_exists('IPPOLBTxnStatusEnum',$initPropName))
				{
					$this->{$initPropName} = $initPropVal;
				}
				else
				{
					if ($verbose)
						echo "Property does not exist ($initPropName) in class (".get_class($this).")";
				}
			}
		}

		/**
		 * @xmlType value
		 * @var string
		 */
		public $value;	const IPPOLBTXNSTATUSENUM_PENDING = "Pending";
	const IPPOLBTXNSTATUSENUM_APPROVED = "Approved";
	const IPPOLBTXNSTATUSENUM_DELETED = "Deleted";

} // end class IPPOLBTxnStatusEnum
