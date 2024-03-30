<?php
include "client/controllers/user.php";
if (!isset($_SESSION['mycart'])) {
    $_SESSION['mycart'] = array();
}
if (isset($_GET['act']) && ($_GET['act'] != "")) {
    $act = $_GET['act'];
    switch ($act) {
        case 'sanphamct':
            if (isset($_GET['idsp']) && ($_GET['idsp'] > 0)) {
                $onesp = loadone_sanpham($_GET['idsp']);
                $sp_cung_loai = load_sanpham_cungloai($_GET['idsp'], $onesp['genre_id']);
                $listbinhluan = load_binhluan($_GET['idsp']);
                // $binhluan = load_binhluan($_GET['idsp']);
                include "client/products/chitietsp.php";
            } else {
                include "client/layout/home.php";
            }

            break;
        case 'sanpham':
            if (isset($_POST['listok']) && ($_POST['listok'] != 0)) {
                $kyw = $_POST['kyw'];
                $iddm = $_POST['iddm'];
                
            } else {
                $kyw = "";
                $iddm = 0;
            }
            $dssp = loadall_product($kyw, $iddm);
            $tendm = loadall_genre();
            include 'client/products/sanpham.php';
            break;

       
        case "login":
            if (isset($_POST['loginaccount']) && ($_POST['loginaccount'])) {
                $user = $_POST['nameaccount'];
                $password = $_POST['password'];
                $checkuser = checkuser($user, $password);
                if (is_array($checkuser)) {
                    // Kiểm tra role của người dùng, nếu bằng 4 thì thông báo tài khoản bị chặn
                    if ($checkuser['role'] == 4) {
                        $thongbao = "Tài khoản của bạn đã bị chặn !!";
                    } else {
                        // Nếu không bị chặn, lưu thông tin người dùng vào phiên làm việc và chuyển hướng
                        $_SESSION['user'] = $checkuser;
                        if ($_SESSION['user']['role'] == 1 || $_SESSION['user']['role'] == 2) {
                            header('location:admin/index.php?act=home');
                        } else {
                            header('location:index.php?act=home');
                        }
                        exit; // Thêm exit để ngăn không chạy thêm mã sau khi chuyển hướng
                    }
                } else {
                    $thongbao = "Tài khoản hoặc mật khẩu không đúng!";
                }
            }
            include "client/taikhoan/login.php";
            break;

        case "register":
            if (isset($_POST['addaccount']) && ($_POST['addaccount'])) {
                $user = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                $email = $_POST['email'] ?? '';
                $accname = $_POST['accname'] ?? '';
                $role = $_POST['role'] ?? '2'; // 2 có thể là giá trị mặc định cho người dùng
                $tel = $_POST['tel'] ?? '';
                $address = $_POST['address'] ?? '';
                if ($password !== $confirm_password) {
                    $thongbao = "Mật khẩu và mật khẩu xác nhận không khớp.";
                } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*]).{8,}$/", $password)) {
                    $thongbao = "Mật khẩu phải bao gồm ít nhất 8 ký tự, bao gồm chữ thường, chữ hoa, số và ký tự đặc biệt.";
                } elseif (empty($user) || empty($password) || empty($email)) {
                    $thongbao = "Vui lòng nhập đủ thông tin bắt buộc.";
                } else {
                    insert_account_user($user, $password, $email, $accname, $tel, $address, $role);
                    $thongbao = "Thêm tài khoản thành công.";
                }
            }
            include "client/taikhoan/register.php";
            break;
        case "accinfo":
            if (isset($_POST['capnhapuser'])) {
                $name = $_POST['name'];
                $email = $_POST['email'];
                $address = $_POST['address'];
                $tel = $_POST['tel'];
                $id = $_SESSION['user']['acc_id'];

                // Cập nhật thông tin tài khoản trong database
                update_capnhat_tk($id, $name, $email, $address, $tel);

                // Cập nhật thông tin trong session
                $_SESSION['user']['acc_name'] = $name;
                $_SESSION['user']['acc_email'] = $email;
                $_SESSION['user']['acc_address'] = $address;
                $_SESSION['user']['acc_tel'] = $tel;

                // Chuyển hướng người dùng về trang thông tin cá nhân
                header('Location: index.php?act=accinfo');
                exit();
            }
            // Lấy thông tin tài khoản từ session để hiển thị
            $list = $_SESSION['user'] ?? null;
            include "client/taikhoan/accinfo.php";
            break;
        case "comment":
            if (!isset($_SESSION['user'])) {
                header('Location: index.php?act=login');
                unset($_SESSION['selected_items']);
                unset($_SESSION['tongdonhang']);
                unset($_SESSION['mycart']);
                exit();
            }
            $acc_id = $_SESSION['user']['acc_id'];

            break;
        case "comment_add":
            $acc_id = $_SESSION['user']['acc_id']; 
            $product_id = $_POST['product_id']; 
            $content = $_POST['content']; 
            $date = date('Y-m-d H:i:s');
            $sql = "INSERT INTO comment (acc_id, product_id, content, date) VALUES (?, ?, ?, ?)";

            pdo_execute($sql, $acc_id, $product_id, $content, $date);

            header("Location: index.php?act=sanphamct&idsp=" . $product_id);
            exit();
            break;
        case "exit":
            session_unset();
            header('location:index.php?act=sanpham');
            break;
        default:
            include "client/layout/home.php";
            break;
    }
} else {
    include "client/layout/home.php";
}
if ($isAddToCartPage) {
    echo '<style>.none { display: none;}</style>';
}
include "client/layout/footer.php";
