<div class="context-block">

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">Convert Instructions for converting class '{$source_class_name}' to '{$dest_class_name}'</h1>

<div class="header-mainline"></div>
</div></div></div></div></div></div>

<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">

<div>
A file with all params for this convertion has been created in '{$convert_file_path}' and can be used 
for batch convertions of all remaining ({$source_object_count} objects) '{$source_class_name}' to 
class '{$dest_class_name}'.<br /><br />

{if $convert_file_ok|not}
<div style="padding: 1em; border: 2px solid red; margin: 1em;">
<b>ERROR:</b> eZFile could not store parameter file!<br /> Do you have write access to path ({$convert_file_path})?
</div><br />
{/if}

<i>Command line command for running this script would be like this:</i><br />
<pre>
php extension/ezchangeclass/scripts/classconvert.php -s example --param-file={$convert_file_name}
</pre>
Notice:<br />
* Switch 'example' with the name of your siteaccess, preferably the one you are using right now!<br />&nbsp; ( so your sure it uses same db and cache folder )<br />
* Please make sure that the 'php' command is actually the php 4 cli (command line interface), and not php5!<br />
<br /><br />
</div>

<div>
<i>Content of convert 'paramfile' ({$convert_file_name}):</i><br />
<pre>
{$convert_file_content}
</pre>
</div>

</div>

</div></div></div>

<div class="controlbar">
<div class="box-bc"><div class="box-ml"><div class="box-mr">

<div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
    <a href={$redir_uri|ezurl}>Continue to new object</a>
</div>
</div></div></div>

</div></div></div>
</div>

