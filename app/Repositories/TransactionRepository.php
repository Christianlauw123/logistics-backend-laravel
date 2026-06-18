<?php

namespace App\Repositories;

use App\Enums\TransactionDetails\TransactionDetailStatus;
use App\Enums\Transactions\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TransactionRepository
{
    // Allowed sort columns — whitelist prevents SQL injection
    private const SORTABLE = [
        'do_date',
        'do_actual_date',
        'created_at',
    ];

    public function paginate(array $filters, array $sort = [], int $perPage = 15): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search customers.name, originSubDistrict.name. destinationSubDistrict name properties
                - customerId: specific customerId
                - perPage: by default 15
        */
        $sortBy        = in_array($sort['sort_by'] ?? '', self::SORTABLE) ? $sort['sort_by'] : 'created_at';
        $sortDirection = ($sort['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Transaction::query()
            ->with([
                'customer',
                'originSubDistrict',
                'destinationSubDistrict',
                'bankAccount',
                'vehicle',
            ])
            // exact filters
            ->when(
                ! empty($filters['status']),
                fn ($q) => $q->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['customer_id']),
                fn ($q) => $q->where('customer_id', $filters['customer_id'])
            )
            ->when(
                ! empty($filters['origin_sub_district_id']),
                fn ($q) => $q->where('origin_sub_district_id', $filters['origin_sub_district_id'])
            )
            ->when(
                ! empty($filters['dest_sub_district_id']),
                fn ($q) => $q->where('dest_sub_district_id', $filters['dest_sub_district_id'])
            )
            ->when(
                ! empty($filters['bank_account_id']),
                fn ($q) => $q->where('bank_account_id', $filters['bank_account_id'])
            )
            ->when(
                ! empty($filters['vehicle_id']),
                fn ($q) => $q->where('vehicle_id', $filters['vehicle_id'])
            )
            // date range filters
            ->when(
                // do_date, do_actual_date
                !empty($filters['filter_date_key']) && (!empty($filters['date_start']) || !empty($filters['date_end'])),
                function ($q) use ($filters) {
                    $column = $filters['filter_date_key']; // e.g., 'do_date' or 'do_actual_date'

                    $q->when(!empty($filters['date_start']), fn($query) => $query->whereDate($column, '>=', $filters['date_start']))
                    ->when(!empty($filters['date_end']),   fn($query) => $query->whereDate($column, '<=', $filters['date_end']));
                }
            )
            // keyword search across related names
            ->when(
                ! empty($filters['search']),
                fn ($q) => $q->where(function ($q) use ($filters) {
                    $q->where('customer_name', 'ilike', "%{$filters['search']}%")
                      ->orWhere('vehicle_plate', 'ilike', "%{$filters['search']}%")
                      ->orWhere('vehicle_type', 'ilike', "%{$filters['search']}%")
                      ->orWhere('bank_account_num', 'ilike', "%{$filters['search']}%")
                      ->orWhere('dest_address', 'ilike', "%{$filters['search']}%")
                      ->orWhere('customer_name', 'ilike', "%{$filters['search']}%")
                      ->orWhere('note', 'ilike', "%{$filters['search']}%")
                      ->orWhere('origin_district', 'ilike', "%{$filters['search']}%")
                      ->orWhere('destination_district', 'ilike', "%{$filters['search']}%")
                      ->orWhere('do_number', 'ilike', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['deleted']) && $filters['deleted']==true,
                fn ($q) => $q->withTrashed()
            )
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByIdOrFail(string $id): Transaction
    {
        return Transaction::with([
            'user',
            'lastUpdatedBy',
            'transactionDetails.attachment',
            'transactionDetails.lastUpdatedBy',
            'transactionDetails.user',
            'attachments',
            'bankAccount',
            'customer',
            'vehicle',
            'originSubDistrict',
            'destinationSubDistrict'
        ])->findOrFail($id);
    }

    public function create(array $data): Transaction
    {
        $transaction = Transaction::create($data);
        $this->prePopulateCreateTransaction($transaction);

        return $transaction->refresh();
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);
        $this->prePopulateTransaction($transaction->refresh());
        $transaction->refresh()->save();

        return $transaction->refresh();
    }

    public function updateStatus(Transaction $transaction, string $status): Transaction
    {
        $transaction->update(['status' => $status]);

        return $transaction->refresh();
    }

    public function updateRevisionDestination(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);
        $this->prePopulateRevisionDestinationTransaction($transaction->refresh());
        return $transaction->refresh();
    }

    public function delete(Transaction $transaction): void
    {
        $transaction->delete();
    }

    // Custom Function
    /**
     * prePopulateTransaction after Create and Update
     *
     */
    public function prePopulateTransaction(Transaction $transaction): void {
        $transaction->trip_price_amount = $transaction->tripPrice->base_price;
        $transaction->vehicle_plate = $transaction->vehicle->plate_number;
        $transaction->vehicle_type = $transaction->vehicle->type;
        $transaction->vehicle_capacity = $transaction->vehicle->capacity;
        $transaction->origin_district = $transaction->getDistrictLabelAttribute($transaction->originSubDistrict);
        $transaction->destination_district = $transaction->getDistrictLabelAttribute($transaction->destinationSubDistrict);
        $transaction->bank_account_num = $transaction->bankAccount->account_identifier_number;
        $transaction->customer_name = $transaction->customer->name;
        $transaction->driver_name = $transaction->driver->name;
    }

    /**
     * prePopulateTransaction after Creation only
     *
     */
    public function prePopulateCreateTransaction(Transaction $transaction): void {
        $this->prePopulateTransaction($transaction);
        $transaction->status = TransactionStatus::SUBMITTED;
        $transaction->do_date = $transaction->created_at->timezone('Asia/Jakarta');
        $transaction->revision_dest_sub_district_id  = $transaction->dest_sub_district_id;
        $transaction->revision_trip_price_amount = $transaction->tripPrice->base_price;
        $transaction->revision_destination_district = $transaction->getDistrictLabelAttribute($transaction->destinationSubDistrict);
        $transaction->revision_trip_price_id = $transaction->trip_price_id;
        $transaction->save();
    }

    /**
     * prePopulateTransaction after Update the revision destination
     *
     */
    public function prePopulateRevisionDestinationTransaction(Transaction $transaction): void {
        $transaction->revision_trip_price_amount = $transaction->revisionTripPrice->base_price;
        $transaction->revision_destination_district = $transaction->getDistrictLabelAttribute($transaction->revisionDestinationSubDistrict);
        $transaction->save();
    }

    public function preCalculateCurrentTransactionTotal(string $transactionId, ?float $amount = null): array {
        $transaction = $this->findByIdOrFail($transactionId)->refresh();
        $transactionDetails = $transaction->transactionDetails;
        // Only check the one that not special case for the limit
        $total = $transactionDetails ? $transactionDetails->whereIn('status', TransactionDetailStatus::requestedDefaults())->where('is_special_case',false)->sum('amount') : 0;

        $state = true;
        if ($amount !== null) {
            if ($total + $amount > $transaction->revision_trip_price_amount || $total + $amount < 0)
                $state = false;
        }
        return [
            'state' => $state,
            'trip_price_amount' => $transaction->revision_trip_price_amount,
            'current_total_approved' => $transactionDetails->whereIn('status', TransactionDetailStatus::approvedDefaults())->sum('amount'),
            'current_total' => $total
        ];
    }

    public function setGoogleDriveFolder(string $transactionId, string $transactionFolderId, string $transactionDetailFolderId): void {
        $transaction = $this->findByIdOrFail($transactionId)->refresh();
        $transaction->file_folder_id = $transactionFolderId;
        $transaction->file_sub_folder_id = $transactionDetailFolderId;
        $transaction->file_provider = 'google-drive';
        $transaction->save();
    }

    public function exportFunction(array $filters, array $sort = []): Collection
    {
        /*
            filters
                - search: keyword search customers.name, originSubDistrict.name. destinationSubDistrict name properties
                - customerId: specific customerId
                - perPage: by default 15
        */
        $sortBy        = in_array($sort['sort_by'] ?? '', self::SORTABLE) ? $sort['sort_by'] : 'created_at';
        $sortDirection = ($sort['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Transaction::query()->with(['transactionDetails.lastUpdatedBy','transactionDetails.attachment'])
            ->when(
                ! empty($filters['search']),
                fn ($q) => $q->where(function ($q) use ($filters) {
                    $q->where('customer_name', 'ilike', "%{$filters['search']}%")
                      ->orWhere('vehicle_plate', 'ilike', "%{$filters['search']}%")
                      ->orWhere('vehicle_type', 'ilike', "%{$filters['search']}%")
                      ->orWhere('bank_account_num', 'ilike', "%{$filters['search']}%")
                      ->orWhere('dest_address', 'ilike', "%{$filters['search']}%")
                      ->orWhere('customer_name', 'ilike', "%{$filters['search']}%")
                      ->orWhere('note', 'ilike', "%{$filters['search']}%")
                      ->orWhere('origin_district', 'ilike', "%{$filters['search']}%")
                      ->orWhere('destination_district', 'ilike', "%{$filters['search']}%")
                      ->orWhere('revision_destination_district', 'ilike', "%{$filters['search']}%")
                      ->orWhere('do_number', 'ilike', "%{$filters['search']}%");
                })
            )
            // exact filters
            ->when(
                ! empty($filters['status']),
                fn ($q) => $q->where('status', $filters['status'])
            )
            ->when(
                isset($filters['deleted']) && $filters['deleted']==true,
                fn ($q) => $q->withTrashed()
            )
            ->when(
                ! empty($filters['customer_id']),
                fn ($q) => $q->where('customer_id', $filters['customer_id'])
            )
            ->when(
                ! empty($filters['origin_sub_district_id']),
                fn ($q) => $q->where('origin_sub_district_id', $filters['origin_sub_district_id'])
            )
            ->when(
                ! empty($filters['dest_sub_district_id']),
                fn ($q) => $q->where('dest_sub_district_id', $filters['dest_sub_district_id'])
            )
            ->when(
                ! empty($filters['bank_account_id']),
                fn ($q) => $q->where('bank_account_id', $filters['bank_account_id'])
            )
            ->when(
                ! empty($filters['vehicle_id']),
                fn ($q) => $q->where('vehicle_id', $filters['vehicle_id'])
            )
            // date range filters
            ->when(
                // do_date, do_actual_date
                !empty($filters['filter_date_key']) && (!empty($filters['date_start']) || !empty($filters['date_end'])),
                function ($q) use ($filters) {
                    $column = $filters['filter_date_key']; // e.g., 'do_date' or 'do_actual_date'

                    $q->when(!empty($filters['date_start']), fn($query) => $query->whereDate($column, '>=', $filters['date_start']))
                    ->when(!empty($filters['date_end']),   fn($query) => $query->whereDate($column, '<=', $filters['date_end']));
                }
            )
            ->orderBy($sortBy, $sortDirection)
            ->get();
    }
}

