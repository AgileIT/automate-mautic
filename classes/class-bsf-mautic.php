<?php
/**
 * BSF Mautic initial setup
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'BSF_Mautic' ) ) :

	class BSF_Mautic {

		private static $instance;
		/**
		* Initiator
		*/
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new BSF_Mautic();
				self::$instance->includes();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		// __clone and __wakeup functions >>> singlton pattern
		public function includes() {
			require_once BSF_MAUTIC_PLUGIN_DIR . '/classes/class-bsfm-init.php';
			require_once BSF_MAUTIC_PLUGIN_DIR . '/classes/class-bsfm-postmeta.php';
		}
		public function hooks() {
			register_activation_hook( __FILE__, array( $this, 'bsfm_activation_reset' ) );
			add_action( 'init', array( $this, 'bsf_mautic_register_posttype' ) );
			add_action( 'wp_head', array( $this, 'bsf_mautic_tracking_script' ) );
			add_action( 'wp_footer', array( $this, 'bsf_mautic_tracking_image' ) );
			//add new registered user
			add_action( 'user_register', array( $this, 'bsfm_add_registered_user' ), 10, 1 );
			//add comment author
			add_action( 'comment_post', array( $this, 'bsfm_add_comment_author' ), 10, 3 );
		}
		public function bsfm_activation_reset() {
			delete_option( 'bsfm_hide_branding' );
		}
		/**
		* Register a bsf-mautic-rule post type.
		* @Since 1.0.0
		* @link http://codex.wordpress.org/Function_Reference/register_post_type
		*/
		public function bsf_mautic_register_posttype() {
			$labels = array(
				'name'               => _x( 'Rules', 'post type general name', 'bsfmautic' ),
				'singular_name'      => _x( 'Rule', 'post type singular name', 'bsfmautic' ),
				'menu_name'          => _x( 'Rules', 'admin menu', 'bsfmautic' ),
				'name_admin_bar'     => _x( 'Rule', 'add new on admin bar', 'bsfmautic' ),
				'add_new'            => _x( 'Add New', 'rule', 'bsfmautic' ),
				'add_new_item'       => __( 'Add New Rule', 'bsfmautic' ),
				'new_item'           => __( 'New Rule', 'bsfmautic' ),
				'edit_item'          => __( 'Edit Rule', 'bsfmautic' ),
				'view_item'          => __( 'View Rule', 'bsfmautic' ),
				'all_items'          => __( 'All Rules', 'bsfmautic' ),
				'search_items'       => __( 'Search Rules', 'bsfmautic' ),
				'parent_item_colon'  => __( 'Parent Rules:', 'bsfmautic' ),
				'not_found'          => __( 'No rules found.', 'bsfmautic' ),
				'not_found_in_trash' => __( 'No rules found in Trash.', 'bsfmautic' )
			);
			$args = array(
				'labels'             => $labels,
                'description'        => __( 'Description.', 'bsfmautic' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'bsf-mautic-rule' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'menu_icon'			 => 'dashicons-chart-line',
				'supports'           => array( 'title' )
			);
			register_post_type( 'bsf-mautic-rule', $args );
		}
		/**
		 * Writes Tracking JS to the HTML source of WP head
		 */
		public function bsf_mautic_tracking_script()
		{
			$bsfm_options = BSF_Mautic_Init::$bsfm_options['bsf_mautic_settings'];
			$enable_mautic_tracking	= true;
			if ( !empty( $bsfm_options ) && array_key_exists( 'bsfm-enabled-tracking', $bsfm_options ) ) {
				if( $bsfm_options['bsfm-enabled-tracking'] == 1 ) {
					$enable_mautic_tracking = true;
				} else {
					$enable_mautic_tracking = false;
				}
			}
			if ( $enable_mautic_tracking ) {
				$base_url = trim($bsfm_options['bsfm-base-url'], " \t\n\r\0\x0B/");
				$bsfm_trackingJS = "<script>
			    (function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
			        w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
			        m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
			    })(window,document,'script','{$base_url}/mtc.js','mt');
			    mt('send', 'pageview');
				</script>";
				echo $bsfm_trackingJS;
			}
		}

		/**
		 * Writes Mautic Tracking image to site 
		 */
		public function bsf_mautic_tracking_image( $atts, $content = null )
		{
			global $wp;
			$bsfm_options	= BSF_Mautic_Init::$bsfm_options['bsf_mautic_settings'];
			$enable_img_tracking	= true;
			if ( !empty( $bsfm_options ) && array_key_exists( 'bsfm-enabled-tracking-img', $bsfm_options ) ) { 
				if( $bsfm_options['bsfm-enabled-tracking-img'] == 1 ) {
					$enable_img_tracking = true;
				} else {
					$enable_img_tracking = false;
				}	
			}
			if ( $enable_img_tracking ) {
				$base_url = trim($bsfm_options['bsfm-base-url'], " \t\n\r\0\x0B/");
				$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
				$attrs = array();
				$attrs['title']	 = 'title';
				$attrs['language']  = get_locale();
				$attrs['referrer']  = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $current_url;
				$attrs['url']	   = $current_url;
				$url_query = $attrs;
				$encoded_query = urlencode(base64_encode(serialize($url_query)));
				$image   = '<img style="display:none" src="' . $base_url . '/mtracking.gif?d=' . $encoded_query . '" alt="mautic is open source marketing automation" />';
				echo $image;
			}
		}
		public function bsfm_add_registered_user( $user_id ) {
			if( !$user_id ) return;
			$user_info = get_userdata( $user_id );
			$method = 'POST';
			$url = '/api/contacts/new';
			$body = array(
				'firstname'	=> $user_info->first_name,
				'lastname'	=> $user_info->last_name,
				'email'		=> $user_info->user_email
			);
			self::bsfm_mautic_api_call($url, $method, $body);
		}

		public function bsfm_add_comment_author( $id, $approved, $commentdata ) {
			if( !isset($commentdata['comment_author_email']) ) return;
			// check if condition and action is set
			// @function bsfm_authorize_comment_condition 
			// print_r($commentdata['comment_post_ID']); comment post
			
			$status = Bsfm_Postmeta::bsfm_authorize_comment_condition( $commentdata );

			if( is_array($status) ) {
				//grap actions array 
				Bsfm_Postmeta::bsfm_get_rule_actions($status);
			}
			else {
				return;
			}

			$method = 'POST';
			$url = '/api/contacts/new';
			//$arraytags = array('aaa','bbb');
			$body = array(
				'firstname'	=>	$commentdata['comment_author'],
				'email'		=>	$commentdata['comment_author_email'],
				'website'	=>	$commentdata['comment_author_url']
			);
			self::bsfm_mautic_api_call($url, $method, $body);
		}

		public static function bsfm_mautic_api_call( $url, $method, $segment = null, $param = array() ) {
			$status = 'success';
            $credentials = get_option( 'bsfm_mautic_credentials' );
            // if token expired, get new access token
			if( $credentials['expires_in'] < time() ) {
				$grant_type = 'refresh_token';
				$response = BSFMauticAdminSettings::bsf_mautic_get_access_token( $grant_type );
				if ( is_wp_error( $response ) ) {
				  	$errorMsg = $response->get_error_message();
				    $status = 'error';
				    $msg = $default_error_msg;
				} else {
					$access_details = json_decode( $response['body'] );
					$expiration = time() + $access_details->expires_in;
					$credentials['access_token'] = $access_details->access_token;
					$credentials['expires_in'] = $expiration;
					$credentials['refresh_token'] = $access_details->refresh_token;
					update_option( 'bsfm_mautic_credentials', $credentials );
				}
			} // refresh code token ends
			// add contacts
			$credentials = get_option( 'bsfm_mautic_credentials' );
			$access_token = $credentials['access_token'];
			$param['access_token'] = $access_token;
			$url = $credentials['baseUrl'] . $url;
			if( $method=="GET" ) {
				$url = $url .'?access_token='. $access_token;
				$response = wp_remote_get( $url );
				if( $method=="GET" ) {
					$response_body = $response['body'];
					$contact_created = json_decode($response_body);
					return $contact_created;
				}
			}
			else if( $method=="POST" ) {			// add new contact to mautic request
				$response = wp_remote_post( $url, array(
				'method' => $method,
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $param,
				'cookies' => array()
				));
			}
			if ( is_wp_error( $response ) ) {
				$errorMsg = $response->get_error_message();
				$status = 'error';
				$msg = $default_error_msg;
			} else {
				if( is_array($response) ) {
					$response_code = $response['response']['code'];
					if( $response_code != 201 ) {
						if( $response_code != 200 ) {
							$ret = false;
							$status = 'error';
							$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
							$msg = $default_error_msg;
						}
					} else {
						$response_body = $response['body'];
						$contact_created = json_decode($response_body);
						$contact = $contact_created->contact;
			   			/*
			   			 * if contact is created add to segment here
			   			 */
	   					if( isset($contact->id) ) {
			   				$contact_id =  (int)$contact->id;
			   				// add this contact to segment now
			   				// fetch segment_id from rule
			   				$segment_id = 6;
			   				$res = self::bsfm_mautic_add_contact_to_segment( $segment_id, $contact_id, $credentials ); 
			   				$status = $res['status'];
			   				$errorMsg  = $res['error_message'];
			   			}
			   		}
				}
			}
		}
		function bsfm_mautic_add_contact_to_segment( $segment_id, $contact_id, $mautic_credentials ) {
			$errorMsg = '';
			$status = 'error';
			if( is_int( $segment_id ) && is_int ( $contact_id ) ) {
				$url = $mautic_credentials['baseUrl'] . "/api/segments/".$segment_id."/contact/add/".$contact_id;
				$access_token = $mautic_credentials['access_token'];
				$body = array(	
					"access_token" => $access_token
				);
				$response = wp_remote_post( $url, array(
					'method' => 'POST',
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => $body,
					'cookies' => array()
				    )
				);
				if ( is_wp_error( $response ) ) {
				  	$errorMsg = $response->get_error_message();
				    $status = 'error';
				} else {

					if( is_array($response) ) { 							

				   		$response_code = $response['response']['code'];

				   		if( $response_code != 200 ) {
	                        $status = 'error';
	                        $errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
				   		} else {
	                        $status = 'success';
	   					}
					}
				}
			}
			$response = array(
				'status' => $status,
				'error_message' => $errorMsg            
			);
			return $response;
		}

		/*	function activation_reset() {

			$no_memory = $this->check_memory_limit();

				if( $no_memory == true && ! defined( 'WP_CLI' ) ) {

					$msg  = __('Unfortunately, plugin could not be activated as the memory allocated by your host has almost exhausted. UABB plugin recommends that your site should have 15M PHP memory remaining. ', 'uabb');
					$msg .= '<br/><br/>' . __('Please check ', 'uabb') . '<a target="_blank" href="https://www.ultimatebeaver.com/docs/increase-memory-limit-site/">' . __('this article', 'uabb') . '</a> ';
					$msg .= __(' for solution or contact ', 'uabb') . '<a target="_blank" href="http://store.brainstormforce.com/support">' . __(' support', 'uabb') . '</a>.';
					$msg .= '<br/><br/><a class="button button-primary" href="'.network_admin_url( 'plugins.php' ). '">' . __('Return to Plugins Page', 'uabb') . '</a>';

					deactivate_plugins( plugin_basename( __FILE__ ) );
					wp_die( $msg );
				}
			delete_option( 'uabb_hide_branding' );
			// Force check graupi bundled products
			update_site_option( 'bsf_force_check_extensions', true );
		} */
	}
endif;