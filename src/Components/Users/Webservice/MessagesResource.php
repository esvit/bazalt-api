<?php

namespace Components\Users\Webservice;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Rest\Response;
use Bazalt\Data\Validator;
use Components\Payments\Model\Account;
use Components\Payments\Model\Transaction;
use Components\Users\Model\Message;

/**
 * MessagesResource
 *
 * @uri /auth/users/messages
 */
class MessagesResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList()
    {
        $user = \Bazalt\Auth::getUser();
        if ($user->isGuest()) {
            return Response(Response::FORBIDDEN, ['user' => 'Permission denied']);
        }
        $collection = Message::getCollection();

        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('created_at')
              ->filterBy('message', function($collection, $columnName, $value) {
                    $collection->andWhere('`' . $columnName . '` LIKE ?', '%' . $value . '%');
                });

        return new Response(Response::OK, $table->fetch($_GET));
    }

    /**
     * @method GET
     * @action count
     * @json
     */
    public function getCount()
    {
        return new Response(200, [
            'count' => Message::getUnreadedCount(\Bazalt\Auth::getUser()->id, 0)
        ]);
    }

    /**
     * @method POST
     * @json
     */
    public function saveMessage($id)
    {
        $message = Message::getById((int)$id);
        $data = Validator::create((array)$this->request->data);

        $emailField = $data->field('message')->required();
        $data->field('to_id')->required()->int(1);

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $message->to_id = $data['to_id'];
        $message->message = $data['message'];
        $message->is_moderated = $data['is_moderated'];
        $message->translate = $data['translate'];
        $message->save();

        return new Response(200, $message->toArray());
    }
}