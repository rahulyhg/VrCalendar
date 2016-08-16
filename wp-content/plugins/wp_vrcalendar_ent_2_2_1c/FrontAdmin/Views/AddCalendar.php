<?php

if (edd_sample_check_license_new()){
$VRCalendarEntity = VRCalendarEntity::getInstance();
if(isset($_GET['cal_id'])) {
    /* Fetch Calendar Details */
    $cdata = $VRCalendarEntity->getCalendar($_GET['cal_id']);
}
else {
    $cdata = $VRCalendarEntity->getEmptyCalendar();
}
$layout_option_size = array(
    'small'=>__('Small', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'medium'=>__('Medium', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'large'=>__('Large', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
);
$enable_booking = array(
    'yes'=>__('Yes', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'no'=>__('No', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
);
$offer_weekly = array(
    'yes'=>__('Yes', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'no'=>__('No', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
);
$offer_monthly = array(
    'yes'=>__('Yes', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'no'=>__('No', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
);
$tax_type = array(
    'flat'=>'$',
    'percentage'=>'%'
);
$payment_method = array(
    /*'deposit'=>__('Deposit', VRCALENDAR_PLUGIN_TEXT_DOMAIN),*/
    'paypal'=>__('PayPal', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'stripe'=>__('Stripe', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'both'=>__('Both', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'none'=>__('None', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
);
$alert_double_booking = array(
    'yes'=>__('Yes', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'no'=>__('No', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
);
$requires_admin_approval = array(
    'yes'=>__('Yes', VRCALENDAR_PLUGIN_TEXT_DOMAIN),
    'no'=>__('No', VRCALENDAR_PLUGIN_TEXT_DOMAIN)
);
?>
<div class="wrap vrcal-content-wrapper edit_dashboard">
    <h2><?php _e('Add Calendar', VRCALENDAR_PLUGIN_TEXT_DOMAIN); ?></h2>
	<div class="cont-fuild vr-dashboard">
	<div class="left-panel-vr-plg">
		<?php include('sidebar.php'); ?>
	</div>
	<div class="right-panel-vr-plg">
		<div class="tabs-wrapper">
			<h2 class="nav-tab-wrapper">
				<a class='nav-tab nav-tab-active' href='#general-options'><?php _e('General', VRCALENDAR_PLUGIN_TEXT_DOMAIN); ?></a>
				<a class='nav-tab' href='#booking-options'><?php _e('Booking Options', VRCALENDAR_PLUGIN_TEXT_DOMAIN); ?></a>
				<a class='nav-tab' href='#color-options'><?php _e('Color Options', VRCALENDAR_PLUGIN_TEXT_DOMAIN); ?></a>
				<a class='nav-tab' href='#email-options'><?php _e('Email Template', VRCALENDAR_PLUGIN_TEXT_DOMAIN); ?></a>
			</h2>
			<div class="tabs-content-wrapper">
				<form method="post" action="" >
					<div id="general-options" class="tab-content tab-content-active">
						<?php require(VRCALENDAR_PLUGIN_DIR.'/FrontAdmin/Views/Part/AddCalendar/General.php'); ?>
					</div>
					<div id="booking-options" class="tab-content">
						<?php require(VRCALENDAR_PLUGIN_DIR.'/FrontAdmin/Views/Part/AddCalendar/Booking.php'); ?>
					</div>
					<div id="color-options" class="tab-content">
						<?php require(VRCALENDAR_PLUGIN_DIR.'/FrontAdmin/Views/Part/AddCalendar/Color.php'); ?>
					</div>
					<div id="email-options" class="tab-content">
							<?php require(VRCALENDAR_PLUGIN_DIR.'/FrontAdmin/Views/Part/AddCalendar/emaltemplate.php'); ?>
						</div>
					<div class="bottom_button">
						<input type="hidden" name="vrc_cmd" id="vrc_cmd" value="VRCalendarFrontAdmin:saveCalendar" />
						<input type="hidden" name="calendar_id" id="calendar_id" value="<?php echo $cdata->calendar_id; ?>" />
						<?php if(isset($_GET['subcase']) && $_GET['subcase']=='dup') {
						echo '<input type="hidden" name="calendar_subcase" value="'.__('dup', VRCALENDAR_PLUGIN_TEXT_DOMAIN).'" />';
						} ?>
						<input type="submit" value="<?php _e('Save', VRCALENDAR_PLUGIN_TEXT_DOMAIN); ?>" class="button button-primary">
					</div>
					
				</form>
			</div>
		</div>
	</div>

	</div>
	</div>
<?php } else {
    _e('Please check your license key', VRCALENDAR_PLUGIN_TEXT_DOMAIN);
    }
?>
