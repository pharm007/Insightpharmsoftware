<?php

namespace App\Models\Reports;

use App\Models\Item;

class Inventory_reorder extends Report
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
            ['quantity' => lang('Reports.quantity')],
            ['reorder_level' => lang('Reports.reorder_level')],
            ['reorder_quantity' => lang('Reports.reorder_quantity')],
            ['location_name' => lang('Reports.stock_location')]
        ];
    }

    /**
     * @param array $inputs
     * @return array
     */
    public function getData(array $inputs): array
    {
        $location_id = $inputs['location_id'] ?? 'all';

        $item = model(Item::class);

        $builder = $this->db->table('items AS items');
        $builder->select(
            $item->get_item_name('items.name') . ' AS name,
            items.item_number,
            items.category,
            item_quantities.quantity,
            items.reorder_level,
            (items.reorder_level - item_quantities.quantity) AS reorder_quantity,
            stock_locations.location_name'
        );
        $builder->join('item_quantities AS item_quantities', 'item_quantities.item_id = items.item_id');
        $builder->join('stock_locations AS stock_locations', 'stock_locations.location_id = item_quantities.location_id');

        $builder->where('items.deleted', 0);
        $builder->where('items.stock_type', 0);
        $builder->where('stock_locations.deleted', 0);
        $builder->where('items.reorder_level >', 0);
        $builder->where('item_quantities.quantity <= items.reorder_level');

        if ($location_id !== 'all') {
            $builder->where('stock_locations.location_id', $location_id);
        }

        $builder->orderBy('reorder_quantity', 'DESC');
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
            'reorder_item_count' => 0,
            'reorder_total_quantity' => 0
        ];

        foreach ($inputs as $row) {
            $summary['reorder_item_count']++;
            $summary['reorder_total_quantity'] += max(0, (float)$row['reorder_quantity']);
        }

        return $summary;
    }
}
