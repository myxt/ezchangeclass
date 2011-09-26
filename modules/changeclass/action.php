<?php
//
// Created on: <13-Jan-2007>
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




include_once( 'lib/ezfile/classes/ezfile.php' );
include_once( 'lib/ezutils/classes/ezhttptool.php' );
include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
include_once( 'kernel/common/template.php' );
include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezcontentcachemanager.php' );
include_once( 'extension/ezchangeclass/classes/functions.php' );


$http =& eZHTTPTool::instance();
$Module =& $Params["Module"];

//some variables that are reused in the end
global $sourceClassName, $destClassName, $sourceClassIdentifier, $destClassIdentifier, $sourceObjectCount;
$redir_node            = 2;
$destClassName         = '';
$sourceClassName       = '';
$sourceClassIdentifier = 0;
$destClassIdentifier   = 0;
$sourceObjectCount     = 0;


if ( $Module->isCurrentAction( 'SelectSourceObject' ) )
{
    return $Module->redirectTo( '/changeclass/select_class/' . $http->postVariable( 'NodeID' ) );
}
elseif ( $Module->isCurrentAction( 'ModifyObject' )  )
{

    if ( $http->hasPostVariable( 'SourceObjectID' ) )
    {
        $sourceObjectID = $http->postVariable( 'SourceObjectID' );
    }
    if ( $http->hasPostVariable( 'DestinationClassID' ) )
    {
        $destinationClassID = $http->postVariable( 'DestinationClassID' );
    }
    if ( $http->hasPostVariable( 'SourceNodeID' ) )
    {
        $sourceNodeID = $http->postVariable( 'SourceNodeID' );
    }    
    $mapping = $http->postVariable( 'SourceAttribute' );

    conversionFunctions::convertObject( $sourceObjectID, $destinationClassID, $mapping );
    
    $redir_node = $sourceNodeID;
	
}
elseif ( $Module->isCurrentAction( 'CreateObject' ) )
{

    if ( $http->hasPostVariable( 'SourceObjectID' ) )
    {
        $sourceObjectID = $http->postVariable( 'SourceObjectID' );
    }
    if ( $http->hasPostVariable( 'DestinationClassID' ) )
    {
        $destinationClassID = $http->postVariable( 'DestinationClassID' );
    }
    if ( $http->hasPostVariable( 'SourceNodeID' ) )
    {
        $sourceNodeID = $http->postVariable( 'SourceNodeID' );
    }

    $sourceObject =& eZContentObject::fetch( $sourceObjectID );
    if ( !is_object( $sourceObject ) )
    {
        return;
    }
    $sourceObjectDataMap = $sourceObject->dataMap();
    $sectionID = $sourceObject->SectionID;
    $node = eZContentObjectTreeNode::fetch( $sourceNodeID );
    $parentNodeID = $node->ParentNodeID;
    $CreatorUserID = eZUser::currentUserID();
    $class =& eZContentClass::fetch( $destinationClassID );
    $newContentObject =& $class->instantiate( $CreatorUserID, $sectionID );
    $sourceClass =& eZContentClass::fetchByIdentifier( $sourceObject->attribute( 'class_identifier' ) );
    
    $sourceClassName = $sourceObject->className();
    $destClassName = $newContentObject->className();
    $sourceClassIdentifier = $sourceObject->contentClassIdentifier();
    $destClassIdentifier   = $newContentObject->contentClassIdentifier();
    $sourceObjectCount     = $sourceClass->objectCount();

    // Create a node for the object in the tree.
    $nodeAssignment =& eZNodeAssignment::create( array(
                             'contentobject_id' => $newContentObject->attribute( 'id' ),
                             'contentobject_version' => 1,
                             'parent_node' => $parentNodeID,
                             'sort_field' => 2, // Published
                             'sort_order' => 1, // Descending
                             'is_main' => 1));
    $nodeAssignment->store();

    // Set a status for the content object version
    $newContentObjectVersion =& $newContentObject->version( $newContentObject->attribute( 'current_version' ) );
    $newContentObjectVersion->setAttribute( 'status', EZ_VERSION_STATUS_DRAFT);
    $newContentObjectVersion->store();

    // Set the title of the folder
    $newObjectDataMap = $newContentObjectVersion->dataMap();

    // Setting mapped attributes from source object
    foreach ( $http->postVariable( 'SourceAttribute' ) as $destAttr => $sourceAttr  )
    {
        if ( !empty( $sourceAttr ) )
        {
            //echo $sourceAttr;
            $newObjectDataMap[$destAttr]->setAttribute( "data_text",  $sourceObjectDataMap[$sourceAttr]->attribute( 'data_text' ) );
            $newObjectDataMap[$destAttr]->setAttribute( "data_int",   $sourceObjectDataMap[$sourceAttr]->attribute( 'data_int' ) );
            $newObjectDataMap[$destAttr]->setAttribute( "data_float", $sourceObjectDataMap[$sourceAttr]->attribute( 'data_float' ) );
            conversionFunctions::customConverter( $newObjectDataMap[$destAttr], $sourceObjectDataMap[$sourceAttr], $newObjectDataMap[$destAttr] );
            if ( $sourceAttr == 'ezuser' )
            {

                $userAccountObject = eZUser::fetch( $newObjectDataMap[$destAttr]->ContentObjectID );
                $userAccountObject->setInformation( $newContentObject->attribute('id'), $username, $email, $password, $confirmPassword);
                $userAccountObject->store();
            }
            $newObjectDataMap[$destAttr]->store();
        }
    }
    
    
    // We copy related objects before the attributes, this means that the related objects
    // are available once the datatype code is run.
    $relatedObjects =& $sourceObject->relatedContentObjectArray( );
    foreach ( array_keys( $relatedObjects ) as $key )
    {
        $relatedObject =& $relatedObjects[$key];
        $objectID = $relatedObject->attribute( 'id' );
        $newContentObject->addContentObjectRelation( $objectID );
    }

    // Set source object's creator and creation date if needed
    if ( $http->hasPostVariable( 'KeepOwnerAndDate' ) )
    {
        $newContentObject->setAttribute( 'owner_id', $sourceObject->attribute( 'owner_id' ) );
        $newContentObject->setAttribute( 'published', $sourceObject->attribute( 'published' ) );
        $newContentObject->store();
    }


    // Now publish the object.
    $operationResult = eZOperationHandler::execute( 'content', 'publish',
                              array( 'object_id' => $newContentObject->attribute( 'id' ),
                                     'version' => $newContentObject->attribute('current_version' ) ) );



    // Adding location for all children - if option selected
    if ( $http->hasPostVariable( 'CopyChildren' )  )
    {
    
        $sourceNode = eZContentObjectTreeNode::fetch( $sourceNodeID );
        $sourceObjectChildren = $sourceNode->children();
        $node =& eZContentObjectTreeNode::fetchByContentObjectID( $newContentObject->ID );
        $newObjectNodeID = $node[0]->MainNodeID;
        foreach ( $sourceObjectChildren as $child )
        {
            // Create a node for the object in the tree.
            $object =& eZContentObject::fetch( $child->ContentObjectID );

            $db =& eZDB::instance();
            $db->begin();
            $nodeAssignment =& eZNodeAssignment::create( array(
                                 'contentobject_id' => $object->attribute( 'id' ),
                                 'contentobject_version' => $object->attribute( 'current_version' ),
                                 'parent_node' => $newObjectNodeID,
                                 'is_main' => 0));
            $nodeAssignment->store();
            $db->commit();
        
            //$child->ContentObjectID
            $operationResult = eZOperationHandler::execute( 'content', 'publish',
                              array( 'object_id' => $object->attribute( 'id' ),
                                     'version' => $object->attribute( 'current_version' ) ) );
        }
    }
    
    $redir_node = $newObjectNodeID;
}
else
    return $Module->redirectTo( '/content/view/full/2' );


if ( $http->hasPostVariable( 'GenerateConsoleParameters' ) )
{

    $file_content = $sourceClassIdentifier . ':' . $destClassIdentifier;
    foreach ( $http->postVariable( 'SourceAttribute' ) as $destAttr => $sourceAttr  )
    {
        $file_content .=  "\n" . $sourceAttr . ':' . $destAttr;
    }
    $file_name = time() . '.txt';
    $file_path = eZSys::cacheDirectory();
    
    $r = eZFile::create( $file_name, $file_path, $file_content );
    
    $tpl =& templateInit();
    $tpl->setVariable( 'redir_uri', 'content/view/full/' . $redir_node );
    $tpl->setVariable( 'dest_class_name', $destClassName );
    $tpl->setVariable( 'source_class_name', $sourceClassName);
    $tpl->setVariable( 'convert_file_ok', $r );
    $tpl->setVariable( 'convert_file_path', $file_path . '/'. $file_name );
    $tpl->setVariable( 'convert_file_name', $file_name );
    $tpl->setVariable( 'convert_file_content',  $file_content );
    $tpl->setVariable( 'source_object_count', $sourceObjectCount );
    
    $Result = array();
    $Result['content'] =& $tpl->fetch( 'design:changeclass/action.tpl' );
    $Result['path'] = array( array( 'url' => false,
                                    'text' => 'Generated Console Params' ) );
}
else
    $Module->redirectTo( '/content/view/full/' . $redir_node );



?>
