<?php
class Sql
{
  var $vars = array();
  public function __construct()
  {
    $db['host'] = 'localhost';  // location
    $db['database'] = 'domus';	// DataBase Name
    $db['username'] = 'root';	// Mysql User Name	: root
    $db['password'] = '';	// Mysql Password

    @$this->db = new mysqli($db['host'], $db['username'], $db['password'], $db['database']);
    if (mysqli_connect_errno())
      exit('No Connect To DataBases');
    else
      $this->Query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
  }

  public function returnid()
  {
    return ($this->db->insert_id);
  }

  function inner($query)
  {
    $return = array();
    if ($query) {
      $result = $this->Query($query);

      $num_result = $result->num_rows;
      if ($num_result) {
        for ($i = 0; $i < $num_result; $i++) {
          $return[] = $result->fetch_assoc();
        }
      }
    }
    return $return;
  }

  function Insert($Table, $Columns)
  {

    $CName = '';
    $CValue = '';
    foreach ($Columns as $Key => $Value) {
      $CName .= "`$Key`,";
      if ($Value == 'null')
        $CValue .= "$Value,";
      else
        $CValue .= "'$Value',";
    }
    $Query = "INSERT INTO `$Table` ($CName) VALUES ($CValue);";
    $Query = str_replace(',)', ')', $Query);
    $Query = str_replace('`,)', '`)', $Query);
    $Query = str_replace("',)", "')", $Query);
    if ($this->Query($Query))
      return true;
    else
      return false;

  }

  function Query($query)
  {
    $Result = $this->db->query($query);
    if (isset($Result->error)) {
      echo $Result->error;
    }

    return $Result;
  }

  function Selects($Table, $where = '', $orderby = '', $DESC = 'ASC', $Limit = '', $Column = '*')
  {
    $return = array();
    if ($orderby) {
      $orderby = "order by `$orderby` $DESC";
    }
    if ($where) {
      $query = "SELECT $Column FROM `$Table` WHERE $where $orderby $Limit;";
    } else {
      $query = "SELECT $Column FROM `$Table` $orderby $Limit; ";
    }
    //$this->SetLog($query  , 'query');
    //echo $query.'<br>';

    $result = $this->Query($query);
    @$num_result = $result->num_rows;
    if (@$num_result == 1) {
      $return[] = $result->fetch_assoc();
    } else {
      for ($i = 0; $i < $num_result; $i++) {
        $return[] = $result->fetch_assoc();
      }
    }

    return $return;

  }

  function Select($Table, $where = '', $orderby = '', $DESC = 'ASC', $Limit = '', $Column = '*')
  {
    $return = array();
    if ($orderby) {
      $orderby = "order by `$orderby` $DESC";
    }
    if ($where) {
      $query = "SELECT $Column FROM `$Table` WHERE $where $orderby $Limit;";
    } else {
      $query = "SELECT $Column FROM `$Table` $orderby $Limit; ";
    }
    // echo $query.'<br>';

    $result = $this->Query($query);
    if (!$result)
      return false;

    $return = $result->fetch_assoc();
    return $return;

  }

  function Update($Table, $Columns, $where = '')
  {
    $CName = '';

    foreach ($Columns as $Key => $Value) {
      if ($Value == 'null')
        $CValue = "$Value,";
      else
        $CValue = "'$Value',";

      $CName .= "`$Key`	=	$CValue";

    }
    $Query = "UPDATE `$Table` SET $CName WHERE $where;";
    $Query = str_replace(', WHERE', ' WHERE', $Query);

    $Query = str_replace(',)', ')', $Query);
    $Query = str_replace('`,)', '`)', $Query);
    $Query = str_replace("',)", "')", $Query);


    //echo $Query;
    $result = $this->Query($Query);
    //return $result->num_rows;
    return $result;
  }

  function Delete($Table, $where = '')
  {
    $query = "DELETE FROM `$Table` WHERE $where;";
    $result = $this->Query($query);
    //return $result->num_rows;
    return $result;
  }

  function Find($Config)
  {
    return var_dump($Config);
  }

  function Num_rows($Table, $where = '')
  {
    if ($where) {
      $query = "SELECT count(*) FROM `$Table` WHERE $where;";
    } else {
      $query = "SELECT count(*) FROM `$Table`;";
    }

    $result = $this->Query($query);
    $return = $result->fetch_assoc();
    return $return['count(*)'];
  }

  function GetSP($SP, $Column)
  {
    $return = array();

    $query = "CALL $SP($Column);";
    // echo $query.'<br>';

    $result = $this->Query($query);
    @$num_result = $result->num_rows;
    if (@$num_result == 1) {
      $return = $result->fetch_assoc();
    } else {
      for ($i = 0; $i < $num_result; $i++) {
        $return[] = $result->fetch_assoc();
      }
    }

    return $return;
  }

  function SetLog($log, $type = null)
  {
    $LogInsert['id'] = 'null';
    $LogInsert['log'] = (string) $log;
    $LogInsert['type'] = $type;
    $Insert = $this->Insert('logs', $LogInsert);
  }

  public function RequestToServer($url, $data, $bg = false)
  {

    $curl = curl_init($url);
    $curl_post_data = array(
      "data" => json_encode($data),
    );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    if ($bg) {
      curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    }


    @curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
    $curl_response = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($curl_response);
    //$this->SetLog('dfg' , 2222);
    // $this->SetLog($curl_response , 2222);
    return $result;
  }


  public function RequestToServerServer($url, $data)
  {
    $curl = curl_init($url);
    $curl_post_data = array(
      "data" => json_encode($data),
    );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    @curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
    $curl_response = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($curl_response);
    return $result;
  }
}

?>