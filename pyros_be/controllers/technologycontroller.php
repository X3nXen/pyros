<?php
require_once __DIR__ . "/../database.php";
require_once __DIR__ . "/../services/uuidgenerator.php";
class TechnologyController
{
    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->handleGet();
                break;

            case 'POST':
                $this->distinguishPost();
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'A kért HTTP metódus nem támogatott']);
                break;
        }
    }

    private function distinguishPost()
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        if (!isset($data['method'])) {
            $this->handlePost($data);
        } else {
            if ($data['method'] == "EDIT") {
                $this->handlePut($data);
            } else {
                $this->handlePost($data);
            }
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
            $sql = "SELECT id, name, technology_type FROM technology WHERE project_id=:projectId";
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

    private function handlePut($data)
    {
        if (!$data) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Hiányzó kötelező mezők!']);
        }
        $id = $data['id'] ?? null;
        if (!$id) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Hiányzó adatok!'
            ]);
        }

        try {
            $db = Database::getConnection();

            if (!empty($data['machines'])) {
                foreach ($data['machines'] as &$machine) {
                    $machineId = guidv4();
                    $machine['id'] = $machineId;
                    $valuePlaceholders[] = '(?, ?, ?)';
                    $params[] = $machine['standing'];
                    $params[] = $machineId;
                    $params[] = 'TECHNOLOGY';
                }
            }
            $sql = "UPDATE technology SET name=:name, json=:json, technology_type=:technology_type, complex=:complexId WHERE id=:id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':json' => json_encode($data),
                ':technology_type' => $data['technologyType'],
                ':complexId' => $data['complex'],
                ':id' => $id
            ]);

            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Sikeres mentés!']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis elérési hiba: ' . $e->getMessage()
            ]);
        }
    }

    private function handlePost($data)
    {
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

            $valuePlaceholders = [];
            $params = [];
            if (!empty($data['machines'])) {
                foreach ($data['machines'] as &$machine) {
                    $machineId = guidv4();
                    $machine['id'] = $machineId;
                    $valuePlaceholders[] = '(?, ?, ?)';
                    $params[] = $machine['standing'];
                    $params[] = $machineId;
                    $params[] = 'TECHNOLOGY';
                }
            }
            $sql = "INSERT INTO technology (name, json, technology_type, project_id, complex) VALUES (:name, :json, :technology_type, :projectId, :complexId)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':json' => json_encode($data),
                ':technology_type' => $data['technologyType'],
                ':projectId' => $projectId,
                ':complexId' => $data['complex']
            ]);

            if (!empty($data['machines'])) {
                $sql = 'INSERT INTO standings_to_other (standing, reference, type) VALUES ' . implode(', ', $valuePlaceholders);

                $stmt = $db->prepare($sql);
                $stmt->execute($params);
            }

            $db->commit();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Sikeres mentés!']);
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