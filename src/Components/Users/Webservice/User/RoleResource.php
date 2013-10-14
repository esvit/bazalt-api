<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * RoleResource
 *
 * @uri /auth/users/roles
 * @uri /auth/users/:id/roles
 */
class RoleResource extends \Tonic\Resource
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
    public function getRoles($id = null)
    {
        if (!$id) {
            $roles = Role::getAll();
        } else {
            $user = User::getById((int)$id);
            if (!$user) {
                return new Response(Response::NOTFOUND, ['id' => 'User not found']);
            }
            $roles = $user->getRoles();
        }
        //$users->page((int)$_GET['page']);
        //$users->countPerPage((int)$_GET['count']);
        $result = [];
        foreach ($roles as $role) {
            $result []= $role->toArray();
        }
        return new Response(Response::OK, $result);
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
        if ($data->getData('id')) {
            $user = User::getById($data->getData('id'));
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

        $data->field('login')->required();
        $data->field('gender')->required();

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $user->login = $data->getData('email');
        $user->email = $data->getData('email');
        $user->firstname = $data->getData('first');
        $user->lastname = $data->getData('last');
        $user->password = User::cryptPassword($data->getData('password'));
        $user->gender = $data->getData('gender');
        $user->is_active = $data->getData('is_active');
        $user->save();

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