<?php
//
// Created on: <06-Oct-2002 16:01:10 amos>
//
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.9.0
// BUILD VERSION: 17785
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
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

/*! \file function_definition.php
*/

$FunctionList = array();
$FunctionList['simple_conversion'] = array( 'name' => 'simple_conversion',
                                      'operation_types' => array( 'read' ),
                                      'call_method' => array( 'include_file' => 'extension/ezchangeclass/classes/functions.php',
                                                              'class' => 'conversionFunctions',
                                                              'method' => 'fetchSimpleConversionArray' ),
                                      'parameter_type' => 'standard',
                                      'parameters' => array() ) ;
?>
