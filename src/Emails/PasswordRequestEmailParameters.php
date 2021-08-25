<?php
namespace App\Emails;

use Twig\Environment;

class PasswordRequestEmailParameters implements EmailParameters
{

	public function getTitle($vars = [])
	{
		return 'Your password request';
	}

	public function getBodies(Environment $renderer)
	{
		return [
			new PasswordRequestHtmlEmailBody($renderer),
			new PasswordRequestTxtEmailBody($renderer)
		];
	}

	public function getFakeValues()
	{
		return [
			'name' => 'lastname firstname',
			'login' => 'my-login',
			'requestUuid' => 'gdf6g5dg54dg65d4g3df1fsfddptfs54hgfdqfgfdg654'
		];
	}

}

