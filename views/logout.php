<?php 

session_start();

session_destroy();

session_regenerate_id();

redirect('/');
exit;