<?php
class myDB
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "smart_stock";
    public $res;
    public $conn; // public so any file can run its own manual query when needed

    public function __construct()
    {
        try {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        } catch (Exception $e) {
            die("Database connection Error! . <br>" . $e);
        }
    }

    public function __destruct()
    {
        $this->conn->close();
    }

    public function insert($table, $data)
    {
        try {
            $columns = implode(',', array_keys($data));
            $placeholders = implode(',', array_fill(0, count($data), '?'));

            $types = '';
            foreach ($data as $value) {
                $types .= $this->getBindType($value);
            }

            $stmt = $this->conn->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
            $stmt->bind_param($types, ...array_values($data));
            $stmt->execute();

            $insertId = $this->conn->insert_id;

            $stmt->close();

            return $insertId;
        } catch (Exception $e) {
            die("Error while inserting data.<br>" . $e);
        }
    }

    public function select($table, $row = "*", $where = null)
    {
        try {

            if ($where !== null) {

                $conditions = [];
                $types = "";
                $params = [];

                foreach ($where as $column => $value) {
                    if ($value === null) {
                        $conditions[] = "$column IS NULL";
                    } else {
                        $conditions[] = "$column = ?";
                        $types .= $this->getBindType($value);
                        $params[] = $value;
                    }
                }

                $sql = "SELECT $row FROM $table WHERE " . implode(" AND ", $conditions);
                $stmt = $this->conn->prepare($sql);

                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
            } else {
                $stmt = $this->conn->prepare("SELECT $row FROM $table");
            }

            $stmt->execute();
            $this->res = $stmt->get_result();

        } catch (Exception $e) {
            die("Error requesting data.<br>" . $e);
        }
    }

    public function update($table, $data, $where)
    {
        try {
            $set = [];
            $conditions = [];

            $types = "";
            $params = [];

            // SET
            foreach ($data as $column => $value) {
                $set[] = "$column = ?";
                $types .= $this->getBindType($value);
                $params[] = $value;
            }

            // WHERE
            foreach ($where as $column => $value) {

                if ($value === null) {
                    $conditions[] = "$column IS NULL";
                } else {
                    $conditions[] = "$column = ?";
                    $types .= $this->getBindType($value);
                    $params[] = $value;
                }
            }

            $sql = "UPDATE $table SET "
                . implode(", ", $set)
                . " WHERE "
                . implode(" AND ", $conditions);

            $stmt = $this->conn->prepare($sql);

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $stmt->close();

        } catch (Exception $e) {
            die("Error while updating data.<br>" . $e);
        }
    }

    public function delete($table, $where)
    {
        try {
            $conditions = [];
            $types = "";
            $params = [];

            foreach ($where as $column => $value) {

                if ($value === null) {
                    $conditions[] = "$column IS NULL";
                } else {
                    $conditions[] = "$column = ?";
                    $types .= $this->getBindType($value);
                    $params[] = $value;
                }
            }

            $sql = "DELETE FROM $table WHERE " . implode(" AND ", $conditions);
            $stmt = $this->conn->prepare($sql);

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $stmt->close();

        } catch (Exception $e) {
            die("Error while deleting data.<br>" . $e);
        }
    }


    private function getBindType($value)
    {
        if (is_int($value)) {
            return 'i';
        }

        if (is_float($value)) {
            return 'd';
        }

        return 's';
    }
}