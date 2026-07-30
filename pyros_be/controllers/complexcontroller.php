<?php
require_once __DIR__ . '/../database.php';

class ComplexController {

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
        try {
            $db = Database::getConnection();

                $sql = "SELECT id, name FROM complex ORDER BY name ASC";
                $stmt = $db->prepare($sql);
                $stmt->execute();

            $standings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($standings as &$item) {
                $item['id'] = (string)$item['id'];
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

    private function handlePost() {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        $name            = $data['name'] ?? null;
        $address = $data['address'] ?? null;
        $postal           = $data['postal'] ?? null;
        $city          = $data['city'] ?? null;
        $parcelNumber     = $data['parcelNumber'] ?? null;
        
        $standingIds = $data['meterStandings'] ?? [];

        if (!$name || !$address || !$postal || !$city || !$parcelNumber || count($standingIds) === 0) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Hiányzó kötelező mezők!']);
            return;
        }

        $db = null;

        try {
            $db = Database::getConnection();

            $db->beginTransaction();

            $sql = "INSERT INTO complex 
                        (name, address, postal, city, parcelNumber) 
                    VALUES 
                        (:name, :address, :postal, :city, :parcelNumber)";

            $stmt = $db->prepare($sql);

            $stmt->execute([
                ':name'             => $name,
                ':address'          => $address,
                ':postal'           => $postal,
                ':city'             => $city,
                ':parcelNumber'     => $parcelNumber
            ]);

            $insertedId = $db->lastInsertId();

            foreach($standingIds as $standingId){
                $sql = "INSERT INTO standings_to_other(standing, reference, type) VALUES (:standingId, :complexId, :complexType)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                ':standingId' => (int) $standingId,
                ':complexId' => (int) $insertedId,
                ':complexType' => 'COMPLEX' 
                ]);
            }

            $db->commit();

            http_response_code(201);
            echo json_encode([
                'status'  => 'success',
                'message' => 'Telephely sikeresen elmentve!',
                'id' => $insertedId
            ]);

        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Adatbázis hiba: ' . $e->getMessage()
            ]);
        }
    }
}