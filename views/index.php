<?php
require_once '../core/init.php';

$portfolioItem = new PortfolioItem;

$allItems = $portfolioItem->getAll();

var_dump($allItems);

require_once 'includes/header.php';
?>

<?php require_once 'includes/footer.php'; ?>