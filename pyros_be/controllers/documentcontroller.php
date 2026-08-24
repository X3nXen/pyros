<?php

use PhpOffice\PhpWord\TemplateProcessor;
class DocumentController
{
    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->handleGet();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'A kért HTTP metódus nem támogatott']);
        }
    }

    private function handleGet()
    {
        try {
            // 1. Sablon betöltése
            $templateProcessor = new TemplateProcessor(__DIR__ . '/test_temp.docx');

            // 2. Változó cseréje TESZT stringre
            $templateProcessor->setValue('GAZDÁLKODÓ SZERVEZET MEGNEVEZÉSE', 'TESZT');

            // 3. Ideiglenes fájl mentése
            $tempFileName = 'dokumentacio_' . time() . '.docx';
            $tempPath = sys_get_temp_dir() . '/' . $tempFileName;
            $templateProcessor->saveAs($tempPath);

            // 4. HTTP Fejlécek beállítása a letöltéshez
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="generalt_dokumentacio.docx"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($tempPath));

            // 5. Fájl kiküldése a böngészőnek és az ideiglenes fájl törlése
            readfile($tempPath);
            unlink($tempPath);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo "Hiba történt a dokumentum generálása során: " . $e->getMessage();
        }
    }
}