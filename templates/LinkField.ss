<input $AttributesHTML style='display: none'/>

<% if Value %>
	<strong>$LinkObject &nbsp;</strong>
	<button href='#' class='linkfield-button ss-ui-button ss-ui-button-small'>Update Link</button>
<% else %>
	<button href='#' class='linkfield-button ss-ui-button ss-ui-button-small'>Insert Link</button>
<% end_if %>

<div class='linkfield-dialog'></div>