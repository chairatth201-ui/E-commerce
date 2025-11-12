<?php
session_start();
header('Content-Type: text/plain; charset=utf-8');
var_export($_SESSION);
