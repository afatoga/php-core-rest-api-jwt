<?php
namespace App\Controllers;

class HomeController
{
	public function home(): void
	{
		echo 'domovska stranka';
	}

	public function jsonResponse(int $code, string $msg = null, array $body = null): void
	{
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        //header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
		header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

		http_response_code($code);

		if (is_null($body))
		{
			echo json_encode(["message" => $msg]);
		} else {
			echo json_encode($body);
		}

    }
}