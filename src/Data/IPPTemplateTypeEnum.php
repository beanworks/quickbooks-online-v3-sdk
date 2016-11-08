<?php

/**
 * @xmlNamespace http://schema.intuit.com/finance/v3
 * @xmlType string
 * @xmlName IPPTemplateTypeEnum
 * @var IPPTemplateTypeEnum
 * @xmlDefinition 
				Product: ALL
				Description: Enumeration of template types.
			
 */
class IPPTemplateTypeEnum
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
				if (property_exists('IPPTemplateTypeEnum',$initPropName))
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
		public $value;	const IPPTEMPLATETYPEENUM_BUILDASSEMBLY = "BuildAssembly";
	const IPPTEMPLATETYPEENUM_CREDITMEMO = "CreditMemo";
	const IPPTEMPLATETYPEENUM_ESTIMATE = "Estimate";
	const IPPTEMPLATETYPEENUM_INVOICE = "Invoice";
	const IPPTEMPLATETYPEENUM_PURCHASEORDER = "PurchaseOrder";
	const IPPTEMPLATETYPEENUM_SALESORDER = "SalesOrder";
	const IPPTEMPLATETYPEENUM_SALESRECEIPT = "SalesReceipt";
	const IPPTEMPLATETYPEENUM_STATEMENTCHARGE = "StatementCharge";

} // end class IPPTemplateTypeEnum
