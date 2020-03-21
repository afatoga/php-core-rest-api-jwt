<?php

namespace App\Controllers;

use \App\DatabaseService;
use \App\Controllers\OrderController;

class PrintController
{
    private $db;

    function __construct()
    {
        $databaseService = new DatabaseService();
        $this->db = $databaseService->getConnection();
    }

    public function getOrderPDF(int $orderId, int $ownerId)
    {
        $orderController = new OrderController;
        $totalPrice = $orderController->getOrderTotalPrice($orderId, $ownerId);

        $mpdf = new \Mpdf\Mpdf();

        //http://af-orderlist.test/print?doc=6e68544sse107b9265078dfc75d&orderId=1&ownerId=2
        $html = '
                <div id="wrapper">
                    <div class="section">
                        <p class="text-center m-0">VITAREX s.r.o.</p>
                        <p class="text-center m-0">Lipanska 781/10 130 00 Praha 3</p>
                    </div>
                    <div class="section">
                        <table class="topLevelTable">
                            <tr>
                                <td>
                                    <p class="text-left">Provozovna: 1</p>
                                    <p class="text-left">Datum:&nbsp;28.08.8888</p>
                                    <p class="text-left">Čas:&nbsp;00:00:00</p>
                                </td>
                                <td class="text-right">
                                <p>Poklada: 1</p>
                                <p>Číslo&nbsp;účtenky:&nbsp;888555444</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="section m-0">
                        <table class="topLevelTable">
                            <tr>
                                <td class="text-left">Položka 1</td>
                                <td class="text-right">1500,00 Kč</td>
                            </tr>
                            <tr>
                                <td class="text-left">Položka 2</td>
                                <td class="text-right">3878,00 Kč</td>
                            </tr>
                        </table>
                    </div>
                    <div class="section m-0">
                        <p class="text-center m-0">Celková částka</p>
                        <p class="text-center font-bigger">'.$totalPrice.'</p>
                        <p class="text-center m-0">Nejsme plátci DPH</p>
                    </div>
                <div>
        ';
        $mpdf->shrink_tables_to_fit = 1;

        $stylesheet = file_get_contents('./App/Src/pdf-styles.css');

        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output();
    }
}
