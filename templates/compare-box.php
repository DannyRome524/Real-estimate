<?php 
	$saved_table_label = rem_get_option('property_compare_columns');

	if (!empty($saved_table_label)) {
		$array_value = explode("\n", $saved_table_label);
		foreach ($array_value as $value) {
			$value = trim($value);
			if ($value != '') {
				$column_value = explode( "|", $value);
				$table_columns_labels[] = $column_value['0'];
			}
		}
	}else {
		$default_labels = array(
			__( 'Price', 'real-estate-manager' ),
			__( 'Status', 'real-estate-manager' ),
			__( 'Type', 'real-estate-manager' ),
			__( 'Area', 'real-estate-manager' ),
			__( 'Purpose', 'real-estate-manager' ),
			__( 'Bedrooms', 'real-estate-manager' ),
			__( 'Bathrooms', 'real-estate-manager' ),
		);
		$default_labels = apply_filters( 'rem_compare_table_default_labels', $default_labels );
		$table_columns_labels = $default_labels;
	}

?>
<div class="prop-compare-wrapper ich-settings-main-wrap" >
	<div class="prop-compare">
		<h4 class="title_compare"><?php _e( 'Compare Listings', 'real-estate-manager' ); ?></h4>
		<button class="compare_close" title="<?php _e( 'Close Compare Panel', 'real-estate-manager' ); ?>" style="display: none"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
		<button class="compare_open" title="<?php _e( 'Open Compare Panel', 'real-estate-manager' ); ?>" style="display: none"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
		<div class="rem-compare-table">
			<table class="property-box">
				
			</table>
		</div>
		<button id="submit_compare" class="btn btn-default compare_prop_button" data-izimodal-open="#rem-compare-modal"> <?php _e( "Compare", "real-estate-manager" ) ?> </button>
	</div>
</div>
<div id="rem-compare-modal" class="ich-settings-main-wrap">
	<button data-izimodal-close="" class="icon-close"><i class="fa fa-times" aria-hidden="true"></i></button>
	<div class="table-responsive">
	  <table class="table rem-compare-table">
        <thead>
          <tr>
            <th class='fixed-row'><?php _e( "Title", "real-estate-manager" ); ?></th>
            <?php foreach ($table_columns_labels as $label) { ?>
            	<th><?php _e( $label, "real-estate-manager" ); ?></th>
            <?php } ?>
          </tr>
        </thead>
        <tbody>
          
        </tbody>
      </table>
	</div>
</div>