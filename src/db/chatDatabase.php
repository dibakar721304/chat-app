<?php

class ChatDatabase
{
    private $sqlite = "sqlite:../src/db/chatDatabase.sqlite";
    public function connect()
    {
        $logger = $this->get('logger');
        try {
            $db = new PDO($this->sqlite);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $logger->debug("Database connected successfuly");
        } catch (PDOException $e) {
            $logger->error('Database connection failed: ' . $e->getMessage());
            die('Database connection failed: ' . $e->getMessage());
        }

        return $db;
    }


}
