<?php

require_once __DIR__ . "/../database.php";
require_once __DIR__ . "/../services/hmvcalculation.php";

class HMVController
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
            $projectId = $_GET['project_id'];
            $stmt = $db->prepare("SELECT id, name FROM hmv_systems WHERE id=:projectId");
            $stmt->execute([
                ":projectId" => $projectId
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode([
                "success:" => true,
                "message" => $data
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis hiba: ' . $e->getMessage()
            ]);
        }
    }

    private function handlePost()
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        $name = $data['name'] ?? null;
        $complex_id = $data['complex'] ?? null;
        $building_id = $data['building'] ?? null;
        $standing_id = $data['standing'] ?? null;
        $heater_id = $data['heating'] ?? null;
        $type = $data['type'] ?? null;
        $amount = $data['amount'] ?? null;
        $regulation = $data['regulation'] ?? null;
        $circulation = $data['circulation'] ?? false;
        $containment = $data['containment'] ?? false;
        $projectId = $data['projectId'] ?? null;

        if ($name == null || $complex_id == null || $building_id == null || $standing_id == null || $heater_id == null || $type == null || $amount == null || $regulation == null || $projectId == null) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Hiányzó adat'
            ]);
        }

        $db = null;
        $qf = 0;

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT measurement_type FROM standings WHERE id=:standingId");
            $stmt->execute([
                ":standingId" => $standing_id
            ]);
            $standingType = $stmt->fetchColumn();
            $stmt = $db->prepare("SELECT json_data FROM buildings WHERE id=:buildingId");
            $stmt->execute([
                ":buildingId" => $building_id
            ]);
            $jsonData = $stmt->fetchColumn();
            $buildingData = json_decode($jsonData, true);

            $qf = calculateHMVQf($buildingData['usage'], $containment, $circulation, $buildingData['size'], $type);

            if ($standingType == "VIRTUAL") {
                $consumption = json_encode(["total" => $qf]);
                $stmt = $db->prepare("UPDATE standings SET consumption=:consumption_json WHERE id=:standing_id");
                $stmt->execute([
                    ":consumption_json" => $consumption,
                    ":standing_id" => $standing_id
                ]);
            }

            $stmt = $db->prepare("INSERT INTO hmv_systems(
            name, complex_id, building_id, standing_id, type, amount, heater_id, regulation, circulation, containment, qf, project_id) 
            VALUES (:name, :complexId, :buildingId, :standingId, :type, :amount, :heaterId, :regulation, :circulation, :containment, :qf, :projectId)");
            $stmt->execute([
                ":name" => $name,
                ":complexId" => $complex_id,
                ":buildingId" => $building_id,
                ":standingId" => $standing_id,
                ":type" => $type,
                ":amount" => $amount,
                ":heaterId" => $heater_id,
                ":regulation" => $regulation,
                ":circulation" => (int) $circulation,
                ":containment" => (int) $containment,
                ":qf" => $qf,
                ":projectId" => $projectId
            ]);

            $db->commit();

            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'HMV rendszer sikeresen elmentve!',
            ]);


        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis hiba: ' . $e->getMessage()
            ]);
        }
    }
}