<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../services/ventilationcalculation.php';

class VentilationController
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
            $sql = "SELECT id, name FROM ventilation_systems WHERE project_id=:projectId";
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
        $rawInput = null;
        $data = null;
        if (isset($_POST['data'])) {
            $data = json_decode($_POST['data'], true);
        } else {
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
        }

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Nem elegendő vagy érvénytelen adat.'
            ]);
            return;
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

            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Segédfüggvény a képek kétlépcsős mentéséhez (image_info -> move -> update img_<id>)
            $processImage = function ($fileKey, $referenceType, $referenceId) use ($db, $uploadDir) {
                if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
                    return null;
                }

                $tmpName = $_FILES[$fileKey]['tmp_name'];
                $originalName = $_FILES[$fileKey]['name'];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                $sqlImage = "INSERT INTO image_info (file_name, reference_type, reference_id) 
                                VALUES (:fileName, :referenceType, :referenceId)";
                $stmtImage = $db->prepare($sqlImage);
                $stmtImage->execute([
                    ':fileName' => $originalName,
                    ':referenceType' => $referenceType,
                    ':referenceId' => (int) $referenceId
                ]);

                $imageId = $db->lastInsertId();
                $newFileName = 'img_' . $imageId . ($extension ? '.' . $extension : '');
                $targetPath = $uploadDir . $newFileName;

                if (!move_uploaded_file($tmpName, $targetPath)) {
                    throw new Exception("Hiba a kép mentése során a lemezre: " . $originalName);
                }

                $sqlUpdateImg = "UPDATE image_info SET file_name = :fileName WHERE id = :id";
                $stmtUpdateImg = $db->prepare($sqlUpdateImg);
                $stmtUpdateImg->execute([
                    ':fileName' => $newFileName,
                    ':id' => $imageId
                ]);

                return [
                    'id' => $imageId,
                    'file_name' => $newFileName
                ];
            };

            $calculated = calculateValues($data);

            $sql = 'INSERT INTO ventilation_systems(name, json, sfp, category, project_id) VALUES (:name, :json, :sfp, :category, :projectId)';
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':json' => json_encode($data),
                ':sfp' => $calculated['specificVal'],
                ':category' => $calculated['spfCat'],
                ':projectId' => $projectId
            ]);

            $insertedVentilation = $db->lastInsertId();
            $image_ids = [];

            for ($i = 0; $i < 3; $i++) {
                $fileKey = "ventilation_{$i}_imageFile";
                $imgResult = $processImage($fileKey, 'VENTILATION', $insertedVentilation);
                if ($imgResult) {
                    array_push($image_ids, $imgResult['id']);
                }
            }

            $sql = 'UPDATE ventilation_systems SET first_image = :image_1_id, second_image = :image_2_id, third_image = :image_3_id WHERE id = :ventilationid';
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':image_1_id' => $image_ids[0],
                ':image_2_id' => $image_ids[1],
                ':image_3_id' => $image_ids[2],
                ':ventilationid' => $insertedVentilation
            ]);

            $db->commit();

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Légkezelő rendszer sikeresen elmentve!',
            ]);

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis lekérdezési hiba: ' . $e->getMessage()
            ]);
        }
    }
}