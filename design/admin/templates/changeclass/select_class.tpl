{def $node=fetch( 'content', 'node', hash( 'node_id', $source_node_id ) )
     $source_class_attributes=fetch( 'class', 'attribute_list', hash( 'class_id', $source_class_id ) )
     $classes=fetch( 'class', 'list' )
     
}


<form action={"changeclass/map_attributes"|ezurl} method="post" >
<input type="hidden" name="SourceObjectID" value="{$source_object_id}" />
<input type="hidden" name="SourceNodeID" value="{$source_node_id}" />

<div class="context-block">

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">Change object content class &lt;{$node.name|wash()}&gt; [{$node.class_name}]</h1>

<div class="header-mainline"></div>

</div></div></div></div></div></div>

<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">

    <table class="list" cellspacing="0">
        <tr>
            <th><label>General object's informations</label></th>
        </tr>
        <tr>
            <td>

    <div class="block">
        <label>Content class name:</label>
        {$node.class_name}
    </div>

    <div class="block">
        <label>Children count:</label>
        {$node.children_count}
    </div>
    {*
    <div class="block">
        <label>Location:</label>
        {$node.main_node_id}
    </div>
      *}
    <div class="block">
        <label>Current version:</label>
        {$node.contentobject_version}
    </div>

    <div class="block">
        {def $related_objects=fetch( 'content', 'related_objects', hash( 'object_id', $node.contentobject_id ) )}
        <label>Related objects [{$related_objects|count()}]:</label>
        {if $related_objects}
            {foreach $related_objects as $related_object}
                {$related_object.name}{delimiter}, {/delimiter}
            {/foreach}
        {else}
            no objects
        {/if}
    </div>

    <div class="block">
        {def $reverse_related_objects=fetch( 'content', 'reverse_related_objects', hash( 'object_id', $node.contentobject_id ) )}
        <label>Reverse related objects [{$reverse_related_objects|count()}]:</label>
        {if $reverse_related_objects}
            {foreach $reverse_related_objects as $reverse_related_object}
                {$reverse_related_object.name}{delimiter}, {/delimiter}
            {/foreach}
        {else}
            no objects
        {/if}
    </div>

    <div class="block">
        <label>Attributes (datatype):</label>
        {foreach $source_class_attributes as $attribute}

            {$attribute.name} ({$attribute.data_type_string}) <br />
        {/foreach}
    </div>


            </td>
        </tr>
    </table>
</div>

</div></div></div>

<div class="controlbar">
<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">

    <label>Destination content class:</label>
    <br />
    <select name="DestinationClassID">
    {foreach $classes as $class}

        <option value="{$class.id}">{$class.name}</option>
    {/foreach}

</select>

<input class="button" type="submit" name="SelectDestinationClassButton" value="Select" />

</div>
</div></div></div></div></div></div>
</div>

</div>
</form>

{*$node|attribute(show)*}
