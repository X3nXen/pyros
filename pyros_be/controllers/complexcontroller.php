<?php

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
        $data = [
            ["id" => "10", "name" => "Martonvásárhelyi teszt telephely"],
            ["id" => "11", "name" => "Budapesti teszt telephely"]
        ];
        http_response_code(200);
        echo json_encode($data);
    }

    private function handlePost() {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        $response = [
            'status' => 'success',
            'message' => 'POST kérés sikeresen feldolgozva!',
            'receivedData' => $data
        ];

        http_response_code(200);
        echo json_encode($response);
    }
}