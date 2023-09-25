<?php
include_once 'connectdb.php';
session_start();
if (empty($_SESSION['useremail'])) {
  header('Location: ../index.php');
}
if($_SESSION['role'] == 'Admin'){
  include_once 'header.php';
}else{
  include_once 'headeruser.php';
}

if (isset($_POST['btn_update'])) {
    $oldpassword = $_POST['old_password'];
    $newpassword = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    $email = $_SESSION['useremail'];
    $select = $pdo->prepare("SELECT * FROM tbl_user WHERE useremail='$email'");
    $select->execute();
    $row = $select->fetch(PDO::FETCH_ASSOC);

    $useremail_db = $row['useremail'];
    $userpassword_db = $row['userpassword'];

    if ($oldpassword == $userpassword_db) {
        if ($newpassword == $confirm_new_password) {
            $update = $pdo->prepare('UPDATE tbl_user SET userpassword=:pass WHERE useremail=:email');
            $update->bindParam(':pass', $newpassword);
            $update->bindParam(':email', $email);
            if ($update->execute()) {
                $_SESSION['status'] = 'Password Updated Successfully';
                $_SESSION['status_code'] = 'success';
            } else {
                $_SESSION['status'] = 'Password Not Updated Successfully';
                $_SESSION['status_code'] = 'error';
            }
        } else {
            $_SESSION['status'] = 'New Password Does Not Matched';
            $_SESSION['status_code'] = 'error';
        }
    } else {
        $_SESSION['status'] = 'Old Password Does Not Matched';
        $_SESSION['status_code'] = 'error';
    }
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Change Password</h1>
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
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Change Password</h3>
                        </div>
                        <!-- /.card-header -->
                        <!-- form start -->
                        <form class="form-horizontal" action="" method="post">
                            <div class="card-body">
                                <div class="form-group row">
                                    <label for="inputPassword3" class="col-sm-2 col-form-label">Old Password</label>
                                    <div class="col-sm-10 input-group" id="show_hide_old_password">
                                        <input type="password" class="form-control" placeholder="Old Password..."
                                            name="old_password">
                                        <a href="javascript:;" class="input-group-text bg-transparent"><i
                                                class='fa-solid fa-eye-slash'></i></a>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputPassword3" class="col-sm-2 col-form-label">New Password</label>
                                    <div class="col-sm-10 input-group" id="show_hide_password">
                                        <input type="password" class="form-control" placeholder="New Password..."
                                            name="new_password">
                                        <a href="javascript:;" class="input-group-text bg-transparent"><i
                                                class='fa-solid fa-eye-slash'></i></a>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputPassword3" class="col-sm-2 col-form-label">Retype New
                                        Password</label>
                                    <div class="col-sm-10 input-group" id="show_hide_confirm_password">
                                        <input type="password" class="form-control" placeholder="Retype New Password..."
                                            name="confirm_new_password">
                                        <a href="javascript:;" class="input-group-text bg-transparent"><i
                                                class='fa-solid fa-eye-slash'></i></a>
                                    </div>
                                </div>

                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer">
                                <button type="submit" class="btn btn-info float-right" name="btn_update">Update
                                    Password</button>
                            </div>
                            <!-- /.card-footer -->
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
<script>
    $(document).ready(function() {
        $("#show_hide_old_password a").on('click', function(event) {
            event.preventDefault();
            if ($('#show_hide_old_password input').attr("type") == "text") {
                $('#show_hide_old_password input').attr('type', 'password');
                $('#show_hide_old_password i').addClass("fa-eye-slash");
                $('#show_hide_old_password i').removeClass("fa-eye");
            } else if ($('#show_hide_old_password input').attr("type") == "password") {
                $('#show_hide_old_password input').attr('type', 'text');
                $('#show_hide_old_password i').removeClass("fa-eye-slash");
                $('#show_hide_old_password i').addClass("fa-eye");
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $("#show_hide_password a").on('click', function(event) {
            event.preventDefault();
            if ($('#show_hide_password input').attr("type") == "text") {
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass("fa-eye-slash");
                $('#show_hide_password i').removeClass("fa-eye");
            } else if ($('#show_hide_password input').attr("type") == "password") {
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass("fa-eye-slash");
                $('#show_hide_password i').addClass("fa-eye");
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $("#show_hide_confirm_password a").on('click', function(event) {
            event.preventDefault();
            if ($('#show_hide_confirm_password input').attr("type") == "text") {
                $('#show_hide_confirm_password input').attr('type', 'password');
                $('#show_hide_confirm_password i').addClass("fa-eye-slash");
                $('#show_hide_confirm_password i').removeClass("fa-eye");
            } else if ($('#show_hide_confirm_password input').attr("type") == "password") {
                $('#show_hide_confirm_password input').attr('type', 'text');
                $('#show_hide_confirm_password i').removeClass("fa-eye-slash");
                $('#show_hide_confirm_password i').addClass("fa-eye");
            }
        });
    });
</script>

<?php
include_once 'footer.php';
?>

<?php
  if(isset($_SESSION['status']) && !empty($_SESSION['status'])){
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
