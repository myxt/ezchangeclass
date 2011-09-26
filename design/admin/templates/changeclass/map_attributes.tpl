{def $source_class_attributes      = fetch( 'class', 'attribute_list', hash( 'class_id', $source_class_id ) )
     $destination_class_attributes = fetch( 'class', 'attribute_list', hash( 'class_id', $destination_class_id ) )
     $source_class                 = fetch( 'class', 'list', hash( 'class_filter', array( $source_class_id ) ) )
     $destination_class            = fetch( 'class', 'list', hash( 'class_filter', array( $destination_class_id ) ) )
     $node                         = fetch( 'content', 'node', hash( 'node_id', $source_node_id ) )
     $simple_conversion            = fetch( 'changeclass', 'simple_conversion' )
}

<form action={"changeclass/action"|ezurl} method="post" >
<input type="hidden" name="NodeID" value="{$source_node_id}" />
<div class="context-block">

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">Change object content class &lt;{$node.name|wash()}&gt; [{$node.class_name}]</h1>

<div class="header-mainline"></div>
</div></div></div></div></div></div>

<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">

    {if $warnings}
    <div>Warnings:
        <ul>
        {if is_set($warnings.unsupported_datatypes)}
            <li class="message-error">Destination class has at least one unsupported datatype. You can't use this class.</li>
        {/if}
        {if is_set($warnings.no_children)}
            <li>Destination class is not configured to be container!</li>
        {/if}
        {if is_set($warnings.lost_attributes)}
            <li>The following attributes can not be mapped:
                <ul>
                {foreach $warnings.lost_attributes as $attr}
                    <li>{$attr.name} ({$attr.datatype})</li>
                {/foreach}
                </ul
            </li>
        {/if}
        </ul>
    </div>
    {else}
    <div>No warnigs for this operation</div>
    {/if}



</div>

</div></div></div>

<div class="controlbar">
<div class="box-bc"><div class="box-ml"><div class="box-mr">

<div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
    <input class="button" type="submit" name="SelectSourceObjectButton" value="Back to destination class selection" />
</div>
</div></div></div>

</div></div></div>
</div>


{if is_unset($warnings.unsupported_datatypes)}

<div class="context-block">

<input type="hidden" name="SourceObjectID" value="{$source_object_id}" />
<input type="hidden" name="SourceClassID" value="{$source_class_id}" />
<input type="hidden" name="SourceNodeID" value="{$source_node_id}" />
<input type="hidden" name="DestinationClassID" value="{$destination_class_id}" />


<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h2 class="context-title">Attributes mapping</h2>

<div class="header-subline"></div>

</div></div></div></div></div></div>

<div class="box-ml"><div class="box-mr"><div class="box-content">
<div class="context-attributes">

<table class="list" cellspacing="0">
  <tr>
    <th>Source class ({$source_class.0.name})</th>
    <th>Destination class ({$destination_class.0.name})</th>
  </tr>
{def $iter=0}
{foreach $destination_class_attributes as $dest_attribute sequence array( 'bglight', 'bgdark' ) as $style}
  <tr class="{$style}">
    <td>
    <select name="SourceAttribute[{$dest_attribute.identifier}]">
        <option value="">(leave empty)</option>
    {set $iter=0}
    {foreach $source_class_attributes as $source_attribute}
        {if or( eq( $source_attribute.data_type_string, $dest_attribute.data_type_string ),
                and( is_set($additional_attribute_map[ $source_attribute.data_type_string ]), $additional_attribute_map[ $source_attribute.data_type_string ]|contains( $dest_attribute.data_type_string ) ),
                and(is_set( $simple_conversion[ $source_attribute.data_type_string ] ), $simple_conversion[ $source_attribute.data_type_string ]|contains( $dest_attribute.data_type_string ))
             )}
        <option value="{$source_attribute.identifier}"{if eq( $iter, 0 )} selected="selected"{/if}>{$source_attribute.name} ({$source_attribute.data_type_string})</option>
          {set $iter=inc( $iter )}
        {/if}
    {/foreach}
    </td>
    <td>{$dest_attribute.name} ({$dest_attribute.data_type_string})</td>
  </tr>
{/foreach}

</table>

    <div class="block">
        <label>Options:</label>


    <div><input type="checkbox" name="KeepOwnerAndDate" checked="checked" />Keep original object creator and creation date</div>
    {if gt( $node.children_count, 0 )}
    <div><input type="checkbox" name="CopyChildren" {if is_set($warnings.no_children)}disabled="disabled"{else}checked="checked"{/if} />Add source object's children to new object</div>
    {/if}
    <div><input type="checkbox" name="GenerateConsoleParameters" />Generate parameters for converting all instancees of this class</div>
    {*
    <div><input type="checkbox" name="LeaveOldObject" checked="checked" disabled="disabled" />Leave old object renamed</div>
    <div><input type="checkbox" name="AllVersions" disabled="disabled" />Include all versions</div>
    *}
    </div>

</div>

</div></div></div>

<div class="controlbar">
<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">

    <div class="button-right">
        <input class="button" type="submit" name="ModifyObjectContentClassButton" value="Modify Object" />
        <input class="button" type="submit" name="ChangeObjectContentClassButton" value="Copy Object" />
    </div>
    <div class="break"></div>
</div>
</div></div></div></div></div></div>
</div>
</div>

{/if}
</form>
