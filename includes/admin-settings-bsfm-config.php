<div id="automate-config-form" class="bsfm-settings-form bsfm-config-bsfm-settings-form">
	<?php 
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'all_rules';
		if( isset( $_GET['action'] ) ) {
			$current_action = $_GET[ 'action' ];
			$active_tab = '';
		}
		else {
			$current_action = '';
		}
	?>
	<h2 class="nav-tab-wrapper">
		<a href="?page=bsf-mautic&tab=all_rules" class="nav-tab <?php echo $active_tab == 'all_rules' ? 'nav-tab-active' : ''; ?>"> <?php _e('All Rules', 'automateplus-mautic-wp'); ?> </a>
		<?php if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) { ?>
			<a href="?page=bsf-mautic&tab=edd_mautic" class="nav-tab <?php echo $active_tab == 'edd_mautic' ? 'nav-tab-active' : ''; ?>"> <?php _e('EDD', 'automateplus-mautic-wp'); ?> </a>
		<?php } ?>
		<a href="?page=bsf-mautic&tab=auth_mautic" class="nav-tab <?php echo $active_tab == 'auth_mautic' ? 'nav-tab-active' : ''; ?>"> <?php _e('Authenticate', 'automateplus-mautic-wp'); ?> </a>
		<a href="?page=bsf-mautic&tab=enable_tracking" class="nav-tab <?php echo $active_tab == 'enable_tracking' ? 'nav-tab-active' : ''; ?>"> <?php _e('Tracking', 'automateplus-mautic-wp'); ?> </a>
	</h2>
	<?php
	if( $active_tab == 'all_rules' ) {
			BSFMauticAdminSettings::bsfm_rules_list();
	}
	if( $active_tab == 'add_new_rule' || $current_action == 'edit' ) { ?>
		<?php
			APM_RulePanel::bsf_mautic_metabox_view();
		?>
	<?php } ?>
	<form id="bsfm-config-form" action="<?php BSFMauticAdminSettings::render_form_action( 'bsfm-config' ); ?>" method="post">
		<div class="bsfm-settings-form-content">
			<?php
				$bsfm = BSF_Mautic_Init::$bsfm_options['bsf_mautic_settings'];
				if( empty($bsfm) ) {
					$bsfm 	=	BSFMauticAdminSettings::get_bsfm_mautic();	
				}
				$bsfm_enabled_track = $bsfm_base_url = $bsfm_public_key = $bsfm_secret_key = '';
				if( is_array($bsfm) ) {
					$bsfm_enabled_track	= ( array_key_exists( 'bsfm-enabled-tracking', $bsfm ) && $bsfm['bsfm-enabled-tracking'] == 1 )  ? ' checked' : '';
					$bsfm_base_url = ( array_key_exists( 'bsfm-base-url', $bsfm ) ) ? $bsfm['bsfm-base-url'] : '';
					$bsfm_public_key = ( array_key_exists( 'bsfm-public-key', $bsfm ) ) ? $bsfm['bsfm-public-key'] : '';
					$bsfm_secret_key = ( array_key_exists( 'bsfm-secret-key', $bsfm ) ) ? $bsfm['bsfm-secret-key'] : '';
				}

			if( $active_tab == 'auth_mautic' ) { ?>
			<!-- Base Url -->
			<div class="bsfm-config-fields">
				<h4><?php _e( 'Base URL', 'automateplus-mautic-wp' ); ?></h4>
				<p class="bsfm-admin-help">
					<?php _e('This setting is required for Mautic Integration.', 'automateplus-mautic-wp'); ?>
				</p>
				<input type="text" class="regular-text" name="bsfm-base-url" value="<?php echo $bsfm_base_url; ?>" class="bsfm-wp-text bsfm-google-map-api" />
			</div>

			<?php 
			$credentials = get_option( 'bsfm_mautic_credentials' );
			if( ! isset( $credentials['access_token'] ) ) { ?>
			<!-- Client Public Key -->
			<div class="bsfm-config-fields">
				<h4><?php _e( 'Public Key', 'automateplus-mautic-wp' ); ?></h4>
				<input type="text" class="regular-text" name="bsfm-public-key" class="bsfm-wp-text bsfm-google-map-api" />
			</div>
			
			<!-- Client Secret Key -->
			<div class="bsfm-config-fields">
				<h4><?php _e( 'Secret Key', 'automateplus-mautic-wp' ); ?></h4>	
				<input type="text" class="regular-text" name="bsfm-secret-key" class="bsfm-wp-text bsfm-google-map-api" />
				<p class="bsfm-admin-help">
					<?php _e('This setting is required to integrate Mautic in your website.<br>Need help to get Mautic API public and secret key? Read ', 'automateplus-mautic-wp'); ?><a target='_blank' href="https://docs.brainstormforce.com/how-to-get-mautic-api-credentials/"><?php _e('this article.', 'automateplus-mautic-wp'); ?></a>
				</p>
			</div>
			<p class="submit">
				<input type="submit" name="bsfm-save-authenticate" class="button-primary" value="<?php esc_attr_e( 'Save and Authenticate', 'automateplus-mautic-wp' ); ?>" />
			</p>

			<?php wp_nonce_field('bsfmautic', 'bsf-mautic-nonce'); ?>
			<?php } ?>
				<?php
				// If not authorized 
				if( isset( $credentials['access_token'] ) ) { ?>
				<p class="submit">
					<input type="button" name="bsfm-disconnect-mautic" class="button-primary" value="<?php _e( 'Connected', 'automateplus-mautic-wp' ); ?>" />
					<!-- input type="submit" name="bsfm-disconnect-mautic" class="button" value="<?php esc_attr_e( 'Disconnect Mautic', 'automateplus-mautic-wp' ); ?>" / -->
					<a class="bsfm-disconnect-mautic"> <?php _e( 'Disconnect Mautic', 'automateplus-mautic-wp' ); ?> </a> 
				</p>
				<?php } ?>
			<!-- Enable pixel tracking -->
			<?php 
			}

			if( $active_tab == 'enable_tracking' ) { ?>
 			<div class="bsfm-config-fields">
				<h4><?php _e( 'Enable Mautic Tracking', 'automateplus-mautic-wp' ); ?></h4>
				<p class="bsfm-admin-help">
					<?php _e( 'This setting enables you to add Mautic tracking code in your site.<br>Need more information about tracking? Read <a target="_blank" href="https://mautic.org/docs/en/contacts/contact_monitoring.html">this article</a>.', 'automateplus-mautic-wp'); ?>
				</p>
				<label>
					<input type="checkbox" class="bsfm-enabled-panels" name="bsfm-enabled-tracking" value="" <?php echo $bsfm_enabled_track; ?> ><?php _e( 'Enable Tracking', 'automateplus-mautic-wp' ); ?>
				</label><br>
			</div>
			<p class="submit">
				<input type="submit" name="save-bsfm" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'automateplus-mautic-wp' ); ?>" />
			</p>
			<?php wp_nonce_field('bsfmautictrack', 'bsf-mautic-nonce-tracking'); ?>
		<?php } ?>
		</div>
	</form>
</div>