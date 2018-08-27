<?php
/**
 * Created by Dipin Krishna.
 * Date: 8/27/18
 * Time: 12:36 AM
 */

namespace DK\MerchantSuite;

class MerchantSuite
{
	protected $username;
	protected $password;
	protected $membershipID;
	protected $mode;

	public function __construct($username, $password, $membershipID, $mode = 'live') {

		$this->username = $username;
		$this->password = $password;
		$this->membershipID = $membershipID;
		$this->mode = $mode;

	}

	public function getCredentials() {
		$mode = isset($this->mode) && $this->mode == 'test' ? Mode::UAT : Mode::Live;

		URLDirectory::setBaseURL("reserved", "https://www.merchantsuite.com/api/v2");
		$credentials = new Credentials($this->username, $this->password, $this->membershipID, $mode);

		return $credentials;
	}

}