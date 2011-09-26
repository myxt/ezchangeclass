<?php
//
// Created on: <13-Jun-2007 ar@ez>
//
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 0.5
// COPYRIGHT NOTICE: Copyright (C) 2007 Lagardere
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

class HFMRelationToBloc
{
    function convertToObjectRelationBlocList( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
    {
        
        include_once( "lib/ezxml/classes/ezxml.php" );
        
        $xml = new eZXML();
        $dom =& $xml->domTree( $sourceObjectAttr->DataText );
        $rel_list = $dom->get_elements_by_tagname( 'relation-item' );
        
        //Note to self: php4 foreach copy's array values instead of referencing them
        for( $i = 0, $c = count( $rel_list ); $c > $i ; $i++)
        {
            $rel_list[$i]->setAttribute('dateDebDay', '');
            $rel_list[$i]->setAttribute('dateDebMonth', '');
            $rel_list[$i]->setAttribute('dateDebYear', '');
            $rel_list[$i]->setAttribute('dateDebHour', '');
        }
        
       
        $newObjectAttr->setAttribute( 'data_text', $dom->toString());
        
        unset( $rel_list ); 
        unset( $dom );
        
        return true;
    }
}

?>