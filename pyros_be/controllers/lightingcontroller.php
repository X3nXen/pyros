<?php

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../services/lightingcalculation.php';
require_once __DIR__ . '/../services/uuidgenerator.php';

class LightingController
{

    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->handleGet();
                break;

            case 'POST':
                $this->handlePost();
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'A kért HTTP metódus nem támogatott']);
                break;
        }
    }

    private function handleGet()
    {
        try {
            if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Nincs megadva projekt!']);
            }
            $projectId = $_GET['project_id'];
            $db = Database::getConnection();
            $sql = "SELECT * FROM lighting_systems WHERE project_id=:projectId";
            $stmt = $db->prepare($sql);

            $stmt->execute([
                ':projectId' => $projectId
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($data);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis lekérdezési hiba: ' . $e->getMessage()
            ]);
        }
    }

    private function handlePost()
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        $db = null;
        $link = guidv4();
        $projectId = $data['project_id'] ?? null;
        if (!$projectId) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Nincs megadott projekt'
            ]);
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            foreach ($data as $system) {
                $zoneName = $system['zone'] ?? null;
                $size = $system['size'] ?? null;
                $solution = $system['solution'] ?? null;
                $dim = $system['dim'] ?? null;
                $zoneUsage = $system['zoneUsage'] ?? null;
                $regulation = $system['regulation'] ?? null;
                $naturalLight = $system['naturalLight'] ?? null;
                $emergency = $system['emergency'] ?? null;
                $standBy = $system['standBy'] ?? null;
                if (!$zoneName || !$size || !$solution || !$dim || !$zoneUsage || !$regulation || !$naturalLight || $emergency === null || $standBy === null) {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Hiányzó kötelező mezők!']);
                }

                $calculated = calculateValues($system);
                $sql = "INSERT INTO lighting_systems (link, 
            name, 
            size, 
            solution, 
            dim, 
            zone_usage, 
            regulation, 
            natural_light, 
            emergency, 
            standby, 
            specific_sum, 
            yearly_sum,
            project_id) VALUES (:link, :name, :size, :solution, :dim, :usage, :regulation, :natural, :emergency, :standby, :specific, :sum, :projectId)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':link' => $link,
                    ':name' => $zoneName,
                    ':size' => $size,
                    ':solution' => $solution,
                    ':dim' => $dim,
                    ':usage' => $zoneUsage,
                    ':regulation' => $regulation,
                    ':natural' => $naturalLight,
                    ':emergency' => (int) $emergency,
                    ':standby' => (int) $standBy,
                    ':specific' => $calculated['specific'],
                    ':sum' => $calculated['sum'],
                    ':projectId' => $projectId
                ]);
            }
            $db->commit();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Világítási rendszerek sikeresen elmentve']);
        } catch (Exception $error) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis hiba: ' . $error->getMessage()
            ]);
        }
    }
}