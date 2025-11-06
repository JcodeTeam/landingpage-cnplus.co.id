<?php
if (mail('pujamasjoo@gmail.com', 'Test', 'Test email CNPLUS')) { 
    echo "Mail() sukses!";
} else {
    echo "Mail() gagal.";
}
?>
