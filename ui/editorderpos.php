<?php
include_once 'connectdb.php';
ob_start();
session_start();
if (empty($_SESSION['useremail'])) {
  header('Location: ../index.php');
}
if ($_SESSION['role'] == 'Admin') {
  include_once 'header.php';
} else {
  include_once 'headeruser.php';
}

function fill_product($pdo)
{
  $output = '';
  $select = $pdo->prepare("SELECT * FROM tbl_product ORDER BY product ASC");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);

  foreach ($result as $row) {
    $output .= '<option value="' . $row['pid'] . '">' . $row['product'] . '</option>';
  }
  return $output;
}

$id = $_GET["id"];

$select = $pdo->prepare("select * from tbl_invoice where invoice_id=$id");
$select->execute();
$row = $select->fetch(PDO::FETCH_ASSOC);

$order_date = date('Y-m-d', strtotime($row['order_date']));
$subtotal     = $row['subtotal'];
$sgst         = $row['sgst'];
$cgst         = $row['cgst'];
$discount     = $row['discount'];
$total        = $row['total'];
$paid         = $row['paid'];
$due          = $row['due'];
$payment_type = $row['payment_type'];

$select=$pdo->prepare("select * from tbl_invoice_details where invoice_id=$id");
$select->execute();
$row_invoice_details=$select->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['btnupdateorder'])) {

  //Get values from text fields and from array in variables.
  $txt_orderdate     = date('Y-m-d');
  $txt_subtotal      = $_POST['txtsubtotal'];
  $txt_discount      = $_POST['txtdiscount'];
  $txt_sgst          = $_POST['txtsgst'];
  $txt_cgst          = $_POST['txtcgst'];
  $txt_total         = $_POST['txttotal'];
  $txt_payment_type  = $_POST['rb'];
  $txt_due           = $_POST['txtdue'];
  $txt_paid          = $_POST['txtpaid'];


  $arr_pid     = $_POST['pid_arr'];
  $arr_barcode = $_POST['barcode_arr'];
  $arr_name    = $_POST['product_arr'];
  $arr_stock   = $_POST['stock_c_arr'];
  $arr_qty     = $_POST['quantity_arr'];
  $arr_price   = $_POST['price_c_arr'];
  $arr_total   = $_POST['saleprice_arr'];

  //Write update query for tbl_product add stock.
  foreach ($row_invoice_details as $product_invoice_details) {
    $updateproduct_stock = $pdo->prepare("update tbl_product set stock=stock+" . $product_invoice_details['qty'] . " where pid='" . $product_invoice_details['product_id'] . "'");
    $updateproduct_stock->execute();
  }

  //Write delete query for tbl_invoice_details table data where invoice_id =$id .
  $delete_invoice_details = $pdo->prepare("delete from tbl_invoice_details where invoice_id =$id");
  $delete_invoice_details->execute();

  //Write update query for tbl_invoice table data.
  $update_tbl_invoice = $pdo->prepare("update tbl_invoice SET order_date=:orderdate,subtotal=:subtotal,discount=:discount,sgst=:sgst,cgst=:cgst,total=:total,payment_type=:payment_type,due=:due,paid=:paid where invoice_id=$id");

  $update_tbl_invoice->bindParam(':orderdate', $txt_orderdate);
  $update_tbl_invoice->bindParam(':subtotal', $txt_subtotal);
  $update_tbl_invoice->bindParam(':discount', $txt_discount);
  $update_tbl_invoice->bindParam(':sgst', $txt_sgst);
  $update_tbl_invoice->bindParam(':cgst', $txt_cgst);
  $update_tbl_invoice->bindParam(':total', $txt_total);
  $update_tbl_invoice->bindParam(':payment_type', $txt_payment_type);
  $update_tbl_invoice->bindParam(':due', $txt_due);
  $update_tbl_invoice->bindParam(':paid', $txt_paid);

  $update_tbl_invoice->execute();

  $invoice_id = $pdo->lastInsertId();
  if ($invoice_id != null) {

    //Write select query for tbl_product table to get out stock value.
    for ($i = 0; $i < count($arr_pid); $i++) {
      $selectpdt = $pdo->prepare("select * from tbl_product where pid='" . $arr_pid[$i] . "'");
      $selectpdt->execute();
      while ($rowpdt = $selectpdt->fetch(PDO::FETCH_OBJ)) {
        $db_stock[$i] = $rowpdt->stock;
        $rem_qty = $db_stock[$i] - $arr_qty[$i];
        if ($rem_qty < 0) {
          return "Order is not completed";
        } else {
          //Write update query for tbl_product table to update stock values.
          $update = $pdo->prepare("update tbl_product SET stock='$rem_qty' where pid='" . $arr_pid[$i] . "'");
          $update->execute();
        }
      }
      //Write insert query for tbl_invoice_details for insert new records.
      $insert = $pdo->prepare("insert into tbl_invoice_details (invoice_id,barcode,product_id,product_name,qty,rate,saleprice,order_date) values (:invid,:barcode,:pid,:name,:qty,:rate,:saleprice,:order_date)");
      $insert->bindParam(':invid', $id);
      $insert->bindParam(':barcode', $arr_barcode[$i]);
      $insert->bindParam(':pid', $arr_pid[$i]);
      $insert->bindParam(':name', $arr_name[$i]);
      $insert->bindParam(':qty', $arr_qty[$i]);
      $insert->bindParam(':rate', $arr_price[$i]);
      $insert->bindParam(':saleprice', $arr_total[$i]);
      $insert->bindParam(':order_date', $txt_orderdate);
      if (!$insert->execute()) {
        print_r($insert->errorInfo());
      }
    }
    header('location:orderlist.php');
  }
}
ob_end_flush();
$select = $pdo->prepare("SELECT * FROM tbl_taxdis WHERE taxdis_id = 1");
$select->execute();
$row = $select->fetch(PDO::FETCH_OBJ);
?>
<style type="text/css">
  .tableFixHead {
    overflow: scroll;
    height: 520px;
  }

  .tableFixHead thead th {
    position: sticky;
    top: 0;
    z-index: 1;
  }

  table {
    border-collapse: collapse;
    width: 100px;
  }

  th,
  td {
    padding: 8px 16px;
  }

  th {
    background: #eee;
  }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Point Of Sale</h1>
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
              <h5 class="m-0">POS</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-8">
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                    </div>
                    <input type="text" class="form-control" placeholder="Scan Barcode" name="txtbarcode" id="txtbarcode_id">
                  </div>
                  <form action="" method="post" name="">
                    <select class="form-control select2" data-dropdown-css-class="select2-purple" style="width: 100%;">
                      <option selected disabled>Select OR Search</option>
                      <?php
                      echo fill_product($pdo);
                      ?>
                    </select>
                    <br>
                    <div class="tableFixHead">
                      <table id="producttable" class="table table-bordered table-striped">
                        <thead>
                          <tr class="text-center">
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Delete</th>
                          </tr>
                        </thead>
                        <tbody class="details" id="itemtable">
                          <tr data-widget="expandable-table" aria-expanded="false">

                          </tr>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                </div>
                <div class="col-md-4">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Subtotal</span>
                    </div>
                    <input type="text" class="form-control" id="txtsubtotal_id" name="txtsubtotal" value="<?php echo $subtotal; ?>" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">$</span>
                    </div>
                  </div>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Discount</span>
                    </div>
                    <input type="text" class="form-control" id="txtdiscount_p" name="txtdiscount" value="<?php echo $row->discount; ?>">
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Discount</span>
                    </div>
                    <input type="text" class="form-control" id="txtdiscount_n" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">$</span>
                    </div>
                  </div>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">SGST</span>
                    </div>
                    <input type="text" class="form-control" id="txtsgst_id_p" name="txtsgst" value="<?php echo $row->sgst; ?>" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">CGST</span>
                    </div>
                    <input type="text" class="form-control" id="txtcgst_id_p" name="txtcgst" value="<?php echo $row->cgst; ?>" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">SGST</span>
                    </div>
                    <input type="text" class="form-control" id="txtsgst_id_n" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">$</span>
                    </div>
                  </div>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">CGST</span>
                    </div>
                    <input type="text" class="form-control" id="txtcgst_id_n" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">$</span>
                    </div>
                  </div>
                  <hr style="height:2px; border-width:0; color:black; background-color:black;">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Total</span>
                    </div>
                    <input type="text" class="form-control form-control-lg total" id="txttotal" name="txttotal" value="<?php echo $total; ?>" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">$</span>
                    </div>
                  </div>
                  <hr style="height:2px; border-width:0; color:black; background-color:black;">
                  <div class="icheck-success d-inline">
                    <input type="radio" name="rb" value="Cash" <?php echo ($payment_type == 'Cash') ? 'checked' : ''; ?> id="radioSuccess1">
                    <label for="radioSuccess1">
                      Cash
                    </label>
                  </div>
                  <div class="icheck-primary d-inline">
                    <input type="radio" name="rb" value="Card" <?php echo ($payment_type == 'Cash') ? 'checked' : ''; ?> id="radioSuccess2">
                    <label for="radioSuccess2">
                      Card
                    </label>
                  </div>
                  <div class="icheck-danger d-inline">
                    <input type="radio" name="rb" value="Check" <?php echo ($payment_type == 'Cash') ? 'checked' : ''; ?> id="radioSuccess3">
                    <label for="radioSuccess3">
                      Check
                    </label>
                  </div>
                  <hr style="height:2px; border-width:0; color:black; background-color:black;">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Due</span>
                    </div>
                    <input type="text" class="form-control" id="txtdue" name="txtdue" value="<?php echo $due; ?>" readonly>
                    <div class="input-group-append">
                      <span class="input-group-text">$</span>
                    </div>
                  </div>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Paid</span>
                    </div>
                    <input type="text" class="form-control" id="txtpaid" name="txtpaid" value="<?php echo $paid; ?>" require>
                    <div class="input-group-append">
                      <span class="input-group-text">$</span>
                    </div>
                  </div>
                  <hr style="height:2px; border-width:0; color:black; background-color:black;">
                  <div class="card-footer text-center">
                    <div class="text-center">
                      <button type="submit" class="btn btn-info" name="btnupdateorder">Update Order</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </form>
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
  //Initialize Select2 Elements
  $('.select2').select2()
  //Initialize Select2 Elements
  $('.select2bs4').select2({
    theme: 'bootstrap4'
  })

  var productArr = [];

  $.ajax({
    type: "get",
    url: "getorderproduct.php",
    data: {
      id: <?php echo $_GET['id']; ?>
    },
    dataType: "json",
    success: function(data) {
      // alert('pid');
      console.log(data);
      $.each(data, function(key, data) {
        if (jQuery.inArray(data['product_id'], productArr) !== -1) {
          var actualqty = parseInt($('#qty_id' + data["product_id"]).val()) + 1;
          $('#qty_id' + data["product_id"]).val(actualqty);

          var saleprice = parseInt(actualqty) * data["saleprice"];

          $('#saleprice_id' + data["product_id"]).html(saleprice);
          $('#saleprice_idd' + data["product_id"]).val(saleprice);

          calculate(0, 0);

        } else {

          addrow(data["product_id"], data["product_name"], data["qty"], data["rate"], data["saleprice"], data["stock"], data["barcode"]);

          productArr.push(data["product_id"]);

          // $("#txtbarcode_id").val("");

          function addrow(product_id, product_name, qty, rate, saleprice, stock, barcode) {

            var tr = '<tr>' +

              '<input type="hidden" class="form-control barcode" name="barcode_arr[]" id="barcode_id' + barcode + '" value="' + barcode + '" >' +

              '<td style="text-align:left; vertical-align:middle; font-size:17px;"><class="form-control product_c" name="product_arr[]" <span class="badge badge-dark">' + product_name + '</span><input type="hidden" class="form-control pid" name="pid_arr[]" value="' + product_id + '" ><input type="hidden" class="form-control product" name="product_arr[]" value="' + product_name + '" >  </td>' +

              '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-primary stocklbl" name="stock_arr[]" id="stock_id' + product_id + '">' + stock + '</span><input type="hidden" class="form-control stock_c" name="stock_c_arr[]" id="stock_idd' + product_id + '" value="' + stock + '"></td>' +

              '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-warning price" name="price_arr[]" id="price_id' + product_id + '">' + saleprice + '</span><input type="hidden" class="form-control price_c" name="price_c_arr[]" id="price_idd' + product_id + '" value="' + saleprice + '"></td>' +

              '<td><input type="text" class="form-control qty" name="quantity_arr[]" id="qty_id' + product_id + '" value="' + qty + '" size="1"></td>' +

              '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-success totalamt" name="netamt_arr[]" id="saleprice_id' + product_id + '">' + rate * qty + '</span><input type="hidden" class="form-control saleprice" name="saleprice_arr[]" id="saleprice_idd' + product_id + '" value="' + rate * qty + '"></td>' +

              //remove button code start here

              // '<td style="text-align:left; vertical-align:middle;"><center><name="remove" class"btnremove" data-id="'+pid+'"><span class="fas fa-trash" style="color:red"></span></center></td>'+
              // '</tr>';

              '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="' + product_id + '"><span class="fas fa-trash"></span></center></td>' +


              '</tr>';

            $('.details').append(tr);
            calculate(0, 0);
          } //end function addrow
        }
      });
    }
  });

  $(function() {
    $('#txtbarcode_id').on('change', function() {
      var barcode = $('#txtbarcode_id').val();
      $.ajax({
        type: "get",
        url: "getproduct.php",
        data: {
          id: barcode,
        },
        dataType: "json",
        success: function(data) {
          // alert('pid');
          console.log(data);
          if (jQuery.inArray(data['pid'], productArr) !== -1) {
            var actualqty = parseInt($('#qty_id' + data["pid"]).val()) + 1;
            $('#qty_id' + data["pid"]).val(actualqty);

            var saleprice = parseInt(actualqty) * data["saleprice"];

            $('#saleprice_id' + data["pid"]).html(saleprice);
            $('#saleprice_idd' + data["pid"]).val(saleprice);

            calculate(0, 0);
            $("#txtpaid").val("");
            $("#txtdue").val("");

          } else {

            addrow(data["pid"], data["product"], data["saleprice"], data["stock"], data["barcode"]);

            productArr.push(data["pid"]);

            $("#txtbarcode_id").val("");

            function addrow(pid, product, saleprice, stock, barcode) {

              var tr = '<tr>' +

                '<input type="hidden" class="form-control barcode" name="barcode_arr[]" id="barcode_id' + barcode + '" value="' + barcode + '" >' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><class="form-control product_c" name="product_arr[]" <span class="badge badge-dark">' + product + '</span><input type="hidden" class="form-control pid" name="pid_arr[]" value="' + pid + '" ><input type="hidden" class="form-control product" name="product_arr[]" value="' + product + '" >  </td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-primary stocklbl" name="stock_arr[]" id="stock_id' + pid + '">' + stock + '</span><input type="hidden" class="form-control stock_c" name="stock_c_arr[]" id="stock_idd' + pid + '" value="' + stock + '"></td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-warning price" name="price_arr[]" id="price_id' + pid + '">' + saleprice + '</span><input type="hidden" class="form-control price_c" name="price_c_arr[]" id="price_idd' + pid + '" value="' + saleprice + '"></td>' +

                '<td><input type="text" class="form-control qty" name="quantity_arr[]" id="qty_id' + pid + '" value="' + 1 + '" size="1"></td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-success totalamt" name="netamt_arr[]" id="saleprice_id' + pid + '">' + saleprice + '</span><input type="hidden" class="form-control saleprice" name="saleprice_arr[]" id="saleprice_idd' + pid + '" value="' + saleprice + '"></td>' +

                '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="' + pid + '"><span class="fas fa-trash"></span></center></td>' +

                '</tr>';

              $('.details').append(tr);

              calculate(0, 0);
              $("#txtpaid").val("");
              $("#txtdue").val("");
            } //end function addrow
          }
        }
      });
    });
  });



  var productArr = [];
  $(function() {
    $('.select2').on('change', function() {
      var product_id = $('.select2').val();
      $.ajax({
        type: "get",
        url: "getproduct.php",
        data: {
          id: product_id,
        },
        dataType: "json",
        success: function(data) {
          // alert('pid');
          console.log(data);
          if (jQuery.inArray(data['pid'], productArr) !== -1) {
            var actualqty = parseInt($('#qty_id' + data["pid"]).val()) + 1;
            $('#qty_id' + data["pid"]).val(actualqty);

            var saleprice = parseInt(actualqty) * data["saleprice"];

            $('#saleprice_id' + data["pid"]).html(saleprice);
            $('#saleprice_idd' + data["pid"]).val(saleprice);

            calculate(0, 0);
            $("#txtpaid").val("");
            $("#txtdue").val("");

          } else {

            addrow(data["pid"], data["product"], data["saleprice"], data["stock"], data["barcode"]);

            productArr.push(data["pid"]);

            $("#txtbarcode_id").val("");

            function addrow(pid, product, saleprice, stock, barcode) {

              var tr = '<tr>' +

                '<input type="hidden" class="form-control barcode" name="barcode_arr[]" id="barcode_id' + barcode + '" value="' + barcode + '" >' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><class="form-control product_c" name="product_arr[]" <span class="badge badge-dark">' + product + '</span><input type="hidden" class="form-control pid" name="pid_arr[]" value="' + pid + '" ><input type="hidden" class="form-control product" name="product_arr[]" value="' + product + '" >  </td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-primary stocklbl" name="stock_arr[]" id="stock_id' + pid + '">' + stock + '</span><input type="hidden" class="form-control stock_c" name="stock_c_arr[]" id="stock_idd' + pid + '" value="' + stock + '"></td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-warning price" name="price_arr[]" id="price_id' + pid + '">' + saleprice + '</span><input type="hidden" class="form-control price_c" name="price_c_arr[]" id="price_idd' + pid + '" value="' + saleprice + '"></td>' +

                '<td><input type="text" class="form-control qty" name="quantity_arr[]" id="qty_id' + pid + '" value="' + 1 + '" size="1"></td>' +

                '<td style="text-align:left; vertical-align:middle; font-size:17px;"><span class="badge badge-success totalamt" name="netamt_arr[]" id="saleprice_id' + pid + '">' + saleprice + '</span><input type="hidden" class="form-control saleprice" name="saleprice_arr[]" id="saleprice_idd' + pid + '" value="' + saleprice + '"></td>' +

                '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove" data-id="' + pid + '"><span class="fas fa-trash"></span></center></td>' +

                '</tr>';

              $('.details').append(tr);

              calculate(0, 0);
              $("#txtpaid").val("");
              $("#txtdue").val("");
            } //end function addrow
          }
        }
      });
    });
  });

  $("#itemtable").delegate(".qty", "keyup change", function() {

    var quantity = $(this);
    var tr = $(this).parent().parent();

    if ((quantity.val() - 0) > (tr.find(".stock_c").val() - 0)) {

      Swal.fire("WARNING!", "SORRY! This Much Of Quantity Is Not Available", "warning");
      quantity.val(1);

      tr.find(".totalamt").text(quantity.val() * tr.find(".price").text());

      tr.find(".saleprice").val(quantity.val() * tr.find(".price").text());
      calculate(0, 0);
      $("#txtpaid").val("");
      $("#txtdue").val("");
    } else {
      tr.find(".totalamt").text(quantity.val() * tr.find(".price").text());

      tr.find(".saleprice").val(quantity.val() * tr.find(".price").text());
      calculate(0, 0);
      $("#txtpaid").val("");
      $("#txtdue").val("");
    }
  });

  function calculate(dis, paid) {

    var subtotal = 0;
    var discount = dis;
    var sgst = 0;
    var cgst = 0;
    var total = 0;
    var paid_amt = paid;
    var due = 0;

    $(".saleprice").each(function() {

      subtotal = subtotal + ($(this).val() * 1);
    });

    $("#txtsubtotal_id").val(subtotal.toFixed(2));

    sgst = parseFloat($("#txtsgst_id_p").val());

    cgst = parseFloat($("#txtcgst_id_p").val());

    discount = parseFloat($("#txtdiscount_p").val());

    sgst = sgst / 100;
    sgst = sgst * subtotal;

    cgst = cgst / 100;
    cgst = cgst * subtotal;

    discount = discount / 100;
    discount = discount * subtotal;

    $("#txtsgst_id_n").val(sgst.toFixed(2));

    $("#txtcgst_id_n").val(cgst.toFixed(2));

    $("#txtdiscount_n").val(discount.toFixed(2));

    total = sgst + cgst + subtotal - discount;
    due = total - paid_amt;

    $("#txttotal").val(total.toFixed(2));

    paid_db = parseFloat($("#txtpaid").val());
    due_db = paid_db - total;

    $("#txtdue").val(due_db.toFixed(2));

  }

  $('#txtdiscount_p').keyup(function() {
    var discount = $(this).val();
    calculate(discount, 0);
  });

  $('#txtpaid').keyup(function() {
    var paid = $(this).val();
    var discount = $('#txtdiscount_p').val();
    calculate(discount, paid);
  });

  $(document).on('click', '.btnremove', function() {

    var removed = $(this).attr("data-id");
    productarr = jQuery.grep(productArr, function(value) {

      return value != removed;
      calculate(0, 0);
      $("#txtpaid").val("");
      $("#txtdue").val("");
    })

    $(this).closest('tr').remove();
    calculate(0, 0);
    $("#txtpaid").val("");
    $("#txtdue").val("");
  });
</script>
