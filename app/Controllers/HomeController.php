<?php
namespace App\Controllers;

class HomeController
{
	public function home(): void
	{
		echo '<h1>domovska stranka</h1>';
	}

	public function jsonResponse(int $code, string $msg = null, array $body = null): void
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Credentials: true");
        header("Content-Type: application/json; charset=utf-8");
        header("Access-Control-Allow-Methods: GET,POST,OPTIONS");
        header("Access-Control-Max-Age: 3600");
		header("Access-Control-Allow-Headers: Content-Type,Access-Control-Allow-Headers,Authorization,X-Requested-With");

		http_response_code($code);

		if (is_null($body))
		{
			echo json_encode(["message" => $msg]);
		} else {
			echo json_encode($body);
		}

    }
}