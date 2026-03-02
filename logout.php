<?php
// logout.php (في المجلد الرئيسي)
session_start();

// 1. تفريغ session
session_unset();

// 2. تدمير session
session_destroy();

// 3. حذف cookie (نضع تاريخ منتهي الصلاحية)
setcookie('last_login', '', time() - 3600, '/');

// 4. التوجيه إلى login مع رسالة
header('Location: login.php?msg=deconnecte');
exit();
?>