<?php

namespace Components\Users\Webservice;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Components\Users\Model\Image;
use Components\Payments\Model\Account;
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
                })
              ->filterBy('gender');

        return new Response(Response::OK, $table->fetch($_GET, function($item, $user) {

            $account = Account::getDefault($user);
            $item['account'] = $account->state;
            $item['profile'] = unserialize($user->setting('registrationData'));
            return $item;
        }));
    }

    /**
     * @method POST
     * @json
     */
    public function saveUser()
    {
        $data = Validator::create((array)$this->request->data);

        $emailField = $data->field('email')->required()->email();
        $data->field('password')->required()->equal($data['spassword']);

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

        copy(__DIR__ . '/../default-avatar.gif', SITE_DIR . '/uploads/default-avatar.gif');
        $user->avatar = '/uploads/default-avatar.gif';
        if (isset($data['birth_date'])) {
            $user->birth_date = date('Y-m-d', strToTime($data['birth_date']));
        }
        $user->gender = isset($data['gender']) ? $data['gender'] : 'unknown';
        if ($isNew) {
            $user->password = User::cryptPassword($data['password']);
        }
        //$user->gender = $data['gender'];
        $user->is_active = 0;
        $user->save();
        $user->setting('registrationData', serialize((array)$this->request->data));


        $ids = [];
        $i = 0;
        $dataV = $data;
        if (isset($dataV['images']) && count($dataV['images'])) {
            foreach ($dataV['images'] as $data) {
                $image = (array)$data;
                if (isset($image['error'])) {
                    continue;
                }

                $img = isset($image['id']) ? Image::getById((int)$image['id']) : Image::create();

                $img->name = $image['name'];
                $img->title = isset($image['title']) ? $image['title'] : null;
                $img->description = isset($image['description']) ? $image['description'] : null;

                $config = \Bazalt\Config::container();
                $img->url = str_replace($config['uploads.prefix'], '', $image['url']);
                $img->size = $image['size'];
                $img->sort_order = $i;
                $img->is_main = isset($image['is_main']) && $image['is_main'] == 'true';
                if ($img->is_main) {
                    $user->avatar = $img->url;
                    $user->save();
                }
                $img->user_id = $user->id;
                $img->save();

                $ids [] = $img->id;
            }
        }
        //$user->Images->clearRelations($ids);

        if ($isNew) {
            // Create the message
            $message = \Swift_Message::newInstance()

              // Give the message a subject
              ->setSubject('Брачное агенство Шерше ля фам')

              // Set the From address with an associative array
              ->setFrom(array('admin@cherchelafam.com' => 'Admin'))

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
