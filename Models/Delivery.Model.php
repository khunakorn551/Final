<?php
class DeliveryModel {
  private $pdo;

  public function __construct() {
    $this->pdo = new PDO('mysql:host=localhost;dbname=delivery', 'root', '');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error reporting
  }

  public function getClientIdByName($client_name) {
    $sql = "SELECT user_id FROM users WHERE name = :client_name AND role = 'client'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':client_name', $client_name);
    $stmt->execute();
    return $stmt->fetchColumn();
  }

  public function getDriverIdByName($driver_name) {
    $sql = "SELECT user_id FROM users WHERE name = :driver_name AND role = 'driver'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':driver_name', $driver_name);
    $stmt->execute();
    return $stmt->fetchColumn();
  }

  public function getClientNames() {
    $sql = "SELECT name FROM users WHERE role = 'client'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public function getDriverNames() {
    $sql = "SELECT name FROM users WHERE role = 'driver'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public function getAllOrders($limit, $offset) {
    $sql = "SELECT orders.order_id, u1.name AS client_name, u2.name AS driver_name, orders.status 
            FROM orders 
            INNER JOIN users u1 ON orders.client_id = u1.user_id 
            LEFT JOIN users u2 ON orders.driver_id = u2.user_id
            LIMIT :limit OFFSET :offset";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }

  public function AdminUpdateOrder($Order_status, $Order_ID) {
    $this->pdo->beginTransaction();
    try {
      $stmt = $this->pdo->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
      $stmt->bindParam(":status", $Order_status);
      $stmt->bindParam(":order_id", $Order_ID);
      $stmt->execute();
      $this->pdo->commit();
    } catch (PDOException $e) {
      $this->pdo->rollBack();
      echo "Error updating order: " . $e->getMessage();
    }
  }

  public function AdminsignOut() {
    session_destroy();
    header("Location: login.php");
    exit();
  }

  public function getDrivers() {
    $sql = "SELECT * FROM users WHERE role = 'driver'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }

  public function getClients() {
    $sql = "SELECT * FROM users WHERE role = 'client'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }

  public function getOrders($limit, $offset, $searchQuery = '', $statusFilter = '') {
    $sql = "SELECT * FROM orders WHERE 1=1";
    if (!empty($searchQuery)) {
      $sql .= " AND order_id = :searchQuery";
    }
    if (!empty($statusFilter)) {
      $sql .= " AND status = :statusFilter";
    }
    $sql .= " LIMIT :limit OFFSET :offset";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    if (!empty($searchQuery)) {
      $stmt->bindParam(':searchQuery', $searchQuery, PDO::PARAM_INT);
    }
    if (!empty($statusFilter)) {
      $stmt->bindParam(':statusFilter', $statusFilter);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }

  public function getTotalOrders() {
    $sql = "SELECT COUNT(*) as total FROM orders";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetch()['total'];
  }

  public function createOrder($clientId, $trackingNumber, $status, $driverId) {
    $sql = "INSERT INTO orders (client_id, tracking_number, status, driver_id) VALUES (:client_id, :tracking_number, :status, :driver_id)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(":client_id", $clientId);
    $stmt->bindParam(":tracking_number", $trackingNumber);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":driver_id", $driverId);
    $stmt->execute();
  }

  public function signIn($email, $pass, $role) {
    $sql = "SELECT * FROM users WHERE email = :email AND role = :role";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":role", $role);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && password_verify($pass, $result['password'])) {
      return $result;
    }
    return false;
  }

  public function getAssignedOrders($driverId, $limit = null, $offset = null) {
    $sql = "SELECT * FROM orders WHERE driver_id = :driver_id";
    if ($limit !== null && $offset !== null) {
      $sql .= " LIMIT :limit OFFSET :offset";
    }
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
    if ($limit !== null && $offset !== null) {
      $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTotalAssignedOrders($driverId) {
    $sql = "SELECT COUNT(*) as total FROM orders WHERE driver_id = :driver_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':driver_id', $driverId);
    $stmt->execute();
    return $stmt->fetch()['total'];
  }

  public function getOrderById($orderId) {
    $sql = "SELECT * FROM orders WHERE order_id = :order_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':order_id', $orderId);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function updateOrderStatus($orderId, $status) {
    $sql = "UPDATE orders SET status = :status WHERE order_id = :order_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $orderId);
    return $stmt->execute();
  }

  public function signUp($name, $email, $pass, $role) {
    return $this->setUser($name, $email, $pass, $role);
  }

  public function setUser($name, $email, $pass, $role) {
    $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :pass, :role)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":pass", $hashedPass);
    $stmt->bindParam(":role", $role);
    return $stmt->execute();
  }

  public function getTotalClientOrders($clientId) {
    $sql = "SELECT COUNT(*) as total FROM orders WHERE client_id = :client_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':client_id', $clientId);
    $stmt->execute();
    return $stmt->fetch()['total'];
  }

  public function getTotalClientPastOrders($clientId) {
    $sql = "SELECT COUNT(*) as total FROM orders WHERE client_id = :client_id AND status = 'delivered'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':client_id', $clientId);
    $stmt->execute();
    return $stmt->fetch()['total'];
  }

  public function getDeliveredOrders($driverId, $limit, $offset) {
    $sql = "SELECT * FROM orders WHERE driver_id = :driver_id AND status = 'delivered' LIMIT :limit OFFSET :offset";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTotalDeliveredOrders($driverId) {
    $sql = "SELECT COUNT(*) FROM orders WHERE driver_id = :driver_id AND status = 'delivered'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':driver_id', $driverId);
    $stmt->execute();
    return $stmt->fetchColumn();
  }

  public function getOrderByTrackingNumber($trackingNumber) {
    $sql = "SELECT * FROM orders WHERE tracking_number = :tracking_number";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':tracking_number', $trackingNumber);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // Corrected function using $this->pdo instead of $this->db
  public function getOrderHistory($limit, $offset) {
    $sql = "SELECT * FROM orders WHERE status IN ('delivered', 'cancelled') LIMIT :limit OFFSET :offset";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

public function createUser($name, $email, $password, $role) {
  try {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      // Status will default to 'offline'
      $status = 'offline';
      $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
      return $stmt->execute([$name, $email, $hashedPassword, $role, $status]);
  } catch (PDOException $e) {
      return false;
  }
}

}
?>
