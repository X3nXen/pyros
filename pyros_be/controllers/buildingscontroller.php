<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../services/buildingcalculation.php';

class BuildingsController {

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

    private function handleGet() {
        try{

            $db = Database::getConnection();

            $sql = "SELECT id, name FROM buildings";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($data as &$item){
                $item['id'] = (string)$item['id'];
            }
            
            http_response_code(200);
            echo json_encode($data);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis lekérdezési hiba: ' . $e->getMessage()
            ]);
        }
    }

    private function handlePost() {
        $rawInput = null;
        $data = null;
        if (isset($_POST['data'])) {
            $data = json_decode($_POST['data'], true);
        } else {
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
        }

        $name = $data['name'] ?? null;
        $complex = $data['complex'] ?? null;
        $standingIds = $data['standings'] ?? [];
        $res = null;
        $db = null;

        if(!$complex || !$name){
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Nem elegendő adat.'
            ]);
        }

        try{
            $db = Database::getConnection();
            $db->beginTransaction();
            $sql = "SELECT postal FROM complex WHERE id=:complexId";
            $stmt = $db->prepare($sql);

            $stmt->execute([
                ':complexId' => $complex
            ]);

            $postal = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data['postal'] = $postal[0]['postal'];

            $res = calculateValues($data);
            $qf = $data['qf'] ?? round($res['q_f'], 2);
            $heatLoss = $data['heatLoss'] ?? round($res['total_loss_watt'] / 1000, 2);

            $sql = "INSERT INTO buildings(name, json_data, calculated_values, qf, heat_loss, image_id, complex) VALUES (:name, :json_data, :calculated_values, :qf, :heat_loss, :image_id, :complex)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':json_data' => json_encode($data),
                ':calculated_values' => json_encode($res),
                ':qf' => $qf,
                ':heat_loss' => $heatLoss,
                ':image_id' => null,
                ':complex' => $complex
            ]);

            $insertedId = $db->lastInsertId();

            foreach($standingIds as $standingId){
                $sql = "INSERT INTO standings_to_other(standing, reference, type) VALUES (:standingId, :buildingId, :buildingType)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                ':standingId' => (int) $standingId,
                ':buildingId' => (int) $insertedId,
                ':buildingType' => 'BUILDING' 
                ]);
            }

            if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['imageFile']['tmp_name'];
                $originalName = $_FILES['imageFile']['name'];
    
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                $sqlImage = "INSERT INTO image_info (file_name, reference_type, reference_id) 
                            VALUES (:fileName, 'BUILDING', :referenceId)";
                $stmtImage = $db->prepare($sqlImage);
                $stmtImage->execute([
                    ':fileName'    => $originalName,
                    ':referenceId' => (int) $insertedId
                ]);

                $imageId = $db->lastInsertId();
                $newFileName = 'img_' . $imageId . ($extension ? '.' . $extension : '');

                $uploadDir = __DIR__ . '/../images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $targetPath = $uploadDir . $newFileName;

                if (!move_uploaded_file($tmpName, $targetPath)) {
                    throw new Exception("Hiba a kép mentése során a lemezre.");
                }

                $sqlUpdateImg = "UPDATE image_info SET file_name = :fileName WHERE id = :id";
                $stmtUpdateImg = $db->prepare($sqlUpdateImg);
                $stmtUpdateImg->execute([
                    ':fileName' => $newFileName,
                    ':id'       => $imageId
                ]);

                $sqlUpdateBuilding = "UPDATE buildings SET image_id = :imageId WHERE id = :buildingId";
                $stmtUpdateBuilding = $db->prepare($sqlUpdateBuilding);
                $stmtUpdateBuilding->execute([
                    ':imageId'    => $imageId,
                    ':buildingId' => $insertedId
                ]);
            }

            $db->commit();
            http_response_code(201);
            echo json_encode([
                'status'  => 'success',
                'message' => 'Épület sikeresen elmentve!',
                'id' => $insertedId,
                'calculated' => $res
            ]);

        } catch(PDOException $e){
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis lekérdezési hiba: ' . $e->getMessage()
            ]);
        }
    }
}