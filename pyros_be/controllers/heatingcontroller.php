<?php
require_once __DIR__ . '/../database.php';

class HeatingController {

    public function index() {
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

    private function handleGet(){
        try{
            $db = Database::getConnection();
            $sql = "SELECT purpose, heaters FROM heating_systems";
            $stmt = $db->prepare($sql);
    
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($data);
        } catch(PDOException $e){
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis lekérdezési hiba: ' . $e->getMessage()
            ]);
        }
    }

    private function handlePost(){
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

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            // 1. ENUM értékek átfordítása az adatbázis formátumra
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

            // Képmappa előkészítése
            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Segédfüggvény a képek kétlépcsős mentéséhez (image_info -> move -> update img_<id>)
            $processImage = function($fileKey, $referenceType, $referenceId) use ($db, $uploadDir) {
                if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
                    return null;
                }

                $tmpName = $_FILES[$fileKey]['tmp_name'];
                $originalName = $_FILES[$fileKey]['name'];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                // 1. Beszúrás az eredeti névvel
                $sqlImage = "INSERT INTO image_info (file_name, reference_type, reference_id) 
                            VALUES (:fileName, :referenceType, :referenceId)";
                $stmtImage = $db->prepare($sqlImage);
                $stmtImage->execute([
                    ':fileName'      => $originalName,
                    ':referenceType' => $referenceType,
                    ':referenceId'   => (int)$referenceId
                ]);

                $imageId = $db->lastInsertId();
                $newFileName = 'img_' . $imageId . ($extension ? '.' . $extension : '');
                $targetPath = $uploadDir . $newFileName;

                // 2. Fájl mozgatása az új img_<imageId> névvel
                if (!move_uploaded_file($tmpName, $targetPath)) {
                    throw new Exception("Hiba a kép mentése során a lemezre: " . $originalName);
                }

                // 3. Rekord frissítése a végleges fájlnévre
                $sqlUpdateImg = "UPDATE image_info SET file_name = :fileName WHERE id = :id";
                $stmtUpdateImg = $db->prepare($sqlUpdateImg);
                $stmtUpdateImg->execute([
                    ':fileName' => $newFileName,
                    ':id'       => $imageId
                ]);

                return [
                    'id'        => $imageId,
                    'file_name' => $newFileName
                ];
            };

            // 2. Hőtermelők (Heaters) feldolgozása
            $heaters = $data['heaters'] ?? [];
            $heaterStandings = [];

            foreach ($heaters as $index => &$heater) {
                // Biztonságos INT azonosító generálása MySQL INT határokon belül (max 2 147 483 647)
                if (empty($heater['id'])) {
                    $heater['id'] = mt_rand(1000000, 99999999);
                } else {
                    $heater['id'] = (int)$heater['id'];
                }

                // Kép feldolgozása
                $fileKey = "heaters_{$index}_imageFile";
                $imgResult = $processImage($fileKey, 'HEATER', $heater['id']);
                if ($imgResult) {
                    $heater['image_id'] = $imgResult['id'];
                    $heater['image_name'] = $imgResult['file_name'];
                }
                unset($heater['imageFile']);

                // Mérőóra elmentése
                if (!empty($heater['standing'])) {
                    $heaterStandings[] = [
                        'standing'  => (int)$heater['standing'],
                        'reference' => (int)$heater['id']
                    ];
                }
            }
            unset($heater);

            // 3. Szivattyúk (Pumps) feldolgozása
            $pumps = $data['pumps'] ?? [];
            foreach ($pumps as $index => &$pump) {
                if (empty($pump['id'])) {
                    $pump['id'] = mt_rand(100000000, 199999999);
                } else {
                    $pump['id'] = (int)$pump['id'];
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

            // 4. Hőleadók (Emitters) feldolgozása
            $emitters = $data['emitters'] ?? [];
            foreach ($emitters as $index => &$emitter) {
                if (empty($emitter['id'])) {
                    $emitter['id'] = mt_rand(200000000, 299999999);
                } else {
                    $emitter['id'] = (int)$emitter['id'];
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

            // 5. Beszúrás a heating_systems táblába
            $sqlSystem = "INSERT INTO heating_systems (name, purpose, regulation, description, heaters, pumps, emitters) 
                          VALUES (:name, :purpose, :regulation, :description, :heaters, :pumps, :emitters)";
            $stmtSystem = $db->prepare($sqlSystem);
            $stmtSystem->execute([
                ':name'        => $data['name'] ?? '',
                ':purpose'     => $purpose,
                ':regulation'  => $regulation,
                ':description' => $regulationDesc,
                ':heaters'     => json_encode($heaters, JSON_UNESCAPED_UNICODE),
                ':pumps'       => json_encode($pumps, JSON_UNESCAPED_UNICODE),
                ':emitters'    => json_encode($emitters, JSON_UNESCAPED_UNICODE)
            ]);

            $systemId = $db->lastInsertId();

            // 6. Rendszer saját mérőórájának beszúrása (SYSTEM)
            if (!empty($data['standing'])) {
                $sqlStanding = "INSERT INTO standings_to_other (standing, reference, type) 
                                VALUES (:standing, :reference, 'SYSTEM')";
                $stmtStanding = $db->prepare($sqlStanding);
                $stmtStanding->execute([
                    ':standing'  => (int)$data['standing'],
                    ':reference' => (int)$systemId
                ]);
            }

            // 7. Hőtermelők mérőóráinak beszúrása (HEATER)
            if (!empty($heaterStandings)) {
                $sqlHeaterStanding = "INSERT INTO standings_to_other (standing, reference, type) 
                                      VALUES (:standing, :reference, 'HEATER')";
                $stmtHeaterStanding = $db->prepare($sqlHeaterStanding);
                foreach ($heaterStandings as $hs) {
                    $stmtHeaterStanding->execute([
                        ':standing'  => $hs['standing'],
                        ':reference' => $hs['reference']
                    ]);
                }
            }

            $db->commit();

            http_response_code(201);
            echo json_encode([
                'status'  => 'success',
                'message' => 'Fűtési rendszer sikeresen elmentve!',
                'id'      => $systemId
            ]);

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Adatbázis lekérdezési hiba: ' . $e->getMessage()
            ]);
        }
    }
}