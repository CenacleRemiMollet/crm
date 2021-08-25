<?php
namespace App\Emails;

use Twig\Environment;


interface EmailParameters
{

	public function getTitle($vars = []);

	public function getBodies(Environment $renderer);

	public function getFakeValues();
}

