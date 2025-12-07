<?php
$password= 'admin';
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
echo $hashed_password;



$pa_code =null;
$hmo_code = '051';
$hmo_name= 'NONSUCH';
$staff_id = $_SESSION['id'];
$rand = rand(0000,9999);
$year = new(y-h-m);
$month =new(month);
$pa_code = $hmo_code.'/'.$hmo_name.'/'.$rand.'/'.$year.'/'$month.'/'.$staff_id.'/'.$last_id;