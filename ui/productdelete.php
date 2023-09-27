<?php
include_once 'connectdb.php';
session_start();

$id = $_POST["pidd"];
$delete = $pdo->prepare("DELETE FROM tbl_product WHERE pid=$id");
if($delete->execute()){

}else{
  echo "Error in deleting product";
}
