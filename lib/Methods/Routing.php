<?php

function redirect($loc) {
	echo '<script language="Javascript">; window.location="' . $loc . '"; </script>';
	echo '<META HTTP-EQUIV="Refresh" Content="0; URL=' . $loc . '">';
	exit();
}