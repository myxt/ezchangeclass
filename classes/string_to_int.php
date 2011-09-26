<?php
//
// Created on: <18-Sep-2007 ar>
//
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 0.5
// COPYRIGHT NOTICE: Copyright (C) 2007 eZ Systems
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



class eZStringToInt
{
	function convertToInt( &$newObjectAttr, $sourceObjectAttr, $destObjectAttr )
	{
		$type = $newObjectAttr->attribute('data_type_string');
		$text = $sourceObjectAttr->attribute('data_text');
		switch ( $type )
		{
    		case 'ezfloat':
    		    // we'll convert comma to dot since float only works with . as a
    		    // decimal separator
    		    $newObjectAttr->setAttribute( 'data_float', (float) str_replace(',', '.', $text ) );
        		break;
            case 'ezinteger':
                $newObjectAttr->setAttribute( 'data_int', (int) $text );
                break;
		}

		return true;
	}
}

?>
