<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * UserResource
 *
 * @uri /auth/users/:id
 */
class UserResource extends \Tonic\Resource
{
    /**
     * Condition method to turn output into JSON
     */
    protected function json()
    {
        $this->before(function ($request) {
            if ($request->contentType == "application/json") {
                $request->data = json_decode($request->data);
            }
        });
        $this->after(function ($response) {
            $response->contentType = "application/json";

            if (isset($_GET['jsonp'])) {
                $response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
            } else {
                $response->body = json_encode($response->body);
            }
        });
    }

    /**
     * @method GET
     * @json
     */
    public function getUser($id)
    {
        $user = User::getById($id);
        if (!$user) {
            return new Response(400, ['id' => 'User not found']);
        }
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method DELETE
     * @json
     */
    public function deleteUser($id)
    {
        $user = User::getById($id);
        if (!$user) {
            return new Response(400, ['id' => 'User not found']);
        }
        $user->is_deleted = 1;
        $user->save();
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method POST
     * @json
     */
    public function saveUser()
    {
        $data = Validator::create((array)$this->request->data);

        $emailField = $data->field('email')->required()->email();

        $isNew = false;
        if ($data['id']) {
            $user = User::getById($data['id']);
            if (!$user) {
                return new Response(400, ['id' => 'User not found']);
            }
        } else {
            $user = User::create();

            // check email
            $emailField->validator('uniqueEmail', function($email) {
                return User::getUserByEmail($email, false) == null;
            }, 'User with this email already exists');
            $isNew = true;
        }

        $userRoles = [];
        $data->field('roles')->validator('validRoles', function($roles) use (&$userRoles) {
            foreach ($roles as $role) {
                $userRoles[$role] = Role::getById($role);
                if (!$userRoles[$role]) {
                    return false;
                }
            }
            return true;
        }, 'Invalid roles');

        $data->field('login')->required();
        $data->field('gender')->required();

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $user->login = $data['login'];
        $user->email = $data['email'];
        $user->firstname = $data['firstname'];
        $user->secondname = $data['secondname'];
        $user->patronymic = $data['patronymic'];
        if ($isNew) {
            $user->password = User::cryptPassword($data['password']);
        }
        $user->gender = $data['gender'];
        $user->is_active = $data['is_active'];
        $user->is_deleted = $data['is_deleted'];
        $user->save();

        $user->Roles->clearRelations(array_keys($userRoles));
        foreach ($userRoles as $role) {
            $user->Roles->add($role, ['site_id' => 6]);
        }

        if ($isNew) {
            // Create the message
            $message = \Swift_Message::newInstance()

              // Give the message a subject
              ->setSubject('Your subject')

              // Set the From address with an associative array
              ->setFrom(array('john@doe.com' => 'John Doe'))

              // Set the To addresses with an associative array
              ->setTo([$user->email])

              // Give it a body
              ->setBody('Here is the message itself')

              // And optionally an alternative body
              ->addPart('<q>Here is the message itself</q>', 'text/html');

              $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
              ->setUsername('no-reply@mistinfo.com')
              ->setPassword('gjhndtqy777')
              ;
              $mailer = \Swift_Mailer::newInstance($transport);
              $result = $mailer->send($message);
            print_r($result);
        }
        return new Response(200, $user->toArray());
    }
}