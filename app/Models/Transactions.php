<?php

namespace App\Models;

use App\Components\Transactions\TransactionRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $operation_id
 * @property integer $service_id
 * @property integer $amount
 * @property integer $move
 * @property integer $partner_id
 * @property integer $cashdesk
 * @property string $status
 * @property string $currency
 * @property string $foreign_id
 * @property string $transaction_type
 * @property integer $object_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $game_id
*/
class Transactions extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = 'transaction_history';

    protected $fillable = [
        'user_id',
        'operation_id',
        'service_id',
        'amount',
        'move',
        'partner_id',
        'cashdesk',
        'status',
        'currency',
        'foreign_id',
        'object_id',
        'transaction_type',
        'game_id'
    ];

    public function getAmountAttribute($value){
        return abs($value) / 100;
    }

    public function setAmountAttribute($value){
        $this->attributes['amount'] = abs($value) * 100;
    }

    /**
     * @param int $serviceId
     * @param $externalId
     * @param string $transactionType
     * @param int $partner_id
     * @return Transactions
     */
    public static function getTransaction(int $serviceId, $externalId, string $transactionType, int $partner_id){
        return Transactions::where([
            ['service_id', $serviceId],
            ['foreign_id', $externalId],
            ['transaction_type', $transactionType],
            ['partner_id', $partner_id]
        ])->first();
    }

    /**
     * @param int $serviceId
     * @param int $userId
     * @param $objectId
     * @param int $partner_id
     * @return Transactions
     */
    public static function getBetTransaction(int $serviceId, int $userId, $objectId, int $partner_id){
        return Transactions::where([
            ['service_id', $serviceId],
            ['user_id', $userId],
            ['object_id', $objectId],
            ['partner_id', $partner_id],
            ['transaction_type', TransactionRequest::TRANS_BET],
            ['status', TransactionRequest::STATUS_COMPLETED]
        ])->first();
    }
}
