<?php
class VRCalendarAdmin extends VRCSingleton {

    protected function __construct(){

        add_action('init', array($this, 'handleCommands'));
        add_action( 'admin_notices', array( $this,'adminNotice') );

        add_action( 'admin_menu', array($this,'registerAdminPages') );

        
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueueStyles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
    }
    function adminNotice() {
        $type = __('updated', VRCALENDAR_PLUGIN_TEXT_DOMAIN);

        if(isset ($_GET['vrc_msg']) ) {
            $msg = urldecode($_GET['vrc_msg']);
            if(!empty($msg))
            {
                if(isset ($_GET['vrc_msg_type']) ) {
                    $type = $_GET['vrc_msg_type'];
                }
                ?>
                <div class="<?php echo $type; ?>">
                    <p><?php echo $msg; ?></p>
                </div>
            <?php
            }
        }
    }
    function handleCommands() {
        if(isset($_REQUEST['vrc_cmd'])) {
            $cmd = $_REQUEST['vrc_cmd'];
            $cmd = explode(':', $cmd);
            $cmd = array_filter($cmd);
            $callable = false;
            if(count($cmd) == 2) {
                $callable = array($cmd[0], $cmd[1]);
            }
            else {
                $callable = $cmd[0];
            }
            call_user_func($callable);
        }
    }
    function syncCalendar() {
        $msg = __('Something went wrong!', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
        $type = __('error', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
        if(isset($_GET['cal_id'])) {
            $VRCalendarEntity = VRCalendarEntity::getInstance();
            $VRCalendarEntity->synchronizeCalendar( $_GET['cal_id'] );
            $msg = __('Calendar Synchronized successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
            $type = __('updated', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
        }
        $msg = rawurlencode($msg);
        $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}&vrc_msg_type={$type}");
        wp_redirect($redirect_url);
    }
    function syncAllCalendars() {
        $VRCalendarEntity = VRCalendarEntity::getInstance();

        /* Fetch all calendars */
        $cals = $VRCalendarEntity->getAllCalendar();
        foreach($cals as $cal) {
            $VRCalendarEntity->synchronizeCalendar($cal->calendar_id);
        }
    }
    function deleteCalendar() {
        $VRCalendarEntity = VRCalendarEntity::getInstance();
        $VRCalendarEntity->deleteCalendar( $_GET['cal_id'] );
        $msg = __('Calendar deleted successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
        $msg = rawurlencode($msg);
        $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");
        wp_redirect($redirect_url);
    }

    

    function cloneCalendar() {
        
        global $gbversiontype;
        
        $VRCalendarEntity = VRCalendarEntity::getInstance();
        $cals = $VRCalendarEntity->getAllCalendar();
        
        if ($gbversiontype == "enterprisepaid"){
            $cals = $VRCalendarEntity->getAllCalendar();
            if( count($cals)>=100 ) {
                $msg = __('Only 100 calendars are allowed', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
                $msg = rawurlencode($msg);
                $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");
                
                echo '<script>window.location = "'.$redirect_url.'"</script>';
                //wp_redirect($redirect_url);
                exit;
            }
        } else if ($gbversiontype == "enterprise500"){
            $cals = $VRCalendarEntity->getAllCalendar();
            if( count($cals)>=500 ) {
                $msg = __('Only 500 calendars are allowed', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
                $msg = rawurlencode($msg);
                $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");
                
                echo '<script>window.location = "'.$redirect_url.'"</script>';
                exit;
            }
        } else { // type of pro or any other type
            $cals = $VRCalendarEntity->getAllCalendar();
            if( count($cals)>=10 ) {
                $msg = __('Only 10 calendars are allowed', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
                $msg = rawurlencode($msg);
                $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");
                
                wp_redirect($redirect_url);
                exit;
            }
        } //end checking calendar numbers
        
        $VRCalendarEntity->cloneCalendar( $_GET['cal_id'] );
        
        $msg = __('Calendar cloned successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
        $msg = rawurlencode($msg);
        $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");
        wp_redirect($redirect_url);
        exit;
    }
    

    function exportICAL() {

        $VRCalendarEntity = VRCalendarEntity::getInstance();
        $VRCalendarEntity->downloadCalendar( $_GET['cal_id'] );

        exit;
    }

    function saveCalendar() {
        $data = $_POST;	

       /* echo "<pre>";
//        print_r(array_filter( $data['calendar_links'] ));
        print_r( $data );
        echo "</pre>";
        die();*/

        $msg = __('Calendar updated successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN);;
        if($data['calendar_id']<=0) {
            $data['calendar_created_on'] = date('Y-m-d H:i:s');
            $data['calendar_author_id'] = get_current_user_id();
            $msg = __('Calendar created successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
		}
        if($data['calendar_id']>0 && @$data['calendar_subcase'] =='dup') {
			$data['calendar_created_on'] = date('Y-m-d H:i:s');
            $data['calendar_author_id'] = get_current_user_id();
			$msg = __('Calendar duplicated successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
		}
		$data['calendar_offer_weekly'] = (isset($data['calendar_offer_weekly'])?$data['calendar_offer_weekly']:'no');
		$data['calendar_offer_monthly'] = (isset($data['calendar_offer_monthly'])?$data['calendar_offer_monthly']:'no');
        $data['calendar_modified_on'] = date('Y-m-d H:i:s');
        
        //$data['calendar_links'] = array_filter( $data['calendar_links'] );

        array_pop($data['calendar_links']['name']);
        array_pop($data['calendar_links']['url']);
        $calendar_links= array();
        /* convert this to required format */
        foreach($data['calendar_links']['name'] as $k=>$v){
            $tmp = array();
            $tmp['name'] = $data['calendar_links']['name'][$k];
            $tmp['url'] = $data['calendar_links']['url'][$k];
            $calendar_links[] = $tmp;
        }
        $data['calendar_links'] = $calendar_links;

        /* remove last element from variation entries */
        array_pop($data['calendar_price_exception']['start_date']);
        array_pop($data['calendar_price_exception']['end_date']);
        array_pop($data['calendar_price_exception']['price_per_night']);
        array_pop($data['calendar_price_exception']['price_per_week']);
	    array_pop($data['calendar_price_exception']['price_per_month']);
        array_pop($data['calendar_price_exception']['seasonal_minimum_nights']);

        $exception = array();
        /* convert this to required format */
        foreach($data['calendar_price_exception']['start_date'] as $k=>$v)
        {
            $tmp = array();
            $tmp['start_date'] = $data['calendar_price_exception']['start_date'][$k];
            $tmp['end_date'] = $data['calendar_price_exception']['end_date'][$k];
            $tmp['price_per_night'] = $data['calendar_price_exception']['price_per_night'][$k];
            $tmp['price_per_week'] = $data['calendar_price_exception']['price_per_week'][$k];
            $tmp['price_per_month'] = $data['calendar_price_exception']['price_per_month'][$k];
            $tmp['seasonal_minimum_nights'] = $data['calendar_price_exception']['seasonal_minimum_nights'][$k];
            $exception[] = $tmp;
        }


        $VRCalendarBooking = VRCalendarBooking::getInstance();
        $VRCalendarBooking->saveUnableBookingDate($data);

        $data['calendar_price_exception'] = $exception;
        $VRCalendarEntity = VRCalendarEntity::getInstance();
        $VRCalendarEntity->saveCalendar( $data );
        $msg = rawurlencode($msg);
        $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");
        wp_redirect($redirect_url);
        exit();
    }

    function saveSettings() {
        $VRCalendarSettings = VRCalendarSettings::getInstance();

        $VRCalendarSettings->setSettings('booking_page', $_POST['booking_page']);
        $VRCalendarSettings->setSettings('payment_page', $_POST['payment_page']);
        $VRCalendarSettings->setSettings('thank_you_page', $_POST['thank_you_page']);
        $VRCalendarSettings->setSettings('payment_cancel_page', $_POST['payment_cancel_page']);
        $VRCalendarSettings->setSettings('paypal_email', $_POST['paypal_email']);
        $VRCalendarSettings->setSettings('stripe_api_key', $_POST['stripe_api_key']);
        $VRCalendarSettings->setSettings('payment_mode', $_POST['payment_mode']);
        $VRCalendarSettings->setSettings('auto_sync', $_POST['auto_sync']);
        $VRCalendarSettings->setSettings('attribution', $_POST['attribution']);

        $VRCalendarSettings->setSettings('load_jquery_ui_css', $_POST['load_jquery_ui_css']);

		$VRCalendarSettings->setSettings('attr_currency', $_POST['attr_currency']);
        $VRCalendarSettings->setSettings('language', $_POST['language']);
         $VRCalendarSettings->setSettings('searchbar_result_page', $_POST['searchbar_result_page']);
        /* Updated sync hook */
        wp_clear_scheduled_hook( 'vrc_cal_sync_hook' );
        wp_schedule_event( time(), $VRCalendarSettings->getSettings('auto_sync', 'daily'), 'vrc_cal_sync_hook' );
        $msg = __('Settings saved successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
        $msg = rawurlencode($msg);
        $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-settings&vrc_msg={$msg}");
        wp_redirect($redirect_url);
        exit();
    }

    function deleteBooking() {
        $booking_id = $_GET['bid'];
        $cal_id = $_GET['cal_id'];
        $VRCalendarBooking = VRCalendarBooking::getInstance();
        $VRCalendar = VRCalendarEntity::getInstance();

        $booking_details = $VRCalendarBooking->getBookingByID($booking_id);
        $cal_data = $VRCalendar->getCalendar($booking_details->booking_calendar_id);

        $VRCalendarBooking->deleteBooking($booking_id);
        $email_data = array(
            'calendar_id'=>$cal_id,
            'booking_user_fname'=>$booking_details->booking_user_fname,
            'booking_user_lname'=>$booking_details->booking_user_lname,
            'booking_id'=>$booking_details->booking_id,
            'booking_created_on'=>$booking_details->booking_created_on,
            'booking_date_from'=>$booking_details->booking_date_from,
            'booking_date_to'=>$booking_details->booking_date_to,
            'booking_guests'=>$booking_details->booking_guests,
        );
        /* Send email to user about booking cancellation */

        $VRCTransactionalEmail = VRCTransactionalEmail::getInstance();
        $VRCTransactionalEmail->sendBookingRemoved($email_data, array($booking_details->booking_user_email));

        $msg = __('Booking removed successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
        $msg = rawurlencode($msg);
        $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&view=bookings&cal_id={$cal_id}&vrc_msg={$msg}");
        wp_redirect($redirect_url);
        exit();
    }

    function approveBooking() {
        $booking_id = $_GET['bid'];
        $cal_id = $_GET['cal_id'];
        $VRCalendarBooking = VRCalendarBooking::getInstance();
        $VRCalendarBooking->approveBooking($booking_id);
        $msg = __('Booking approved successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
        $msg = rawurlencode($msg);
        $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&view=bookings&cal_id={$cal_id}&vrc_msg={$msg}");
        wp_redirect($redirect_url);
        exit();
    }

                
    //12:29:2015 permissions change for admin panel: was Manage_options changed now to edit_dashboard
    function registerAdminPages() {
        add_menu_page( VRCALENDAR_PLUGIN_NAME, VRCALENDAR_PLUGIN_NAME, 'edit_dashboard', VRCALENDAR_PLUGIN_SLUG.'-dashboard', array($this,'dashboard') );
        add_submenu_page( VRCALENDAR_PLUGIN_SLUG.'-dashboard', __('Dashboard', VRCALENDAR_PLUGIN_TEXT_DOMAIN), __('Dashboard', VRCALENDAR_PLUGIN_TEXT_DOMAIN), 'edit_dashboard', VRCALENDAR_PLUGIN_SLUG.'-dashboard', array($this,'dashboard') );
        add_submenu_page( VRCALENDAR_PLUGIN_SLUG.'-dashboard', __('Add Calendar', VRCALENDAR_PLUGIN_TEXT_DOMAIN), __('Add Calendar', VRCALENDAR_PLUGIN_TEXT_DOMAIN), 'edit_dashboard', VRCALENDAR_PLUGIN_SLUG.'-add-calendar', array($this,'addCalendar') );
		// ..................add searchbars...................................start.......................... 
		add_submenu_page( VRCALENDAR_PLUGIN_SLUG.'-dashboard', __('Add Search Bar', VRCALENDAR_PLUGIN_TEXT_DOMAIN), __('Add Search Bar', VRCALENDAR_PLUGIN_TEXT_DOMAIN), 'edit_dashboard', VRCALENDAR_PLUGIN_SLUG.'-add-search-bar', array($this,'addSearchbar') );
		// ..................add searchbars...................................end.......................... 
        //add_submenu_page( VRCALENDAR_PLUGIN_SLUG.'-dashboard', 'Bookings', 'Bookings', 'manage_options', VRCALENDAR_PLUGIN_SLUG.'-calendar-bookings', array($this,'calendarBookings') );
        add_submenu_page( VRCALENDAR_PLUGIN_SLUG.'-dashboard', __('Settings', VRCALENDAR_PLUGIN_TEXT_DOMAIN), __('Settings', VRCALENDAR_PLUGIN_TEXT_DOMAIN), 'edit_dashboard', VRCALENDAR_PLUGIN_SLUG.'-settings', array($this,'settings') );
        add_submenu_page( VRCALENDAR_PLUGIN_SLUG.'-dashboard', __('Information', VRCALENDAR_PLUGIN_TEXT_DOMAIN), __('Information', VRCALENDAR_PLUGIN_TEXT_DOMAIN), 'edit_dashboard', VRCALENDAR_PLUGIN_SLUG.'-information', array($this,'information') );
    }

    function information() {
        require(VRCALENDAR_PLUGIN_DIR.'/Admin/Views/Information.php');
    }

    function settings() {
        require(VRCALENDAR_PLUGIN_DIR.'/Admin/Views/Settings.php');
    }

    function addCalendar() {
        global $gbversiontype;
        
        $VRCalendarEntity = VRCalendarEntity::getInstance();
        if(isset($_GET['cal_id'])) {
            $cal = $VRCalendarEntity->getCalendar($_GET['cal_id']);
            if(!isset($cal->calendar_id)) {
                $msg = __('Invalid calendar!', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
                $msg = rawurlencode($msg);
                $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");

                echo '<script>window.location = "'.$redirect_url.'"</script>';
                exit;
            }
        } else if ($gbversiontype == "enterprisepaid"){
            $cals = $VRCalendarEntity->getAllCalendar();
            if( count($cals)>=100 ) {
                $msg = __('Only 100 calendars are allowed', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
                $msg = rawurlencode($msg);
                $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");

                echo '<script>window.location = "'.$redirect_url.'"</script>';
                exit;
            }
        } else if ($gbversiontype == "enterprise500"){
            $cals = $VRCalendarEntity->getAllCalendar();
            if( count($cals)>=500 ) {
                $msg = __('Only 500 calendars are allowed', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
                $msg = rawurlencode($msg);
                $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");
                
                echo '<script>window.location = "'.$redirect_url.'"</script>';
                exit;
            }
        } else { // type of pro or any other type
            $cals = $VRCalendarEntity->getAllCalendar();
            if( count($cals)>=10 ) 
            {
                $msg = __('Only 10 calendars are allowed', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
                $msg = rawurlencode($msg);
                $redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-dashboard&vrc_msg={$msg}");
                   
                echo '<script>window.location = "'.$redirect_url.'"</script>';
                exit;
            }
        }
        require(VRCALENDAR_PLUGIN_DIR.'/Admin/Views/AddCalendar.php');
            
}
	// .................added searchbars..............................start...........................
    function addSearchbar(){
        global $gbversiontype;
        
        //06:10:2016 asm
        //In the case of Pro type versions, we just want to display an upsell info page
        //with more information about what a searchbar is and how to upgrade. In other cases
        //we get down to work about what a searchbar can really do
        if ($gbversiontype == "pro" OR $gbversiontype == "pro-envato"){
            require(VRCALENDAR_PLUGIN_DIR.'/Admin/Views/AddSearchbarPro.php');
            exit;
        }
        
		$VRCalendarEntity = VRCalendarEntity::getInstance();
		$cals = $VRCalendarEntity->getAllCalendar();
		$seresultpage = array(
			'result'=>__('Search Bar Result Page', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
			'same'=>__('Same Page', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
		);
		$se_useprice_filter = array(
			'yes'=>__('Yes', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
			'no'=>__('No', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
		);
		$se_show_image = array(
			'yes'=>__('Yes', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
			'no'=>__('No', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
		);
		$se_show_address = array(
			'yes'=>__('Yes', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
			'no'=>__('No', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
		);
		$action =(isset($_GET['action'])) ? $_GET['action'] : '';
		switch($action){
			case 'add':
				if(isset($_POST['search_bar_save'])){
				$searchbardata = $_POST;
				if(!isset($searchbardata['searchbar_id'])){
					$searchbardata['author'] = get_current_user_id();					
					$searchbardata['created_on'] = date('Y-m-d');	
				}
				$VRCalendarEntity->saveSearchbar($searchbardata);
				$redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-add-search-bar&vrc_msg=".__('Search Bar added successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN)."");
                echo '<script>window.location = "'.$redirect_url.'"</script>';
                exit;
			    }			
				require(VRCALENDAR_PLUGIN_DIR.'/Admin/Views/AddSearchbar.php'); 
				break;
            case 'edit':
				if(isset($_POST['search_bar_save'])){
				$searchbardata = $_POST;					
				$VRCalendarEntity->saveSearchbar($searchbardata);
				$redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-add-search-bar&vrc_msg=".__('Search Bar updated successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN)."");
                echo '<script>window.location = "'.$redirect_url.'"</script>';
                exit;
			    }	
				if(isset($_GET['searchbar_id'])) { 
				   $presearchbarid = $_GET['searchbar_id'];
				   $searchbardata = $VRCalendarEntity->getSearchbar($presearchbarid);  
				   require(VRCALENDAR_PLUGIN_DIR.'/Admin/Views/UpdateSearchbar.php');
			    }
				break;
            case 'del':
				if(isset($_GET['searchbar_id'])) { 
				$searchbar_id = $_GET['searchbar_id'];
				$VRCalendarEntity->deleteSearchbar($searchbar_id);
				$redirect_url = admin_url("admin.php?page=".VRCALENDAR_PLUGIN_SLUG."-add-search-bar&vrc_msg=".__('Search Bar deleted successfully', VRCALENDAR_PLUGIN_TEXT_DOMAIN)."");
                echo '<script>window.location = "'.$redirect_url.'"</script>';
                exit;
			    }
				break;
			default:			
			require(VRCALENDAR_PLUGIN_DIR.'/Admin/Views/ListSearchbar.php');
						
		}
	}// .................added searchbars..............................end...........................
    function dashboard() {
        $view = 'Dashboard';
        if(isset($_GET['view']))
            $view = ucfirst($_GET['view']);

        require(VRCALENDAR_PLUGIN_DIR.'/Admin/Views/'.$view.'.php');
    }

    /**
     * Register and enqueue admin-facing style sheet.
     *
     * @since    1.0.0
     */
    public function enqueueStyles()
    {
        wp_enqueue_style( 'wp-color-picker' );
       // wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

		wp_enqueue_style( VRCALENDAR_PLUGIN_SLUG . '-jquery-ui-core', VRCALENDAR_PLUGIN_URL.'assets/css/jquery-ui-core.min.css', array(), VRCalendar::VERSION );
        wp_enqueue_style( VRCALENDAR_PLUGIN_SLUG . '-plugin-styles', VRCALENDAR_PLUGIN_URL.'assets/css/admin.css', array(), VRCalendar::VERSION );
    }

    /**
     * Register and enqueues admin-facing JavaScript files.
     *
     * @since    1.0.0
     */
    public function enqueueScripts()
    {
        wp_enqueue_media();
		wp_enqueue_script( 'wp-color-picker');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script( VRCALENDAR_PLUGIN_SLUG . '-plugin-script', VRCALENDAR_PLUGIN_URL.'/assets/js/admin.js', array( 'jquery' ), VRCalendar::VERSION );
    }

}