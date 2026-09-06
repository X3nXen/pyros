<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . "/../services/AuditService.php";

class SankeyController
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
        $projectId = $_GET['project_id'] ?? null;

        if (!$projectId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'A project_id megadása kötelező!'
            ]);
            return;
        }

        try {
            $db = Database::getConnection();

            $stmt = $db->prepare("SELECT * FROM standings WHERE project_id = :project_id");
            $stmt->execute(['project_id' => $projectId]);
            $standings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($standings)) {
                echo json_encode([]);
                return;
            }

            $carrierRows = AuditService::getEnergyCarrierSummaryRows($standings);

            $links = [];
            $buildingNode = 'Épület';
            $productNode = 'Tevékenység';
            $vehicleNode = 'Szállítás';

            $parseValue = function ($val) {
                if (is_numeric($val))
                    return (float) $val;
                if (empty($val))
                    return 0.0;
                $clean = preg_replace('/[^\d\,\.]/', '', str_replace(['&nbsp;', "\xC2\xA0", ' ', 'kWh'], '', $val));
                $clean = str_replace(',', '.', $clean);
                return (float) $clean;
            };

            foreach ($carrierRows as $row) {
                $source = trim(strip_tags($row['carrier_name'] ?? 'Energia'));

                $bVal = $parseValue($row['carrier_building'] ?? 0);
                $pVal = $parseValue($row['carrier_product'] ?? 0);
                $vVal = $parseValue($row['carrier_vehicle'] ?? 0);

                if ($bVal > 0) {
                    $links[] = ['from' => $source, 'to' => $buildingNode, 'flow' => $bVal];
                }
                if ($pVal > 0) {
                    $links[] = ['from' => $source, 'to' => $productNode, 'flow' => $pVal];
                }
                if ($vVal > 0) {
                    $links[] = ['from' => $source, 'to' => $vehicleNode, 'flow' => $vVal];
                }
            }

            http_response_code(200);
            echo json_encode($links);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Hiba történt az adatok feldolgozása során: ' . $e->getMessage()
            ]);
        }
    }
}