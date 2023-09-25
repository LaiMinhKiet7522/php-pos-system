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

error_reporting(0);
$id = $_GET['id'];
if(!empty($id)){
  $delete = $pdo->prepare("DELETE FROM tbl_user WHERE userid='$id'");
  if($delete->execute()){
    $_SESSION['status'] = 'Account deleted successfully';
    $_SESSION['status_code'] = 'success';
  }else{
    $_SESSION['status']="Account Is Not Deleted";
    $_SESSION['status_code']="warning";
  }
}


if (isset($_POST['btn_save'])) {
    $username = $_POST['txtname'];
    $useremail = $_POST['txtemail'];
    $userpassword = $_POST['txtpassword'];
    $userrole = $_POST['role'];

    if (isset($_POST['txtemail'])) {
        $select = $pdo->prepare("SELECT useremail FROM tbl_user WHERE useremail='$useremail'");
        $select->execute();

        if ($select->rowCount() > 0) {
            $_SESSION['status'] = 'Email already exists. Create Account From New Email';
            $_SESSION['status_code'] = 'warning';
        } else {
            $insert = $pdo->prepare('INSERT INTO tbl_user (username, useremail, userpassword, role) VALUES (:name, :email, :password, :role)');
            $insert->bindParam(':name', $username);
            $insert->bindParam(':email', $useremail);
            $insert->bindParam(':password', $userpassword);
            $insert->bindParam(':role', $userrole);

            if ($insert->execute()) {
                $_SESSION['status'] = 'Insert successfully the user into the database';
                $_SESSION['status_code'] = 'success';
            } else {
                $_SESSION['status'] = 'Error inserting the user into the database';
                $_SESSION['status_code'] = 'error';
            }
        }
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
                    <h1 class="m-0">Registration</h1>
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
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h5 class="m-0">Registration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form action="" method="post">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Name</label>
                                    <input type="text" class="form-control" placeholder="Enter name" name="txtname"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Email address</label>
                                    <input type="email" class="form-control" placeholder="Enter email" name="txtemail"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Password</label>
                                    <input type="password" class="form-control" placeholder="Password"
                                        name="txtpassword" required>
                                </div>
                                <div class="form-group">
                                    <label>Role</label>
                                    <select class="form-control" name="role" required>
                                        <option value="" selected disabled>Select</option>
                                        <option value="Admin">Admin</option>
                                        <option value="User">User</option>
                                    </select>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" name="btn_save">Save</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <td>No.</td>
                                        <td>Name</td>
                                        <td>Email</td>
                                        <td>Role</td>
                                        <td>Delete</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $select = $pdo->prepare('SELECT * FROM tbl_user ORDER BY userid ASC');
                                    $select->execute();
                                    $row = $select->fetchAll(PDO::FETCH_OBJ);

                                    foreach ($row as $key => $value) {
                                        echo '
                                    <tr>
                                    <td>' .
                                            $value->userid .
                                            '</td>
                                    <td>' .
                                            $value->username .
                                            '</td>
                                    <td>' .
                                            $value->useremail .
                                            '</td>
                                    <td>' .
                                            $value->role .
                                            '</td>
                                    <td>

                                    <a href="registration.php?id=' .
                                            $value->userid .
                                            '" class="btn btn-danger"><i class="fa fa-trash-alt"></i></a>
                                    </td>


                                    </tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
include_once 'footer.php';
?>
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
