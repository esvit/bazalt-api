<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * UsersResource
 *
 * @uri /auth/users
 */
class UsersResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList()
    {
        $collection = User::getCollection();

        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('login')
              ->filterBy('login', function($collection, $columnName, $value) {
                    $collection->andWhere('`' . $columnName . '` LIKE ?', '%' . $value . '%');
                });

        return new Response(Response::OK, $table->fetch($_GET));
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

        //$data->field('login')->required();
        //$data->field('gender')->required();

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $user->login = $data['email'];
        $user->email = $data['email'];
        $user->firstname = $data['firstname'];
        $user->secondname = $data['secondname'];
        $user->patronymic = $data['patronymic'];
        if ($isNew) {
            $user->password = User::cryptPassword($data['password']);
        }
        $user->gender = $data['gender'];
        $user->is_active = $data['is_active'];
        $user->save();

        if ($isNew) {
            // Create the message
            $message = \Swift_Message::newInstance()

              // Give the message a subject
              ->setSubject('Регистрация на портале HellVin')

              // Set the From address with an associative array
              ->setFrom(array('faust@hellv.in' => 'Фауст'))

              // Set the To addresses with an associative array
              ->setTo([$user->email])

              // Give it a body
              ->setBody(sprintf('Для активации перейдите по ссылке http://%s/user/activate/%d/%s', \Bazalt\Site::get()->domain, $user->id, $user->getActivationKey()));

              // And optionally an alternative body
              //->addPart('<q>Here is the message itself</q>', 'text/html');

              $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
              ->setUsername('no-reply@mistinfo.com')
              ->setPassword('gjhndtqy777')
              ;
              $mailer = \Swift_Mailer::newInstance($transport);
              $result = $mailer->send($message);
        }
        return new Response(200, $user->toArray());
    }
}