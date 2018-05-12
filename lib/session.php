<?php
$lifeTime = 8*3600; 
session_set_cookie_params($lifeTime); 
session_start(); 
session_cache_expire($lifeTime);
?>