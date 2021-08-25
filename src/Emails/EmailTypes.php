<?php
namespace App\Emails;

class EmailTypes
{

	public static function getTypes() {
		return [
			'passwordrequest' => new PasswordRequestEmailParameters()
		];
	}
}

