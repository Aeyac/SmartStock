<?php

// Without this, mysqli fails SILENTLY on bad queries instead of throwing
// an exception, so your try/catch blocks never see the real error.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class myDB
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "smart_stock";
    public $res;
    private $conn;

    public function __construct()
    {
        try {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
            ;
        } catch (Exception $e) {
            die("Database connection Error! . <br>" . $e);
        }
    }

    public function __desctruct()
    {
        $this->conn->close();
    }

    public function insert($table, $data)
    {
        try {
            $table_columns = implode(',', array_keys($data));
            $prep = $types = "";

            foreach ($data as $key => $value) {
                $prep .= '?,';
                $types .= substr(gettype($value), 0, 1);
            }

            $prep = substr($prep, 0, -1);
            $stmt = $this->conn->prepare("INSERT INTO $table($table_columns) VALUES ($prep)");
            if (!$stmt) {
                die("PREPARE FAILED: " . $this->conn->error . " | SQL: INSERT INTO $table($table_columns) VALUES ($prep)");
            }
            $stmt->bind_param($types, ...array_values($data));
            $stmt->execute();
            $stmt->close();

        } catch (Exception $e) {
            die("Error while inserting data!. <br>" . $e);
        }
    }

    public function select($table, $row = "*", $where = NULL)
    {
        try {
            if (!is_null($where)) {
                $cond = $types = "";
                foreach ($where as $key => $value) {
                    $cond .= $key . " = ? AND ";
                    $types .= substr(gettype($value), 0, 1);
                }

                $cond = substr($cond, 0, -4);
                $stmt = $this->conn->prepare("SELECT $row FROM $table WHERE $cond");
                $stmt->bind_param($types, ...array_values($where));
            } else {
                $stmt = $this->conn->prepare("SELECT $row FROM $table");
            }
            $stmt->execute();
            $data = $this->res = $stmt->get_result();
            return $data;
        } catch (Exception $e) {
            die("Error requesting data! . <br>" . $e);
        }
    }

    public function delete($table, $where = NULL)
    {
        try {
            if (!is_null($where) && is_array($where)) {
                $cond = "";
                $types = "";

                foreach ($where as $key => $value) {
                    $cond .= $key . " = ? AND ";
                    $types .= substr(gettype($value), 0, 1);
                }

                $cond = substr($cond, 0, -5);

                $stmt = $this->conn->prepare("DELETE FROM $table WHERE $cond");
                $stmt->bind_param($types, ...array_values($where));

                $success = $stmt->execute();
                $stmt->close();

                return $success; // Returns true on success, false on failure
            } else {
                throw new Exception("A WHERE clause is required to prevent accidental truncation.");
            }

        } catch (Exception $e) {
            die("Error deleting data! . <br>" . $e->getMessage());
        }
    }




}


