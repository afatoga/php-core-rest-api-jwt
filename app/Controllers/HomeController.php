<?php
namespace App\Controllers;

use Ifsnop\Mysqldump as IMysqldump;

class HomeController
{
	public function home(): void
	{
		echo '<!DOCTYPE html>

		<html lang="cs">
		<head>
		  <meta charset="utf-8">
		
		  <title>The HTML5 Herald</title>
		  <meta name="description" content="The HTML5 Herald">
		  <meta name="author" content="dubsea-cz">
		
		</head>
		
		<body>
		  <h1>Domovská stránka</h1>
		  <p>Stránka pro sdílení hudebního zážitku.</p>
		</body>
		</html>';
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
	
	public function dumpDb(): void
	{

		try {
			$dump = new IMysqldump\Mysqldump('mysql:host=localhost;dbname=aforderlist', 'root', '');
			$dump->start('dump.sql');

			$file = 'dump.sql';

			if (file_exists($file)) {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.basename($file).'"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));
				readfile($file);
				//unlink($file);
			}

		} catch (\Exception $e) {
			echo 'mysqldump-php error: ' . $e->getMessage();
		}

	}
}


