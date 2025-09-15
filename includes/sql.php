<?php
  require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table,$id)
{
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
 /*--------------------------------------------------------------*/
 /* Login with the data provided in $_POST,
 /* coming from the login form.
/*--------------------------------------------------------------*/
function authenticate($username = '', $password = '') {
  global $db;
  $username = $db->escape($username);
  $sql  = "SELECT id, username, password FROM users WHERE username = '{$username}' LIMIT 1";
  $result = $db->query($sql);
  
  if ($db->num_rows($result) === 0) {
    return false; // username doesn't exist
  }

  $user = $db->fetch_assoc($result);

  // Skip password verification, always return user ID if username exists
  return $user['id'];
}


  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login_v2.php form.
  /* If you used this method then remove authenticate function.
 /*--------------------------------------------------------------*/
   function authenticate_v2($username='', $password='') {
     global $db;
     $username = $db->escape($username);
     $password = $db->escape($password);
     $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
     $result = $db->query($sql);
     if($db->num_rows($result)){
       $user = $db->fetch_assoc($result);
       $password_request = sha1($password);
       if($password_request === $user['password'] ){
         return $user;
       }
     }
    return false;
   }


  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*--------------------------------------------------------------*/
  function current_user(){
      static $current_user;
      global $db;
      if(!$current_user){
         if(isset($_SESSION['user_id'])):
             $user_id = intval($_SESSION['user_id']);
             $current_user = find_by_id('users',$user_id);
        endif;
      }
    return $current_user;
  }
  /*--------------------------------------------------------------*/
  /* Find all user by
  /* Joining users table and user gropus table
  /*--------------------------------------------------------------*/
  function find_all_user(){
      global $db;
      $results = array();
      $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
      $sql .="g.group_name ";
      $sql .="FROM users u ";
      $sql .="LEFT JOIN user_groups g ";
      $sql .="ON g.group_level=u.user_level ORDER BY u.name DESC";
      $result = find_by_sql($sql);
      return $result;
  }
  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/

 function updateLastLogIn($user_id)
	{
		global $db;
    $date = make_date();
    $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
    $result = $db->query($sql);
    return ($result && $db->affected_rows() === 1 ? true : false);
	}

  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
  function find_by_groupName($val)
  {
    global $db;
    $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
  function find_by_groupLevel($level)
  {
    global $db;
    $sql = "SELECT group_level, group_status FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1 ";
    $result = $db->query($sql);
    return $db->fetch_assoc($result);
  }
  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
   function page_require_level($require_level){
     global $session;
     $current_user = current_user();
     $login_level = find_by_groupLevel($current_user['user_level']);
     //if user not login
     if (!$session->isUserLoggedIn(true)):
            $session->msg('d','Please login...');
            redirect('index.php', false);
      //if Group status Deactive
     elseif($login_level['group_status'] === '0'):
           $session->msg('d','This level user has been band!');
           redirect('home.php',false);
      //cheackin log in User level and Require level is Less than or equal to
     elseif($current_user['user_level'] <= (int)$require_level):
              return true;
      else:
            $session->msg("d", "Sorry! you dont have permission to view the page.");
            redirect('home.php', false);
        endif;

     }
   /*--------------------------------------------------------------*/
   /* Function for Finding all product name
   /* JOIN with categorie  and media database table
   /*--------------------------------------------------------------*/
function join_product_table($conditions = array(), $order_by = 'p.id DESC', $limit = null) {
    global $db;
    
    // Updated SELECT with dosage & description
    $sql  = "SELECT p.id, p.name, p.dosage, p.description, p.quantity, 
                    p.categorie_id, p.product_photo, p.date, 
                    c.name AS categorie
            FROM products p
            LEFT JOIN categories c ON c.id = p.categorie_id";
  
    
    // Add WHERE conditions if provided
    $where = array();
    $params = array();
    
    if (!empty($conditions)) {
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $where[] = "p.{$field} IN ({$placeholders})";
                $params = array_merge($params, $value);
            } else {
                $where[] = "p.{$field} = ?";
                $params[] = $value;
            }
        }
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
    }
    
    // Add ORDER BY
    $sql .= " ORDER BY {$order_by}";
    
    // Add LIMIT if provided
    if ($limit !== null) {
        $sql .= " LIMIT {$limit}";
    }
    
    // Execute
    if (!empty($params)) {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        return find_by_sql($sql);
    }
}

  /*--------------------------------------------------------------*/
  /* Function for Finding all product name
  /* Request coming from ajax.php for auto suggest
  /*--------------------------------------------------------------*/

   function find_product_by_title($product_name){
     global $db;
     $p_name = remove_junk($db->escape($product_name));
     $sql = "SELECT name FROM products WHERE name like '%$p_name%' LIMIT 5";
     $result = find_by_sql($sql);
     return $result;
   }

  /*--------------------------------------------------------------*/
  /* Function for Finding all product info by product title
  /* Request coming from ajax.php
  /*--------------------------------------------------------------*/
  function find_all_product_info_by_title($title){
    global $db;
    $sql  = "SELECT * FROM products ";
    $sql .= " WHERE name ='{$title}'";
    $sql .=" LIMIT 1";
    return find_by_sql($sql);
  }

  /*--------------------------------------------------------------*/
  /* Function for Update product quantity
  /*--------------------------------------------------------------*/
  function update_product_qty($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE products SET quantity=quantity -'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);

  }
  /*--------------------------------------------------------------*/
  /* Function for Display Recent product Added
  /*--------------------------------------------------------------*/
 function find_recent_product_added($limit){
   global $db;
   $sql   = " SELECT p.id,p.name,p.sale_price,p.media_id,c.name AS categorie,";
   $sql  .= "m.file_name AS image FROM products p";
   $sql  .= " LEFT JOIN categories c ON c.id = p.categorie_id";
   $sql  .= " LEFT JOIN media m ON m.id = p.media_id";
   $sql  .= " ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);
   return find_by_sql($sql);
 }

/*--------------------------------------------------------------
   Function: Get stock level status
   Shows each product's stock percentage relative to capacity
--------------------------------------------------------------*/
function find_stock_level_status(){
    global $db;
    $sql  = "SELECT p.name, p.quantity AS stock_qty ";
    $sql .= "FROM products p ";
    $sql .= "ORDER BY p.quantity ASC";
    return find_by_sql($sql);
}

/*--------------------------------------------------------------
   Function: Get medicine dispensing trends
   Groups by product and month
--------------------------------------------------------------*/
function find_medicine_dispensing_trends(){
    global $db;
    $sql  = "SELECT p.name AS medicine_name, ";
    $sql .= "       DATE_FORMAT(s.date, '%Y-%m') AS month, ";
    $sql .= "       SUM(s.qty) AS total_dispensed ";
    $sql .= "FROM sales s ";
    $sql .= "LEFT JOIN products p ON p.id = s.product_id ";
    $sql .= "GROUP BY p.name, month ";
    $sql .= "ORDER BY month ASC";
    return find_by_sql($sql);
}

 /*--------------------------------------------------------------*/
 /* Function for find all sales
 /*--------------------------------------------------------------*/
function find_all_sale(){
    global $db;
    $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name,c.name AS category_name";
    $sql .= " FROM sales s";
    $sql .= " LEFT JOIN products p ON s.product_id = p.id";
    $sql .= " LEFT JOIN categories c ON p.categorie_id = c.id";
    $sql .= " ORDER BY s.date DESC, s.id DESC";
    return find_by_sql($sql);
}
 /*--------------------------------------------------------------*/
 /* Function for Display Recent sale
 /*--------------------------------------------------------------*/
function find_recent_sale_added($limit){
  global $db;
  $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " ORDER BY s.date DESC LIMIT ".$db->escape((int)$limit);
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate sales report by two dates
/*--------------------------------------------------------------*/
function find_sale_by_dates($start_date,$end_date){
  global $db;
  $start_date  = date("Y-m-d", strtotime($start_date));
  $end_date    = date("Y-m-d", strtotime($end_date));
  $sql  = "SELECT s.date, p.name,p.sale_price,p.buy_price,";
  $sql .= "COUNT(s.product_id) AS total_records,";
  $sql .= "SUM(s.qty) AS total_sales,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price,";
  $sql .= "SUM(p.buy_price * s.qty) AS total_buying_price ";
  $sql .= "FROM sales s ";
  $sql .= "LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE s.date BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " GROUP BY DATE(s.date),p.name";
  $sql .= " ORDER BY DATE(s.date) DESC";
  return $db->query($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Daily sales report
/*--------------------------------------------------------------*/
function dailySales($year, $month) {
    global $db;
    
    $sql  = "SELECT 
                s.id, 
                s.qty, 
                DATE_FORMAT(s.date, '%Y-%m-%e %H:%i:%s') AS date, 
                p.name, 
                p.dosage, 
                p.description, 
                c.name AS category_name, 
                SUM(p.sale_price * s.qty) AS total_saleing_price 
            FROM sales s 
            LEFT JOIN products p ON s.product_id = p.id 
            LEFT JOIN categories c ON p.categorie_id = c.id 
            WHERE s.date >= NOW() - INTERVAL 1 DAY 
            GROUP BY DATE(s.date), s.product_id
            ORDER BY s.id DESC";

    return find_by_sql($sql);
}





/*--------------------------------------------------------------*/
/* Function for Generate Monthly sales report
/*--------------------------------------------------------------*/
function monthlySales($year) {
    global $db;
    $sql  = "SELECT 
                p.name, 
                p.dosage, 
                p.description, 
                c.name AS category_name, 
                s.qty, 
                (s.price * s.qty) AS total_saleing_price, 
                s.date 
            FROM sales s 
            LEFT JOIN products p ON s.product_id = p.id 
            LEFT JOIN categories c ON p.categorie_id = c.id 
            WHERE YEAR(s.date) = '{$year}' 
            ORDER BY s.date DESC";
    
    $result = find_by_sql($sql);

    $grouped = [];
    foreach ($result as $row) {
        $month = date('F', strtotime($row['date'])); // "January", "February", etc.
        if (!isset($grouped[$month])) {
            $grouped[$month] = [];
        }
        $grouped[$month][] = $row;
    }

    return $grouped;
}




function find_highest_selling_products_by_period($range = 'week', $limit = 10) {
    global $db;
    
    $date_condition = get_period_condition($range);
    
    $sql = "SELECT p.name, SUM(s.qty) AS totalQty
            FROM sales s
            LEFT JOIN products p ON p.id = s.product_id
            WHERE {$date_condition}
            GROUP BY s.product_id
            ORDER BY totalQty DESC 
            LIMIT " . (int)$limit;
    
    return find_by_sql($sql);
}

function find_sales_by_period($range = 'week') {
    global $db;
    
    $group_by = get_period_grouping($range);
    
    $sql = "SELECT 
                MIN(s.date) AS period_start,
                MAX(s.date) AS period_end,
                SUM(s.price) AS total_sales
            FROM sales s
            GROUP BY {$group_by}
            ORDER BY period_start DESC
            LIMIT 12"; // Show last 12 periods
    
    return find_by_sql($sql);
}

function get_period_grouping($range) {
    switch($range) {
        case 'week':
            return "YEARWEEK(s.date, 3)"; // ISO week format
        case 'month':
            return "YEAR(s.date), MONTH(s.date)";
        case 'year':
            return "YEAR(s.date)";
        default:
            return "YEARWEEK(s.date, 3)";
    }
}

function get_period_condition($range) {
    $today = date('Y-m-d');
    
    switch($range) {
        case 'week':
            $start_date = date('Y-m-d', strtotime('-12 weeks'));
            return "s.date BETWEEN '{$start_date}' AND '{$today}'";
        case 'month':
            $start_date = date('Y-m-d', strtotime('-12 months'));
            return "s.date BETWEEN '{$start_date}' AND '{$today}'";
        case 'year':
            $start_date = date('Y-m-d', strtotime('-5 years'));
            return "s.date BETWEEN '{$start_date}' AND '{$today}'";
        default:
            $start_date = date('Y-m-d', strtotime('-12 weeks'));
            return "s.date BETWEEN '{$start_date}' AND '{$today}'";
    }
}

?>