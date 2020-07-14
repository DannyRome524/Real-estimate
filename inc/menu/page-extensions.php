<?php
$plugins_data = array(
	array(
		'title' => 'XML, CSV Importer',
		'desc' => 'Import property data from XML or CSV file using this addon and WP All Import, easily and free',
		'class_name' => 'RapidAddon',
		'button_url' => 'https://webcodingplace.com/xml-csv-importer-real-estate-manager/',
		'plugin_type' => 'free',
		'docs' => 'https://kb.webcodingplace.com/real-estate-manager/importer-for-wp-all-import-xml-csv/',
	),
	array(
		'title' => 'Export Import',
		'desc' => 'It lets you export Settings, Fields, and Properties from one site in a JSON file, and then allows you to import them into another site.',
		'class_name' => 'REM_Export_Import',
		'button_url' => 'https://webcodingplace.com/export-import-real-estate-manager/',
		'plugin_type' => 'free',
		'docs' => 'https://kb.webcodingplace.com/real-estate-manager/export-and-import/',
	),
	array(
		'title' => 'Property Listing Styles',
		'desc' => 'Add 20+ more property listing styles to your site by just installing this addon. All styles are fully responsive and supported with all major browsers.',
		'class_name' => 'REM_Property_Styles',
		'button_url' => 'https://webcodingplace.com/property-listing-styles-real-estate-manager-extension/',
		'plugin_type' => 'pro',
		'docs' => 'https://kb.webcodingplace.com/real-estate-manager/property-listing-styles/',
	),
	array(
		'title' => 'Social Share Properties',
		'desc' => 'Share properties socially. More than 15 social networks are integrated including Facebook, Twitter, Google+, Tumblr, E-Mail, Pinterest, LinkedIn, Reddit, XING, WhatsApp, Hacker News, VK and Telegram.',
		'class_name' => 'REM_Social_Share',
		'button_url' => 'https://webcodingplace.com/social-share-real-estate-manager-extension/',
		'plugin_type' => 'pro',
		'docs' => 'https://kb.webcodingplace.com/real-estate-manager/social-share-properties/',
	),
	array(
		'title' => 'Google Map Filters',
		'desc' => 'Display properties on map and let the users search them on map directly. You can also display results in map and page at the same time.',
		'class_name' => 'REM_Map_Filters',
		'button_url' => 'https://webcodingplace.com/google-map-filters-real-estate-manager/',
		'plugin_type' => 'free',
		'docs' => 'https://kb.webcodingplace.com/real-estate-manager/google-map-filters/',
	),
	array(
		'title' => 'Filterable Properties Grid',
		'desc' => 'Create animated grid of properties with filter menu. You can filter properties by Type, Purpose, Status or any custom field. You can provide custom button colors for active and hover state.',
		'class_name' => 'REM_Filterable_Grid',
		'button_url' => 'https://webcodingplace.com/filterable-properties-grid-real-estate-manager-extension/',
		'plugin_type' => 'pro',
		'docs' => 'https://kb.webcodingplace.com/real-estate-manager/filterable-properties-grid/',
	),
	array(
		'title' => 'Add to Wishlist',
		'desc' => 'Let the users add properties to wishlist and display them on a separate page using shortcode. Bulk contact is also available.',
		'class_name' => 'REM_WISHLIST',
		'button_url' => 'https://webcodingplace.com/add-to-wish-list-real-estate-manager/',
		'plugin_type' => 'free',
		'docs' => 'https://kb.webcodingplace.com/real-estate-manager/wish-list-addon/',
	),
	array(
		'title' => 'Conditional Fields',
		'desc' => 'Make property meta fields dependable in search form and when creating/editing properties.',
		'class_name' => 'REM_CONDITIONAL_FIELDS',
		'button_url' => 'https://webcodingplace.com/conditional-fields-real-estate-manager-extension/',
		'plugin_type' => 'pro',
		'docs' => 'https://kb.webcodingplace.com/real-estate-manager/conditional-fields-addon/',
	),
	array(
		'title' => 'Woo Estato',
		'desc' => 'A WooCommerce Addon to manage monthly/annually paid subscriptions based on number of properties for agents.',
		'class_name' => 'REM_WOO_ESTATO',
		'button_url' => 'https://webcodingplace.com/woo-estato-real-estate-manager/',
		'plugin_type' => 'free',
		'docs' => 'https://kb.webcodingplace.com/real-estate-manager/woo-estato-paid-listings/',
	),
);
?>

<div class="wrap ich-settings-main-wrap">
	<div class="row">
		<div class="col-sm-12">
			<h3>Extend the functionality of Real Estate Manager by installing the following addons</h3>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
		
		<?php foreach ($plugins_data as $key => $plugin_data) { ?>

			
			<div class="card">
				<div class="card-body">
					<h4 class="card-title"><?php echo $plugin_data['title']; ?>
						<?php if (class_exists($plugin_data['class_name'])) { ?>
							<span title="Installed and Active" class="glyphicon glyphicon-ok"></span>
						<?php } ?>
					</h4>
					<p class="card-text">
						<?php echo $plugin_data['desc']; ?>
						<hr>
						<?php if(!class_exists($plugin_data['class_name'])) { ?>
							<a href="<?php echo $plugin_data['button_url']; ?>" target="_blank" class="btn btn-success">
								<span class="glyphicon glyphicon-download-alt"></span> <?php echo ($plugin_data['plugin_type'] == 'pro') ? 'Details / Purchase' : 'Download Free' ; ?>
							</a>
						<?php } ?>
						<?php if (isset($plugin_data['docs']) && $plugin_data['docs'] != '') { ?>
							<a href="<?php echo $plugin_data['docs']; ?>" target="_blank" class="btn btn-info">
								<span class="glyphicon glyphicon-question-sign"></span> 
								Documentation
							</a>
						<?php } ?>
					</p>
				</div>
			</div>

			<?php echo ($key == 4) ? '</div><div class="col-sm-6">' : '' ; ?>

		<?php } ?>
		</div>
	</div>
</div>