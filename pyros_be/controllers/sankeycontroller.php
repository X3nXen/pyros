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

            $rawConsumptionData = AuditService::calculateTotalConsumptionList($standings);
            // Megjegyzés: A calculateTotalConsumptionList már meghívja az adjustConsumptionList-et is a kódod alapján!

            $links = [];
            $buildingNode = 'Épület';
            $productNode = 'Tevékenység';
            $vehicleNode = 'Szállítás';

            foreach ($rawConsumptionData as $sourceKey => $data) {
                $sourceName = AuditService::EnergySources[$sourceKey] ?? ucfirst(strtolower($sourceKey));

                $bVal = (float) ($data['subs']['BUILDING']);
                $pVal = (float) ($data['subs']['SERVICE']);
                $vVal = (float) ($data['subs']['CARRY']);

                if ($bVal > 0) {
                    $links[] = ['from' => $sourceName, 'to' => $buildingNode, 'flow' => $bVal];
                }
                if ($pVal > 0) {
                    $links[] = ['from' => $sourceName, 'to' => $productNode, 'flow' => $pVal];
                }
                if ($vVal > 0) {
                    $links[] = ['from' => $sourceName, 'to' => $vehicleNode, 'flow' => $vVal];
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