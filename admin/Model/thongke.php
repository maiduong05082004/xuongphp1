<?php

function loadall_sp_of_genre(){
    $sql="SELECT c.genre_name, COUNT(p.product_id) AS product_count
    FROM genre c
    JOIN product p ON c.genre_id = p.genre_id
    GROUP BY c.genre_name";
    $listsanpham= pdo_query($sql);
    return $listsanpham;
}

?>