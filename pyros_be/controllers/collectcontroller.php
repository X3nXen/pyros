<?php
require_once __DIR__ . '/../database.php';

class CollectController
{

    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->handleGet();
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'A kért HTTP metódus nem támogatott']);
                break;
        }
    }

    private function handleGet()
    {
        $project_id = $_GET['project_id'] ?? null;
        if ($project_id == null) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Nincs kiválasztott projekt!']);
            exit;
        }
        $db = null;
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $result = [
                'standing' => [],
                'complex' => [],
                'building' => [],
                'heating_system' => [],
                'hmv_system' => [],
                'hvac_system' => [],
                'lighting_system' => [],
                'technology' => [],
                'vehicle' => [],
                'product' => [],
                'variable' => []
            ];

            $stmt = $db->prepare("SELECT id, name FROM standings WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['standing'][] = ['id' => $dataRow['id'], 'name' => $dataRow['name']];
            }

            $stmt = $db->prepare("SELECT id, name FROM complex WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['complex'][] = ['id' => $dataRow['id'], 'name' => $dataRow['name']];
            }
            $stmt = $db->prepare("SELECT id, name FROM buildings WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['building'][] = ['id' => $dataRow['id'], 'name' => $dataRow['name']];
            }
            $stmt = $db->prepare("SELECT id, name FROM heating_systems WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['heating_systems'][] = ['id' => $dataRow['id'], 'name' => $dataRow['name']];
            }
            $stmt = $db->prepare("SELECT id, name FROM hmv_systems WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['hmv_systems'][] = ['id' => $dataRow['id'], 'name' => $dataRow['name']];
            }
            $stmt = $db->prepare("SELECT id, name FROM ventilation_systems WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['hvac_systems'][] = ['id' => $dataRow['id'], 'name' => $dataRow['name']];
            }
            $stmt = $db->prepare("SELECT id, name FROM lighting_systems WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['lighting_systems'][] = ['id' => $dataRow['id'], 'name' => $dataRow['name']];
            }
            $stmt = $db->prepare("SELECT id, name FROM technology WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['technology'][] = ['id' => $dataRow['id'], 'name' => $dataRow['name']];
            }
            $stmt = $db->prepare("SELECT id, name FROM vehicles WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['vehicle'][] = ['id' => $dataRow['id'], 'name' => $dataRow['name']];
            }
            $stmt = $db->prepare("SELECT id, product_name FROM product WHERE project_id=:projectId");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['product'][] = ['id' => $dataRow['id'], 'name' => $dataRow['product_name']];
            }
            $stmt = $db->prepare("SELECT id FROM variables WHERE project_id=:projectId LIMIT 1");
            $stmt->execute([
                ":projectId" => $project_id
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $dataRow) {
                $result['variable'][] = ['id' => $dataRow['id'], 'name' => 'Változók'];
            }

            $db->commit();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => $result]);

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Adatbázis hiba=> ' . $e->getMessage()
            ]);
            exit;
        }
    }
}