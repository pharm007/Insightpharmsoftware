<?php

namespace App\Models\Reports;

use App\Models\Item;

class Inventory_expiry extends Report
{
    /**
     * @return array[]
     */
    public function getDataColumns(): array
    {
        return [
            ['item_name' => lang('Reports.item_name')],
            ['item_number' => lang('Reports.item_number')],
            ['category' => lang('Reports.category')],
            ['batch_number' => lang('Reports.batch_number')],
            ['expiry_date' => lang('Reports.expiry_date')],
            ['days_to_expiry' => lang('Reports.days_to_expiry')],
            ['quantity' => lang('Reports.quantity')],
            ['expiry_status' => lang('Reports.expiry_status')],
            ['location_name' => lang('Reports.stock_location')]
        ];
    }

    /**
     * @param array $inputs
     * @return array
     */
    public function getData(array $inputs): array
    {
        $days_threshold = max(0, (int)($inputs['days_threshold'] ?? 90));
        $location_id = $inputs['location_id'] ?? 'all';
        $expiry_status = $inputs['expiry_status'] ?? 'all';
        $days_threshold = (int)($inputs['days_threshold'] ?? 90);
        $location_id = $inputs['location_id'] ?? 'all';

        $item = model(Item::class);

        $builder = $this->db->table('attribute_definitions AS expiry_definition');
        $builder->select(
            $item->get_item_name('items.name') . ' AS name,
            items.item_number,
            items.category,
            COALESCE(batch_value.attribute_value, "-") AS batch_number,
            expiry_value.attribute_date AS expiry_date,
            DATEDIFF(expiry_value.attribute_date, CURDATE()) AS days_to_expiry,
            item_quantities.quantity,
            stock_locations.location_name,
            CASE
                WHEN expiry_value.attribute_date < CURDATE() THEN "expired"
                WHEN DATEDIFF(expiry_value.attribute_date, CURDATE()) <= 30 THEN "critical"
                WHEN DATEDIFF(expiry_value.attribute_date, CURDATE()) <= {$days_threshold} THEN "warning"
                WHEN DATEDIFF(expiry_value.attribute_date, CURDATE()) <= ' . $days_threshold . ' THEN "warning"
                ELSE "ok"
            END AS expiry_status'
        );
        $builder->join('attribute_links AS expiry_link', 'expiry_link.definition_id = expiry_definition.definition_id', 'inner');
        $builder->join('attribute_values AS expiry_value', 'expiry_value.attribute_id = expiry_link.attribute_id', 'inner');
        $builder->join('items', 'items.item_id = expiry_link.item_id', 'inner');
        $builder->join('item_quantities', 'item_quantities.item_id = items.item_id', 'inner');
        $builder->join('stock_locations', 'stock_locations.location_id = item_quantities.location_id', 'inner');
        $builder->join('attribute_definitions AS batch_definition', "batch_definition.definition_name = 'Batch Number' AND batch_definition.deleted = 0", 'left');
        $builder->join('attribute_links AS batch_link', 'batch_link.definition_id = batch_definition.definition_id AND batch_link.item_id = items.item_id AND batch_link.sale_id IS NULL AND batch_link.receiving_id IS NULL', 'left');
        $builder->join('attribute_values AS batch_value', 'batch_value.attribute_id = batch_link.attribute_id', 'left');

        $builder->where('expiry_definition.definition_name', 'Expiry Date');
        $builder->where('expiry_definition.deleted', 0);
        $builder->where('expiry_definition.definition_type', DATE);
        $builder->where('expiry_link.sale_id', null);
        $builder->where('expiry_link.receiving_id', null);
        $builder->where('items.deleted', 0);
        $builder->where('items.stock_type', 0);
        $builder->where('stock_locations.deleted', 0);
        $builder->where('item_quantities.quantity >', 0);
        $builder->where('expiry_value.attribute_date IS NOT NULL');
        $builder->where('DATEDIFF(expiry_value.attribute_date, CURDATE()) <=', $days_threshold);

        if ($location_id !== 'all') {
            $builder->where('stock_locations.location_id', $location_id);
        }

        if ($expiry_status === 'expired') {
            $builder->where('DATEDIFF(expiry_value.attribute_date, CURDATE()) <', 0);
        } elseif ($expiry_status === 'critical') {
            $builder->where('DATEDIFF(expiry_value.attribute_date, CURDATE()) >=', 0);
            $builder->where('DATEDIFF(expiry_value.attribute_date, CURDATE()) <=', 30);
        } elseif ($expiry_status === 'warning') {
            $builder->where('DATEDIFF(expiry_value.attribute_date, CURDATE()) >', 30);
            $builder->where('DATEDIFF(expiry_value.attribute_date, CURDATE()) <=', $days_threshold);
        }

        $builder->orderBy('days_to_expiry', 'ASC');
        $builder->orderBy('items.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * @param array $inputs
     * @return array
     */
    public function getSummaryData(array $inputs): array
    {
        $summary = [
            'expired_items' => 0,
            'critical_items' => 0,
            'warning_items' => 0,
            'total_quantity' => 0
        ];

        foreach ($inputs as $row) {
            $summary['total_quantity'] += $row['quantity'];

            if ($row['days_to_expiry'] < 0) {
                $summary['expired_items']++;
            } elseif ($row['days_to_expiry'] <= 30) {
                $summary['critical_items']++;
            } else {
                $summary['warning_items']++;
            }
        }

        return $summary;
    }

    /**
     * @param string $location_id
     * @return array
     */
    public function getDashboardData(string $location_id = 'all'): array
    {
        $report_data = $this->getData([
            'days_threshold' => 180,
            'location_id' => $location_id
        ]);

        $dashboard = [
            'expired' => 0,
            'expiring_30' => 0,
            'expiring_90' => 0,
            'expiring_180' => 0
        ];

        foreach ($report_data as $row) {
            if ($row['days_to_expiry'] < 0) {
                $dashboard['expired']++;
            } elseif ($row['days_to_expiry'] <= 30) {
                $dashboard['expiring_30']++;
            } elseif ($row['days_to_expiry'] <= 90) {
                $dashboard['expiring_90']++;
            } else {
                $dashboard['expiring_180']++;
            }
        }

        return $dashboard;
    }
}
