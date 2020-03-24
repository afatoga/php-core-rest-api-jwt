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

    public function getOrderPDF(int $orderId, int $ownerId): void
    {
        $orderController = new OrderController;
        $totalPrice = $orderController->getOrderTotalPrice($orderId, $ownerId);
        $itemList = $orderController->getOrderItems($orderId);

        if (empty($itemList)) return;

        $mpdf = new \Mpdf\Mpdf();
        $currentDate = date('d.m.Y');
        $currentTime = date('H:i:s');

        //http://af-orderlist.test/print?doc=6e68544sse107b9265078dfc75d&orderId=1&ownerId=2
        $html = '
                <div id="wrapper">
                    <div class="section">
                        <p class="text-center m-0">VITAREX s.r.o.</p>
                        <p class="text-center m-0">Lipanská 781/10 130 00 Praha 3</p>
                        <p class="text-center m-0">Tel. 608 000 419, www.vitarex.cz</p>
                        <p class="text-center m-0">IČO: 26177901</p>
                    </div>
                    <div class="section">
                        <table class="topLevelTable">
                            <tr>
                                <td>
                                    <p class="text-left">Provozovna: 1</p>
                                    <p class="text-left">Datum:&nbsp;'.$currentDate.'</p>
                                    <p class="text-left">Čas:&nbsp;'.$currentTime.'</p>
                                </td>
                                <td class="text-right">
                                <p>Poklada: 1</p>
                                <p>Č. účtenky:&nbsp;888555444</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="section m-0">
                        <table class="topLevelTable">';

        foreach ($itemList as $item) {
            $item['Price'] = str_replace('.', ',', $item['Price']);

            $html .= '
                    <tr>
                        <td class="text-left">' . $item['Title'] . '</td>
                        <td class="text-right">' . $item['Price'] . ' Kč</td>
                    </tr>';
        }

        $html .=    '
                        </table>
                    </div>
                    <div class="section m-0">
                        <p class="text-center m-0">Celková částka: <strong>' . $totalPrice . ',- Kč</strong></p>
                        <p class="text-center m-0">Nejsme plátci DPH</p>
                    </div>
                    <div class="section m-0">
                        <p class="text-left mb-0">Režim tržby: běžný</p>
                        <p class="text-center m-0">FIK</p>
                        <p class="text-center m-0 colorGray">xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx-xx</p>
                        <p class="text-center m-0">BKP</p>
                        <p class="text-center m-0 colorGray">xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx</p>
                    </div>
                <div>
        ';
        //$mpdf->shrink_tables_to_fit = 1;

        $stylesheet = file_get_contents('./App/Src/pdf-styles.css');

        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output();
    }
}
