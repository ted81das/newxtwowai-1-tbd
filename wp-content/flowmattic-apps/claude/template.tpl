<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-claude-data-template">
	<div class="flowmattic-claude-data-form">
		<div class="form-group w-100 m-t-20">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Connect Account', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-select-field">
				<select name="connect_id" class="workflow-api-connect form-control w-100 d-block" title="Choose Connect" data-live-search="true">
				<?php
					$all_connects = wp_flowmattic()->connects_db->get_all();
					foreach ( $all_connects as $key => $connect_item ) {
						$connect_id   = $connect_item->id;
						$connect_name = $connect_item->connect_name;
						?>
						<option <# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.connect_id === '<?php echo esc_attr( $connect_id ); ?>' ) { #>selected<# } #> value="<?php echo esc_attr( $connect_id ); ?>" data-subtext="ID: <?php echo esc_attr( $connect_id ); ?>"><?php echo esc_attr( $connect_name ); ?></option>
						<?php
					}
				?>
				</select>
			</div>
		</div>
		<div class="flowmattic-claude-action-fields"></div>
	</div>
</script>
<script type="text/html" id="flowmattic-claude-create_completion-data-template">
	<div class="form-group w-100">
		<h4>AI Model <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-select-input-field">
			<select name="ai_model" class="widget-select form-control w-100" required title="Select AI Model">
			  <?php
			  $ai_models = array(
				  'claude-2.1'                 => 'Claude 2',
				  'claude-3-5-sonnet-20240620' => 'Claude 3.5 Sonnet',
				  'claude-3-opus-20240229'     => 'Claude 3 Opus',
				  'claude-3-haiku-20240307'    => 'Claude 3 Haiku',
			  );

			  foreach ( $ai_models as $ai_model => $title ) {
				?>
				<option
				  <# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $ai_model; ?>' === actionAppArgs.ai_model ) { #>selected<# } #>
				  value="<?php echo $ai_model; ?>" data-subtext="<?php echo $ai_model; ?>">
				  <?php echo $title; ?>
				</option>
				<?php
			  }
			  ?>
			</select>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="input-title"><?php echo esc_attr__( 'Prompt Text', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="prompt" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.prompt ) { #>{{{ actionAppArgs.prompt }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			The prompt text to generate completions for. Learn how to <a href="https://docs.anthropic.com/claude/docs/optimizing-your-prompt" target="_blank">optimize your prompt</a>
		</div>
	</div>
	<div class="form-group w-100">
		<h4>Max Tokens <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control" name="max_tokens_to_sample" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.max_tokens_to_sample }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			The maximum number of tokens to generate before stopping.
		</div>
	</div>
	<div class="form-group w-100">
		<h4>Stop Sequences</h4>
		<div class="fm-dynamic-input-field">
			<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="stop_sequences" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.stop_sequences }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			Sequences that will cause the model to stop generating completion text
		</div>
	</div>
	<div class="form-group w-100">
		<h4>Temperature</h4>
		<div class="fm-dynamic-input-field">
			<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="temperature" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.temperature }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			Amount of randomness injected into the response. Enter the sampling value between 0 to 1. Leave empty to use default value, which is 1
		</div>
	</div>
	<div class="form-group w-100">
		<h4>Top P</h4>
		<div class="fm-dynamic-input-field">
			<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="top_p" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.top_p }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			Enter nucleus sampling to restrict token options when cumulative probability reaches a specified Top-p value. Modify either temperature or Top-p, not both simultaneously (Ranges from 0 to 1)
		</div>
	</div>
	<div class="form-group w-100">
		<h4>Top K</h4>
		<div class="fm-dynamic-input-field">
			<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="top_k" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.top_k }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			Enter samples from the top K options for each following token. This is used to filter out low-probability responses in the "long tail." <a href="https://towardsdatascience.com/how-to-sample-from-language-models-682bceb97277" target="_blank">Learn more technical details here</a>
		</div>
	</div>
	<div class="form-group w-100">
		<h4>User ID</h4>
		<div class="fm-dynamic-input-field">
			<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="user_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.user_id }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			Enter an external identifier (UUID, hash, or opaque code) associated with the user's request. Anthropic may use this to detect abuse (Avoid sharing personal details like name, email, or phone number)
		</div>
	</div>
</script>
