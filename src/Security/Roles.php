<?php

namespace App\Security;

class Roles
{
	public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
	public const ROLE_ADMIN = 'ROLE_ADMIN';
	public const ROLE_CLUB_MANAGER = 'ROLE_CLUB_MANAGER';
	public const ROLE_TEACHER = 'ROLE_TEACHER';
	public const ROLE_STUDENT = 'ROLE_STUDENT';
	public const ROLE_USER = 'ROLE_USER';
	public const ROLE_ANONYMOUS = 'IS_AUTHENTICATED_ANONYMOUSLY';

	public const ROLES = array(
		self::ROLE_SUPER_ADMIN,
		self::ROLE_ADMIN,
		self::ROLE_CLUB_MANAGER,
		self::ROLE_TEACHER,
		self::ROLE_STUDENT,
		self::ROLE_USER,
		self::ROLE_ANONYMOUS
	);

}