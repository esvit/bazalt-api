<?php

namespace Components\Payments\Model;

class Transaction extends Base\Transaction
{
    const TYPE_UP = 'up';

    const TYPE_DOWN = 'down';

    public static function beginTransaction(Account $account, $type, $sum, $comment = null)
    {
        $transaction = new Transaction();
        $transaction->account_id = $account->id;
        $transaction->type = $type;
        $transaction->sum = $sum;
        $transaction->comment = $comment;
        $transaction->save();
        return $transaction;

    }

    public function endTransaction($state, $periodState, $data = array(), $comment = null)
    {
        $this->state = $state;
        $this->period_state = $periodState;
        $this->data = $data;
        $this->end_date = gmdate('Y-m-d H:i:s');
        if ($comment !== null) {
            $this->comment = $comment;
        }
        $this->save();
        return $this;
    }

    public function failed($data = array(), $comment = null)
    {
        return $this->endTransaction('failed', $this->Account->state, $data, $comment);
    }

    public function cancel($data = array(), $comment = null)
    {
        return $this->endTransaction('cancel', $this->Account->state, $data, $comment);
    }

    public function complete($data = array(), $comment = null)
    {
        $account = $this->Account;
        if($this->type == Transaction::TYPE_UP) {
            $account->state += $this->sum;
        } elseif($this->type == Transaction::TYPE_DOWN) {
            $account->state -= $this->sum;
        }
        $account->save();
        return $this->endTransaction('completed', $account->state, $data, $comment);
    }
}