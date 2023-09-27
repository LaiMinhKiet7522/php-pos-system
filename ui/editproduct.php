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

$id = $_GET["id"];
$select = $pdo->prepare("SELECT * FROM tbl_product WHERE pid = $id");
$select->execute();
$row = $select->fetch(PDO::FETCH_ASSOC);

$id_db = $row['pid'];
$barcode_db = $row['barcode'];
$product_db = $row['product'];
$category_db = $row['category'];
$description_db = $row['description'];
$stock_db = $row['stock'];
$purchaseprice_db = $row['purchaseprice'];
$saleprice_db = $row['saleprice'];
$image_db = $row['image'];

if (isset($_POST["btn_update"])) {
  // $barcode_txt = $_POST["txtbarcode"];
  $product_txt = $_POST["txtproduct"];
  $category_txt = $_POST["category"];
  $description_txt = $_POST["txtdescription"];
  $stock_txt = $_POST["txtstock"];
  $purchaseprice_txt = $_POST["txtpurchaseprice"];
  $saleprice_txt = $_POST["txtsaleprice"];

  $f_name = $_FILES['productimage']['name'];

  if (!empty($f_name)) {
    $f_tmp = $_FILES['productimage']['tmp_name'];
    $f_size = $_FILES['productimage']['size'];
    $f_extension = explode('.', $f_name);
    $f_extension = strtolower(end($f_extension));
    $f_newfile = uniqid() . '.' . $f_extension;

    $store = "upload/" . $f_newfile;

    if ($f_extension == 'jpg' || $f_extension == 'png' || $f_extension == 'jpeg' || $f_extension == 'gif') {
      if ($f_size >= 1000000) {
        $_SESSION["status"] = 'Max file should be 1MB';
        $_SESSION["status_code"] = 'warning';
      } else {
        if (move_uploaded_file($f_tmp, $store)) {
          $update = $pdo->prepare("UPDATE tbl_product SET product=:product, category=:category, description=:description, stock=:stock, purchaseprice=:pprice , saleprice=:sprice , image=:image WHERE pid=$id");
          $update->bindParam(':product', $product_txt);
          $update->bindParam(':category', $category_txt);
          $update->bindParam(':description', $description_txt);
          $update->bindParam(':stock', $stock_txt);
          $update->bindParam(':pprice', $purchaseprice_txt);
          $update->bindParam(':sprice', $saleprice_txt);
          $update->bindParam(':image', $f_newfile);

          if ($update->execute()) {
            $_SESSION["status"] = 'Product Updated Successfully With New Image';
            $_SESSION["status_code"] = 'success';
          } else {
            $_SESSION["status"] = 'Product Updated Failed';
            $_SESSION["status_code"] = 'error';
          }
        }
      }
    }
  } else {
    $update = $pdo->prepare("UPDATE tbl_product SET product=:product, category=:category, description=:description, stock=:stock, purchaseprice=:pprice , saleprice=:sprice , image=:image WHERE pid=$id");
    $update->bindParam(':product', $product_txt);
    $update->bindParam(':category', $category_txt);
    $update->bindParam(':description', $description_txt);
    $update->bindParam(':stock', $stock_txt);
    $update->bindParam(':pprice', $purchaseprice_txt);
    $update->bindParam(':sprice', $saleprice_txt);
    $update->bindParam(':image', $image_db);

    if ($update->execute()) {
      $_SESSION["status"] = 'Product Updated Successfully';
      $_SESSION["status_code"] = 'success';
    } else {
      $_SESSION["status"] = 'Product Updated Failed';
      $_SESSION["status_code"] = 'error';
    }
  }
}

$select = $pdo->prepare("SELECT * FROM tbl_product WHERE pid = $id");
$select->execute();
$row = $select->fetch(PDO::FETCH_ASSOC);

$id_db = $row['pid'];
$barcode_db = $row['barcode'];
$product_db = $row['product'];
$category_db = $row['category'];
$description_db = $row['description'];
$stock_db = $row['stock'];
$purchaseprice_db = $row['purchaseprice'];
$saleprice_db = $row['saleprice'];
$image_db = $row['image'];

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Product</h1>
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
          <div class="card card-success card-outline">
            <div class="card-header">
              <h5 class="m-0">Edit Product</h5>
            </div>
            <form action="" method="post" enctype="multipart/form-data" name="formeditproduct">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Barcode</label>
                      <input type="text" class="form-control" value="<?php echo $barcode_db; ?>" placeholder="Enter Barcode" name="txtbarcode" disabled>
                    </div>
                    <div class="form-group">
                      <label>Product Name</label>
                      <input type="text" class="form-control" value="<?php echo $product_db; ?>" placeholder="Enter Product Name" name="txtproduct" required>
                    </div>
                    <div class="form-group">
                      <label>Category</label>
                      <select class="form-control" name="category" required>
                        <option value="" selected disabled>Select Category</option>
                        <?php
                        $select = $pdo->prepare("SELECT * FROM tbl_category ORDER BY catid ASC");
                        $select->execute();
                        $row = $select->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($row as $item) {
                        ?>
                          <option <?php if ($item['category'] == $category_db) { ?> selected="selected" <?php } ?>><?php echo $item['category']; ?></option>
                        <?php
                        }
                        ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Description</label>
                      <textarea class="form-control" placeholder="Enter Description" name="txtdescription" rows="4"><?php echo $description_db; ?></textarea>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Stock Quantity</label>
                      <input type="number" min="1" step="any" class="form-control" placeholder="Enter Stock" name="txtstock" value="<?php echo $stock_db; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Purchase Price</label>
                      <input type="number" min="1" step="any" class="form-control" placeholder="Enter Purchase Price" name="txtpurchaseprice" value="<?php echo $purchaseprice_db; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Sale Price</label>
                      <input type="number" min="1" step="any" class="form-control" placeholder="Enter Sale Price" name="txtsaleprice" value="<?php echo $saleprice_db; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Product Image</label><br>
                      <image src="upload/<?php echo $image_db; ?>" class="img-rounded mb-2" width="200px" height="200px">
                        <input type="file" class="input-group" name="productimage">
                        <p>Upload Image</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <div class="text-center">
                  <button type="submit" class="btn btn-success" name="btn_update">Update Product</button>
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
