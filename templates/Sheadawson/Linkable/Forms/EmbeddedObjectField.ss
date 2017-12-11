<% if $isReadonly %>
	<span id="$ID"
	      <% if $extraClass %>class="$extraClass"<% end_if %>
	      <% if $Description %>title="$Description"<% end_if %>>
		$Value
	</span>
<% else %>
	<div class="embeddedObjectUrl">
		<input type='button' class="embeddedObjectLoad ss-ui-button ss-ui-button-small" data-href="$Link(update)" value='<% _t('Linkable.INSPECT','Inspect') %>' />
		$SourceURL.SmallFieldHolder
	</div>

<% if $Message %>
<div class="errorMessage"><p>$Message</p></div>
<% end_if %>

	<% if $Title %>
	<div class="embedThumb">
	$ThumbImage
	</div>

	<div class="embeddedObjectProperties">
		$ObjectTitle.SmallFieldHolder

		<div class="dimensions">
			<div class="widthHeightField">
			$Width.SmallFieldHolder
			</div>
			<div class="widthHeightField">
			$Height.SmallFieldHolder
			</div>
		</div>

		$ObjectDescription.SmallFieldHolder

		$ExtraClass.SmallFieldHolder

		$ThumbURL.SmallFieldHolder
		$Type.SmallFieldHolder
		$EmbedHTML.SmallFieldHolder
	</div>
	<% end_if %>
<% end_if %>
