<?php
/**
 * Helps repair encoding of QuickBase responses.
 */
class EncodingFixer {

	/**
	 * A list of characters for which QuickBase always uses Windows-1252 encoding. For use in FixQuickBaseEncoding().
	 * "LEFT DOUBLE QUOTATION MARK"
	 * "RIGHT DOUBLE QUOTATION MARK"
	 * "EN DASH"
	 * @var dictionary $Replacements
	 */
	private static $Replacements = array(147, '\u201C',
	                                     148, '\u201D',
	                                     150, '\u2013');
	
	/**
	 * QuickBase has a unique feature which converts certain input characters into windows-1252 encoding and stores them in the database
	 * (This assists Windows users when they use QuickBase HTML UI). When data containing such characters is queried,
	 * the windows-1252 encoding will not change and will remain surrounded by the usual UTF8-encoded XML. If this data requires XML parsing,
	 * the windows-1252 encoded characters have to be re-encoded to UTF8 encoding.This unique feature was recently removed from workplace,
	 * but still exists in QuickBase.
	 * 
	 * @param string $encodedString
	 * @return string $fixedString
	 */
	public function FixQuickBaseEncoding($encodedString)
	{
		// This version of the SDK assumes that it will be used only for workplace not for QuickBase, therefore
		// no workaround is necessary
		return $encodedString;
	}
}

?>