<div class="row step-controls active">
	<div class="col-md-4 col-4 text-left">
		<button class="btn btn-open-blue-outline btn-lg <?php echo $data['prev_class']; ?>">
            <span class="dashicons dashicons-arrow-left-alt2"></span>
            <span><?php echo $data['text_prev']; ?></span>
        </button>
	</div>
	<div class="col-md-4 col-4 text-center">
		<button class="btn btn-open-blue-outline btn-lg <?php echo $data['save_class']; ?>">
            <span class="dashicons dashicons-yes"></span>
            <span><?php echo $data['text_save']; ?></span>
        </button>
	</div>
	<div class="col-md-4 col-4 text-right">
		<button class="btn btn-open-blue-outline btn-lg <?php echo $data['next_class']; ?>">
            <span><?php echo $data['text_next']; ?></span>
			<span class="dashicons dashicons-arrow-right-alt2"></span></button>
	</div>
</div>
