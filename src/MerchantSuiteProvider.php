<?php
/**
 * Created by Dipin Krishna.
 * Date: 8/27/18
 * Time: 12:30 AM
 */

namespace DK\MerchantSuite;

use Illuminate\Support\ServiceProvider;

class MerchantSuiteProvider extends ServiceProvider
{

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		$this->registerMerchantSuite();
	}

	/**
	 * {@inheritDoc}
	 */
	public function provides() {
		return [
			'merchantsuite'
		];
	}

	/**
	 * Register the MerchantSuite API class.
	 *
	 * @return void
	 */
	protected function registerMerchantSuite() {
		$this->app->singleton('merchantsuite', function ($app) {
			$config = $app['config']->get('services.merchantsuite');
			$username = isset($config['username']) ? $config['username'] : null;
			$password = isset($config['password']) ? $config['password'] : null;
			$membershipID = isset($config['membershipID']) ? $config['membershipID'] : null;
			$mode = isset($config['mode']) ? $config['mode'] : null;
			return new MerchantSuite($username, $password, $membershipID, $mode);
		});
		$this->app->alias('merchantsuite', 'DK\MerchantSuite\MerchantSuite');
	}

}

?>