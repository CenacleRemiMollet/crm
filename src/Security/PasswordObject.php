<?php
namespace App\Security;

use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

class PasswordObject
{

	// See : https://github.com/rollerworks/PasswordStrengthValidator

	// Strengths :
	// 1: Very Weak (any character)
	// 2: Weak (at least one lower and capital)
	// 3: Medium (at least one lower and capital and number)
	// 4: Strong (at least one lower and capital and number) (recommended for most usages)
	// 5: Very Strong (recommended for admin or finance related services)
	
	
	/**
	 * @RollerworksPassword\PasswordStrength(minLength=6, minStrength=1)
	 * @RollerworksPassword\PasswordRequirements(requireLetters=false, requireNumbers=false, requireCaseDiff=false)
	 */
	private $password;
	
	public function __construct(string $password)
	{
		$this->password = $password;
	}
	
	public function getPassword(): string
	{
		return $this->password;
	}
}

