<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Middleware
{
    private $secretKey = '-mysecrete@key@200';

    public function error(...$data)
    {
        $this->json(...$data);
        exit;
    }

    public function json(...$data)
    {
        echo json_encode([...$data]);
    }

    public function clean_assoc($array, $check_subs = false)
    {
        $empties = [];

        foreach ($array as $key => $value) {
            if ($this->is_assoc($value)) {
                if (count($value) <= 0) {
                    array_push($empties, $key);
                }

                [$sub_empties, $value] = $this->clean_assoc($value);
                if ($check_subs !== false) {
                    array_push($empties, ...$sub_empties);
                }
            } elseif (is_array($value)) {
                if (count($value) <= 0) {
                    array_push($empties, $key);
                }

                for ($i = 0; $i < count($value); ++$i) {
                    [$sub_empty, $value[$i]] = $this->clean($value[$i]);
                    if ($sub_empty and $check_subs !== false) {
                        array_push($empties, "$i");
                    }
                }
            } else {
                [$error, $value] = $this->clean($value);
                if ($error) {
                    array_push($empties, $key);
                }
            }

            $array[$key] = $value;
        }

        return [$empties, $array];
    }

    public function clean($value)
    {
        $value = htmlspecialchars($value);

        return [empty(str_replace(' ', '', $value)), $value];
    }

    public function clean_array($array)
    {
        $empty = false;

        if (count($array) <= 0) {
            $empty = true;
        }

        for ($i = 0; $i < count($array); ++$i) {
            [$sub_empty, $array[$i]] = $this->clean($array[$i]);
        }

        return [$empty, $array];
    }

    public function is_assoc($array)
    {
        if (!is_array($array)) {
            return false;
        }

        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    public function uploadFile($target, $name, $type)
    {
        if (!isset($_FILES[$target])) {
            return 'File not uploaded';
        }

        $destination = $_SERVER['DOCUMENT_ROOT'].'/backend/uploads/';
        // Define allowed file types based on your requirements
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
        $allowedDocumentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/xml', 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'video/mp4', 'video/mp3', 'video/webm',
        ];
        $allowedVideoTypes = ['video/mp4', 'video/mp3', 'video/webm'];

        // Define maximum file sizes for each type (in bytes)
        $maxImageSize = 10 * 1024 * 1024; // 10 MB
        $maxDocumentSize = 20 * 1024 * 1024; // 20 MB
        $maxVideoSize = 100 * 1024 * 1024; // 100 MB

        // Check if the uploaded file is of the allowed type
        $allowedTypes = [];
        $maxSize = 0;
        switch ($type) {
            case 'image':
                $allowedTypes = $allowedImageTypes;
                $maxSize = $maxImageSize;
                $destination .= 'images/';
                break;
            case 'document':
                $allowedTypes = $allowedDocumentTypes;
                $maxSize = $maxDocumentSize;
                $destination .= 'documents/';
                break;
            case 'video':
                $allowedTypes = $allowedVideoTypes;
                $maxSize = $maxVideoSize;
                $destination .= 'videos/';
                break;
            default:
                return 'Invalid file type';
        }

        if (!in_array($_FILES[$target]['type'], $allowedTypes)) {
            return 'File type not allowed';
        }

        // Check for file size
        if ($_FILES[$target]['size'] > $maxSize) {
            return 'File size exceeded';
        }

        // Check for any errors during file upload
        if ($_FILES[$target]['error'] !== UPLOAD_ERR_OK) {
            return 'File upload error';
        }

        // Create a safe filename
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
        $extension = pathinfo($_FILES[$target]['name'], PATHINFO_EXTENSION);
        $finalFilename = $safeFilename.'.'.$extension;

        // Move the uploaded file to the desired destination
        $uploadPath = $destination.'/'.$finalFilename;
        if (move_uploaded_file($_FILES[$target]['tmp_name'], $uploadPath)) {
            return [$finalFilename, 'File uploaded successfully'];
        } else {
            return 'File upload failed';
        }
    }

    public function validateToken()
    {

        $jwt = $_SERVER['HTTP_AUTHORIZATION'];

        $jwt = str_replace('Bearer ', '', $jwt);

        try {
            $headers = new \stdClass();
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'), $headers);

            if (time() > $decoded->exp) {
                return [
                    'status' => 'error',
                    'title' => 'Oops',
                    'message' => "It's been a while since your last login, please login again so we can be sure you own this account."
                ];
            }

            return $decoded;
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'title' => 'Oops',
                'message' => "Your account is under review due to some suspicious activities."
            ];
        }
    }

    public function isLoggedIn()
    {
        if(!isset($_SERVER['HTTP_AUTHORIZATION'])) return false;

        $jwt = $_SERVER['HTTP_AUTHORIZATION'];

        $jwt = str_replace('Bearer ', '', $jwt);

        try {
            $headers = new \stdClass();
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'), $headers);

            if (time() > $decoded->exp) {
                return false; 
            }

            return $this->decode($decoded->data->id);
        } catch (Exception $e) {
            return false;
        }
    }

    public function generateToken($data = [])
    {
        $tokenId = base64_encode(random_bytes(32));
        $issuedAt = time();
        $expires = $issuedAt + (3600 * 6); // Token expires in 6 hour
        $data = [
            'iat' => $issuedAt,
            'exp' => $expires,
            'data' => $data,
            // ... other claims ...
        ];
        $token = JWT::encode($data, $this->secretKey, 'HS256');

        return $token;
    }

    public function encode($userID)
    {
        // Convert the user ID to a string and then encode it using base64
        $encodedUserID = base64_encode("$userID".$this->secretKey);

        return $encodedUserID;
    }

    public function decode($encodedUserID)
    {
        // Decode the base64-encoded user ID and convert it back to an integer
        $decodedUserID = base64_decode($encodedUserID);

        return str_replace($this->secretKey, '', $decodedUserID);
    }

    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function verifyPassword($password, $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }

    public function startSession()
    {
        // Check if session has not already started
        if (session_status() === PHP_SESSION_NONE) {
            // Set session cookie parameters for added security (optional)
            session_set_cookie_params([
                'lifetime' => 3600, // Session lifetime in seconds
                'path' => '/',
                'secure' => true, // Use secure (HTTPS) connection for cookies
                'httponly' => true, // Prevent JavaScript access to cookies
                'samesite' => 'Lax', // Lax is a recommended value for most use cases
            ]);

            // Start the session
            session_start();

            // Regenerate the session ID to prevent session fixation attacks
            session_regenerate_id(true);
        }
    }
}
