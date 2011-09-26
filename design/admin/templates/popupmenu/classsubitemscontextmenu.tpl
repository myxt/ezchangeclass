 <hr/>
    <a id="menu-class-change" href="#" onmouseover="ezpopmenu_mouseOver( 'ContextMenu' )"
       onclick="ezpopmenu_submitForm( 'menu-form-class-change-sub' ); return false;">{"Change content class"|i18n("design/admin/changeclass")}</a>


<form id="menu-form-class-change-sub" method="post" action={"/changeclass/action"|ezurl}>
  <input type="hidden" name="NodeID" value="%nodeID%" />
  <input type="hidden" name="ObjectID" value="%objectID%" />
  <input type="hidden" name="SelectSourceObjectButton" value="submit" />
</form>
