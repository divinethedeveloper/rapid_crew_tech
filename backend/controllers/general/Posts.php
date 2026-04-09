<?php

namespace Controller\General;

use Controller\Controller;
use Middleware\Validator;
use Model\User\User;
use Model\Product\Order;
use Model\Product\Product;

class Posts extends Controller
{
    public function createUser()
    {
        $user = new User();
        $validator = new Validator();

        [$empties, $values] = $this->clean_assoc([
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'number' => $_POST['number'] ?? '0000000000',
            'password' => $_POST['password'] ?? '',
            'repeat-password' => $_POST['repeat-password'] ?? '',
            'avatar' => $_FILES['avatar']['tmp_name'] ?? '',
            'role' => $_POST['role'] ?? '2',
            'location' => $_POST['address'] ?? 'rapid-nation',
            'lat' => $_POST['lat'] ?? '0.00',
            'lng' => $_POST['lng'] ?? '0.00',
            'agree' => $_POST['agree'] ?? '',
            'guest' => $_POST['guest'] ?? uniqid('guest_' . time()),

        ]);

        if ($user->userExists($values['email'], $values['number'])) {
            return [
                'status' => 'error',
                'title' => 'Account Exists',
                'message' => 'Seems this account already exists, please login to use this account',
            ];
        }

        if ($values['password'] !== $values['repeat-password']) {
            return [
                'status' => 'error',
                'title' => 'Password Mismatch',
                'message' => 'Please check that both passwords are identical and try again',
            ];
        }

        if (count($empties) > 0) {
            return [
                'status' => 'error',
                'title' => 'Empty Inputs Test' . $empties[0],
                'message' => 'Please check your inputs and try again.',
            ];
        }

        [$empties, $values] = $validator->validate($values);

        if (count($empties) > 0) {
            return [
                'status' => 'error',
                'title' => 'Invalid Inputs',
                'message' => 'Please check your inputs and try again.',
                'data' => $empties,
                'values' => $values,
            ];
        }

        

        $values['plan'] = 1;


        if ($values['role'] !== "$user->skilled_role_id") {
            $values['plan'] = 0;
        }

        if (isset($values['plan'])) { // if the user is not a skilled person or the user is a skilled person and the plan is selected
            $image_upload = $this->uploadFile('avatar', uniqid('avatar_'.time()), 'image');

            if ($values['plan'] <= 1) {
                $values['rank'] = 1;
            } elseif ($values['plan'] <= 2) {
                $values['rank'] = 2;
            } else {
                $values['rank'] = 3;
            }

            if (!isset($image_upload[0])) {
                $values['avatar'] = 'default.jpg';
            } else {
                $values['avatar'] = $image_upload[0];
            }

            if ($user->new($values)) {
                return [
                    'status' => 'success',
                    'title' => 'Account Created Successfully',
                    'message' => 'Please log in to verify your account',
                ];
            } else {
                return [
                    'status' => 'error',
                    'title' => 'Account Exists',
                    'message' => 'Seems this account already exists, please login to use this account',
                ];
            }
        }

        return [
            'status' => 'notSubmited',
            'title' => 'Valid Inputs',
        ];
    }

    public function sendVerificationCode($id = null, $email = null)
    {
        $this->startSession();

        if ($id === null) {
            $id = $this->decode($_SESSION['rapid_test']);
        }
        if ($email === null) {
            $email = $_POST['email'];
        }

        [$empty, $id] = $this->clean($id);
        [$empty, $email] = $this->clean($email);

        if ($empty) {
            return [
                'status' => 'error',
                'title' => 'Empty Inputs',
                'message' => 'Please provide the verification inputs and try again',
            ];
        }

        $user = new User();

        $code = uniqid('@verify@my@rapid@account@');

        $verificationLink = $this->frontend_endpoint.'/verify/'.$this->encode($code);

        if ($user->user($id)['status'] !== 'unverified') {
            return [
                'status' => 'success',
                'title' => 'Account Already Verified',
                'message' => 'This account has already been verified! please login to use your account.',
            ];
        }

        $user->verificationCode($id, $code);

        if ($this->sendEmail(
            $email,
            'Email Verification',
            'Congratulations! welcome to The Rapid Crew, please click the button bellow to verify your accout, if you did not create an account with us please ignore this email.',
            $verificationLink,
            'Verify Account'
        )) {
            return [
                'status' => 'success',
                'title' => 'Verification Link Sent',
                'message' => 'Please check your email for your verification link',
            ];
        } else {
            return [
                'status' => 'error',
                'title' => 'System Busy',
                'message' => 'System is unable to verify your account at the moment, please try again later',
            ];
        }
    }

    public function verifyEmail($code = null)
    {
        if ($code === null) {
            $code = $_POST['code'];
        }

        if (empty($code)) {
            return [
                'status' => 'error',
                'title' => 'Invalid Code',
                'message' => 'please use the code provided to you in the verification email',
            ];
        }

        $user = new User();

        $code = $this->decode($code);
        [$verified, $user_id] = $user->verifyCode($code);

        if ($verified) {
            return [
                'status' => 'success',
                'title' => 'Account Verified',
                'message' => 'Your account has been verified successfully!',
                'user_status' => $user->user($user_id)['status'],
            ];
        }

        if ($user_id !== null and $user->user($user_id)['status'] !== 'unverified') {
            return [
                'status' => 'success',
                'title' => 'Account Already Verified',
                'message' => 'This account has already been verified! please login to use your account.',
            ];
        }

        return [
            'status' => 'warning',
            'title' => 'Unable To Verify',
            'message' => 'Please click the verify button to verify your account or contact The Rapid Crew for further assistance.',
            'user_status' => 'unverified',
        ];
    }

    public function login()
    {
        $user = new User();

        $values = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'guest' => $_POST['guest'] ?? uniqid('guest_' . time()),
        ];

        [$empties, $values] = $this->clean_assoc($values);

        if (count($empties) > 0) {
            return [
                'status' => 'error',
                'title' => 'Empty Inputs',
                'message' => 'Please fill in all inputs and try again',
            ];
        }

        $user = $user->user($values['email']);

        

        if (count($user) <= 0 or !$this->verifyPassword($values['password'], $user['password'])) {
            return [
                'status' => 'error',
                'title' => 'Invalid Inputs',
                'message' => 'Please check your inputs and try again.',
            ];
        }

        $guest = new User();

        $guest->updateGuest($values['email'], $values['guest']);


        return [
            'status' => 'success',
            'title' => 'Login Successfull',
            'message' => '',
            'token' => $this->generateToken([
                'id' => $this->encode($user['id']),
            ]),
            'user_status' => $user['status'],
            'role' => $user['role_id'],
        ];
    }

    public function confirmPayment()
    {
        $user = new User();
        [$empty, $reference] = $this->clean($_POST['reference'] ?? '');
        [$empty, $name] = $this->clean($_POST['name'] ?? '');
        [$empty, $email] = $this->clean($_POST['email'] ?? '');

        $error = [
            'status' => 'error',
            'title' => 'Payment Confirmation',
            'message' => 'System is unable to confirm payment, please contact The Rapid Crew for further assistance if payment was made.',
            'data' => [$reference, $empty],
        ];

        $success = [
            'status' => 'success',
            'title' => 'Payment Successfull',
            'message' => 'Payment made is confirmed, thank you!',
        ];

        if ($reference === '' or $empty) {
            return $error;
        }

        if ($user->confirmPayment($reference, $name, $email)) {
            return $success;
        }

        if ($this->registerPayment($reference, $name, $email)) {
            return $success;
        }

        return $error;
    }

    public function registerPayment($reference, $name, $email)
    {
        $secretKey = 'sk_test_ee4258abd0e5f27ceee1f020d765e1d4b77bf31c';

        // Initialize cURL session
        $ch = curl_init('https://api.paystack.co/transaction/verify/'.$reference);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$secretKey,
            'Content-Type: application/json',
        ]);

        // Execute cURL session
        $response = curl_exec($ch);

        // Close cURL session
        curl_close($ch);

        // Decode the JSON response
        $responseData = json_decode($response, true);

        // Check if the request was successful
        if ($responseData['status']) {
            $status = $responseData['data']['status'];
            if ($status === 'success') {
                $user = new User();
                $user->registerPayment($reference, $name, $email);

                return true;
            }
        }

        return false;
    }

    public function addToWaitlist()
    {
        [$empties, $values] = $this->clean_assoc($_POST);

        if (count($empties) > 0) {
            return [
                'status' => 'warning',
                'title' => 'Empty Inputs',
                'message' => 'Please provide your name and email and try again',
            ];
        }

        $user = new User();

        if ($user->addToWaitlist($values['name'], $values['email'])) {
            $this->sendEmail(
                $values['email'],
                'RAPID CREW WAITLIST DISCOUNT',
                'Congratulations! welcome to THE RAPID CREW DIGITAL STORE, you have been shortlisted for an up to 40% discount promo code on lunch of our online store. bringing tech to you just got rapid!',
            );
            return [
                'status' => 'success',
                'title' => 'Waitlist Registeration Successfull',
                'message' => 'Please check your email account for your congratulatory message!',
            ];
        }

        return [
            'status' => 'warning',
            'title' => 'Already On The Waitlist',
            'message' => "You've already been added to the waitlist, check your email for your congratulatory message!",
        ];

    }


    public function createOrder()
    {
        [$empties, $values] = $this->clean_assoc([
            'name' => $_REQUEST['name'] ?? '',
            'email' => $_REQUEST['email'] ?? '',
            'number' => $_REQUEST['contact'] ?? '',
            'note' => $_REQUEST['note'] ?? 'I would like my product delivered on time, Author: rapidcrew',
            'lat' => $_REQUEST['lat'] ?? '',
            'lng' => $_REQUEST['lng'] ?? '',
            'address' => $_REQUEST['address'] ?? '',
            'reference' => $_REQUEST['reference'] ?? '',
            'products' => $_REQUEST['products'] ?? '',
            'quantities' => $_REQUEST['quantities'] ?? '',
            'amount'    => $_REQUEST['amount'] ?? '',
            'guest_id'  => $_REQUEST['guest_id'] ?? uniqid('guest_' . time()),
        ]);

        if(count($empties) > 0) {
            return [
                'status' => 'info',
                'title' => 'No Products In Cart',
                'message' => 'Please add some products to carts and try again!',
                'data'=> $empties,
            ];
        }

        try{
            $user_id = $this->isLoggedIn();
        }
        catch(\Exception $e) {
            $user_id = '';
        }

        if($user_id === false) $user_id = '';

        $values['user_id'] = $user_id;

        $products = array_filter(explode(',', $values['products']), fn ($id) => $id !== "");
        $quantities = array_filter(explode(',', $values['quantities']), fn ($id) => $id !== "");


        $order = new Order();
        $product = new Product();

        $order->new($values, function ($order_id) use($order, $product, $products, $quantities, $values) {

            $index = 0;
    
            foreach($products as $id) {
                $item = $product->product($id);
    
                $sum_of_product = $item['selling_price'] * (int)$quantities[$index];

                $order->order_to_product($order_id, $id, $values['reference'], $quantities[$index], $sum_of_product, $values['user_id'], $values['guest_id']);
    
                $index++;
            }
        });


        return [
            'status' => 'success',
            'title'  => 'Order Created Successfully',
            'message' => '',
            'guest' => $values['guest_id'],
        ];
    }

    public function updateOrder()
    {
        [$empty, $guest_id] = $this->clean($_REQUEST['guest'] ?? '');

        try{
            $user_id = $this->isLoggedIn();
        }
        catch(\Exception $e) {
            $user_id = '';
        }

        if($user_id === false) $user_id = '';

        if($empty && $user_id == '') return [
            'status' => 'error',
            'title' => 'System Busy',
            'message' => 'System is currently unable to update your order status, please try again later'
        ];

        [$empty, $status] = $this->clean($_REQUEST['status'] ?? '');
        [$empty1, $order_id] = $this->clean($_REQUEST['order_id'] ?? '');

        if($empty1 && $empty ) return [
            'status' => 'error',
            'title' => 'System Busy',
            'message' => 'System is currently unable to update your order status, please try again later'
        ];

        $order = new Order();

        $order->updateOrder($user_id, $guest_id, $order_id, 'confirmed');

        return [
            'status' => 'success',
            'title'  => 'Order Confirmed Successfully',
            'message' => ''
        ];

    }


}
