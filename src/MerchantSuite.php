<?php
/**
 * Created by Dipin Krishna.
 * Date: 8/27/18
 * Time: 12:36 AM
 */

namespace DK\MerchantSuite;

use Illuminate\Support\Facades\Config;

class MerchantSuite
{
	private $username;
	private $password;
	private $membershipID;
	private $mode;

	public function __construct($mode = 'live') {

		$config = Config::get('services.merchantsuite');
		$this->username = isset($config['username']) ? $config['username'] : null;
		$this->password = isset($config['password']) ? $config['password'] : null;
		$this->membershipID = isset($config['membershipID']) ? $config['membershipID'] : null;
		if($mode) {
			$this->mode = isset($mode) && $mode == 'test' ? Mode::UAT : Mode::Live;
		} else {
			$this->mode = isset($config['mode']) && $config['mode'] == 'test' ? Mode::UAT : Mode::Live;
		}

	}

	public function getCredentials() {

		URLDirectory::setBaseURL("reserved", "https://www.merchantsuite.com/api/v2");
		$credentials = new Credentials($this->username, $this->password, $this->membershipID, $this->mode);

		return $credentials;
	}

}