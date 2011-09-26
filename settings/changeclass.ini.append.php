<?php /* #?ini charset="iso-8859-1"?

[General]
#Logs changes done using the batch script in your cache folder
#in the file: logEzChangeClass.txt
ScriptLog=enabled

#List of unsupported datatypes
UnsupportedDataTypeArray[]
UnsupportedDataTypeArray[]=ezuser


# Custom mapping of datatypes
# makes it possible to map one datatype to another
# thru your own scripts
# example:
# [ezstring]
# SupportedDestination[]
# SupportedDestination[]=eztext
# SupportedDestination[]=ezxmltext
# Script=extension/ezchangeclass/classes/ezstring.php
# Class=myConverterClass
# Function=convert
#
# You'll get the $newAttribute byRef, $sourceAttribute byVal and $destinationAttribute byVal 

[ezstring]
SupportedDestination[]
SupportedDestination[]=ezxmltext
Script=extension/ezchangeclass/classes/text_to_xml.php
Class=PolitoTextToXml
Function=convertToXml

[eztext]
SupportedDestination[]
SupportedDestination[]=ezxmltext
Script=extension/ezchangeclass/classes/text_to_xml.php
Class=PolitoTextToXml
Function=convertToXml

[ezobjectrelationlist]
SupportedDestination[]
SupportedDestination[]=ezobjectrelationlistbloc
Script=extension/ezchangeclass/classes/relation_to_bloc.php
Class=HFMRelationToBloc
Function=convertToObjectRelationBlocList

[ezstring]
SupportedDestination[]
SupportedDestination[]=ezinteger
SupportedDestination[]=ezfloat
Script=extension/ezchangeclass/classes/string_to_int.php
Class=eZStringToInt
Function=convertToInt

[SimpleConversion]
# Simple conversion between different datatypes
# example:
# SupportedConversion[]=source_datatype;destination_datatype
SupportedConversion[]
SupportedConversion[]=eztext;ezstring
SupportedConversion[]=ezstring;eztext
SupportedConversion[]=ezemail;eztext
SupportedConversion[]=ezemail;ezstring

*/?>
