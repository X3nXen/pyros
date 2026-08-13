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

            $sql = "SELECT id, product_name FROM products";
            $stmt = $db->query($sql);
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

        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Érvénytelen adatok!']);
            return;
        }

        $metric = $data['metric'] ?? null;

        if (!$metric) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Mértékegység (metric) megadása kötelező!']);
            return;
        }

        try {
            $productName = 'Termék';
            $productionJson = json_encode([], JSON_UNESCAPED_UNICODE);

            $fileKey = isset($_FILES['excel']) ? 'excel' : (isset($_FILES['file']) ? 'file' : null);

            if ($fileKey && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                // Kizárólag az Excel fájl elérési útját adjuk át
                $parsed = parseProductExcel($_FILES[$fileKey]['tmp_name']);

                $productName = $parsed['product_name'];
                $productionJson = json_encode($parsed['json'], JSON_UNESCAPED_UNICODE);
            }

            $db = Database::getConnection();

            $sql = "INSERT INTO product (product_name, metric, json) VALUES (:product_name, :metric, :json)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':product_name' => $productName,
                ':metric' => $metric,
                ':json' => $productionJson
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