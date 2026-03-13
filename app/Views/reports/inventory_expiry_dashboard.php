<?php
/**
 * @var string $title
 * @var array $dashboard_data
 */
?>

<?= view('partial/header') ?>

<div id="page_title"><?= esc($title) ?></div>

<div class="row">
    <div class="col-md-3">
        <div class="panel panel-danger">
            <div class="panel-heading"><?= lang('Reports.expiry_status_expired') ?></div>
            <div class="panel-body h3"><?= esc($dashboard_data['expired']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-warning">
            <div class="panel-heading"><?= lang('Reports.expiry_30_days') ?></div>
            <div class="panel-body h3"><?= esc($dashboard_data['expiring_30']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-info">
            <div class="panel-heading"><?= lang('Reports.expiry_90_days') ?></div>
            <div class="panel-body h3"><?= esc($dashboard_data['expiring_90']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading"><?= lang('Reports.expiry_180_days') ?></div>
            <div class="panel-body h3"><?= esc($dashboard_data['expiring_180']) ?></div>
        </div>
    </div>
</div>

<div>
    <a class="btn btn-default btn-sm" href="<?= site_url('reports/inventory_expiry') ?>">
        <?= lang('Reports.inventory_expiry') ?>
    </a>
</div>

<?= view('partial/footer') ?>
