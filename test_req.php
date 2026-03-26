<?php
$url = "http://127.0.0.1:8000/admin/buildywebadmin/Login?=AdminPanel";
$headers = get_headers($url);
print_r($headers);
