<div class="wrap ich-settings-main-wrap">
	<h3 class="page-header">
		<?php _e( 'Real Estate Manager - Settings', 'real-estate-manager' ); ?> <small>v<?php echo REM_VERSION; ?></small>
		<?php if (!is_rem_validated()) { ?>
			<small style="background:red;" class="badge"><?php _e( 'Not Registered!', 'real-estate-manager' ) ?></small>
			<button type="button" class="rem-register-btn pull-right btn-sm btn btn-primary"><?php _e( 'Register', 'real-estate-manager' ); ?></button>
		<?php } else { ?>
			<small style="background:green;" class="badge"><?php _e( 'Registered!', 'real-estate-manager' ) ?></small>
			<button type="button" class="rem-deregister-btn pull-right btn-sm btn btn-danger"><?php _e( 'De-register', 'real-estate-manager' ); ?></button>
		<?php } ?>
		
	</h3>
	<div class="row">
		<div class="col-sm-3">
			<div class="list-group wcp-tabs-menu">
				<?php $all_fields_settings = $this->admin_settings_fields();
					foreach ($all_fields_settings as $panel) { ?>
						<a href="#<?php echo $panel['panel_name']; ?>" role="button" class="list-group-item">
							<?php echo (isset($panel['icon'])) ? $panel['icon'] : '' ; ?>
							<?php echo $panel['panel_title']; ?>
						</a>
				<?php } ?>
			</div>			
		</div>
		<div class="col-sm-9">
			<form id="rem-settings-form" class="form-horizontal">
				<input type="hidden" name="action" value="wcp_rem_save_settings">
				<?php $all_fields_settings = $this->admin_settings_fields();
					foreach ($all_fields_settings as $panel) { ?>
						<div class="panel panel-default" id="<?php echo $panel['panel_name']; ?>">
							<div class="panel-heading">
								<b><?php echo (isset($panel['icon'])) ? $panel['icon'] : '' ; ?> <?php echo $panel['panel_title']; ?></b>
							</div>
							<div class="panel-body">
								<?php foreach ($panel['fields'] as $field) {
									echo $this->render_setting_field($field);
								} ?>
							</div>
						</div>
				<?php } ?>
				<p class="text-right">
					<span class="wcp-progress" style="display:none;"><?php _e( 'Please Wait...', 'real-estate-manager' ); ?></span>					
					<input class="btn btn-success" type="submit" value="<?php _e( 'Save Settings', 'real-estate-manager' ); ?>">
				</p>
			</form>
		</div>
	</div>
</div>