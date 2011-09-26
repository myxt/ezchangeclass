<?php
//
// Created on: <7-Sep-2007 mauro.innocenti>
//
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 0.5
// COPYRIGHT NOTICE: Copyright (C) 2007 Politecnico di Torino
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

include_once( 'kernel/classes/datatypes/ezxmltext/handlers/input/ezsimplifiedxmlinputparser.php' );
include_once( 'kernel/classes/datatypes/ezxmltext/ezxmltexttype.php' );

class PolitoTextToXml
{
	function convertToXml( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
	{
		$text = $sourceObjectAttr->attribute('data_text');
		$XMLContent = $text ? "<section><paragraph>$text</paragraph></section>" : '';
		$parser = new eZSimplifiedXMLInputParser( $newObjectAttr->ContentObjectID );
		$parser->setParseLineBreaks( true );
		$document = $parser->process( $XMLContent );
		$xml_string = eZXMLTextType::domString( $document );
		$newObjectAttr->setAttribute( 'data_text', $xml_string );

		return true;
	}
}

?>
