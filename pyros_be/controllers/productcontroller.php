<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../services/excelparser.php';

class ProductController
{

    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                if (isset($_GET['primary'])) {
                    $this->handlePrimaryRequest();
                } else {
                    $this->handleGet();
                }
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

    private function handlePrimaryRequest()
    {
        $projectId = $_GET['project_id'] ?? null;

        if (!$projectId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Hiányzó projekt azonosító!'
            ]);
            exit;
        }

        try {
            $db = Database::getConnection();

            $stmt = $db->prepare("SELECT COUNT(*) FROM product WHERE project_id = :projectId AND is_primary = 1");
            $stmt->execute([':projectId' => $projectId]);
            $count = (int) $stmt->fetchColumn();

            http_response_code(200);
            echo json_encode([
                'is_primary' => $count > 0
            ]);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis hiba: ' . $e->getMessage()
            ]);
            exit;
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

            $sql = "SELECT id, product_name FROM products WHERE project_id=:projectId";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':projectId' => $projectId
            ]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $products]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Adatbázis hiba: ' . $e->getMessage()]);
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

        $projectId = $data['project_id'] ?? null;
        if (!$projectId) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Nincs megadott projekt'
            ]);
        }

        if (!$data) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Érvénytelen adatok!']);
            return;
        }

        $metric = $data['metric'] ?? null;
        $isPrimary = $data['isPrimary'] ?? false;

        if (!$metric) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Mértékegység (metric) megadása kötelező!']);
            return;
        }

        try {
            $productionJson = json_encode([], JSON_UNESCAPED_UNICODE);

            $fileKey = isset($_FILES['excel']) ? 'excel' : (isset($_FILES['file']) ? 'file' : null);

            if ($fileKey && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                // Kizárólag az Excel fájl elérési útját adjuk át
                $parsed = parseProductExcel($_FILES[$fileKey]['tmp_name']);

                $productName = $data['name'] ?? $parsed['product_name'] ?? "Termék";
                $productionJson = json_encode($parsed['json'], JSON_UNESCAPED_UNICODE);
            }

            $db = Database::getConnection();

            $sql = "INSERT INTO product (product_name, metric, json, project_id, is_primary) VALUES (:product_name, :metric, :json, :projectId, :isPrimary)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':product_name' => $productName,
                ':metric' => $metric,
                ':json' => $productionJson,
                ':projectId' => $projectId,
                ':isPrimary' => (int) $isPrimary
            ]);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Termék sikeresen elmentve!',
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