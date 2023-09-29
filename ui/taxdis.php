<?php
include_once 'connectdb.php';
session_start();
if (empty($_SESSION['useremail'])) {
  header('Location: ../index.php');
}
if ($_SESSION['role'] == 'Admin') {
  include_once 'header.php';
} else {
  include_once 'headeruser.php';
}
if (isset($_POST["btn_save"])) {

  $sgst = $_POST["txtsgst"];
  $cgst = $_POST["txtcgst"];
  $discount = $_POST["txtdiscount"];

  if (empty($sgst)) {
    $_SESSION["status"] = 'Category Field is Empty';
    $_SESSION["status_code"] = 'warning';
  } else {
    $insert = $pdo->prepare("INSERT INTO tbl_taxdis (sgst,cgst,discount) VALUES (:sgst, :cgst, :discount)");
    $insert->bindParam(':sgst', $sgst);
    $insert->bindParam(':cgst', $cgst);
    $insert->bindParam(':discount', $discount);

    if ($insert->execute()) {
      $_SESSION["status"] = 'Tax And Discount Added Successfully';
      $_SESSION["status_code"] = 'success';
    } else {
      $_SESSION["status"] = 'Tax And Discount Added Failed Failed';
      $_SESSION["status_code"] = 'error';
    }
  }
}

if (isset($_POST["btn_update"])) {
  $sgst = $_POST["txtsgst"];
  $cgst = $_POST["txtcgst"];
  $discount = $_POST["txtdiscount"];
  $id = $_POST["txtid"];

  if (empty($sgst)) {
    $_SESSION["status"] = 'Field is Empty';
    $_SESSION["status_code"] = 'warning';
  } else {
    $update = $pdo->prepare("UPDATE tbl_taxdis SET sgst='$sgst', cgst='$cgst', discount='$discount' WHERE taxdis_id='$id'");
    if ($update->execute()) {
      $_SESSION["status"] = 'Tax And Discount Updated Successfully';
      $_SESSION["status_code"] = 'success';
    } else {
      $_SESSION['status'] = "Tax And Discount Update Failed";
      $_SESSION['status_code'] = "error";
    }
  }
}
if (isset($_POST["btndelete"])) {
  $delete = $pdo->prepare("DELETE FROM tbl_taxdis WHERE taxdis_id=" . $_POST["btndelete"]);

  if ($delete->execute()) {
    $_SESSION['status'] = "Tax And Discount Deleted";
    $_SESSION['status_code'] = "success";
  } else {
    $_SESSION['status'] = "Deleted Failed";
    $_SESSION['status_code'] = "error";
  }
}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Tax And Discount</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <!-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Starter Page</li> -->
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12">
          <div class="card card-warning card-outline">
            <div class="card-header">
              <h5 class="m-0">Tax And Discount Form</h5>
            </div>
            <form action="" method="post">
              <div class="card-body">
                <div class="row">
                  <?php
                  if (isset($_POST["btnedit"])) {
                    $select = $pdo->prepare("SELECT * FROM tbl_taxdis WHERE taxdis_id = " . $_POST["btnedit"]);
                    $select->execute();
                    if ($select) {
                      $row = $select->fetch(PDO::FETCH_ASSOC);
                      echo '<div class="col-md-4">
                      <div class="form-group">
                        <input type="hidden" class="form-control" placeholder="Enter category" value="' . $row['taxdis_id'] . '" name="txtid">
                      </div>
                      <div class="form-group">
                      <label for="exampleInputEmail1">SGST(%) </label>
                      <input type="text" class="form-control" placeholder="Enter SGST" value="' . $row['sgst'] . '" name="txtsgst">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">CGST(%) </label>
                      <input type="text" class="form-control" placeholder="Enter CGST" value="' . $row['cgst'] . '" name="txtcgst">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Discount(%) </label>
                      <input type="text" class="form-control" placeholder="Enter Discount" value="' . $row['discount'] . '" name="txtdiscount">
                    </div>
                      <div class="card-footer">
                        <button type="submit" class="btn btn-info" name="btn_update">Update</button>
                      </div>
                    </div>';
                    }
                  } else {
                    echo '<div class="col-md-4">
                      <div class="form-group">
                        <label for="exampleInputEmail1">SGST(%) </label>
                        <input type="text" class="form-control" placeholder="Enter SGST" name="txtsgst">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">CGST(%) </label>
                        <input type="text" class="form-control" placeholder="Enter CGST" name="txtcgst">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Discount(%) </label>
                        <input type="text" class="form-control" placeholder="Enter Discount" name="txtdiscount">
                      </div>
                      <div class="card-footer">
                        <button type="submit" class="btn btn-warning" name="btn_save">Save</button>
                      </div>
                    </div>';
                  }
                  ?>

                  <div class="col-md-8">
                    <table id="tbl_tax" class="table table-striped">
                      <thead>
                        <tr>
                          <td>No.</td>
                          <td>SGST</td>
                          <td>CGST</td>
                          <td>Discount</td>
                          <td>Edit</td>
                          <td>Delete</td>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $select = $pdo->prepare('SELECT * FROM tbl_taxdis ORDER BY taxdis_id ASC');
                        $select->execute();
                        $row = $select->fetchAll(PDO::FETCH_OBJ);

                        foreach ($row as $key => $value) {
                          echo '
                        <tr>
                        <td>' . $value->taxdis_id . '</td>
                        <td>' . $value->sgst . '</td>
                        <td>' . $value->cgst . '</td>
                        <td>' . $value->discount . '</td>
                        <td>
                        <button type="submit" class="btn btn-primary" value="' . $value->taxdis_id . '" name="btnedit">Edit</button></td>
                        <td>
                        <button type="submit" class="btn btn-danger" value="' . $value->taxdis_id . '" name="btndelete">Delete</button></td>
                        </tr>';
                        }
                        ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td>No.</td>
                          <td>SGST</td>
                          <td>CGST</td>
                          <td>Discount</td>
                          <td>Edit</td>
                          <td>Delete</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        <!-- /.col-lg-12 -->

      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
include_once 'footer.php';
?>

<?php
if (isset($_SESSION['status']) && !empty($_SESSION['status'])) {
?>
  <script>
    Swal.fire({
      icon: '<?php echo $_SESSION['status_code']; ?>',
      title: '<?php echo $_SESSION['status']; ?>'
    });
  </script>
<?php
  unset($_SESSION['status']);
}
?>
<script>
  $(document).ready(function() {
    $('#tbl_tax').DataTable();
  });
</script>
