<?php
include '../config/Database.php';

class DeliveryController extends Db {
    
    public function setUser($name, $email, $pass, $role) {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$name, $email, $pass, $role]);

        if($stmt) {
            header("Location: login.php");
        } else {
            echo "An error occurred while registering.";
        }
    }

    public function AddOrder($client_id, $tracking_number, $status, $driver_id) {
        try {
            $pdo = $this->connect();
    
            // Check if client exists
            $clientCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ? AND role = 'client'");
            $clientCheck->execute([$client_id]);
            $clientExists = $clientCheck->fetchColumn();
    
            // Check if driver exists
            $driverCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ? AND role = 'driver'");
            $driverCheck->execute([$driver_id]);
            $driverExists = $driverCheck->fetchColumn();
    
            if ($clientExists && $driverExists) {
                $sql = "INSERT INTO orders (client_id, tracking_number, status, driver_id) VALUES (?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$client_id, $tracking_number, $status, $driver_id]);
    
                if ($stmt) {
                    echo "Order has been added successfully";
                } else {
                    echo "An error occurred while adding the order";
                }
            } else {
                if (!$clientExists) {
                    echo "The specified client ID does not exist or is not assigned the role of 'client'.";
                }
                if (!$driverExists) {
                    echo "The specified driver ID does not exist or is not assigned the role of 'driver'.";
                }
            }
        } catch (PDOException $e) {
            echo "Failed to create order: " . $e->getMessage();
        }
    }

    public function updateOrder($Order_ID, $Order_status) {
        // Update query
        $sql = "UPDATE orders SET status = :status WHERE order_id = :order_id";
        
        // Prepare the query
        $stmt = $this->connect()->prepare($sql);
        
        // Bind the parameters
        $stmt->bindParam(':status', $Order_status);
        $stmt->bindParam(':order_id', $Order_ID);
        
        // Execute the query
        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateOrderStatus($orderId, $orderStatus) {
        // Validate input
        if (!is_numeric($orderId) || $orderId <= 0) {
            throw new InvalidArgumentException("Invalid order ID");
        }
    
        $allowedStatuses = array('processing', 'shipped', 'delivered', 'cancelled');
        if (!in_array($orderStatus, $allowedStatuses)) {
            throw new InvalidArgumentException("Invalid order status");
        }
    
        // Sanitize input
        $orderId = filter_var($orderId, FILTER_SANITIZE_NUMBER_INT);
        $orderStatus = filter_var($orderStatus, FILTER_SANITIZE_STRING);
    
        // SQL query
        $sql = "UPDATE orders SET status = :status WHERE order_id = :order_id";
    
        try {
            $stmt = $this->connect()->prepare($sql);
            $stmt->bindParam(':status', $orderStatus);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();
    
            // Log the update
            error_log("Order $orderId updated to $orderStatus");
    
            // Return success message
            return true;
        } catch (PDOException $e) {
            // Log the error
            error_log("Error updating order $orderId: " . $e->getMessage());
    
            // Throw an exception
            throw new RuntimeException("Failed to update order");
        }
    }
            
    public function register() {
        header("Location: index.php");
    }

    public function login() {
        header("Location: login.php");
    }

    public function AdmingetOut() {
        header("Location: login.php");
    }

    public function DrivergetOut() {
        session_destroy();
        header("Location: login.php");
    }

    public function ClientgetOut() {
        session_destroy();
        header("Location: login.php");
    }

    public function createOrder($clientId, $trackingNumber, $status, $driverId) {
        // Validate input
        if (!is_numeric($clientId) || $clientId <= 0) {
            throw new InvalidArgumentException("Invalid client ID");
        }
        
        if (!is_numeric($driverId) || $driverId <= 0) {
            throw new InvalidArgumentException("Invalid driver ID");
        }
        
        $allowedStatuses = array('processing', 'shipped', 'delivered', 'cancelled');
        if (!in_array($status, $allowedStatuses)) {
            throw new InvalidArgumentException("Invalid order status");
        }
        
        // Sanitize input
        $clientId = filter_var($clientId, FILTER_SANITIZE_NUMBER_INT);
        $trackingNumber = filter_var($trackingNumber, FILTER_SANITIZE_STRING);
        $status = filter_var($status, FILTER_SANITIZE_STRING);
        $driverId = filter_var($driverId, FILTER_SANITIZE_NUMBER_INT);
        
        // SQL query
        $sql = "INSERT INTO orders (client_id, tracking_number, status, driver_id) VALUES (:client_id, :tracking_number, :status, :driver_id)";
        
        try {
            $stmt = $this->connect()->prepare($sql);
            $stmt->bindParam(':client_id', $clientId);
            $stmt->bindParam(':tracking_number', $trackingNumber);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':driver_id', $driverId);
            $stmt->execute();
        } catch (PDOException $e) {
            // Log the error
            error_log("Error creating order: " . $e->getMessage());
            throw new RuntimeException("Error creating order");
        }
    }

    public function getDrivers() {
        $sql = "SELECT * FROM users WHERE role = 'driver'";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getClientInfoByEmailAndOrderId($email, $orderId) {
        $stmt = $this->connect()->prepare("SELECT u.* FROM users u INNER JOIN orders o ON u.user_id = o.client_id WHERE u.email = :email AND o.order_id = :orderId AND u.role = 'client'");
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":orderId", $orderId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDriverInfo($driverId) {
        $stmt = $this->connect()->prepare("SELECT * FROM users WHERE user_id = :driverId AND role = 'driver'");
        $stmt->bindParam(":driverId", $driverId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getClient($clientId) {
        $stmt = $this->connect()->prepare("SELECT * FROM users WHERE user_id = :clientId AND role = 'client'");
        $stmt->bindParam(":clientId", $clientId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getClients() {
        $pdo = $this->connect();
        $sql = "SELECT * FROM users WHERE role = 'client'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getClientOrderDetails($orderId, $trackingNumber) {
        $pdo = $this->connect();
        $sql = "SELECT * FROM orders WHERE order_id = :order_id AND tracking_number = :tracking_number";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':tracking_number', $trackingNumber);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getClientOrders($clientId, $limit = null, $offset = null) {
        $pdo = $this->connect();
        $sql = "SELECT * FROM orders WHERE client_id = :client_id";
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':client_id', $clientId);
        if ($limit !== null && $offset !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalClientPastOrders($clientId) {
        $pdo = $this->connect();
        $sql = "SELECT COUNT(*) as total FROM orders WHERE client_id = :client_id AND status = 'delivered'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':client_id', $clientId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getClientPastOrders($clientId, $limit = null, $offset = null) {
        $pdo = $this->connect();
        $sql = "SELECT * FROM orders WHERE client_id = :client_id AND status = 'delivered'";
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':client_id', $clientId);
        if ($limit !== null && $offset !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalClientOrders($clientId) {
        $pdo = $this->connect();
        $sql = "SELECT COUNT(*) as total FROM orders WHERE client_id = :client_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':client_id', $clientId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function __construct() {
        // Assuming DeliveryModel is another class that handles database operations
        $this->deliveryModel = new DeliveryModel();
    }

    public function getOrderByTrackingNumber($tracking_number) {
        return $this->deliveryModel->getOrderByTrackingNumber($tracking_number);
    }
}

 
class DeliveryModel {
  private $pdo;

  public function __construct() {
    $this->pdo = new PDO('mysql:host=localhost;dbname=delivery', 'root', '');
  }

  public function getClientIdByName($client_name) {
    $sql = "SELECT user_id FROM users WHERE name = :client_name AND role = 'client'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':client_name', $client_name);
    $stmt->execute();
    $client_id = $stmt->fetchColumn();
    return $client_id;
  }

  public function getDriverIdByName($driver_name) {
    $sql = "SELECT user_id FROM users WHERE name = :driver_name AND role = 'driver'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':driver_name', $driver_name);
    $stmt->execute();
    $driver_id = $stmt->fetchColumn();
    return $driver_id;
  }

  public function getClientNames() {
    $sql = "SELECT name FROM users WHERE role = 'client'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $client_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $client_names;
  }

  public function getDriverNames() {
    $sql = "SELECT name FROM users WHERE role = 'driver'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $driver_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $driver_names;
  }

  public function getAllOrders($limit, $offset) {
    $sql = "SELECT orders.order_id, u1.name AS client_name, u2.name AS driver_name, orders.status 
            FROM orders 
            INNER JOIN users u1 ON orders.client_id = u1.user_id 
            LEFT JOIN users u2 ON orders.driver_id = u2.user_id
            LIMIT $limit OFFSET $offset";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $orders;
}

  public function AdminUpdateOrder($Order_status, $Order_ID, $client_id, $driver_id) {
    // Start a transaction
    $this->pdo->beginTransaction();
  
    try {
      // Prepare the update query
      $stmt = $this->pdo->prepare("UPDATE orders SET status = :status, client_id = :client_id, driver_id = :driver_id WHERE order_id = :order_id");
  
      // Bind the parameters
      $stmt->bindParam(":status", $Order_status);
      $stmt->bindParam(":client_id", $client_id);
      $stmt->bindParam(":driver_id", $driver_id);
      $stmt->bindParam(":order_id", $Order_ID);
  
      // Execute the query
      $stmt->execute();
  
      // Commit the transaction
      $this->pdo->commit();
    } catch (PDOException $e) {
      // Roll back the transaction if an error occurs
      $this->pdo->rollBack();
      echo "Error updating order: " . $e->getMessage();
    }
  }

  public function AdminsignOut() {
    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: login.php");
    exit();
  }

  public function getDrivers() {
    $sql = "SELECT * FROM users WHERE role = 'driver'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $drivers;
  }

  public function getClients() {
    $sql = "SELECT * FROM users WHERE role = 'client'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $clients;
  }

  public function getOrders($limit, $offset, $searchQuery = '', $statusFilter = '') {
    // Start building the SQL query
    $sql = "SELECT * FROM orders WHERE 1=1";

    // If there's a search query, add filtering by order ID
    if (!empty($searchQuery)) {
        $sql .= " AND order_id = :searchQuery";
    }

    // If there's a status filter, add filtering by status
    if (!empty($statusFilter)) {
        $sql .= " AND status = :statusFilter";
    }

    // Add pagination
    $sql .= " LIMIT :limit OFFSET :offset";

    // Prepare the statement
    $stmt = $this->pdo->prepare($sql);

    // Bind the limit and offset parameters
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    // If searchQuery is provided, bind it
    if (!empty($searchQuery)) {
        $stmt->bindParam(':searchQuery', $searchQuery, PDO::PARAM_INT);
    }

    // If statusFilter is provided, bind it
    if (!empty($statusFilter)) {
        $stmt->bindParam(':statusFilter', $statusFilter);
    }

    // Execute the statement and fetch the results
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}



public function getTotalOrders() {
  $query = "SELECT COUNT(*) as total FROM orders";
  $stmt = $this->pdo->prepare($query);
  $stmt->execute();
  $result = $stmt->fetch();
  return $result['total'];
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
    } else {
        return false;
    }
}

 

public function getAssignedOrders($driverId, $limit = null, $offset = null) {
  $sql = "SELECT * FROM orders WHERE driver_id = :driver_id";
  if ($limit !== null && $offset !== null) {
      $sql .= " LIMIT :limit OFFSET :offset";
  }
  $stmt = $this->pdo->prepare($sql);
  $stmt->bindParam(':driver_id', $driverId);
  if ($limit !== null && $offset !== null) {
      $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
  }
  $stmt->execute();
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $orders;
}

public function getTotalAssignedOrders($driverId) {
  $sql = "SELECT COUNT(*) as total FROM orders WHERE driver_id = :driver_id";
  $stmt = $this->pdo->prepare($sql);
  $stmt->bindParam(':driver_id', $driverId);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result['total'];
}

public function getOrderById($orderId) {
    $sql = "SELECT * FROM orders WHERE order_id = :order_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':order_id', $orderId);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    return $order;
}

public function updateOrderStatus($orderId, $status) {
    $sql = "UPDATE orders SET status = :status WHERE order_id = :order_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $orderId);
    return $stmt->execute();
}

public function signUp($name, $email, $pass, $role) {
    return $this->setUser  ($name, $email, $pass, $role);
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
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result['total'];
}

public function getTotalClientPastOrders($clientId) {
  $sql = "SELECT COUNT(*) as total FROM orders WHERE client_id = :client_id AND status = 'delivered'";
  $stmt = $this->pdo->prepare($sql);
  $stmt->bindParam(':client_id', $clientId);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result['total'];
}

public function getDeliveredOrders($driverId, $limit, $offset) {
  // Directly concatenate the $limit and $offset into the SQL query
  $sql = "SELECT * FROM orders WHERE driver_id = :driver_id AND status = 'delivered' LIMIT $limit OFFSET $offset";
  
  // Prepare the statement
  $stmt = $this->pdo->prepare($sql);
  
  // Bind the driver_id
  $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
  
  // Execute the query
  $stmt->execute();
  
  // Fetch and return the delivered orders
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function getTotalDeliveredOrders($driverId) {
  $sql = "SELECT COUNT(*) FROM orders WHERE driver_id = ? AND status = 'delivered'";
  $stmt = $this->pdo->prepare($sql);
  $stmt->execute([$driverId]);
  return $stmt->fetchColumn();
}

public function getOrderByTrackingNumber($trackingNumber) {
  $sql = "SELECT * FROM orders WHERE tracking_number = :tracking_number";
  $stmt = $this->pdo->prepare($sql);
  $stmt->bindParam(':tracking_number', $trackingNumber);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}






 

}