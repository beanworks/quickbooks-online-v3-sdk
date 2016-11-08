<?php

/**
 * @xmlNamespace http://schema.intuit.com/finance/v3
 * @xmlType string
 * @xmlName IPPRoundingMethodEnum
 * @var IPPRoundingMethodEnum
 * @xmlDefinition 
				Product: ALL
				Description: Enumeration of rounding methods.
			
 */
class IPPRoundingMethodEnum
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
				if (property_exists('IPPRoundingMethodEnum',$initPropName))
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
		public $value;	const IPPROUNDINGMETHODENUM_DOWN = "Down";
	const IPPROUNDINGMETHODENUM_NEAREST = "Nearest";
	const IPPROUNDINGMETHODENUM_NONE = "None";
	const IPPROUNDINGMETHODENUM_UP = "Up";

} // end class IPPRoundingMethodEnum
