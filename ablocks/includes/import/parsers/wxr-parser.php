<?php

namespace ABlocks\import\parsers;

use ABlocks\import\parsers\WXR_Parser_Regex;
use ABlocks\import\parsers\WXR_Parser_SimpleXML;
use ABlocks\import\parsers\WXR_Parser_XML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Importer class for managing parsing of WXR files.
 */
class WXR_Parser {
	public function parse( $file ) {
		// Attempt to use proper XML parsers first
		if ( extension_loaded( 'simplexml' ) ) {
			$parser = new WXR_Parser_SimpleXML();
			$result = $parser->parse( $file );

			// If SimpleXML succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'SimpleXML_parse_error' !== $result->get_error_code() ) {
				return $result;
			}
		} elseif ( extension_loaded( 'xml' ) ) {
			$parser = new WXR_Parser_XML();
			$result = $parser->parse( $file );

			// If XMLParser succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'XML_parse_error' !== $result->get_error_code() ) {
				return $result;
			}
		}

		// use regular expressions if nothing else available or this is bad XML
		$parser = new WXR_Parser_Regex();
		return $parser->parse( $file );
	}
}
