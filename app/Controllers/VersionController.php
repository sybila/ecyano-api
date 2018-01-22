<?php

declare(strict_types=1);

namespace App\Controllers;

final class VersionController extends AbstractController
{
	public function actionRead()
	{
		$this->payload->version = '0.1';
	}
}
