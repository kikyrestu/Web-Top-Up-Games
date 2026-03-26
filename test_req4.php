<?php
$url = "http://127.0.0.1:8000/admin/buildywebadmin/Login";
$headers = get_headers($url);
print_r($headers[0]);
