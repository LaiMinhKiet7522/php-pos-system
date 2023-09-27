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
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h5 class="m-0">Product List</h5>
            </div>
            <div class="card-body">
              <table class="table table-striped" id="table_product">
                <thead>
                  <tr>
                    <td>Barcode</td>
                    <td>Product</td>
                    <td>Category</td>
                    <td>Description</td>
                    <td>Stock</td>
                    <td>PurchasePrice</td>
                    <td>SalePrice</td>
                    <td>Image</td>
                    <td>Actions</td>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $select = $pdo->prepare('SELECT * FROM tbl_product ORDER BY pid ASC');
                  $select->execute();
                  $row = $select->fetchAll(PDO::FETCH_OBJ);

                  foreach ($row as $key => $value) {
                    echo '
                    <tr>
                    <td>' . $value->barcode . '</td>
                    <td>' . $value->product . '</td>
                    <td>' . $value->category . '</td>
                    <td>' . $value->description . '</td>
                    <td>' . $value->stock . '</td>
                    <td>' . $value->purchaseprice . '</td>
                    <td>' . $value->saleprice . '</td>
                    <td> <image src="upload/' . $value->image . '" class="img-rounded" width="40px" height="40px"></td>
                    <td>

                    <div class="btn-group">

                    <a href="printbarcode.php?id=' . $value->pid . '" class="btn btn-dark btn-xs" role="button"><span class="fa fa-barcode" style="color:#ffffff" data-toggle="tooltip" title="PrintBarcode"></span></a>


                    <a href="viewproduct.php?id=' . $value->pid . '" class="btn btn-warning btn-xs" role="button"><span class="fa fa-eye" style="color:#ffffff" data-toggle="tooltip" title="View Product"></span></a>


                    <a href="editproduct.php?id=' . $value->pid . '" class="btn btn-success btn-xs" role="button"><span class="fa fa-edit" style="color:#ffffff" data-toggle="tooltip" title="Edit Product"></span></a>

                    <button id=' . $value->pid . '  class="btn btn-danger btn-xs btndelete"><span class="fa fa-trash" style="color:#ffffff" data-toggle="tooltip" title="Delete Product"></span></button>


                    </div>

                    </td>
                    </tr>';
                  }
                  ?>
                </tbody>
              </table>
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

<script>
  $(document).ready(function() {
    $('#table_product').DataTable();
  });
</script>

<script>
  $(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>
<script>
  $(document).ready(function() {
    $('.btndelete').click(function() {
      var tdh = $(this);
      var id = $(this).attr("id");

      Swal.fire({
        title: 'Do you want to delete?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {

          $.ajax({
            type: "post",
            url: "productdelete.php",
            data: {
              pidd: id
            },
            success: function(data) {
              tdh.parents('tr').hide();
            }
          });
          Swal.fire(
            'Deleted!',
            'Your Product has been deleted.',
            'success'
          )
        }
      })
    });
  });
</script>
