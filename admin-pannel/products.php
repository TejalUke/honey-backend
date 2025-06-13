<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'db.php'; // Your DB connection

$action = $_GET['action'] ?? $_POST['action'] ?? 'read';

function saveBase64Image($base64, $prefix = 'img_') {
    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
        $data = substr($base64, strpos($base64, ',') + 1);
        $data = base64_decode($data);
        $ext = strtolower($type[1]);
        $fileName = $prefix . uniqid() . '.' . $ext;
        $filePath = 'uploads/' . $fileName;
        file_put_contents($filePath, $data);
        return $filePath;
    }
    return '';
}

switch ($action) {
    case 'read':
        $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
        break;

    case 'create':
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $amount = $_POST['amount'];
        $imageBase64 = $_POST['image'];
        $imagePath = saveBase64Image($imageBase64);

        $stmt = $conn->prepare("INSERT INTO products (name, description, image, price, amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $description, $imagePath, $price, $amount);
        $stmt->execute();

        echo json_encode(["success" => true, "id" => $stmt->insert_id]);
        break;

    case 'update':
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $amount = $_POST['amount'];
        $imageBase64 = $_POST['image'];

        // Only save a new image if it's base64
        if (str_starts_with($imageBase64, 'data:image')) {
            $imagePath = saveBase64Image($imageBase64);
        } else {
            $imagePath = $imageBase64; // already a path
        }

        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, image=?, price=?, amount=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $description, $imagePath, $price, $amount, $id);
        $stmt->execute();

        echo json_encode(["success" => true]);
        break;

    case 'delete':
        $id = $_POST['id'];

        // Optional: delete image file from disk
        $res = $conn->query("SELECT image FROM products WHERE id = $id");
        if ($res && $row = $res->fetch_assoc()) {
            if (file_exists($row['image'])) {
                unlink($row['image']);
            }
        }

        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["success" => true]);
        break;
}
?>
