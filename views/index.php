<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once '../core/init.php';

$portfolioItem = new PortfolioItem;

$allItems = $portfolioItem->getAll();

require_once 'includes/header.php';

$count = 0;
?>
<h1>Chhayaben Patel</h1>
<div class="row">
    <?php foreach ($allItems as $item) { ?>
        <?php if ($count % 2 == 0) { ?>
            <div class="col-2">
                <img src=<?= $item->image ? '../uploads/' . $item->image : '' ?> alt="" class="img-responsive homepage-portfolio-image" width="100">
            </div>
            <div class="col-10">
                <h3><?= $item->title; ?></h3>
                <p><?= $item->description; ?></p>
            </div>
        <?php } else { ?>
            <div class="col-10">
                <h3><?= $item->title; ?></h3>
                <p><?= $item->description; ?></p>
            </div>
            <div class="col-2">
                IMAGE HERE
            </div>
        <?php } ?>

    <?php $count++; } ?>
</div>
<?php require_once 'includes/footer.php'; ?>