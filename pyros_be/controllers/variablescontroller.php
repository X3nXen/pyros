<?php
require_once __DIR__ . '/../database.php';
class VariablesController
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

            $sql = "INSERT INTO variables (project_id, json) 
                    VALUES (:projectId, :jsonData)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':projectId' => $data['project_id'],
                ':jsonData' => json_encode($data)
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
}