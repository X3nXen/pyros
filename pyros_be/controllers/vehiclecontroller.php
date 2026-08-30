<?php
require_once __DIR__ . '/../database.php';

class VehicleController
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
            $sql = "SELECT id, name FROM vehicles WHERE project_id=:projectId";
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

        if (!$data) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Hiányzó kötelező mezők!']);
        }
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
            $sql = 'INSERT INTO vehicles(name, project_id, complex_id, standing_id, usage_metric, usage_value, usage_value2, fuel, hibrid, motor_size)
                    VALUES (:name, :projectId, :complexId, :standingId, :usageMetric, :usageValue, :usageValue2, :fuel, :hibrid, :motorSize)';
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':projectId' => $projectId,
                ':complexId' => $data['complex'],
                ':standingId' => $data['subStanding'],
                ':usageMetric' => $data['usageMetric'],
                ':usageValue' => $data['usageValue'],
                ':usageValue2' => $data['usageValue2'],
                ':fuel' => $data['fuel'],
                ':hibrid' => !empty($data['hibrid']) ? 1 : 0,
                ':motorSize' => $data['motorSize']
            ]);

            $insertedId = $db->lastInsertId();

            $sql = 'INSERT INTO standings_to_other (standing, reference, type)  VALUES(:standing, :reference, :type)';
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':standing' => $data['subStanding'],
                ':reference' => $insertedId,
                ':type' => 'VEHICLE'
            ]);

            $db->commit();

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Jármű sikeresen elmentve'
            ]);
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis elérési hiba: ' . $e->getMessage()
            ]);
        }
    }
}