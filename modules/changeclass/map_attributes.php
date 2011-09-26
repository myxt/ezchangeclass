<?php
//
// Created on: <13-Jan-2007>
//
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 0.1
// COPYRIGHT NOTICE: Copyright (C) 2007 Bartek Modzelewski
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

include_once( 'kernel/common/template.php' );
include_once( "lib/ezutils/classes/ezhttptool.php" );
include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
include_once( 'kernel/classes/ezcontentclass.php' );
include_once( 'extension/ezchangeclass/classes/functions.php' );


$Module =& $Params['Module'];
$http =& eZHTTPTool::instance();


//print_r($_POST);

$sourceObjectID     = $http->postVariable( 'SourceObjectID' );
$sourceNodeID       = $http->postVariable( 'SourceNodeID' );
$destinationClassID = $http->postVariable( 'DestinationClassID' );
$ini = eZINI::instance( 'changeclass.ini' );

$sourceObject = eZContentObject::fetch( $sourceObjectID );
$sourceClassID = $sourceObject->ClassID;
$warnings = array();

// checking children
$sourceNode = eZContentObjectTreeNode::fetch( $sourceNodeID );
if (!is_object( $sourceNode ))
    return false;
$sourceChildrenCount = $sourceNode->attribute( 'children_count' );
$destinationClass =& eZContentClass::fetch( $destinationClassID );
if ( $destinationClass->attribute( 'is_container' ) == 0 )
{
    $warnings['no_children'] = true;
}
// checking lost attributes
foreach ( $destinationClass->dataMap() as $attr )
{
    $destinationDataTypeArray[] = $attr->DataTypeString;
}
$lost_attributes = array();
$i = 0;
$sourceClass =& eZContentClass::fetch( $sourceClassID );
$additionalAttributeMap = array();
$converter = new conversionFunctions();
foreach ( $sourceClass->dataMap() as $sourceClassAttr )
{

    if ( $ini->hasGroup( $sourceClassAttr->DataTypeString ) )
    {
        //In case it's a custom convertion script for this datatype, we need to search for that
        $possible_dest = $ini->variable( $sourceClassAttr->DataTypeString, 'SupportedDestination' );
        if ( !is_array( $possible_dest ) ) $possible_dest = array( $possible_dest );
        $additionalAttributeMap[$sourceClassAttr->DataTypeString] = $possible_dest;
        foreach( $possible_dest as $p_dest )
        {
            if ( in_array( $p_dest, $destinationDataTypeArray ) )
            {
                continue 2;
            }
        }
    }
    if ( !in_array( $sourceClassAttr->DataTypeString, $destinationDataTypeArray ) )
    {
        // Don't give warning if it's possible to make simple conversion
        $simpleConversion = $converter->getSimpleConversionArray();
        foreach ( $destinationDataTypeArray as $destinationDataType )
        {
            if ( isset( $simpleConversion[$sourceClassAttr->DataTypeString] ) && in_array( $destinationDataType, $simpleConversion[$sourceClassAttr->DataTypeString] ) )
            {
                continue 2;
            }
        }
        if ( isset( $sourceClassAttr->Name)) $lost_attributes[$i]['name'] = $sourceClassAttr->Name;
        else $lost_attributes[$i]['name'] = $sourceClassAttr->Identifier;
        $lost_attributes[$i]['datatype'] = $sourceClassAttr->DataTypeString;
        $i++;
    }
}
if ( $i > 0 )
{
    $warnings['lost_attributes'] = $lost_attributes;
}

// checking for unsupported datatypes
$unsupported = $ini->variable( 'General', 'UnsupportedDataTypeArray' );
$unsupportedDataTypes = array();
foreach ( $unsupported as $datatype )
{
    if ( in_array( $datatype, $destinationDataTypeArray ) )
        $unsupportedDataTypes[] = $datatype;
}
if ( !empty( $unsupportedDataTypes ) )
{
    $warnings['unsupported_datatypes'] = $unsupportedDataTypes;
}

//echo "<pre>";
//print_r( $unsupportedDataTypes );
//print_r(  $sourceDataTypeArray );
//echo "</pre>";



$tpl =& templateInit();
$tpl->setVariable( 'source_class_id', $sourceClassID );
$tpl->setVariable( 'source_object_id', $sourceObjectID );
$tpl->setVariable( 'source_node_id', $sourceNodeID );
$tpl->setVariable( 'destination_class_id', $destinationClassID );
$tpl->setVariable( 'additional_attribute_map', $additionalAttributeMap );
$tpl->setVariable( 'warnings', $warnings );

$Result = array();
$Result['content'] =& $tpl->fetch( 'design:changeclass/map_attributes.tpl' );
$Result['path'] = array( array( 'url' => false,
                                'text' => 'Attributes mapping' ) );






?>