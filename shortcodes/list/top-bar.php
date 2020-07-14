<div class="row rem-topbar">
	<form method="GET" action="#">
	<div class="col-sm-3 col-xs-8">
		<input type="hidden" name="list_style" value="<?php echo (isset($_GET['list_style'])) ? esc_attr( $_GET['list_style'] ) : '' ; ?>">
		<select class="form-control" name="sort_by" onchange="this.form.submit()">
			<?php
				$sorting_options = $this->lists_sorting_options();
				foreach ($sorting_options as $option) {
					$selected = (isset($_GET['sort_by']) && $_GET['sort_by'] == $option['value']) ? 'selected' : '' ; ?>
					<option <?php echo $selected; ?> value="<?php echo $option['value']; ?>"><?php echo $option['title']; ?></option>
				<?php }
			?>
		</select>
	</div>
	<div class="col-sm-6 hidden-xs"></div>
	<div class="col-sm-3 col-xs-4 text-right">
	  <a href="<?php echo esc_url( add_query_arg( 'list_style', 'list' ) ); ?>" class="rem-topbar-btn list-view <?php echo ($style == '1') ? 'active' : '' ; ?>">
		<span class="fa fa-bars"></span>
	  </a>
	  <a href="<?php echo esc_url( add_query_arg( 'list_style', 'grid' ) ); ?>" class="rem-topbar-btn grid-view <?php echo ($style != '1') ? 'active' : '' ; ?>">
		<span class="fa fa-th"></span>
	  </a>			
	</div>
	</form>
</div>