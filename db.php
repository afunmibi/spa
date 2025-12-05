<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'spa');
if (mysqli_connect_error()) {
   echo 'Connection to database failed: ' . mysqli_connect_error();
}else {
   echo 'Connection successful';
}