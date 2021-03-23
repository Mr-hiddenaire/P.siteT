<?php
if (!class_exists('ARM_members_activity'))
{
	class ARM_members_activity
	{
		function __construct()
		{
			global $wpdb, $ARMember, $arm_slugs;
			
			add_action('arm_record_activity', array(&$this, 'arm_add_activity'), 1);
			add_action('wp_ajax_arm_delete_member_activities', array(&$this, 'arm_delete_member_activities'));
			/* Ajax Load More Activities */
			add_action('wp_ajax_nopriv_arm_crop_iamge', array(&$this, 'arm_crop_image'));
            add_action('wp_ajax_arm_crop_iamge', array(&$this, 'arm_crop_image'));


            add_action('wp_ajax_arm_upload_front', array(&$this, 'arm_upload_front'), 1);
	        add_action('wp_ajax_nopriv_arm_upload_front', array(&$this, 'arm_upload_front'), 1);

	        add_action('wp_ajax_arm_upload_cover', array(&$this, 'arm_upload_cover'), 1);
	        add_action('wp_ajax_nopriv_arm_upload_cover', array(&$this, 'arm_upload_cover'), 1);

	        add_action('wp_ajax_arm_upload_profile', array(&$this, 'arm_upload_profile'), 1);
	        add_action('wp_ajax_nopriv_arm_upload_profile', array(&$this, 'arm_upload_profile'), 1);

	        add_action('wp_ajax_arm_import_user', array(&$this, 'arm_import_user'), 1);
			
			    
            add_action('admin_init', array(&$this, 'upgrade_data'));

            add_action('admin_footer', array(&$this, 'arm_deactivate_feedback_popup'), 1);

            add_action( 'wp_ajax_armlite_deactivate_plugin', array( $this, 'armite_deactivate_plugin_func') );
		}
                
        function upgrade_data() {
		
			global $arm_newdbversion;

			if (!isset($arm_newdbversion) || $arm_newdbversion == "")
				$arm_newdbversion = get_option('armlite_version');
	
			if (version_compare($arm_newdbversion, '3.0', '<')) {
				$path = MEMBERSHIPLITE_VIEWS_DIR . '/upgrade_latest_data.php';
				include($path);
			}	
		
            
		}
		
		function arm_add_activity($activity = array())
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_social_feature;
			return false;
		}
		
		 function arm_get_remote_post_params($plugin_info = "") {
			global $wpdb;
	
			$action = "";
			$action = $plugin_info;
	
			if (!function_exists('get_plugins')) {
				require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}
			$plugin_list = get_plugins();
			$site_url = ARMLITE_HOME_URL;
			$plugins = array();
	
			$active_plugins = get_option('active_plugins');
	
			foreach ($plugin_list as $key => $plugin) {
				$is_active = in_array($key, $active_plugins);
	
				//filter for only armember ones, may get some others if using our naming convention
				if (strpos(strtolower($plugin["Title"]), "armember") !== false) {
					$name = substr($key, 0, strpos($key, "/"));
					$plugins[] = array("name" => $name, "version" => $plugin["Version"], "is_active" => $is_active);
				}
			}
			$plugins = json_encode($plugins);
	
			//get theme info
			$theme = wp_get_theme();
			$theme_name = $theme->get("Name");
			$theme_uri = $theme->get("ThemeURI");
			$theme_version = $theme->get("Version");
			$theme_author = $theme->get("Author");
			$theme_author_uri = $theme->get("AuthorURI");
	
			$im = is_multisite();
			$sortorder = get_option("armSortOrder");
	
			$post = array("wp" => get_bloginfo("version"), "php" => phpversion(), "mysql" => $wpdb->db_version(), "plugins" => $plugins, "tn" => $theme_name, "tu" => $theme_uri, "tv" => $theme_version, "ta" => $theme_author, "tau" => $theme_author_uri, "im" => $im, "sortorder" => $sortorder);
	
			return $post;
		}
	
		
		function armgetapiurl() {
			$api_url = 'https://arpluginshop.com/';
			return $api_url;
		}
		
		
		   
	  
		  function checksite($str) {
			  update_option('arm_wp_get_version', $str);
		  }
		
		
		function arm_delete_member_activities()
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
			$delete_act = $wpdb->query("DELETE FROM `".$ARMember->tbl_arm_activity."` WHERE `arm_type`!='membership'");
			if ($delete_act) {
				$response = array('type' => 'success', 'msg' => __('Member activities has been deleted successfully.', 'ARMember'));
			} else {
				$response = array('type' => 'error', 'msg' => __('There is a error while deleting member activities, please try again.', 'ARMember'));
			}
			echo json_encode($response);
			die();
		}
		function arm_get_activity_by($field = '', $value = '', $limit = '', $object_type = ARRAY_A)
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans;
			$object_type = !empty($object_type) ? $object_type : ARRAY_A;
			$limit = (!empty($limit)) ? " LIMIT " . $limit : "";
			$result = false;
			if (!empty($field) && $value != '') {
				$result = $wpdb->get_results("SELECT * FROM `".$ARMember->tbl_arm_activity."` WHERE `$field`='$value' ORDER BY `arm_activity_id` DESC $limit", $object_type);
			}
			return $result;
		}
        function arm_crop_image() {

            $_POST['update_meta'] = isset($_POST['update_meta']) ? $_POST['update_meta'] : '';

            $user_id = get_current_user_id();


            /*this change need to confirm with multisite*/
            $_POST['src'] = MEMBERSHIPLITE_UPLOAD_URL.'/'.basename($_POST['src']);

            $info = getimagesize(MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($_POST['src']));
            $file = $_POST['src'];
            if(isset($_POST['cord'])) {
            	$crop = explode(',', $_POST['cord']);
	            $targ_x1 = $crop[0];
	            $targ_y1 = $crop[1];
	            $targ_x2 = $crop[2];
	            $targ_y2 = $crop[3];
            }
            else {
            	$ofile = MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($_POST['src']);
                $orgnl_hw = getimagesize($ofile);
                $orgnl_w = $orgnl_hw[0];
                $orgnl_h = $orgnl_hw[1];
                $targ_x1 = 0;
	            $targ_y1 = 0;
	            $targ_x2 = $orgnl_w;
	            $targ_y2 = $orgnl_h;
            }

            if ($_POST['type'] == 'profile') {

                if ($_POST['update_meta'] != 'no') {
                    update_user_meta($user_id, 'avatar', $file);
                    do_action('arm_upload_bp_avatar', $user_id);
                }

                $thumb_w = 220;
                $thumb_h = 220;
            } else if ($_POST['type'] == 'cover') {
                $thumb_w = 918;
                $thumb_h = 320;

                if ($_POST['update_meta'] != 'no') {
                    update_user_meta($user_id, 'profile_cover', $file);
                    do_action('arm_upload_bp_profile_cover', $user_id);
                }
            }
            
            $file = MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($_POST['src']); 

            if ($info['mime'] == 'image/gif') {

                $img_r = imagecreatefromgif($file);
                $dst_r = imagecreatetruecolor($targ_x2, $targ_y2);
                imagecopy($dst_r, $img_r, 0, 0, $targ_x1, $targ_y1, $targ_x2, $targ_y2);
                imagegif($dst_r, MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($file));

                $original_info = getimagesize($file);
                $original_w = $original_info[0];
                $original_h = $original_info[1];
                $original_img = imagecreatefromgif($file);
                $thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);
                imagecopy($thumb_img, $original_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
                imagegif($thumb_img, MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($file));
            } else if ($info['mime'] == 'image/png') {
 

                $img_r = imagecreatefrompng($file);
                $dst_r = imagecreatetruecolor($targ_x2, $targ_y2);
                imagealphablending($dst_r, false);
                imagesavealpha($dst_r, true);
                imagecopy($dst_r, $img_r, 0, 0, $targ_x1, $targ_y1, $targ_x2, $targ_y2);
                imagepng($dst_r, MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($file));
                $original_info = getimagesize($file);
                $original_w = $original_info[0];
                $original_h = $original_info[1];
                $original_img = imagecreatefrompng($file);
                $thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);
                imagealphablending($thumb_img, false);
                imagesavealpha($thumb_img, true);
                imagecopyresampled($thumb_img, $original_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
                imagepng($thumb_img, MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($file));
            } else {

                $img_r = imagecreatefromjpeg($file);
                $dst_r = imagecreatetruecolor($targ_x2, $targ_y2);
                imagecopy($dst_r, $img_r, 0, 0, $targ_x1, $targ_y1, $targ_x2, $targ_y2);
                imagejpeg($dst_r, MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($file), 100);
                $original_info = getimagesize($file);
                $original_w = $original_info[0];
                $original_h = $original_info[1];
                $original_img = imagecreatefromjpeg($file);
                $thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);
                imagecopyresampled($thumb_img, $original_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
                imagejpeg($thumb_img, MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename($file));
            }
            
            if ($_POST['type'] == 'profile') {
                if ($_POST['update_meta'] != 'no') {
                    update_user_meta($user_id, 'avatar', $_POST['src']);
                    do_action('arm_after_upload_bp_avatar', $user_id);
                }
            } else if ($_POST['type'] == 'cover') {      
                if ($_POST['update_meta'] != 'no') {
                    update_user_meta($user_id, 'profile_cover', $_POST['src']);
                    do_action('arm_after_upload_bp_profile_cover', $user_id);
                }
            }
            
            echo $_POST['src'];
            die();
        }

        function path_only($file) {
            return trailingslashit(dirname($file));
        }

        function arm_allowed_wp_mime_types()
        {
        	$mimes = get_allowed_mime_types();
	        ksort($mimes);
	        $mcount = count($mimes);
	        $third = ceil($mcount / 3);
	        $c = 0;
	        $mimes['exe'] = '';
	        unset($mimes['exe']);

	        $allowed_mimes = array();

	        foreach( $mimes as $ext => $type ){
	            if( strpos($ext, '|') !== false ){
	                $exts = explode('|',$ext);
	                foreach( $exts as $extension){
	                    if( $extension != '' ){
	                        array_push($allowed_mimes,$extension);
	                    }
	                }
	            } else {
	                array_push($allowed_mimes,$ext);
	            }
	        }

	        return $allowed_mimes;
        }

        function arm_upload_front() {
            $upload_dir = MEMBERSHIPLITE_UPLOAD_DIR.'/';
            $upload_url = MEMBERSHIPLITE_UPLOAD_URL.'/';

	        $file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
	        $response = "";
	        if ($file_name)
	        {
	        	$content_length = (int) $_SERVER['CONTENT_LENGTH'];
	        	$file_size_new = number_format( ($content_length/1048576), 2, '.', '');

	        	$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	        	if($arm_is_valid_file)
	        	{
	            	$arm_upload_file_path = $upload_dir.$file_name;
	            	$file_result = $this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);

	                if(isset($_REQUEST['arm_file_type']) && $_REQUEST['arm_file_type'] == 'arm_stripe_icon' ) {
	                	if(file_exists($upload_dir . $file_name)) {
		                	$image = getimagesize($upload_dir . $file_name);
							$width = isset($image[0]) ? $image[0] : 0;
							$height = isset($image[1]) ? $image[1] : 0;
							if($width != 70 || $height != 70) {
								unlink($upload_dir . $file_name);
								$response['status'] = "error";
								$response['message'] = esc_html__("Select Logo image with 70X70 px", "ARMember");
								echo json_encode($response);
								die;
							}
		                }
	                }
	                $response = $upload_url . $file_name;
	            }
	            echo $response;
	            exit;
	        } else {
	            $files = $_FILES['armfileselect'];
	            $file_size = (isset($_REQUEST['allow_size'])) ? $_REQUEST['allow_size'] : '';
	            $file_name = $_REQUEST['fname'];
	            $file_size_new = $files['size'];
	            $file_size_new = number_format($file_size_new / 1048576, 2, '.', '');
	            $arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $files);
	        	if($arm_is_valid_file)
	        	{
	                if (!empty($file_size) && ($file_size_new > $file_size)) {
	                    $response = "<p class='error_upload_size'>".__('File size not allowed', 'ARMember')."</p>";
	                } else {	                		
	                	$arm_upload_file_path = $upload_dir . $file_name;
	                	$this->arm_upload_file_function($files['tmp_name'], $arm_upload_file_path);
	                    $response = $upload_url . $file_name;
	                    echo "<p class='uploaded'>" . $upload_url . $file_name . "</p>";
	                }
	            }
	        }
	        exit;
	    }

	    function arm_upload_cover() {
	        $upload_dir = MEMBERSHIPLITE_UPLOAD_DIR.'/';
	        $upload_url = MEMBERSHIPLITE_UPLOAD_URL.'/';

	        $file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
	        $response = "";
	        $userID = get_current_user_id();
	        if ($file_name && !empty($userID) && $userID != 0) {

	        	$content_length = (int) $_SERVER['CONTENT_LENGTH'];
	        	$file_size_new = number_format( ($content_length/1048576), 2, '.', '');

	        	$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	        	if($arm_is_valid_file)
	        	{
	            	//$oldCover = get_user_meta($userID, 'profile_cover', true);

					$arm_upload_file_path = $upload_dir.$file_name;
	                $this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);

	                $response = $upload_url . $file_name;
	                echo $response;
	                exit;
	            }
	        } else {
	            $files = $_FILES['armfileselect'];
	            $file_size = (isset($_REQUEST['allow_size'])) ? $_REQUEST['allow_size'] : '';
	            $file_name = $_REQUEST['fname'];
	            $file_size_new = $_FILES['armfileselect']['size'];
	            $file_size_new = number_format($file_size_new / 1048576, 2, '.', '');

	            $arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $files );
	        	if($arm_is_valid_file)
	        	{
	                if (!empty($file_size) && ($file_size_new > $file_size)) {
	                    $response = "<p class='error_upload_size'>" . __('File size not allowed', 'ARMember') . "</p>";
	                } else {
	                	$arm_upload_file_path = $upload_dir . $file_name;
	                	$this->arm_upload_file_function($files['tmp_name'], $arm_upload_file_path);
	                    $response = $upload_url . $file_name;
	                    echo "<p class='uploaded'>" . $upload_url . $file_name . "</p>";
	                }
	            }
	        }
	        exit;
	    }

	    function arm_upload_profile() {
	    	$upload_dir = MEMBERSHIPLITE_UPLOAD_DIR.'/';
	    	$upload_url = MEMBERSHIPLITE_UPLOAD_URL.'/';

	    	$file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
	    	$response = "";
	    	$userID = get_current_user_id();
	    	if ($file_name && !empty($userID) && $userID != 0) {
				//$oldCover = get_user_meta($userID, 'profile_cover', true);
	    		$content_length = (int) $_SERVER['CONTENT_LENGTH'];
	    		$file_size_new = number_format( ($content_length/1048576), 2, '.', '');

	    		$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	    		if($arm_is_valid_file)
	    		{
	    			$arm_upload_file_path = $upload_dir.$file_name;
	    			$this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);

	    			$response = $upload_url . $file_name;
	    			echo $response;
	    			exit;
	    		}
	    	} else {
	    		$files = $_FILES['armfileselect'];
	    		$file_size = (isset($_REQUEST['allow_size'])) ? $_REQUEST['allow_size'] : '';
	    		$file_name = $_REQUEST['fname'];
	    		$file_size_new = $files['size'];
	    		$file_size_new = number_format($file_size_new / 1048576, 2, '.', '');

	    		$arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $files );
	    		if($arm_is_valid_file)
	    		{
	    			if (!empty($file_size) && ($file_size_new > $file_size)) {
	    				$response = "<p class='error_upload_size'>" . __('File size not allowed', 'ARMember') . "</p>";
	    			} else {
	    				$arm_upload_file_path = $upload_dir . $file_name;
	    				$this->arm_upload_file_function($files['tmp_name'], $arm_upload_file_path);
	    				$response = $upload_url . $file_name;
	    				echo "<p class='uploaded'>" . $upload_url . $file_name . "</p>";
	    			}
	    		}
	    	}
	    	exit;
	    }

	    function arm_import_user() 
	    {
	    	global $ARMember, $arm_capabilities_global;
	    	
	    	$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');
	        $upload_dir = MEMBERSHIPLITE_UPLOAD_DIR.'/';
	        $upload_url = MEMBERSHIPLITE_UPLOAD_URL.'/';
	        $file_name = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
	        $response = "";
	        $userID = get_current_user_id();
	        if ($file_name && !empty($userID) && $userID != 0) {
	        	$file_size_new = $_FILES['armfileselect']['size'];
	        	$file_size_new = number_format($file_size_new / 1048576, 2, '.', '');

	            $arm_is_valid_file = $this->arm_check_valid_file_ext_data($file_name, $file_size_new, $_FILES['armfileselect'] );
	        	if($arm_is_valid_file)
	        	{
		    		$arm_upload_file_path = $upload_dir.$file_name;
	            	$this->arm_upload_file_function($_FILES['armfileselect']['tmp_name'], $arm_upload_file_path);
	                $response = $upload_url . $file_name;
	                echo $response;
	                exit;
	            }
	        }
	        echo $response;
	        exit;
	    }

	    function arm_upload_file_function($source, $destination){
            if( empty( $source ) || empty( $destination ) ){
                return false;
            }

            if( !function_exists('WP_Filesystem' ) ){
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }

            WP_Filesystem();
            global $wp_filesystem;
            
            $file_content = $wp_filesystem->get_contents( $source );

            $result = $wp_filesystem->put_contents( $destination, $file_content, 0777 );

            return $result;
        }

	    function arm_check_for_invalid_data( $file_content = '' ){
	    	if( '' == $file_content ){
	    		return true;
	    	}

	    	$arm_valid_pattern = '/(\<\?(php))/';

	    	if( preg_match($arm_valid_pattern,$file_content) ){
	            return false;
	        }

	        return true;
	    }

	    function arm_check_valid_file_ext_data($file_name, $file_size, $arm_files_arr)
        {
        	$is_valid_file = 0;
        	if ($file_name && $file_size <= 20 )
	        {
	        	$arm_allowed_mimes = $this->arm_allowed_wp_mime_types();
        		$denyExts = array("php", "php3", "php4", "php5", "pl", "py", "jsp", "asp", "exe", "cgi");

	        	$checkext = explode(".", $file_name);
	            $ext = strtolower( $checkext[count($checkext) - 1] );
	            
	            $actual_file_name = $arm_files_arr['name'];
	            $actual_checkext = explode(".", $actual_file_name);
	            $actual_ext = strtolower( $actual_checkext[count($actual_checkext) - 1] );

	            if (!in_array($ext, $denyExts) && in_array($ext,$arm_allowed_mimes) && !in_array($actual_ext, $denyExts) && in_array($actual_ext,$arm_allowed_mimes)) 
	            {
	            	if( !function_exists('WP_Filesystem' ) )
	            	{
		                require_once(ABSPATH . 'wp-admin/includes/file.php');
		            }
	            	WP_Filesystem();
		            global $wp_filesystem;
		            $file_content = $wp_filesystem->get_contents($arm_files_arr['tmp_name']);

            		$valid_data = $this->arm_check_for_invalid_data( $file_content );

            		if( ! $valid_data ){
            			echo "<p class='error_upload_size'>" . esc_html__('The file could not be uploaded due to security reason as it contains malicious code', 'ARMember'). "</p>";
            			header('HTTP/1.0 401 Unauthorized');
        				die;
            		}
            		else {
            			$is_valid_file = 1;
            		}
	            }
	        }
	        else {
	        	echo "<p class='error_upload_size'>" . esc_html__('This file could not be processed due file limit exceeded.', 'ARMember'). "</p>";
        		die;
	        }
	        return $is_valid_file;
        }

        function arm_deactivate_feedback_popup()
        {
            $question_options = array();
            $question_options['list_data_options'] = array(
                'setup-difficult'           => __( 'Set up is too difficult', 'ARMember' ),
                'docs-improvement'            => __( 'Lack of documentation', 'ARMember' ),
                'features'        => __( 'Not the features I wanted', 'ARMember' ),
                'better-plugin'   => __( 'Found a better plugin', 'ARMember' ),
                'incompatibility' => __( 'Incompatible with theme or plugin', 'ARMember' ),
                'bought-premium' => __( 'I bought premium version of ARMember', 'ARMember' ),
                'maintenance'     => __( 'Other', 'ARMember' ),
            );

            $html = '<div class="armlite-deactivate-form-head"><strong>' . esc_html( __( 'ARMember Lite - Sorry to see you go', 'ARMember' ) ) . '</strong></div>';
            $html .= '<div class="armlite-deactivate-form-body">';
            
            if( is_array( $question_options['list_data_options'] ) ) 
            {
                $html .= '<div class="armlite-deactivate-options">';
                $html .= '<p><strong>' . esc_html( __( 'Before you deactivate the ARMember Lite plugin, would you quickly give us your reason for doing so?', 'ARMember' ) ) . '</strong></p><p>';

                foreach( $question_options['list_data_options'] as $key => $option ) 
                {
                    $html .= '<input type="radio" name="armlite-deactivate-reason" id="' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '"> <label for="' . esc_attr( $key ) . '">' . esc_attr( $option ) . '</label><br>';
                }

                $html .= '</p><label id="armlite-deactivate-details-label" for="armlite-deactivate-reasons"><strong>' . esc_html( __( 'How could we improve ?', 'ARMember' ) ) .'</strong></label><textarea name="armlite-deactivate-details" id="armlite-deactivate-details" rows="2" style="width:100%"></textarea>';

                $html .= '</div>';
            }
            $html .= '<hr/>';
            
            $html .= '</div>';
            $html .= '<p class="deactivating-spinner"><span class="spinner"></span> ' . __( 'Submitting form', 'ARMember' ) . '</p>';
            $html .= '<div class="armlite-deactivate-form-footer"><p>';
            $html .= '<label for="armlite_anonymous" title="'
                . __("If you UNCHECK this then your email address will be sent along with your feedback. This can be used by armlite to get back to you for more info or a solution.",'ARMember')
                . '"><input type="checkbox" name="armlite-deactivate-tracking" checked="checked" id="armlite_anonymous"> ' . esc_html__( 'Send anonymous', 'ARMember' ) . '</label><br>';
            $html .= '<a id="armlite-deactivate-submit-form" class="button button-primary" href="#">'
                . __( '<span>Submit&nbsp;and&nbsp;</span>Deactivate', 'ARMember' )
                . '</a>';
            $html .= '</p></div>';
            ?>
            <div class="armlite-deactivate-form-bg"></div>
            <style type="text/css">
                .armlite-deactivate-form-active .armlite-deactivate-form-bg {background: rgba( 0, 0, 0, .5 );position: fixed;top: 0;left: 0;width: 100%;height: 100%;}
                .armlite-deactivate-form-wrapper {position: relative;z-index: 999;display: none; }
                .armlite-deactivate-form-active .armlite-deactivate-form-wrapper {display: inline-block;}
                .armlite-deactivate-form {display: none;}
                .armlite-deactivate-form-active .armlite-deactivate-form {position: absolute;bottom: 30px;left: 0;max-width: 500px;min-width: 360px;background: #fff;white-space: normal;}
                .armlite-deactivate-form-head {background: #00b2f0;color: #fff;padding: 8px 18px;}
                .armlite-deactivate-form-body {padding: 8px 18px 0;color: #444;}
                .armlite-deactivate-form-body label[for="armlite-remove-settings"] {font-weight: bold;}
                .deactivating-spinner {display: none;}
                .deactivating-spinner .spinner {float: none;margin: 4px 4px 0 18px;vertical-align: bottom;visibility: visible;}
                .armlite-deactivate-form-footer {padding: 0 18px 8px;}
                .armlite-deactivate-form-footer label[for="armlite_anonymous"] {visibility: hidden;}
                .armlite-deactivate-form-footer p {display: flex;align-items: center;justify-content: space-between;margin: 0;}
                #armlite-deactivate-submit-form span {display: none;}
                .armlite-deactivate-form.process-response .armlite-deactivate-form-body,.armlite-deactivate-form.process-response .armlite-deactivate-form-footer {position: relative;}
                .armlite-deactivate-form.process-response .armlite-deactivate-form-body:after,.armlite-deactivate-form.process-response .armlite-deactivate-form-footer:after {content: "";display: block;position: absolute;top: 0;left: 0;width: 100%;height: 100%;background-color: rgba( 255, 255, 255, .5 );}
            </style>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    var armlite_deactivateURL = $("#armlite-deactivate-link-<?php echo esc_attr( 'ARMember' ); ?>")
                        armlite_formContainer = $('#armlite-deactivate-form-<?php echo esc_attr( 'ARMember' ); ?>'),
                        armlite_deactivated = true,
                        armlite_detailsStrings = {
                            'setup-difficult' : '<?php echo __( 'What was the dificult part?', 'ARMember' ) ?>',
                            'docs-improvement' : '<?php echo __( 'What can we describe more?', 'ARMember' ) ?>',
                            'features' : '<?php echo __( 'How could we improve?', 'ARMember' ) ?>',
                            'better-plugin' : '<?php echo __( 'Can you mention it?', 'ARMember' ) ?>',
                            'incompatibility' : '<?php echo __( 'With what plugin or theme is incompatible?', 'ARMember' ) ?>',
                            'bought-premium' : '<?php echo __( 'Please specify experience', 'ARMember' ) ?>',
                            'maintenance' : '<?php echo __( 'Please specify', 'ARMember' ) ?>',
                        };

                    jQuery( armlite_deactivateURL).attr('onclick', "javascript:event.preventDefault();");
                    jQuery( armlite_deactivateURL ).on("click", function(){

                        function ARMLiteSubmitData(armlite_data, armlite_formContainer)
                        {
                            armlite_data['action']          = 'armlite_deactivate_plugin';
                            armlite_data['security']        = '<?php echo wp_create_nonce("armlite_deactivate_plugin" ); ?>';
                            armlite_data['dataType']        = 'json';
                            armlite_formContainer.addClass( 'process-response' );
                            armlite_formContainer.find(".deactivating-spinner").show();
                            jQuery.post(ajaxurl,armlite_data,function(response)
                            {
                                    window.location.href = armlite_url;
                            });
                        }

                        var armlite_url = armlite_deactivateURL.attr( 'href' );
                        jQuery('body').toggleClass('armlite-deactivate-form-active');
                        armlite_formContainer.show({complete: function(){
                            var offset = armlite_formContainer.offset();
                            if( offset.top < 50) {
                                $(this).parent().css('top', (50 - offset.top) + 'px')
                            }
                            jQuery('html,body').animate({ scrollTop: Math.max(0, offset.top - 50) });
                        }});
                        armlite_formContainer.html( '<?php echo $html; ?>');
                        armlite_formContainer.on( 'change', 'input[type=radio]', function()
                        {
                            var armlite_detailsLabel = armlite_formContainer.find( '#armlite-deactivate-details-label strong' );
                            var armlite_anonymousLabel = armlite_formContainer.find( 'label[for="armlite_anonymous"]' )[0];
                            var armlite_submitSpan = armlite_formContainer.find( '#armlite-deactivate-submit-form span' )[0];
                            var armlite_value = armlite_formContainer.find( 'input[name="armlite-deactivate-reason"]:checked' ).val();

                            armlite_detailsLabel.text( armlite_detailsStrings[ armlite_value ] );
                            armlite_anonymousLabel.style.visibility = "visible";
                            armlite_submitSpan.style.display = "inline-block";
                            if(armlite_deactivated)
                            {
                                armlite_deactivated = false;
                                jQuery('#armlite-deactivate-submit-form').removeAttr("disabled");
                                armlite_formContainer.off('click', '#armlite-deactivate-submit-form');
                                armlite_formContainer.on('click', '#armlite-deactivate-submit-form', function(e){
                                    e.preventDefault();
                                    var data = {
                                        armlite_reason: armlite_formContainer.find('input[name="armlite-deactivate-reason"]:checked').val(),
                                        armlite_details: armlite_formContainer.find('#armlite-deactivate-details').val(),
                                        armlite_anonymous: armlite_formContainer.find('#armlite_anonymous:checked').length,
                                    };
                                    ARMLiteSubmitData(data, armlite_formContainer);
                                });
                            }
                        });
                        armlite_formContainer.on('click', '#armlite-deactivate-submit-form', function(e){
                            e.preventDefault();
                            ARMLiteSubmitData({}, armlite_formContainer);
                        });
                        $('.armlite-deactivate-form-bg').on('click',function(){
                            armlite_formContainer.fadeOut();
                            $('body').removeClass('armlite-deactivate-form-active');
                        });
                    });
                });
            </script>
        <?php
        }

        function armite_deactivate_plugin_func() 
        {
            check_ajax_referer( 'armlite_deactivate_plugin', 'security' );
            if(!empty($_POST['armlite_reason']) && isset($_POST['armlite_details']) ) 
            {
                $armlite_anonymous = isset($_POST['armlite_anonymous']) && $_POST['armlite_anonymous'];
                $args = $_POST;
                $args['armlite_site_url']  = ARMLITE_HOME_URL;
                if(!$armlite_anonymous)
                {
                    $args['arm_lite_site_email'] = get_option('admin_email');
                }

                $url = 'https://www.armemberplugin.com/armember_addons/armlite_feedback.php';
                
                $response = wp_remote_post(
                	$url,
                	array(
                		'timeout' => 500,
                		'body' => $args
                	)
                );


            }
            echo json_encode( array(
                'status' => 'OK',
            ) );
            die();
        }

    }
}
global $arm_members_activity;
$arm_members_activity = new ARM_members_activity();