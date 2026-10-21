<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../services/heatercalculation.php';

class HeatingController
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
        $rawInput = null;
        $data = null;
        if (isset($_POST['data'])) {
            $data = json_decode($_POST['data'], true);
        } else {
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
        }
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
            if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Nincs megadva projekt!']);
            }
            $projectId = $_GET['project_id'];
            $db = Database::getConnection();
            $sql = "SELECT purpose, heaters FROM heating_systems WHERE project_id=:projectId";
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
        $projectId = $data['project_id'] ?? null;

        if (!$data || !$projectId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Nem elegendő vagy érvénytelen adat.'
            ]);
            return;
        }

        $id = $data['id'];
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $purposeMap = [
                'Fűtő' => 'HEAT',
                'Hűtő' => 'COOL',
                'Hűtő-fűtő' => 'BOTH',
                'HEAT' => 'HEAT',
                'COOL' => 'COOL',
                'BOTH' => 'BOTH'
            ];
            $purpose = $purposeMap[$data['systemPurpose'] ?? ''] ?? 'HEAT';
            $regulation = $data['systemRegulation'] ?? 'NONE';
            $regulationDesc = $data['systemRegulationDesc'] ?? 'NONE';
            $complex = $data['complex'] ?? '';

            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

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

            $heaters = $data['heaters'] ?? [];
            $pumps = $data['pumps'] ?? [];
            $standingIds = [];
            $buildingIds = [];
            $standingIdsToCalc = [];
            $buildingDataToCalc = [];
            foreach ($heaters as $heater) {
                $standingIds[] = $heater['standing'];
                $buildingIds[] = $heater['building'];
            }
            if (!empty($standingIds)) {
                $standingIds = array_unique($standingIds);
                $placeholders = implode(',', array_fill(0, count($standingIds), '?'));

                $sql = "SELECT id FROM standings WHERE measurement_type = 'VIRTUAL' AND id IN ($placeholders)";

                $stmt = $db->prepare($sql);
                $stmt->execute(array_values($standingIds));
                $standingIdsToCalc = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            if (!empty($buildingIds)) {
                $buildingIds = array_unique($buildingIds);
                $placeholders = implode(',', array_fill(0, count($buildingIds), '?'));

                $sql = "SELECT id, qf, json_data FROM buildings WHERE id IN ($placeholders)";

                $stmt = $db->prepare($sql);
                $stmt->execute(array_values($buildingIds));
                $buildingDataToCalc = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            $standingDataToUpload = calculateVirtualMeasurements($heaters, $standingIdsToCalc, $buildingDataToCalc, $purpose, $pumps);
            foreach ($standingDataToUpload as $key => $value) {
                $stmt = $db->prepare("UPDATE standings SET consumption = :consumptionJson where id = :standingId");
                $stmt->execute([
                    ":standingId" => $key,
                    ":consumptionJson" => json_encode($value)
                ]);
            }
            foreach ($heaters as $index => &$heater) {
                if (empty($heater['id'])) {
                    $heater['id'] = mt_rand(1000000, 99999999);
                } else {
                    $heater['id'] = (int) $heater['id'];
                }

                $fileKey = "heaters_{$index}_imageFile";
                $imgResult = $processImage($fileKey, 'HEATER', $heater['id']);
                if ($imgResult) {
                    $heater['image_id'] = $imgResult['id'];
                    $heater['image_name'] = $imgResult['file_name'];
                }
                unset($heater['imageFile']);
            }
            unset($heater);

            foreach ($pumps as $index => &$pump) {
                if (empty($pump['id'])) {
                    $pump['id'] = mt_rand(100000000, 199999999);
                } else {
                    $pump['id'] = (int) $pump['id'];
                }

                $fileKey = "pumps_{$index}_imageFile";
                $imgResult = $processImage($fileKey, 'PUMP', $pump['id']);
                if ($imgResult) {
                    $pump['image_id'] = $imgResult['id'];
                    $pump['image_name'] = $imgResult['file_name'];
                }
                unset($pump['imageFile']);
            }
            unset($pump);

            $emitters = $data['emitters'] ?? [];
            foreach ($emitters as $index => &$emitter) {
                if (empty($emitter['id'])) {
                    $emitter['id'] = mt_rand(200000000, 299999999);
                } else {
                    $emitter['id'] = (int) $emitter['id'];
                }

                $fileKey = "emitters_{$index}_imageFile";
                $imgResult = $processImage($fileKey, 'EMITTER', $emitter['id']);
                if ($imgResult) {
                    $emitter['image_id'] = $imgResult['id'];
                    $emitter['image_name'] = $imgResult['file_name'];
                }
                unset($emitter['imageFile']);
            }
            unset($emitter);

            $sqlSystem = "UPDATE heating_systems SET name=:name, purpose=:purpose, regulation=:regulation, description=:description, heaters=:heaters, pumps=:pumps, emitters=:emitters, complex=:complexId WHERE id=:id";
            $stmtSystem = $db->prepare($sqlSystem);
            $stmtSystem->execute([
                ':name' => $data['name'] ?? '',
                ':purpose' => $purpose,
                ':regulation' => $regulation,
                ':description' => $regulationDesc,
                ':heaters' => json_encode($heaters, JSON_UNESCAPED_UNICODE),
                ':pumps' => json_encode($pumps, JSON_UNESCAPED_UNICODE),
                ':emitters' => json_encode($emitters, JSON_UNESCAPED_UNICODE),
                ':complexId' => $complex,
                ':id' => $id
            ]);

            $systemId = $db->lastInsertId();

            $db->commit();

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Fűtési rendszer sikeresen elmentve!',
                'id' => $systemId
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

    private function handlePost($data)
    {
        $projectId = $data['project_id'] ?? null;

        if (!$data || !$projectId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Nem elegendő vagy érvénytelen adat.'
            ]);
            return;
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $purposeMap = [
                'Fűtő' => 'HEAT',
                'Hűtő' => 'COOL',
                'Hűtő-fűtő' => 'BOTH',
                'HEAT' => 'HEAT',
                'COOL' => 'COOL',
                'BOTH' => 'BOTH'
            ];
            $purpose = $purposeMap[$data['systemPurpose'] ?? ''] ?? 'HEAT';
            $regulation = $data['systemRegulation'] ?? 'NONE';
            $regulationDesc = $data['systemRegulationDesc'] ?? 'NONE';
            $complex = $data['complex'] ?? '';

            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

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

            $heaters = $data['heaters'] ?? [];
            $pumps = $data['pumps'] ?? [];
            $standingIds = [];
            $buildingIds = [];
            $standingIdsToCalc = [];
            $buildingDataToCalc = [];
            foreach ($heaters as $heater) {
                $standingIds[] = $heater['standing'];
                $buildingIds[] = $heater['building'];
            }
            if (!empty($standingIds)) {
                $standingIds = array_unique($standingIds);
                $placeholders = implode(',', array_fill(0, count($standingIds), '?'));

                $sql = "SELECT id FROM standings WHERE measurement_type = 'VIRTUAL' AND id IN ($placeholders)";

                $stmt = $db->prepare($sql);
                $stmt->execute(array_values($standingIds));
                $standingIdsToCalc = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            if (!empty($buildingIds)) {
                $buildingIds = array_unique($buildingIds);
                $placeholders = implode(',', array_fill(0, count($buildingIds), '?'));

                $sql = "SELECT id, qf, json_data FROM buildings WHERE id IN ($placeholders)";

                $stmt = $db->prepare($sql);
                $stmt->execute(array_values($buildingIds));
                $buildingDataToCalc = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            $standingDataToUpload = calculateVirtualMeasurements($heaters, $standingIdsToCalc, $buildingDataToCalc, $purpose, $pumps);
            foreach ($standingDataToUpload as $key => $value) {
                $stmt = $db->prepare("UPDATE standings SET consumption = :consumptionJson where id = :standingId");
                $stmt->execute([
                    ":standingId" => $key,
                    ":consumptionJson" => json_encode($value)
                ]);
            }
            foreach ($heaters as $index => &$heater) {
                if (empty($heater['id'])) {
                    $heater['id'] = mt_rand(1000000, 99999999);
                } else {
                    $heater['id'] = (int) $heater['id'];
                }

                // Kép feldolgozása
                $fileKey = "heaters_{$index}_imageFile";
                $imgResult = $processImage($fileKey, 'HEATER', $heater['id']);
                if ($imgResult) {
                    $heater['image_id'] = $imgResult['id'];
                    $heater['image_name'] = $imgResult['file_name'];
                }
                unset($heater['imageFile']);
            }
            unset($heater);

            foreach ($pumps as $index => &$pump) {
                if (empty($pump['id'])) {
                    $pump['id'] = mt_rand(100000000, 199999999);
                } else {
                    $pump['id'] = (int) $pump['id'];
                }

                $fileKey = "pumps_{$index}_imageFile";
                $imgResult = $processImage($fileKey, 'PUMP', $pump['id']);
                if ($imgResult) {
                    $pump['image_id'] = $imgResult['id'];
                    $pump['image_name'] = $imgResult['file_name'];
                }
                unset($pump['imageFile']);
            }
            unset($pump);

            $emitters = $data['emitters'] ?? [];
            foreach ($emitters as $index => &$emitter) {
                if (empty($emitter['id'])) {
                    $emitter['id'] = mt_rand(200000000, 299999999);
                } else {
                    $emitter['id'] = (int) $emitter['id'];
                }

                $fileKey = "emitters_{$index}_imageFile";
                $imgResult = $processImage($fileKey, 'EMITTER', $emitter['id']);
                if ($imgResult) {
                    $emitter['image_id'] = $imgResult['id'];
                    $emitter['image_name'] = $imgResult['file_name'];
                }
                unset($emitter['imageFile']);
            }
            unset($emitter);

            $sqlSystem = "INSERT INTO heating_systems (name, purpose, regulation, description, heaters, pumps, emitters, project_id, complex) 
                          VALUES (:name, :purpose, :regulation, :description, :heaters, :pumps, :emitters, :projectId, :complexId)";
            $stmtSystem = $db->prepare($sqlSystem);
            $stmtSystem->execute([
                ':name' => $data['name'] ?? '',
                ':purpose' => $purpose,
                ':regulation' => $regulation,
                ':description' => $regulationDesc,
                ':heaters' => json_encode($heaters, JSON_UNESCAPED_UNICODE),
                ':pumps' => json_encode($pumps, JSON_UNESCAPED_UNICODE),
                ':emitters' => json_encode($emitters, JSON_UNESCAPED_UNICODE),
                ':projectId' => $projectId,
                ':complexId' => $complex
            ]);

            $systemId = $db->lastInsertId();

            $db->commit();

            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Fűtési rendszer sikeresen elmentve!',
                'id' => $systemId
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