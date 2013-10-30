<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Rest\Response;
use Bazalt\Data\Validator;
use Components\Users\Model\Message;

/**
 * MessagesResource
 *
 * @uri /auth/users/:id/messages
 * @uri /auth/users/:id/messages/:toId
 */
class MessagesResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList($id = null, $toId = null)
    {
        $user = \Bazalt\Auth::getUser();
        if ($user->isGuest()) {
            return Response(Response::FORBIDDEN, ['user' => 'Permission denied']);
        }
        $collection = Message::getCollection();

        if (isset($_GET['outbox']) && $_GET['outbox'] == 'true') {
            $collection->andWhere('from_id = ?', $user->id);
        } else {
            $collection->andWhere('to_id = ?', $user->id);
        }

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
        \Bazalt\Auth::getUser()->setting('users.last_activity_messages_' . \Bazalt\Site::getId(), time());

        $collection = Message::getCollection();
        return new Response(200, $message->toArray());
    }

    /**
     * @method POST
     * @json
     */
    public function saveMessage()
    {
        $data = Validator::create((array)$this->request->data);

        $emailField = $data->field('message')->required();
        $data->field('to_id')->required()->int(1);

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $message = Message::create();
        $message->to_id = $data['to_id'];
        $message->message = $data['message'];
        $message->save();

        return new Response(200, $message->toArray());
    }
}