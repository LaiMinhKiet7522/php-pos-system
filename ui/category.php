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
  $category = $_POST["txtcategory"];
  if (empty($category)) {
    $_SESSION["status"] = 'Category Field is Empty';
    $_SESSION["status_code"] = 'warning';
  } else {
    $insert = $pdo->prepare("INSERT INTO tbl_category (category) VALUES (:cat)");
    $insert->bindParam(':cat', $category);

    if ($insert->execute()) {
      $_SESSION["status"] = 'Category Added Successfully';
      $_SESSION["status_code"] = 'success';
    } else {
      $_SESSION["status"] = 'Category Added Failed';
      $_SESSION["status_code"] = 'error';
    }
  }
}

if (isset($_POST["btn_update"])) {
  $category = $_POST["txtcategory"];
  $category_id = $_POST["txtcatid"];

  if (empty($category)) {
    $_SESSION["status"] = 'Category Field is Empty';
    $_SESSION["status_code"] = 'warning';
  } else {
    $update = $pdo->prepare("UPDATE tbl_category SET category='$category' WHERE catid='$category_id'");
    if ($update->execute()) {
      $_SESSION["status"] = 'Category Updated Successfully';
      $_SESSION["status_code"] = 'success';
    } else {
      $_SESSION['status'] = "Category Update Failed";
      $_SESSION['status_code'] = "error";
    }
  }
}
if (isset($_POST["btndelete"])) {
  $delete = $pdo->prepare("DELETE FROM tbl_category WHERE catid=" . $_POST["btndelete"]);

  if ($delete->execute()) {
    $_SESSION['status'] = "Category Deleted";
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
          <h1 class="m-0">Category</h1>
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
              <h5 class="m-0">Category Form</h5>
            </div>
            <div class="card-body">
              <form action="" method="post">
                <div class="row">
                  <?php
                  if (isset($_POST["btnedit"])) {
                    $select = $pdo->prepare("SELECT * FROM tbl_category WHERE catid = " . $_POST["btnedit"]);
                    $select->execute();
                    if ($select) {
                      $row = $select->fetch(PDO::FETCH_ASSOC);
                      echo '<div class="col-md-4">
                      <div class="form-group">
                        <label for="exampleInputEmail1">Category </label>
                        <input type="hidden" class="form-control" placeholder="Enter category" value="' . $row['catid'] . '" name="txtcatid">
                        <input type="text" class="form-control" placeholder="Enter category" value="' . $row['category'] . '" name="txtcategory">
                      </div>

                      <div class="card-footer">
                        <button type="submit" class="btn btn-info" name="btn_update">Update</button>
                      </div>
                    </div>';
                    }
                  } else {
                    echo '<div class="col-md-4">
                      <div class="form-group">
                        <label for="exampleInputEmail1">Category </label>
                        <input type="text" class="form-control" placeholder="Enter category" name="txtcategory">
                      </div>

                      <div class="card-footer">
                        <button type="submit" class="btn btn-warning" name="btn_save">Save</button>
                      </div>
                    </div>';
                  }
                  ?>

                  <div class="col-md-8">
                    <table id="tbl_category" class="table table-striped">
                      <thead>
                        <tr>
                          <td>No.</td>
                          <td>Category</td>
                          <td>Edit</td>
                          <td>Delete</td>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $select = $pdo->prepare('SELECT * FROM tbl_category ORDER BY catid ASC');
                        $select->execute();
                        $row = $select->fetchAll(PDO::FETCH_OBJ);

                        foreach ($row as $key => $value) {
                          echo '
                        <tr>
                        <td>' . $value->catid . '</td>
                        <td>' . $value->category . '</td>
                        <td>
                        <button type="submit" class="btn btn-primary" value="' . $value->catid . '" name="btnedit">Edit</button></td>
                        <td>
                        <button type="submit" class="btn btn-danger" value="' . $value->catid . '" name="btndelete">Delete</button></td>
                        </tr>';
                        }
                        ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td>No.</td>
                          <td>Category</td>
                          <td>Edit</td>
                          <td>Delete</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </form>
            </div>
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
    $('#tbl_category').DataTable();
  });
</script>
