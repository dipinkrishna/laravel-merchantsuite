<?php

namespace DK\MerchantSuite;

use Illuminate\Support\ServiceProvider;

class MerchantSuiteProvider extends ServiceProvider
{

	public function get_credentials()
	{
		$config = $this->app['config']->get('services.merchantsuite', []);
		$mode = isset($config['mode']) && $config['mode'] == 'test' ? Mode::UAT : Mode::Live;

		URLDirectory::setBaseURL("reserved", "https://www.merchantsuite.com/api/v2");
		$credentials = new Credentials($config['username'], $config['password'], $config['membershipID'], $mode);

		return $credentials;
	}

}

?>