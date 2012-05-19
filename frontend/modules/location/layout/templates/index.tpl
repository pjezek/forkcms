{*
	variables that are available:
	- {$locationItems}: contains data about all locations
	- {$locationSettings}: contains this module's settings
*}

{option:locationItems
	{* @remark: do not remove the parseMap-class, it is used by JS *}
	<div id="map" class="parseMap" style="height: {$locationSettings.height}px; width: {$locationSettings.width}px;"></div>

	{* Store item text in a div because JS goes bananas with multiline HTML *}
	{iteration:locationItems}
		<div id="markerText{$locationItems.id}" style="display:none;">
			<p>{$locationItems.street} {$locationItems.number}</p>
			<p>{$locationItems.zip} {$locationItems.city}</p>
		</div>
	{/iteration:locationItems}
{/option:locationItems}