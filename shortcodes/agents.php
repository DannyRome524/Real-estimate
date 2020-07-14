<div class="ich-settings-main-wrap">
	<div class="row <?php echo ($masonry == 'enable') ? 'masonry-agents' : '' ; ?>">
		<?php foreach ($agents as $agent) { ?>
			<div class="<?php echo $columns; ?> rem-agent-container">
				<?php do_action( 'rem_agent_box', $agent->ID, $style ); ?>
			</div>
		<?php } ?>
	</div>
</div>