<?php
class ClickupController
{
    private $team_id = '9014989482';
    private $api_token = 'pk_88439708_D1GMWNYJX8SD6U7657AOCRTCWGLSYCV4';
    private $list_id = '901417228301';
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->handleGet();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'A kért HTTP metódus nem támogatott']);
        }
    }
    private function handleGet()
    {
        $cu_user_id = $_SESSION['cu_id'] ?? null;
        if (!$cu_user_id)
            return [];

        try {
            $url = "https://api.clickup.com/api/v2/team/{$this->team_id}/task?assignees[]={$cu_user_id}&list_ids[]={$this->list_id}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: {$this->api_token}",
                "Content-Type: application/json"
            ]);
            $response = curl_exec($ch);
            $data = json_decode($response, true);
            $tasks = [];
            if (isset($data['tasks'])) {
                foreach ($data['tasks'] as $task) {

                    $tasks[] = [
                        'id' => $task['id'],
                        'name' => $task['name']
                    ];
                }
            }
            http_response_code(200);
            echo json_encode($tasks);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Szerver hiba: ' . $e->getMessage()]);
        }
    }
}