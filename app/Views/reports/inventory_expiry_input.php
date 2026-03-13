<?php
/**
 * @var array $stock_locations
 * @var int $default_days
 */
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?= lang('Reports.report_input') ?></div>

<?= form_open('#', ['id' => 'expiry_report_form', 'class' => 'form-horizontal']) ?>

    <div class="form-group form-group-sm">
        <?= form_label(lang('Reports.stock_location'), 'reports_stock_location_label', ['class' => 'required control-label col-xs-2']) ?>
        <div class="col-xs-3">
            <?= form_dropdown('stock_location', $stock_locations, 'all', 'id="location_id" class="form-control"') ?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?= form_label(lang('Reports.expiry_within_days_label'), 'reports_days_threshold_label', ['class' => 'required control-label col-xs-2']) ?>
        <div class="col-xs-3">
            <?= form_input(['name' => 'days_threshold', 'id' => 'days_threshold', 'class' => 'form-control', 'type' => 'number', 'min' => '1', 'value' => $default_days]) ?>
        </div>
    </div>

    <?php
    echo form_button([
        'name'    => 'generate_report',
        'id'      => 'generate_report',
        'content' => lang('Common.submit'),
        'class'   => 'btn btn-primary btn-sm'
    ]) ?>

<?= form_close() ?>

<div class="top-10">
    <a class="btn btn-default btn-sm" href="<?= site_url('reports/inventory_expiry_dashboard') ?>">
        <?= lang('Reports.expiry_dashboard') ?>
    </a>
</div>

<?= view('partial/footer') ?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#generate_report').click(function() {
            window.location = [window.location, $('#location_id').val(), $('#days_threshold').val()].join('/');
        });
    });
</script>
