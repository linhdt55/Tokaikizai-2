<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2024, www.webfactoryltd.com
 */

class LoginLockdown_Functions extends LoginLockdown
{
  static function countFails($username = "")
  {
    global $wpdb;
    $options = LoginLockdown_Setup::get_options();
    $ip = LoginLockdown_Utility::getUserIP();

    $numFails = $wpdb->get_var( //phpcs:ignore
      $wpdb->prepare(
        "SELECT COUNT(login_attempt_ID) FROM " . $wpdb->lockdown_login_fails . " WHERE login_attempt_date + INTERVAL %d MINUTE > %s AND login_attempt_IP = %s",
        array($options['retries_within'], current_time('mysql'), $ip)
      )
    ); 

    return $numFails;
  }

  static function incrementFails($username = "", $reason = "")
  {
    global $wpdb;
    $options = LoginLockdown_Setup::get_options();
    $ip = LoginLockdown_Utility::getUserIP();

    $username = sanitize_user($username);
    $user = get_user_by('login', $username);

    if ($user || 1 == $options['lockout_invalid_usernames']) {
      if ($user === false) {
        $user_id = -1;
      } else {
        $user_id = $user->ID;
      }

      //phpcs:ignore no need to cache
      $wpdb->insert( //phpcs:ignore
        $wpdb->lockdown_login_fails,
        array(
          'user_id' => $user_id,
          'login_attempt_date' => current_time('mysql'),
          'login_attempt_IP' => $ip,
          'failed_user' => $username,
          'reason' => $reason
        )
      );
    }
  }

  static function lockDown($username = "", $reason = "")
  {
    global $wpdb;
    $options = LoginLockdown_Setup::get_options();
    $ip = LoginLockdown_Utility::getUserIP();

    $username = sanitize_user($username);
    $user = get_user_by('login', $username);
    if ($user || 1 == $options['lockout_invalid_usernames']) {
      if ($user === false) {
        $user_id = -1;
      } else {
        $user_id = $user->ID;
      }

      //phpcs:ignore no need to cache
      $wpdb->insert( //phpcs:ignore
        $wpdb->lockdown_lockdowns,
        array(
          'user_id' => $user_id,
          'lockdown_date' => current_time('mysql'),
          'release_date' => gmdate('Y-m-d H:i:s', strtotime(current_time('mysql')) + $options['lockout_length'] * 60),
          'lockdown_IP' => $ip,
          'reason' => $reason
        )
      );
    }
  }

  static function isLockedDown()
  {
    global $wpdb;
    $ip = LoginLockdown_Utility::getUserIP();

    //phpcs:ignore no need to cache as we always need live data
    $stillLocked = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM " . $wpdb->lockdown_lockdowns . " WHERE release_date > %s AND lockdown_IP = %s AND unlocked = 0", array(current_time('mysql'), $ip))); //phpcs:ignore

    return $stillLocked;
  }

  static function is_rest_request()
  {
    //phpcs: this is a safe check for REST requests, not data processing
    if (defined('REST_REQUEST') && REST_REQUEST || isset($_GET['rest_route']) && strpos(sanitize_text_field(wp_unslash($_GET['rest_route'])), '/', 0) === 0) { //phpcs:ignore
      return true;
    }

    global $wp_rewrite;
    if (null === $wp_rewrite) {
      $wp_rewrite = new WP_Rewrite();
    }

    $rest_url = wp_parse_url(trailingslashit(rest_url()));
    if (!is_array($rest_url) || !array_key_exists('path', $rest_url)) {
      return false;
    }

    $current_url = wp_parse_url(add_query_arg(array()));
    if (!is_array($current_url) || !array_key_exists('path', $current_url)) {
      return false;
    }

    $is_rest     = strpos($current_url['path'], $rest_url['path'], 0) === 0;

    return $is_rest;
  }

  static function wp_authenticate_username_password($user, $username, $password)
  {
    if (is_a($user, 'WP_User')) {
      return $user;
    }

    $options = LoginLockdown_Setup::get_options();

    $whitelisted = false;
    $user_ip = LoginLockdown_Utility::getUserIP();
    if (in_array($user_ip, $options['whitelist'])) {
      $whitelisted = true;
    }

    if (!$whitelisted && self::isLockedDown()) {
      self::lockdown_screen($options['block_message']);
      return new WP_Error('lockdown_fail_count', __("<strong>ERROR</strong>: We're sorry, but this IP has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'login-lockdown'));
    }

    if (!$username) {
      return $user;
    }

    if (self::is_rest_request()) {
      return $user;
    }

    $captcha = self::handle_captcha();
    if (is_wp_error($captcha)) {
      if ($options['max_login_retries'] <= self::countFails($username) && self::countFails($username) > 0) {
        self::lockDown($username, 'Too many captcha fails');
      }
      return $captcha;
    }

    $userdata = get_user_by('login', $username);

    if (!$whitelisted && $options['max_login_retries'] <= self::countFails($username)) {
      if ($options['max_login_retries'] <= self::countFails($username) && self::countFails($username) > 0) {
        self::lockDown($username, 'Too many fails');
      }

      return new WP_Error('lockdown_fail_count', __("<strong>ERROR</strong>: We're sorry, but this IP has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'login-lockdown'));
    }

    if (empty($username) || empty($password)) {
      $error = new WP_Error();

      if (empty($username))
        $error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.', 'login-lockdown'));

      if (empty($password))
        $error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.', 'login-lockdown'));

      return $error;
    }

    if ($userdata === false) {
      /* translators: %s is the url of the WordPress lost password page. */
      return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?', 'login-lockdown'), site_url('wp-login.php?action=lostpassword', 'login')));
    }

    $userdata = apply_filters('wp_authenticate_user', $userdata, $password);
    if (is_wp_error($userdata)) {
      return $userdata;
    }

    if (!is_string($password) || !is_string($userdata->user_pass) || is_null($userdata->ID) || !wp_check_password($password, $userdata->user_pass, $userdata->ID)) {
      /* translators: %s is the url of the WordPress lost password page. */
      return new WP_Error('incorrect_password', sprintf(__('<strong>ERROR</strong>: Incorrect password. <a href="%s" title="Password Lost and Found">Lost your password</a>?', 'login-lockdown'), site_url('wp-login.php?action=lostpassword', 'login')));
    }

    $user =  new WP_User($userdata->ID);
    return $user;
  }

  static function handle_captcha()
  {
    $options = LoginLockdown_Setup::get_options();

    if ($options['captcha'] == 'builtin') {
      if (isset($_POST['loginlockdown_captcha'])) { // phpcs:ignore
        $captcha_responses = array_map('sanitize_text_field', wp_unslash($_POST['loginlockdown_captcha'])); // phpcs:ignore
        $captcha_tokens = array_map('sanitize_text_field', wp_unslash($_POST['loginlockdown_captcha_token'])); // phpcs:ignore
        
        foreach ($captcha_responses as $captcha_id => $captcha_val) {
            if (wp_hash($captcha_val) === $captcha_tokens[$captcha_id]) {
                return true;
            } else {
                return new WP_Error('lockdown_builtin_captcha_failed', __("<strong>ERROR</strong>: captcha verification failed.<br /><br />Please try again.", 'login-lockdown'));
            }
        }
      } else {
        return new WP_Error('lockdown_builtin_captcha_failed', __("<strong>ERROR</strong>: captcha verification failed.<br /><br />Please try again.", 'login-lockdown'));
      }
    }

    return true;
  }

  static function loginFailed($username, $error)
  {
    self::incrementFails($username, $error->get_error_code());
  }

  static function login_error_message($error)
  {
    $options = LoginLockdown_Setup::get_options();

    if ($options['mask_login_errors'] == 1) {
      $error = __('Login Failed', 'login-lockdown');
    }
    return $error;
  }

  static function login_form_fields_print()
  {
      //phpcs:ignore this just prints the recaptcha HTML inline and all variables are already escaped
      echo self::login_form_fields(false); //phpcs:ignore
  }

  static function login_form_fields($output)
  {
    $options = LoginLockdown_Setup::get_options();
    $showcreditlink = $options['show_credit_link'];

    if(false === $output){
        $output = '';
    }

    if ($options['captcha'] == 'builtin') {
        $output .= '<p><label for="loginlockdown_captcha">Are you human? Please solve: ';
        $captcha_id = wp_rand(1000, 9999);
        $captcha = self::math_captcha_generate($captcha_id);
        $output .= '<img class="loginlockdown-captcha-img" style="vertical-align: text-top;" src="' . $captcha['img'] . '" alt="Captcha" />';
        $output .= '<input class="input" type="text" size="3" name="loginlockdown_captcha[' . intval($captcha_id) . ']" id="loginlockdown_captcha" value=""/>';
        $output .= '<input type="hidden" name="loginlockdown_captcha_token[' . intval($captcha_id) . ']" id="loginlockdown_captcha_token" value="' . wp_hash($captcha['value'])  . '" />';
        $output .= '</label></p><br />';
    }

    if ($showcreditlink != "no" && $showcreditlink != 0) {
      $output .=  "<div id='loginlockdown-protected-by' style='display: block; clear: both; padding-top: 20px; text-align: center;'>";
      $output .= esc_html__('Login form protected by', 'login-lockdown');
      $output .=  ' <a target="_blank" href="' . esc_url('https://wploginlockdown.com/') . '">Login Lockdown</a></div>';
      $output .=  '<script>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelector("#loginform").append(document.querySelector("#loginlockdown-protected-by"));
            });
            </script>';
    }
    return $output;
  }

  static function lockdown_screen($block_message = false)
  {
    $main_color = '#29b99a';
    $secondary_color = '#3fccb0';

    echo '<style>
            @import url(\'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400;1,500;1,700&display=swap\');

            #loginlockdown_lockdown_screen_wrapper{
                font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
                width:100%;
                height:100%;
                position:fixed;
                top:0;
                left:0;
                z-index: 999999;
                font-size: 14px;
                color: #333;
                line-height: 1.4;
                background-image: linear-gradient(45deg, ' . esc_attr($main_color) . ' 25%, ' . esc_attr($secondary_color) . ' 25%, ' . esc_attr($secondary_color) . ' 50%, ' . esc_attr($main_color) . ' 50%, ' . esc_attr($main_color) . ' 75%, ' . esc_attr($secondary_color) . ' 75%, ' . esc_attr($secondary_color) . ' 100%);
                background-size: 28.28px 28.28px;
            }

            #loginlockdown_lockdown_screen_wrapper form{
                max-width: 300px;
                top:50%;
                left:50%;
                margin-top:-200px;
                margin-left:-200px;
                border: none;
                background: #ffffffde;
                box-shadow: 0 1px 3px rgb(0 0 0 / 4%);
                position: fixed;
                text-align:center;
                background: #fffffff2;
                padding: 20px;
                -webkit-box-shadow: 5px 5px 0px 1px rgba(0,0,0,0.22);
                box-shadow: 5px 5px 0px 1px rgba(0,0,0,0.22);
            }

            #loginlockdown_lockdown_screen_wrapper p{
                padding: 10px;
                line-height:1.5;
            }

            #loginlockdown_lockdown_screen_wrapper p.error{
                background: #f11c1c;
                color: #FFF;
                font-weight: 500;
            }

            #loginlockdown_lockdown_screen_wrapper form input[type="text"]{
                padding: 4px 10px;
                border-radius: 2px;
                border: 1px solid #c3c4c7;
                font-size: 16px;
                line-height: 1.33333333;
                margin: 0 6px 16px 0;
                min-height: 40px;
                max-height: none;
                width: 100%;
            }

            #loginlockdown_lockdown_screen_wrapper form input[type="submit"]{
                padding: 10px 10px;
                border-radius: 2px;
                border: none;
                font-size: 16px;
                background: ' . esc_attr($main_color) . ';
                color: #FFF;
                cursor: pointer;
                width: 100%;
            }

            #loginlockdown_lockdown_screen_wrapper form input[type="submit"]:hover{
                background: ' . esc_attr($secondary_color) . ';
            }
        </style>

        <script>
        document.title = "' . esc_html(get_bloginfo('name')) . '";
        </script>';
    echo '<div id="loginlockdown_lockdown_screen_wrapper">';

    echo '<form method="POST">';

    if (isset($_POST['loginlockdown_recovery_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['loginlockdown_recovery_nonce'])), 'loginlockdown_recovery')) {
      
      if (!isset($_POST['loginlockdown_recovery_email']) || !filter_var(wp_unslash($_POST['loginlockdown_recovery_email']), FILTER_VALIDATE_EMAIL)) {
        $display_message = '<p class="error">Invalid email address.</p>';
      } else {
        $email = sanitize_text_field(wp_unslash($_POST['loginlockdown_recovery_email']));
        $user = get_user_by('email', $email);        
        if (user_can($user, 'administrator')) {
          $unblock_key = md5(time() . wp_rand(10000, 9999));
          $unblock_attempts = get_transient('loginlockdown_unlock_count_' . $user->ID);
          if (!$unblock_attempts) {
            $unblock_attempts = 0;
          }

          $unblock_attempts++;
          set_transient('loginlockdown_unlock_count_' . $user->ID, $unblock_attempts, HOUR_IN_SECONDS);

          if ($unblock_attempts <= 3) {
            set_transient('loginlockdown_unlock_' . $unblock_key, $unblock_key, HOUR_IN_SECONDS);

            $unblock_url = add_query_arg(array('loginlockdown_unblock' => $unblock_key), wp_login_url());

            $subject  = 'Login Lockdown unblock instructions for ' . site_url();
            $message  = '<p>The IP ' . LoginLockdown_Utility::getUserIP() . ' has been locked down and someone submitted an unblock request using your email address <strong>' . $email . '</strong></p>';
            $message .= '<p>If this was you, and you have locked yourself out please click <a target="_blank" href="' . $unblock_url . '">this link</a> which is valid for 1 hour.</p>';
            $message .= '<p>Please note that for security reasons, this will only unblock the IP of the person opening the link, not the IP of the person who submitted the unblock request. To unblock someone else please do so on the <a href="' . admin_url('options-general.php?page=loginlockdown#loginlockdown_activity') . '">Login Lockdown Activity Page</p>';

            add_filter('wp_mail_content_type', function () {
              return "text/html";
            });

            wp_mail($user->user_email, $subject, $message);
          }
        } else {
          // If no admin using the submitted email exists, ignore silently
        }

        if (isset($unblock_attempts) && $unblock_attempts > 3) {
          $display_message = '<p class="error">You have already attempted to unblock yourself recently, please wait 1 hour before trying again.</p>';
        } else {
          $display_message = '<p>If an administrator having the email address <strong>' . $email . '</strong> exists, an email has been sent with instructions to regain access.</p>';
        }
      }
    }

    echo '<img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/images/loginlockdown-logo.png" alt="Login Lockdown" height="60" title="Login Lockdown">';

    echo '<br />';
    echo '<br />';
    if ($block_message !== false) {
      echo '<p class="error">' . esc_html($block_message) . '</p>';
    } else {
      echo '<p class="error">We\'re sorry, but your IP has been blocked due to too many recent failed login attempts.</p>';
    }
    if (!empty($display_message)) {
      LoginLockdown_Utility::wp_kses_wf($display_message);
    }
    echo '<p>If you are a user with administrative privilege please enter your email below to receive instructions on how to unblock yourself.</p>';
    echo '<input type="text" name="loginlockdown_recovery_email" value="" placeholder="" />';
    echo '<input type="submit" name="loginlockdown_recovery_submit" value="Send unblock email" placeholder="" />';
    wp_nonce_field('loginlockdown_recovery', 'loginlockdown_recovery_nonce');


    echo '</form>';
    echo '</div>';

    exit();
  }

  static function handle_unblock()
  {
    global $wpdb;
    //phpcs:ignore missing nonce as this is called via a link in email or stored by user
    $options = LoginLockdown_Setup::get_options();
    if (isset($_GET['loginlockdown_unblock']) && $options['global_unblock_key'] === sanitize_text_field(wp_unslash($_GET['loginlockdown_unblock']))) { //phpcs:ignore
      $user_ip = LoginLockdown_Utility::getUserIP();
      if (!in_array($user_ip, $options['whitelist'])) {
        $options['whitelist'][] = LoginLockdown_Utility::getUserIP();
      }
      update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
    }

    if (isset($_GET['loginlockdown_unblock']) && strlen($_GET['loginlockdown_unblock']) == 32) { //phpcs:ignore
      $unblock_key = sanitize_key(wp_unslash($_GET['loginlockdown_unblock'])); //phpcs:ignore
      $unblock_transient = get_transient('loginlockdown_unlock_' . $unblock_key);
      if ($unblock_transient == $unblock_key) {
        $user_ip = LoginLockdown_Utility::getUserIP();
        //phpcs:ignore no need to cache
        $wpdb->delete( //phpcs:ignore
          $wpdb->lockdown_lockdowns,
          array(
            'lockdown_IP' => $user_ip
          )
        );

        if (!in_array($user_ip, $options['whitelist'])) {
          $options['whitelist'][] = LoginLockdown_Utility::getUserIP();
        }

        update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
      }
    }
  }

  static function handle_global_block()
  {
    $options = LoginLockdown_Setup::get_options();

    //If user is on local or cloud whitelist, don't check anything else
    $user_ip = LoginLockdown_Utility::getUserIP();
    if (in_array($user_ip, $options['whitelist'])) {
      return false;
    }

    //Check website lock
    if ($options['global_block'] == '1' && self::isLockedDown()) {
      self::lockdown_screen($options['block_message']);
    }
  }

  public static function clean_ip_string($ip)
  {
    $ip = trim($ip);
    return $ip;
  }

  public static function pretty_fail_errors($error_code)
  {
    switch ($error_code) {
      case 'lockdown_location_blocked':
        return 'Blocked Location';
        break;
      case 'lockdown_fail_count':
        return 'User exceeded maximum number of fails';
        break;
      case 'lockdown_bot':
        return 'Bot';
        break;
      case 'empty_username':
        return 'Empty Username';
        break;
      case 'empty_password':
        return 'Empty Password';
        break;
      case 'incorrect_password':
        return 'Incorrect Password';
        break;
      case 'invalid_username':
        return 'Invalid Username';
        break;
      case 'lockdown_builtin_captcha_failed':
        return 'Built-in captcha failed verification';
        break;
      default:
        return 'Unknown';
        break;
    }
  }

  // auto download / install / activate WP 301 Redirects plugin
  static function install_wp301()
  {
    check_ajax_referer('install_wp301');

    if (false === current_user_can('manage_options')) {
      wp_die('Sorry, you have to be an admin to run this action.');
    }

    $plugin_slug = 'eps-301-redirects/eps-301-redirects.php';
    $plugin_zip = 'https://downloads.wordpress.org/plugin/eps-301-redirects.latest-stable.zip';

    @include_once ABSPATH . 'wp-admin/includes/plugin.php';
    @include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    @include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    @include_once ABSPATH . 'wp-admin/includes/file.php';
    @include_once ABSPATH . 'wp-admin/includes/misc.php';
    echo '<style>
		body{
			font-family: sans-serif;
			font-size: 14px;
			line-height: 1.5;
			color: #444;
		}
		</style>';

    echo '<div style="margin: 20px; color:#444;">';
    echo 'If things are not done in a minute <a target="_parent" href="' . esc_url(admin_url('plugin-install.php?s=301%20redirects%20webfactory&tab=search&type=term')) . '">install the plugin manually via Plugins page</a><br><br>';
    echo 'Starting ...<br><br>';

    wp_cache_flush();
    $upgrader = new Plugin_Upgrader();
    echo 'Check if WP 301 Redirects is already installed ... <br />';
    if (self::is_plugin_installed($plugin_slug)) {
      echo 'WP 301 Redirects is already installed! <br /><br />Making sure it\'s the latest version.<br />';
      $upgrader->upgrade($plugin_slug);
      $installed = true;
    } else {
      echo 'Installing WP 301 Redirects.<br />';
      $installed = $upgrader->install($plugin_zip);
    }
    wp_cache_flush();

    if (!is_wp_error($installed) && $installed) {
      echo 'Activating WP 301 Redirects.<br />';
      $activate = activate_plugin($plugin_slug);

      if (is_null($activate)) {
        echo 'WP 301 Redirects Activated.<br />';

        echo '<script>setTimeout(function() { top.location = "' . esc_url(admin_url('options-general.php?page=eps_redirects')) . '"; }, 1000);</script>';
        echo '<br>If you are not redirected in a few seconds - <a href="' . esc_url(admin_url('options-general.php?page=eps_redirects')) . '" target="_parent">click here</a>.';
      }
    } else {
      echo 'Could not install WP 301 Redirects. You\'ll have to <a target="_parent" href="' . esc_url(admin_url('plugin-install.php?s=301%20redirects%20webfactory&tab=search&type=term')) . '">download and install manually</a>.';
    }

    echo '</div>';
  } // install_wp301


  /**
   * Check if given plugin is installed
   *
   * @param [string] $slug Plugin slug
   * @return boolean
   */
  static function is_plugin_installed($slug)
  {
    if (!function_exists('get_plugins')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();

    if (!empty($all_plugins[$slug])) {
      return true;
    } else {
      return false;
    }
  } // is_plugin_installed

  // convert HEX(HTML) color notation to RGB
  static function hex2rgb($color)
  {
      if ($color[0] == '#') {
          $color = substr($color, 1);
      }

      if (strlen($color) == 6) {
          list($r, $g, $b) = array(
              $color[0] . $color[1],
              $color[2] . $color[3],
              $color[4] . $color[5]
          );
      } elseif (strlen($color) == 3) {
          list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
      } else {
          return array(255, 255, 255);
      }

      $r = hexdec($r);
      $g = hexdec($g);
      $b = hexdec($b);

      return array($r, $g, $b);
  } // html2rgb


  // output captcha image
  static function math_captcha_generate($captcha_id = false)
  {
      ob_start();
      
      $a = wp_rand(0, (int) 10);
      $b = wp_rand(0, (int) 10);
      if(isset($_GET['color'])){ // phpcs:ignore
          $color = substr($_GET['color'],0,7); // phpcs:ignore
          $color = urldecode($color);
      } else{
          $color = '#FFFFFF';
      }

      if ($a > $b) {
          $out = "$a - $b";
          $captcha_value = $a - $b;
      } else {
          $out = "$a + $b";
          $captcha_value = $a + $b;
      }

      $font   = 5;
      $width  = ImageFontWidth($font) * strlen($out);
      $height = ImageFontHeight($font);
      $im     = ImageCreate($width, $height);

      $x = imagesx($im) - $width;
      $y = imagesy($im) - $height;

      $white = imagecolorallocate($im, 255, 255, 255);
      $gray = imagecolorallocate($im, 66, 66, 66);
      $black = imagecolorallocate($im, 0, 0, 0);
      $trans_color = $white; //transparent color

      if ($color) {
          $color = self::hex2rgb($color);
          $new_color = imagecolorallocate($im, $color[0], $color[1], $color[2]);
          imagefill($im, 1, 1, $new_color);
      } else {
          imagecolortransparent($im, $trans_color);
      }

      imagestring($im, $font, $x, $y, $out, $black);

      // always add noise
      if (1 == 1) {
          $color_min = 100;
          $color_max = 200;
          $rand1 = imagecolorallocate($im, wp_rand($color_min, $color_max), wp_rand($color_min, $color_max), wp_rand($color_min, $color_max));
          $rand2 = imagecolorallocate($im, wp_rand($color_min, $color_max), wp_rand($color_min, $color_max), wp_rand($color_min, $color_max));
          $rand3 = imagecolorallocate($im, wp_rand($color_min, $color_max), wp_rand($color_min, $color_max), wp_rand($color_min, $color_max));
          $rand4 = imagecolorallocate($im, wp_rand($color_min, $color_max), wp_rand($color_min, $color_max), wp_rand($color_min, $color_max));
          $rand5 = imagecolorallocate($im, wp_rand($color_min, $color_max), wp_rand($color_min, $color_max), wp_rand($color_min, $color_max));

          $style = array($rand1, $rand2, $rand3, $rand4, $rand5);
          imagesetstyle($im, $style);
          imageline($im, wp_rand(0, $width), 0, wp_rand(0, $width), $height, IMG_COLOR_STYLED);
          imageline($im, wp_rand(0, $width), 0, wp_rand(0, $width), $height, IMG_COLOR_STYLED);
          imageline($im, wp_rand(0, $width), 0, wp_rand(0, $width), $height, IMG_COLOR_STYLED);
          imageline($im, wp_rand(0, $width), 0, wp_rand(0, $width), $height, IMG_COLOR_STYLED);
          imageline($im, wp_rand(0, $width), 0, wp_rand(0, $width), $height, IMG_COLOR_STYLED);
      }

      imagegif($im);

      // Get image data
      $image_data = ob_get_clean();
      return array('value' => $captcha_value, 'img' => 'data:image/png;base64,' . base64_encode($image_data));
  } // create
} // class
