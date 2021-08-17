<?php
namespace App\Model;


/**
 * @OA\Schema(
 *   schema="UserUpdate",
 *   required={"uuid", "lastname", "firstname", "birthday", "sex"}
 * )
 */
class UserUpdate extends UserCreate
{

	
}

