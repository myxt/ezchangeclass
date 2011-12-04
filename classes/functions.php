<?php
//
// Created on: <15-Jun-2007>
//
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 0.5
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

include_once( 'lib/ezdb/classes/ezdb.php' );
include_once( 'kernel/classes/ezcontentclass.php' );
include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezcontentcachemanager.php' );

class conversionFunctions
{
    var $simpleConversionArray = false;
    
    function conversionFunctions()
    {
    }

    function getSimpleConversionArray()
    {
        if ( $this->simpleConversionArray )
            return $this->simpleConversionArray;
        
        $ini = eZINI::instance( 'changeclass.ini' );
        if ( $ini->hasGroup( 'SimpleConversion' ) )
        {
            $simpleConversion = $ini->variable( 'SimpleConversion', 'SupportedConversion' );
            if ( empty( $simpleConversion ) )
            {
                return false;
            }
            $simpleConversionArray = array();
            foreach ( $simpleConversion as $item  )
            {
                $element = explode(  ';', $item );
                if  ( isset( $simpleConversionArray[$element[0]] ) )
                    $element[1] = array_merge( $simpleConversionArray[$element[0]], array( $element[1] ) );
                else
                    $element[1] = array( $element[1] );
                          
                $simpleConversionArray[$element[0]] = $element[1];
            }
            $this->simpleConversionArray = $simpleConversionArray;
            return $simpleConversionArray;
        }
    }


    function fetchSimpleConversionArray()
    {
        return array( 'result' => $this->getSimpleConversionArray() );
    }


    function customConverter( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
    {
        // We need to look for custom translation scripts if the datatype differs
        if ( $sourceObjectAttr->DataTypeString != $destObjectAttr->DataTypeString )
        {
            $ini = eZINI::instance( 'changeclass.ini' );
            $simpleConversion = $ini->variable( 'SimpleConversion', 'SupportedConversion' );
            if ( in_array( $sourceObjectAttr->DataTypeString.';'.$destObjectAttr->DataTypeString, $simpleConversion ) )
            {
                return true;
            }
        
        
            $die = true;
            if ( $ini->hasGroup( $sourceObjectAttr->DataTypeString ) )
            {
                $possible_dest = $ini->variable( $sourceObjectAttr->DataTypeString, 'SupportedDestination' );
                if ( !is_array( $possible_dest ) ) $possible_dest = array( $possible_dest );
                foreach( $possible_dest as $p_dest )
                {
                    if ( $p_dest == $destObjectAttr->DataTypeString )
                    {
                        $die = false;
                        break;
                    }
                }
            }
                
            if ( $die )
            {
                echo 'ERROR: Could not find any custom convertions from ' . $sourceObjectAttr->DataTypeString . ' to ' . $destObjectAttr->DataTypeString . "\n<br />\n";
                die();
            }    
            else
            {
                $script = $ini->variable( $sourceObjectAttr->DataTypeString, 'Script' );
                if ( file_exists( $script ) )
                {
                    include_once( $script );
                    $script = $ini->variable( $sourceObjectAttr->DataTypeString, 'Class' );
                    $class = trim( $ini->variable( $sourceObjectAttr->DataTypeString, 'Class' ) );
                    $function = $ini->variable( $sourceObjectAttr->DataTypeString, 'Function' );
    
                    if ( $class != '' )
                        $callback = array( $class, $function );
                    else
                        $callback = $function;
                    
                    if ( is_callable( $callback ) )
                    {
                        $ret = call_user_func_array( $callback, array( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr ) );
                        
                        if ( !$ret )
                        {
                            echo "ERROR: custom converter '" . $function . "' returned false on id: $newObjectAttr->ContentClassAttributeID \n<br />\n";
                            die();
                        }
    
                    }
                }
                else
                {
                    echo 'ERROR: Could not find script for custom converter of datatype ' . $sourceObjectAttr->DataTypeString . "\n<br />\n";
                    die();
                }
            }
        }    
    }
    function convertObject( $sourceObjectID, $destinationClassID, $mapping )
    {
    
       $sourceObject = eZContentObject::fetch( $sourceObjectID );
        
        if ( !is_object( $sourceObject ) )
        {
            return false;
        }
        $sourceObjectDataMap = $sourceObject->dataMap();
        
        // getting attributes from class
        if ( is_numeric( $destinationClassID ) )
        {
            $destClass = eZContentClass::fetch( $destinationClassID );
        }
        else
        {
            $destClass = eZContentClass::fetchByIdentifier( $destinationClassID );
            $destinationClassID = $destClass->ID;
        }
        
        if (!$destClass)
        {
            global $eZContentObjectContentObjectCache, $eZContentObjectDataMapCache, $eZContentObjectVersionCache;
            unset( $eZContentObjectContentObjectCache );
            unset( $eZContentObjectDataMapCache);
            unset( $eZContentObjectVersionCache );
            echo "No destClass for '$destinationClassID'<br />\n";
            return false;	
        }
        
        $destClassDataMap = $destClass->dataMap();
        $sourceClass = eZContentClass::fetchByIdentifier( $sourceObject->attribute( 'class_identifier' ) );
        $sourceClassDataMap = $sourceClass->dataMap();
        
        
    
    
        if ( !$destClassDataMap )
        {
            global $eZContentObjectContentObjectCache, $eZContentObjectDataMapCache, $eZContentObjectVersionCache;
            unset( $eZContentObjectContentObjectCache );
            unset( $eZContentObjectDataMapCache);
            unset( $eZContentObjectVersionCache );
            echo "No destClassDataMap for '$destinationClassID'<br />\n";
            return false;
        }
        
        global $sourceClassName, $destClassName, $sourceClassIdentifier, $destClassIdentifier, $sourceObjectCount;
        
        $sourceClassName       = $sourceObject->className();
        $destClassName         = $destClass->attribute( 'name' );
        $sourceClassIdentifier = $sourceObject->contentClassIdentifier();
        $destClassIdentifier   = $destClass->attribute( 'identifier' );
        $sourceObjectCount     = $sourceClass->objectCount() -1;
        
        
        // building array with missing attributes
        foreach ( array_keys( $destClassDataMap ) as $attribute )
        {
            $destAttributes[] = $attribute;
        }
    
        $versions = $sourceObject->attribute( 'versions' );
        foreach ( ( $versions ) as $version )
        {
            $objectVersions[] = $version->Version;
        }
        
        // changing existing attributes
        $db = eZDB::instance();
        $db->begin();
        $usedAttributes = array();
        $missingAttributes = array();
        $duplicatedAttribures = array();
        
        foreach ( $mapping as $key => $value )
        {
            if ( empty( $value ) )
            {
                //echo 'missing: ' . $key;
                $missingAttributes[] = $key;
                continue;
            }
           /*
            *  It can happen that one or more source attributes should be copied
            *  into more than one new attribute, in this case we need special
            *  part of code to make proper steps, here we gather informations
            */
            if ( in_array( $value, $usedAttributes ) )
            {
                $duplicatedAttribures[$key] = $value;
                continue;
            }
            $usedAttributes[] = $value;
            // foreach version
            foreach ( $objectVersions as $version )
            {
                //echo "<br />dataMap: ".$sourceObjectDataMap[$value]->ID . "  ver: " . $version;
                $sourceObjectAttr = eZContentObjectAttribute::fetch( $sourceObjectDataMap[$value]->ID, $version );
                if ( !is_object( $sourceObjectAttr ) )
                {
                    // echo("skip version");
                    continue;
                }
                $sourceObjectAttr->setAttribute( 'contentclassattribute_id', $destClassDataMap[$key]->ID );
                conversionFunctions::customConverter( $sourceObjectAttr, $sourceObjectDataMap[$value], $destClassDataMap[$key] );
                $sourceObjectAttr->store();
            }
        }
        // adding extra attributes in case when source attribute has been selected to be copied
        // to more than one destination attribute - duplicated attributes
        if ( $duplicatedAttribures )
        {
            foreach ( $duplicatedAttribures as $destAttr => $sourceAttr )
            {
                $attributeID = $destClassDataMap[$destAttr]->ID;
                $iter = 0;
                foreach ( $objectVersions as $version )
                {
                    
                    // if obects has more than one version to update, it should be done with clone method
                    if ( $iter == 0  )
                    {
                        $newAttribute = eZContentObjectAttribute::create( $attributeID, $sourceObjectID, $version );
                        $newAttribute->setContent( $sourceObjectDataMap[$sourceAttr]->content() );
                        conversionFunctions::customConverter( $newAttribute, $sourceObjectDataMap[$sourceAttr], $destClassDataMap[$destAttr] );
                        $newAttribute->store();
                    }
                    else
                    {
                        //echo "<br />version $version. newAttr $newAttr, verion[0] $objectVersions[0] ";
                        $clonedAttribute = $newAttribute->cloneContentObjectAttribute( $version, $objectVersions[0], $sourceObjectID );
                        $clonedAttribute->setContent( $sourceObjectDataMap[$sourceAttr]->content() );
                        conversionFunctions::customConverter( $clonedAttribute, $sourceObjectDataMap[$sourceAttr], $destClassDataMap[$destAttr] );
                        $clonedAttribute->sync();
                    }
                    $iter++;
                }
            }
        }
    
    
        // adding non-existing attributes
        if ( $missingAttributes )
        {
            foreach ( $missingAttributes as $newAttr )
            {
                $attributeID = $destClassDataMap[$newAttr]->ID;
                $iter = 0;
                foreach ( $objectVersions as $version )
                {
                    // if obects has more than one version to update, it should be done with clone method
                    if ( $iter == 0  )
                    {
                        $newAttribute = eZContentObjectAttribute::create( $attributeID, $sourceObjectID, $version );
                        $newAttribute->store();
                    }
                    else
                    {
                        $clonedAttribute = $newAttribute->cloneContentObjectAttribute( $version, $objectVersions[0], $sourceObjectID );
                        $clonedAttribute->sync();
                    }
                    $iter++;
                }
            }
        }
    
        // removing attributes not needed anymore
        foreach ( array_keys( $sourceClassDataMap ) as $oldAttr  )
        {
            if ( !in_array( $oldAttr, $usedAttributes ) )
            {
                $attributeID = $sourceObjectDataMap[$oldAttr]->ID;
                //echo "<br />removing attributeID $attributeID, ver $objectVersions[0]";
                $oldAttribute = eZContentObjectAttribute::fetch( $attributeID, $objectVersions[0] );
                if ( is_object( $oldAttribute ) )
                {
                    $oldAttribute->remove( $attributeID );
                }
            }
        }
        $db->commit();
    
        // setting new object's class class id
        $sourceObject->setAttribute( 'contentclass_id', $destinationClassID );
        
        //Store the object, this also clears its object cache
        $sourceObject->store();
    
        //clear cache
        eZContentCacheManager::clearContentCache( $sourceObjectID );
        
        return true;
        
    
    }
}




?>