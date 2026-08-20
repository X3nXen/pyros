<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../services/excelparser.php';

class StandingsController
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
            $db = Database::getConnection();

            if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Nincs megadva projekt!']);
            }
            $projectId = $_GET['project_id'];

            $type = isset($_GET['type']) ? strtoupper(trim($_GET['type'])) : null;

            if ($type === 'MAIN') {
                $sql = "SELECT id, name FROM standings WHERE measurement_type = 'MAIN' AND project_id=:projectId";
                $stmt = $db->prepare($sql);
                $stmt->execute(['projectId' => $projectId]);

            } elseif ($type === 'SUB' || $type === 'VIRTUAL') {
                $sql = "SELECT id, name FROM standings WHERE measurement_type != 'MAIN' AND project_id=:projectId";
                $stmt = $db->prepare($sql);
                $stmt->execute(['projectId' => $projectId]);

            } else {
                $sql = "SELECT id, name FROM standings WHERE project_id=:projectId";
                $stmt = $db->prepare($sql);
                $stmt->execute(['projectId' => $projectId]);
            }

            $standings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($standings as &$item) {
                $item['id'] = (string) $item['id'];
            }

            http_response_code(200);
            echo json_encode($standings);

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
        $data = null;
        if (isset($_POST['data'])) {
            $data = json_decode($_POST['data'], true);
        } else if (!empty($_POST)) {
            $data = $_POST;
        } else {
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
        }

        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Érvénytelen adatok!']);
            return;
        }

        $name = $data['name'] ?? null;
        $measurementType = $data['measurementType'] ?? null;
        $subTo = !empty($data['subTo']) ? (int) $data['subTo'] : null;
        $source = $data['source'] ?? null;
        $measurement = $data['measurement'] ?? null;

        $dateFrom = !empty($data['dateFrom']) ? date('Y-m-d', strtotime($data['dateFrom'])) : null;
        $dateTo = !empty($data['dateTo']) ? date('Y-m-d', strtotime($data['dateTo'])) : null;
        $projectId = $data['project_id'] ?? null;
        if (!$projectId) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Nincs megadott projekt'
            ]);
        }

        if (!$name || !$measurementType || !$source || !$measurement) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Hiányzó kötelező mezők!']);
            return;
        }

        try {
            $consumptionJson = null;
            $fileKey = isset($_FILES['excel']) ? 'excel' : (isset($_FILES['file']) ? 'file' : null);

            if ($fileKey && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                if (!$dateFrom || !$dateTo) {
                    http_response_code(422);
                    echo json_encode(['status' => 'error', 'message' => 'Excel feldolgozásához a kezdő és záró dátum megadása kötelező!']);
                    return;
                }

                $consumptionArray = parseStandingsExcel($_FILES[$fileKey]['tmp_name'], $dateFrom, $dateTo);
                $consumptionJson = json_encode($consumptionArray, JSON_UNESCAPED_UNICODE);
            } else {
                $consumptionJson = json_encode([], JSON_UNESCAPED_UNICODE);
            }

            $db = Database::getConnection();

            $sql = "INSERT INTO standings 
                    (name, measurement_type, sub_to, source, measurement, date_from, date_to, consumption, project_id) 
                VALUES 
                    (:name, :measurement_type, :sub_to, :source, :measurement, :date_from, :date_to, :consumption, :projectId)";

            $stmt = $db->prepare($sql);

            $stmt->execute([
                ':name' => $name,
                ':measurement_type' => $measurementType,
                ':sub_to' => $subTo,
                ':source' => $source,
                ':measurement' => $measurement,
                ':date_from' => $dateFrom,
                ':date_to' => $dateTo,
                ':consumption' => $consumptionJson,
                ':projectId' => $projectId
            ]);

            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Mérőóra sikeresen elmentve!',
                'id' => $db->lastInsertId()
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Hiba történt: ' . $e->getMessage()
            ]);
        }
    }
}