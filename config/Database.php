<?php

    class Db{
        private $delivery_server = "localhost";
        private $delivery_admin = "root";
        private $delivery_password = "";
        private $delivery_data = "delivery";

        public function connect(){
            $dns = "mysql:host=" . $this->delivery_server . "; dbname=" . $this->delivery_data;
            $pdo = new PDO($dns, $this->delivery_admin, $this->delivery_password);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        }
    }