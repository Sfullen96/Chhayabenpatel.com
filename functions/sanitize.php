<?php 

// Escape data going in/coming out of DB
function escape($string) {
	return htmlentities($string, ENT_QUOTES, 'UTF-8');
}