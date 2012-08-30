<?php
class GoogleAuthenticator extends Plugin
{
	public function action_init( )
	{
		
	}
	
	public function action_plugin_activation( )
	{

	}
	
	public function action_plugin_deactivation( )
	{
		
	}
	
	/**
	 * Add Google Authenticator fields to the User form
	 */
	public function action_form_user( $form, $edit_user )
	{
		$ga = $form->append( 'wrapper', 'google_authenticator', 'Google Authenticator' );
		$ga->class = 'container settings';
		
		$ga->append( 'static', 'google_authenticator', '<h2>' . _t( 'Google Authenticator' ) . '</h2>' );
		
		$ga->append( 'checkbox', 'active', 'user:ga_active', _t( 'Enable' ), 'optionscontrol_checkbox' );
		$ga->active->class[] = 'important item clear';
		
		$ga->append( 'checkbox', 'relaxed_mode', 'user:ga_relaxed_mode', _t( 'Relaxed Mode' ), 'optionscontrol_checkbox' );
		$ga->relaxed_mode->class[] = 'important item clear';
		$ga->relaxed_mode->helptext = _t( 'Relaxed mode allows for more time drifting on your phone clock (±4 min) ');
		
		$ga->append( 'text', 'description', 'user:ga_description', _t( 'Description' ), 'optionscontrol_text' );
		$ga->description->class[] = 'important item clear';
		$ga->description->helptext = _t( "Description that you'll see in the Google Authenticator app on your phone." );
		$ga->description->value = isset( $edit_user->info->ga_description ) ? $edit_user->info->ga_description : 'Habari Blog' . Options::get( 'title' );

		// TODO: Add javascript to regenerate code and show/hide QR code.
		$ga->append( 'text', 'secret', 'user:ga_secret', _t( 'Secret' ), 'optionscontrol_text' );
		$ga->secret->class[] = 'important item clear';
		$ga->secret->value = isset( $edit_user->info->ga_secret ) ? $edit_user->info->ga_secret : self::create_secret();
		$ga->secret->helptext = '<input type="button" value="' . _t( 'Create new secret' ) . '"/> <input type="button" value="' . _t( 'Show/Hide QR code' ) . '" onclick="jQuery(\'#qr_code\').slideToggle(\'slow\');"/>';
		
		// Only append the QR code if the form has been saved and we're active.  This ensures we have the relevant info for the QR code. It also saves an unnecessary call to Google
		if ( $edit_user->info->ga_active ) {
			$chl = urlencode( "otpauth://totp/{$edit_user->info->ga_description}?secret={$edit_user->info->ga_secret}" );
			$qr_url = "https://chart.googleapis.com/chart?cht=qr&amp;chs=300x300&amp;chld=H|0&amp;chl={$chl}";
			$ga->append( 'static', 'qr_code', '<div class="formcontrol important item clear" id="qr_code" style="display: none"><span class="pct25">&nbsp;</span><span class="pct65"><img src="' . $qr_url . '"/><p>' . _t( 'Scan this with the Google Authenticator app.' ) . '</p></span></div>' );
		} else {
			$ga->append( 'static', 'qr_code', '<div class="formcontrol important item clear" id="qr_code" style="display: none"><span class="pct25">&nbsp;</span><span class="pct65"><p>' . _t( 'Please check "Enable" above and save this form to view the QR code.' ) . '</p></span></div>' );
		}

		$form->move_after( $ga, $form->user_info );
	}
	
	/*-----:[ HELPER FUNCTIONS ]:----- */
	/**
	 * Create a new random secret for the Google Authenticator app.
	 * 16 characters, randomly chosen from the allowed Base32 characters
	 * equals 10 bytes = 80 bits, as 256^10 = 32^16 = 2^80
	 * 
	 * TODO: Re-implement this in Javascript - there's no need for this to be in PHP
	 */ 
	private static function create_secret() {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // allowed characters in Base32
		$secret = '';
		for ( $i = 0; $i < 16; $i++ ) {
			$secret .= substr( $chars, rand( 0, strlen( $chars ) - 1 ), 1 );
		}
		return $secret;
	}
}
?>