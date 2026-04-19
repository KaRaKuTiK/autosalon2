<?php require 'config.php'; $stmt = $pdo->query('SHOW COLUMNS FROM cars'); foreach($stmt->fetchAll() as $col) echo $col['Field'].' '; 
