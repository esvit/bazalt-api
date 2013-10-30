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
 * @uri /auth/users/:id/messages/:messageId
 */
class MessageResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getMessage($id, $messageId)
    {
        $user = \Bazalt\Auth::getUser();
        if ($user->isGuest()) {
            return Response(Response::FORBIDDEN, ['user' => 'Permission denied']);
        }
        $message = Message::getById((int)$messageId);

        if (!$message->is_readed && $message->to_id == $user->id) {
            $message->is_readed = 1;
            $message->save();
        }
        return new Response(Response::OK, $message->toArray());
    }
}