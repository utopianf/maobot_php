<?php
/* mysql.class.php
 * Class for operating MySQL
 * $data should be stdClass
 *
 * query($query)
 * - Use this function if it is complex to use the functions below.
 * - It returns results from MySQL as stdClass.
 *
 * insert($table, $data, $format)
 * - Insert data into MySQL and return true if succeed.
 * - 
 *
 * update($table, $data, $format, $where, $where_format)
 * -
 *
 * select($query, $data, $format)
 * -
 *
 * delete($table, $id)
 * -
 *
 */
include_once "settings.php";

class DBRecord {

    private $dbhost;
    private $dbuser;
    private $dbpass;
    private $dbname;

    private $db;

    public function __construct() {
        $this->dbhost = DBHOST;
        $this->dbuser = DBUSER;
        $this->dbpass = DBPASS;
        $this->dbname = DBNAME;
    }

    protected function connect() {
        $mysqli = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
        if (!$mysqli->set_charset('utf8')) {
            printf("Error loading character set utf8: %s\n", $mysqli->error);
            exit();
        } else {
            #printf("Current character set: %s\n", $mysqli->character_set_name());
        }
        return $mysqli;
    }

    public function query($query) {
        $db = $this->connect();
        $res = $db->query($query);
        while ($row = $res->fetch_object()) {
            $results[] = $row;
        }
        return $results;
    }

    public function insert($table, $data, $format) {
        if (empty($table) || empty($data)) {
            return false;
        }

        $db = $this->connect();
        $data = (array) $data;
        $format = (array) $format;

        $format = implode("", $format);
        $format = str_replace("%", "", $format);

        list($fields, $placeholders, $values) = $this->prep_query($data);
        
        array_unshift($values, $format);
        $stmt = $db->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");
        call_user_func_array(array($stmt, 'bind_param'), $this->ref_values($values));
        $stmt->execute();

        if ($stmt->affected_rows) {
            return true;
        }
        return false;
    }

    public function update($table, $data, $format, $where, $where_format) {
        if (empty($table) || empty($data)) {
            return false;
        }

        $db=$this->connect();
        $data = (array)$data;
        $format = (array)$format;

        $format = implode('', $format);
        $format = str_replace('%', '', $format);
        $where_format = implode('', $where_format);
        $where_format = str_replace('%', '', $where_format);
        $format .= $where_format;

        list($fields, $placeholders, $values) = $this->prep_query($data, 'update');

        $where_clause = '';
        $where_values = array();
        $count = 0;

        foreach ($where as $field => $value) {
            if ($count > 0) {
                $where_clause .= ' AND ';
            }
            $where_clause .= $field . '=?';
            $where_values[] = $value;

            $count++;
        }

        array_unshift($values, $format);
        $values = array_merge($values, $where_values);

        #echo "UPDATE {$table} SET {$placeholders} WHERE {$where_clause}\n\n\n\n";
        
        $stmt = $db->prepare("UPDATE {$table} SET {$placeholders} WHERE {$where_clause}");
        call_user_func_array(array($stmt, 'bind_param'), $this->ref_values($values));
        $stmt->execute();

        if ($stmt->affected_rows) {
            return true;
        }
        return false;
    }

    public function select($query, $data=Null, $format=Null) {
        $db = $this->connect();
        $stmt = $db->prepare($query);
        if ($data != Null && $format != Null) {
            $format = implode('', $format);
            $format = str_replace('%', '', $format);
            array_unshift($data, $format);
            call_user_func_array(array($stmt, 'bind_param'), $this->ref_values($data));
        }
        $stmt->execute();

        $res = $stmt->get_result();

        $results = array();
        while ($row = $res->fetch_object()) {
            $results[] = $row;
        }
        return $results;
    }

    public function delete($table, $id) {
        $db = $this->connect();

        $stmt = $db->prepare("DELETE FROM {$table} WHERE ID = ?");
        $stmt->bind_param('d', $id);
        $stmt->execute();

        if ($stmt->affected_row) {
            return true;
        }
        return false;
    }

    private function prep_query($data, $type='insert') {
        $fields = "";
        $placeholders = "";
        $values = array();

        foreach ($data as $field => $value){
            $fields.= "{$field},";
            $values[] = $value;
            if ($type == 'update') {
                $placeholders .= $field . '=?,';
            } else {
                $placeholders .= '?,';
            }
        }

        $fields = substr($fields, 0, -1);
        $placeholders = substr($placeholders, 0, -1);

        return array($fields, $placeholders, $values);
    }

    private function ref_values($array){
        $refs = array();

        foreach ($array as $key => $value) {
            $refs[$key] = &$array[$key];
        }

        return $refs;
    }
}
