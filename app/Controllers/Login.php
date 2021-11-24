<?php

namespace App\Controllers;

/*

    ctr+f kemudian cari "Lihat Hasil" untuk ['google', 'github', 'facebook'] disana kalian bisa memanage data yang telah diberikan, sesuai kebutuhan

*/

class Login extends BaseController
{
    private $keys, $classGoogle;
    
    public function __construct()
    {
        $this->keys = [
            'google' => [
                'clientID' => '47242949804-8kr9d4412b9kppu2sib2ji5rpfpkmi3j.apps.googleusercontent.com',
                'clientSECRET' => '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'
            ],
            'github' => [
                'clientID' => '8b462c4df1b4a2c018f1',
                'clientSECRET' => 'b2ebaa8d1aa23101102fccedfe6a7e3497aa23e6'
            ]
        ];

        $this->classGoogle = new \Google_Client();
        $this->classGoogle->setClientId($this->keys['google']['clientID']);
        $this->classGoogle->setClientSecret($this->keys['google']['clientSECRET']);
        $this->classGoogle->setRedirectUri('http://localhost:8080/login/google');
        $this->classGoogle->addScope('email');
        $this->classGoogle->addScope('profile');
    }

    public function index()
    {
        $data = [
            'urlGoogle' => $this->classGoogle->createAuthUrl(),
            'urlGithub' => 'https://github.com/login/oauth/authorize?client_id='.$this->keys['github']['clientID'].'&redirect_uri='.base_url('/login/github')
        ];

        return view('login/index', $data);
    }

    public function google() {
        if( isset($_GET['code']) ) {
            $token = $this->classGoogle->fetchAccessTokenWithAuthCode($_GET['code']);

            if( !isset($token['error']) ) {
                $this->classGoogle->setAccessToken($token['access_token']);
                $google_service = new \Google_Service_Oauth2($this->classGoogle);
                $user = $google_service->userinfo->get();

                $user_data = array(
                    'first_name' => $user['given_name'],
                    'last_name'  => $user['family_name'],
                    'email_address' => $user['email'],
                    'profile_picture'=> $user['picture']
                );

                dd($user_data); // LIHAT HASIL GOOGLE
            } else {
                echo $token['error_description'];
            }
        }
    }

    public function github() {
        $url = 'https://github.com/login/oauth/access_token';

        if( isset($_GET['code']) ) {
            // DAPATKAN TOKENNNYA DARI $_GET['code']

            $useParams = [
                'client_id' => '8b462c4df1b4a2c018f1',
                'client_secret' => 'b2ebaa8d1aa23101102fccedfe6a7e3497aa23e6',
                'code' => $_GET['code']
            ];
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $useParams);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
            
            $response = curl_exec($ch);
    
            curl_close($ch);
    
            $data = json_decode($response);

            // UBAH TOKENNYA MENJADI DATA USER

            if( isset($data->access_token) ) {
                $access_token = $data->access_token;
                
                $authHeader = "Authorization: token " . $access_token;
                $userAgentHeader = 'User-Agent: Demo';
    
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/user');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', $authHeader, $userAgentHeader));
                
                $user = curl_exec($ch);
    
                curl_close($ch);
    
                dd($user); // LIHAT HASIL GITHUB
            } else {
                echo $data->error_description ;
            }
        }
    }

    public function facebook() {
        // Karena membutuhkan akun facebook dan kebetulan saya tidak bermain facebook, agak rumit ketika buat akun
        // aku nyaranin ikut saja tutor ini https://jurnalmms.web.id/codeigniter/membuat-register-login-with-facebook-di-codeigniter-3/

        // Tinggal kalian sesuai kan dengan codeigniter-4
    }
}
