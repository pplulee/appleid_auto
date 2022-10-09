<?php
include("header.php");
if (isset($_GET['logout'])) {
    logout();
    echo "<script>window.location.href='index.php';</script>";
    exit();
}
$current_user = new user($_SESSION['user_id']);
?>
<div class="container" style="margin-top: 1%">
    <div class="card border-dark">
        <h4 class="card-header">用户中心</h4>
        <ul class="list-group">
            <li class="list-group-item">
                <b>用户ID:</b> <?php echo $current_user->user_id ?>
            </li>
        </ul>
    </div>
</div>