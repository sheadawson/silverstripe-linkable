<input $AttributesHTML style='display: none'/>

<% if $Value %>
	$LinkObject &nbsp;
	<button href='#' class='linkfield-button ss-ui-button ss-ui-button-small'><%t Linkable.EDIT 'Edit' %></button>
	<button href='#' class='linkfield-remove-button ss-ui-button ss-ui-button-small ss-ui-action-destructive'><%t Linkable.REMOVE 'Remove' %></button>
<% else %>
	<button href='#' class='linkfield-button ss-ui-button ss-ui-button-small'><%t Linkable.ADDLINK 'Add Link' %></button>
<% end_if %>

<div class='linkfield-dialog'></div>