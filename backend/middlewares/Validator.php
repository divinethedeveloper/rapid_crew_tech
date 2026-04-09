<?php

namespace Middleware;

class Validator extends Middleware
{
    public function validateEmail($email) {
        // Regular expression for email validation
        $pattern = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
    
        if (preg_match($pattern, $email)) {
            return ['', $email]; // Email is valid
        } else {
            return ['Invalid email', $email]; // Email is not valid
        }
    }

    function validatePassword($password) {
        // Check if the password is at least 8 characters long
        if (strlen($password) < 8) {
            return ['Password must be at least 8 characters long', $password];
        }
    
        // Check if the password contains at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return ['Password must contain at least one lowercase letter', $password];
        }
    
        // Check if the password contains at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return ['Password must contain at least one uppercase letter', $password];
        }
    
        // Check if the password contains at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            return ['Password must contain at least one digit', $password];
        }
    
        // Check if the password contains at least one special character
        if (!preg_match('/[!@#$%^&*()_+{}\[\]:;<>,.?~\\-]/', $password)) {
            return ['Password must contain at least one special character', $password];
        }
    
        // If all checks pass, the password is valid
        return ['', $this->hashPassword($password)];
    }
    

    public function validate($values) {
        $validator_map = [
            'email' => [$this, 'validateEmail'],
            'password' => [$this, 'validatePassword'],
        ];

        $result = [];

        
        foreach($values as $key => $value) {
            if(isset($validator_map[$key]))
            [$result[$key], $values[$key]] = $validator_map[$key]($value);
        }

        $result = array_filter($result, function($value) {
            return !empty($value);
        });

        return [$result, $values];
        
    }


}